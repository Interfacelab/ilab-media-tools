<?php

namespace MediaCloud\Vendor\ShortPixel\persist;

class PNGReader
{
    private $_chunks;
    private $_fp;

    function __construct($file) {
        if (!file_exists($file)) {
            throw new Exception('File does not exist');
        }

        $this->_chunks = array ();

        // Open the file
        $this->_fp = fopen($file, 'r');

        if (!$this->_fp)
            throw new Exception('Unable to open file');

        // Read the magic bytes and verify
        $header = fread($this->_fp, 8);

        if ($header != "\x89PNG\x0d\x0a\x1a\x0a")
            throw new Exception('Is not a valid PNG image');

        // Loop through the chunks. Byte 0-3 is length, Byte 4-7 is type
        $chunkHeader = fread($this->_fp, 8);

        while ($chunkHeader) {
            // Extract length and type from binary data
            $chunk = @unpack('Nsize/a4type', $chunkHeader);

            // Store position into internal array
            if (!isset($this->_chunks[$chunk['type']]) || $this->_chunks[$chunk['type']] === null)
                $this->_chunks[$chunk['type']] = array ();
            $this->_chunks[$chunk['type']][] = array (
                'offset' => ftell($this->_fp),
                'size' => $chunk['size']
            );

            // Skip to next chunk (over body and CRC)
            fseek($this->_fp, $chunk['size'] + 4, SEEK_CUR);

            // Read next chunk header
            $chunkHeader = fread($this->_fp, 8);
        }
    }

    function __destruct() { fclose($this->_fp); }

    // Returns all chunks of said type
    public function get_chunks($type) {
        if (!isset($this->_chunks[$type]) || $this->_chunks[$type] === null)
            return null;

        $chunks = array ();

        foreach ($this->_chunks[$type] as $chunk) {
            if ($chunk['size'] > 0) {
                fseek($this->_fp, $chunk['offset'], SEEK_SET);
                $chunks[] = fread($this->_fp, $chunk['size']);
            } else {
                $chunks[] = '';
            }
        }

        return $chunks;
    }

    public function get_sections() {
        $rawTextData = $this->get_chunks('tEXt');
        if($rawTextData === null) {
            //$rawTextData = gzinflate($this->get_chunks('zTXt'));
        }

        $metadata = array();

        if(!is_array($rawTextData)) return $metadata;

        foreach($rawTextData as $data) {
            $sections = explode("\0", $data);

            if($sections > 1) {
                $key = array_shift($sections);
                $metadata[$key] = implode("\0", $sections);
            } else {
                $metadata[] = $data;
            }
        }
        return $metadata;
    }
}