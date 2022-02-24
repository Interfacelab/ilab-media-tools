<?php
/**
 * User: simon
 * Date: 18.02.2020
 */

namespace MediaCloud\Vendor\ShortPixel\persist;
use MediaCloud\Vendor\ShortPixel\ClientException;
use MediaCloud\Vendor\ShortPixel\ShortPixel;
use MediaCloud\Vendor\ShortPixel\SPLog;
use MediaCloud\Vendor\ShortPixel\persist\TextPersister;

class TextMetaFile {
    const LINE_LENGTH = 465; //including the \r\n at the end
    const LINE_LENGTH_V2 = 650; //including the \r\n at the end - NOT YET USED

    private STATIC $ALLOWED_STATUSES = array('pending', 'success', 'skip', 'deleted');
    private STATIC $ALLOWED_TYPES = array('I', 'D');

    private $fp;
    private $type;
    private $path;
    private $lineLength = false;

    private $logger;
    
    private static $REGISTRY = array();

    /**
     * @param $path
     * @param string $type
     * @return TextMetaFile
     * @throws ClientException
     */
    public static function Get($path, $type = 'update') {
        if(!isset(self::$REGISTRY[$type . ':' . $path])) {
            self::$REGISTRY[$type . ':' . $path] = new TextMetaFile($path, $type);
        }
        return self::$REGISTRY[$type . ':' . $path];

    }

    public static function  IsOpen($path, $type = 'update') {
        return isset(self::$REGISTRY[$type . ':' . $path]);
    }

    private function __construct($path, $type = 'update') {
        $this->logger = SPLog::Get(SPLog::PRODUCER_PERSISTER);

        $metaFile = $path . '/' . ShortPixel::opt("persist_name");
        if(!is_dir($path) && !@mkdir($path, 0777, true)) { //create the folder
            throw new ClientException("The metadata destination path cannot be found. Please check rights", -17);
        }
        $existing = file_exists($metaFile);
        $fp = @fopen($metaFile, $type == 'update' ? 'c+' : 'r');
        if(!$fp) {
            if(is_dir($metaFile)) { //saw this for a client
                throw new ClientException("Could not open persistence file $metaFile. There's already a directory with this name.", -16);
            } else {
                throw new ClientException("Could not open persistence file $metaFile. Please check rights.", -16);
            }
        }
        $this->fp = $fp;
        $this->type = $type;
        $this->path = $path;
        if($existing) {
            while(true) {
                $line = fgets($this->fp);
                if($line === false) break;
                $length = strlen(rtrim($line, "\r\n"));
                if($length == (self::LINE_LENGTH_V2 - 2)) $this->lineLength = self::LINE_LENGTH_V2;
                elseif($length == (self::LINE_LENGTH - 2)) $this->lineLength = self::LINE_LENGTH;
                if($this->lineLength) break;
            }
            if(!$this->lineLength) {
                $this->lineLength = self::LINE_LENGTH_V2;
            }
            fseek($this->fp, 0);
        } else {
            $this->lineLength = self::LINE_LENGTH_V2;
        }
    }

    private static function unSanitizeFileName($fileName) {
        return $fileName;
    }

    public function close() {
        fclose($this->fp);
        unset(self::$REGISTRY[$this->type . ':' . $this->path]);
    }

    public static function find($path) {
        $metaFile = self::Get(dirname($path), 'read');
        $fp = $metaFile->fp;
        fseek($fp, 0);

        $name = \MediaCloud\Vendor\ShortPixel\MB_basename($path);
        for ($i = 0; ($line = fgets($fp)) !== FALSE; $i++) {
            $data = $metaFile->parse($line);
            if(!$data || !property_exists($data, 'file')) {
                SPLog::Get(SPLog::PRODUCER_PERSISTER)->log(SPLog::PRODUCER_PERSISTER, 'META LINE CORRUPT: ' . $line);
            }
            if($data->file === $name) {
                $data->filePos = $i;
                return $data;
            }
        }
        return false;
    }

    /**
     * @return array
     */
    public function readAll() {
        $fp = $this->fp;
        $dataArr = array(); $err = false;
        for ($i = 0; ($line = fgets($fp)) !== FALSE; $i++) {
            $data = $this->parse($line);
            if($data) {
                $data->filePos = $i;
                if(isset($dataArr[$data->file])) {
                    $err = true; //found situations where a line was duplicated, will rewrite but take only the first
                } else {
                    $dataArr[$data->file] = $data;
                }
            } else {
                $err = true;
            }
        }
        if($err) { //at least one error found in the .shortpixel file, rewrite it
            fseek($fp, 0);
            ftruncate($fp, 0);
            foreach($dataArr as $meta) {
                fwrite($fp, $this->assemble($meta));
                fwrite($fp, $line . "\r\n");
            }
        }
        return $dataArr;
    }
    
    /**
     * @param $meta
     * @param bool|false $returnPointer - set this to true if need to have the file pointer back afterwards, such as when updating while reading the file line by line
     */
    public function update($meta, $returnPointer = false) {
        $fp = $this->fp;
        if($returnPointer) {
            $crt = ftell($fp);
        }
        fseek($fp, $this->lineLength * $meta->filePos); // +2 for the \r\n
        fwrite($fp, $this->assemble($meta));
        fflush($fp);
        if($returnPointer) {
            fseek($fp, $crt);
        }
    }

    /**
     * @param $meta
     * @param bool|false $returnPointer - set this to true if need to have the file pointer back afterwards, such as when updating while reading the file line by line
     */
    public function append($meta, $returnPointer = false) {
        $fp = $this->fp;
        if($returnPointer) {
            $crt = ftell($fp);
        }
        $fstat = fstat($fp);
        fseek($fp, 0, SEEK_END);
        $line = $this->assemble($meta);
        //$ob = $this->parse($line);
        fwrite($fp, $line . "\r\n");
        fflush($fp);
        if($returnPointer) {
            fseek($fp, $crt);
        }
        return $fstat['size'] / $this->lineLength;
    }

    public static function newEntry($file, $options) {
        //$this->logger->log(SPLog::PRODUCER_PERSISTER, "newMeta: file $file exists? " . (file_exists($file) ? "Yes" : "No"));
        return (object) array(
            "type" => is_dir($file) ? 'D' : 'I',
            "status" => 'pending',
            "retries" => 0,
            "compressionType" => $options['lossy'] == 1 ? 'lossy' : ($options['lossy'] == 2 ? 'glossy' : 'lossless'),
            "keepExif" => $options['keep_exif'],
            "cmyk2rgb" => $options['cmyk2rgb'],
            "resize" => $options['resize_width'] ? 1 : 0,
            "resizeWidth" => 0 + $options['resize_width'],
            "resizeHeight" => 0 + $options['resize_height'],
            "convertto" => $options['convertto'],
            "percent" => null,
            "optimizedSize" => null,
            "changeDate" => time(),
            "file" => TextPersister::sanitize(\MediaCloud\Vendor\ShortPixel\MB_basename($file)),
            "message" => '',
            //file does not exist if source is a WebFolder and the optimized images are saved to a different target
            "originalSize" => is_dir($file) || !file_exists($file) ? 0 : filesize($file));
    }

    protected function parse($line) {
        $length = strlen(rtrim($line, "\r\n"));
        if($length != ($this->lineLength - 2)) return false;
        $v2offset = $this->lineLength - self::LINE_LENGTH;
        $percent = trim(substr($line, 52, 6));
        $optimizedSize = trim(substr($line, 58, 9));
        $originalSize = trim(substr($line, 454 + $v2offset, 9));

        $convertto = trim(substr($line, 42, 10));
        if(is_numeric($convertto)) {
            //convert to string representation
            $conv = array();
            if($convertto | TextPersister::FLAG_WEBP) $conv[] = '+webp';
            if($convertto | TextPersister::FLAG_AVIF) $conv[] = '+avif';
            $convertto = implode('|', $conv);
            //$this->logger->log(SPLog::PRODUCER_PERSISTER, "Convertto $convertto");
        }

        $ret = (object) array(
            "type" => trim(substr($line, 0, 2)),
            "status" => trim(substr($line, 2, 11)),
            "retries" => trim(substr($line, 13, 2)),
            "compressionType" => trim(substr($line, 15, 9)),
            "keepExif" => trim(substr($line, 24, 2)),
            "cmyk2rgb" => trim(substr($line, 26, 2)),
            "resize" => trim(substr($line, 28, 2)),
            "resizeWidth" => trim(substr($line, 30, 6)),
            "resizeHeight" => trim(substr($line, 36, 6)),
            "convertto" => $convertto,
            "percent" => is_numeric($percent) ? floatval($percent) : 0.0,
            "optimizedSize" => is_numeric($optimizedSize) ? intval($optimizedSize) : 0,
            "changeDate" => strtotime(trim(substr($line, 67, 20))),
            "file" => rtrim(self::unSanitizeFileName(substr($line, 87, 256))), //rtrim because there could be file names starting with a blank!! (had that)
            "message" => trim(substr($line, 343, 111 + $v2offset)),
            "originalSize" => is_numeric($originalSize) ? intval($originalSize) : 0,
        );
        if(!in_array($ret->status, self::$ALLOWED_STATUSES) || !$ret->changeDate) {
            return false;
        }
        return $ret;
    }


    protected function assemble($data) {
        $v2offset = $this->lineLength - self::LINE_LENGTH;
        $convertto = 1;
        if(strpos($data->convertto, '+webp') !== false) $convertto |= TextPersister::FLAG_WEBP;
        if(strpos($data->convertto, '+avif') !== false) $convertto |= TextPersister::FLAG_AVIF;

        $line = sprintf("%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s",
            str_pad($data->type, 2),
            str_pad($data->status, 11),
            str_pad($data->retries % 100, 2), // for folders, retries can be > 100 so do a sanity check here - we're not actually interested in folder retries
            str_pad($data->compressionType, 9),
            str_pad($data->keepExif, 2),
            str_pad($data->cmyk2rgb, 2),
            str_pad($data->resize, 2),
            str_pad(substr($data->resizeWidth, 0 , 5), 6),
            str_pad(substr($data->resizeHeight, 0 , 5), 6),
            str_pad($convertto, 10),
            str_pad(substr(number_format($data->percent, 2, ".",""),0 , 5), 6),
            str_pad(substr(number_format($data->optimizedSize, 0, ".", ""),0 , 8), 9),
            str_pad(date("Y-m-d H:i:s", $data->changeDate), 20),
            str_pad(substr($data->file, 0, 255), 256),
            str_pad(substr($data->message, 0, 110 + $v2offset), 111 + $v2offset),
            str_pad(substr(number_format($data->originalSize, 0, ".", ""),0 , 8), 9)
        );

        if(strlen($line) + 2 !== $this->lineLength) {
            echo("LINE LENGTH CORRUPT. DEBUGINFO: " . base64_encode($line));die();
        }
        return $line;
    }
}