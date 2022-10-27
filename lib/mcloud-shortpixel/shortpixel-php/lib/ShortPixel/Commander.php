<?php
/**
 * User: simon
 * Date: 04.04.2016
 * Time: 14:01
 */

namespace MediaCloud\Vendor\ShortPixel;

/**
 * Class Commander - handles optimization commands such as lossless/lossy, resize, wait, notify_me etc.
 * @package ShortPixel
 */
class Commander {
    private $data, $source, $commands, $logger;

    public function __construct($data, Source $source) {
        $this->source = $source;
        $this->data = $data;
        $this->logger = SPLog::Get(SPLog::PRODUCER_CTRL);
        //$options = ShortPixel::options();
        $this->commands = array();//('lossy' => 0 + $options["lossy"]);
        if(isset($data['refresh']) && $data['refresh']) {
            $this->refresh();
        }
    }

    /**
     * @param int $type 1 - lossy (default), 2 - glossy, 0 - lossless
     * @return $this
     */
    public function optimize($type = 1) {
        $this->commands = array_merge($this->commands, array("lossy" => $type));
        return $this;
    }

    /**
     * resize the image - performs an outer resize (meaning the image will preserve aspect ratio and have the smallest sizes that allow a rectangle with given width and height to fit inside the resized image)
     * @param $width
     * @param $height
     * @param bool $inner - default, false, true to resize to maximum width or height (both smaller or equal)
     * @return $this
     */
    public function resize($width, $height, $inner = false) {
        $this->commands = array_merge($this->commands, array("resize" => ($inner ? ShortPixel::RESIZE_INNER : ShortPixel::RESIZE_OUTER), "resize_width" => $width, "resize_height" => $height));
        return $this;
    }

    /**
     * @param bool|true $keep
     * @return $this
     */
    public function keepExif($keep = true) {
        $this->commands = array_merge($this->commands, array("keep_exif" => $keep ? 1 : 0));
        return $this;
    }

    

    /**
     * @param bool|true $generate - default true, meaning generates WebP.
     * @return $this
     */
    public function generateWebP($generate = true) {
        $convertto = isset($this->commands['convertto']) ? explode('|', $this->commands['convertto']) : array();
        $convertto[] = '+webp';
        $this->commands = array_merge($this->commands, array("convertto" => implode('|', array_unique($convertto))));
        return $this;
    }

    /**
     * @param bool|true $generate - default true, meaning generates WebP.
     * @return $this
     */
    public function generateAVIF($generate = true) {
        $convertto = isset($this->commands['convertto']) ? explode('|', $this->commands['convertto']) : array();
        $convertto[] = '+avif';
        $this->commands = array_merge($this->commands, array("convertto" => implode('|', array_unique($convertto))));
        return $this;
    }

    /**
     * @param bool|true $refresh - if true, tells the server to discard the already optimized image and redo the optimization with the new settings.
     * @return $this
     */
    public function refresh($refresh = true) {
        $this->commands = array_merge($this->commands, array("refresh" => $refresh ? 1 : 0));
        return $this;
    }

    /**
     * will wait for the optimization to finish but not more than $seconds. The wait on the ShortPixel Server side can be a maximum of 30 seconds, for longer waits subsequent server requests will be sent.
     * @param int $seconds
     * @return $this
     */
    public function wait($seconds = 30) {
        $seconds = max(0, intval($seconds));
        $this->commands = array_merge($this->commands, array("wait" => min($seconds, 30), "total_wait" => $seconds));
        return $this;
    }

    /**
     * Not yet implemented
     * @param $callbackURL the full url of the notify.php script that handles the notification postback
     * @return mixed
     */
    public function notifyMe($callbackURL) {
        throw new ClientException("NotifyMe not yet implemented");
        $this->commands = array_merge($this->commands, array("notify_me" => $callbackURL));
        return $this->execute();
    }

    /**
     * call forwarder to Result - when a command is not understood by the Commander it could be a Result method like toFiles or toBuffer
     * @param $method
     * @param $args
     * @return mixed
     * @throws ClientException
     */
    public function __call($method, $args) {
        if (method_exists("MediaCloud\Vendor\ShortPixel\Result", $method)) {
            //execute the commands and forward to Result
            if(isset($this->data["files"]) && !count($this->data["files"]) ||
               isset($this->data["urllist"]) && !count($this->data["urllist"]) ||
               isset($this->data["buffers"]) && !count($this->data["buffers"])) {
                //empty data - no files, no need to send anything, just return an empty result
                return (object) array(
                    'status' => array('code' => 2, 'message' => 'success'),
                    'succeeded' => array(),
                    'pending' => array(),
                    'failed' => array(),
                    'same' => array());
            }
            for($i = 0; $i < 6; $i++) {
                $return = $this->execute(true);
                $this->logger->log(SPLog::PRODUCER_CTRL, "EXECUTE RETURNED: ", $return);
                if(!isset($return->body->Status->Code) || !in_array($return->body->Status->Code, array(-305, -404, -500))) {
                    break;
                }
                // error -404: The maximum number of URLs in the optimization queue reached, wait a bit and retry.
                // error -500: maintenance mode
                sleep((10 + 3 * $i) * ($return->body->Status->Code == -500 ? 6 : 1)); //sleep six times longer if maintenance mode. This gives about 15 minutes in total, then it will throw exception.
            }

            if(isset($return->body->Status->Code) && $return->body->Status->Code < 0) {
                ShortPixel::log("ERROR THROWN: " . $return->body->Status->Message . (isset($return->body->raw) ? "(Server sent: " . substr($return->body->raw, 0, 200) . "...)" : "") . " CODE: " . $return->body->Status->Code);
                throw new AccountException($return->body->Status->Message . (isset($return->body->raw) ? "(Server sent: " . substr($return->body->raw, 0, 200) . "...)" : ""), $return->body->Status->Code);
            }
            return call_user_func_array(array(new Result($this, $return), $method), $args);
        }
        else {
            throw new ClientException('Unknown function '.__CLASS__.':'.$method, E_USER_ERROR);
        }
    }

    /**
     * @internal
     * @param bool|false $wait
     * @return mixed
     * @throws AccountException
     */
    public function execute($wait = false){
        if($wait && !isset($this->commands['wait'])) {
            $this->commands = array_merge($this->commands, array("wait" => ShortPixel::opt("wait"), "total_wait" => ShortPixel::opt("total_wait")));
        }
        ShortPixel::log("EXECUTE OPTIONS: " . json_encode(ShortPixel::options()) . " COMMANDS: " . json_encode($this->commands) . " DATA: " . json_encode($this->data));
        return ShortPixel::getClient()->request("post", array_merge(ShortPixel::options(), $this->commands, $this->data));
    }

    /**
     * @internal
     * @param $pending
     * @return bool|mixed
     * @throws ClientException
     */
    public function relaunch($ctx) {
        ShortPixel::log("RELAUNCH CTX: " . json_encode($ctx) . " COMMANDS: " . json_encode($this->commands) . " DATA: " . json_encode($this->data));
        if(!count($ctx->body) &&
            (isset($this->data["files"]) && !count($this->data["files"]) ||
             isset($this->data["urllist"]) && !count($this->data["urllist"]))) return false; //nothing to do

        //decrease the total wait and exit while if time expired
        $this->commands["total_wait"] = max(0, $this->commands["total_wait"] - min($this->commands["wait"], 30));
        if($this->commands['total_wait'] == 0) return false;

        $pendingURLs = array();
        //currently we relaunch only if we have the URLs that for posted files should be returned in the first pass.
        $type = isset($ctx->body[0]->OriginalURL) ? 'URL' : 'FILE';
        foreach($ctx->body as $pend) {
            if($type == 'URL') {
                if($pend->OriginalURL && !in_array($pend->OriginalURL, $pendingURLs)) {
                    $pendingURLs[$pend->OriginalURL] = $pend->OriginalFile;
                }
            } else {
                //for now
                throw new ClientException("Not implemented (Commander->execute())");
            }
        }
        $this->commands["refresh"] = 0;
        if($type == 'URL' && count($pendingURLs)) {
            $this->data["pendingURLs"] = $pendingURLs;
            //$this->data["fileMappings"] = $ctx->fileMappings;
        }
        return $this->execute();

    }

    public function getCommands() {
        return $this->commands;
    }

    public function getData() {
        return $this->data;
    }

    /*    public function setCommand($key, $value) {
            return $this->commands[$key] = $value;
        }
    */

    public function isDone($item) {
        //remove from local files list
        if(isset($this->data["files"]) && is_array($this->data["files"])) {
            if (isset($item->OriginalFile)) {
                $this->data["files"] = array_diff($this->data["files"], array($item->OriginalFile));
            }
            elseif (isset($item->SavedFile)) {
                $this->data["files"] = array_diff($this->data["files"], array($item->SavedFile));
            }
            elseif(isset($item->OriginalURL) && isset($this->data["pendingURLs"][$item->OriginalURL])) {
                $this->data["files"] = array_diff($this->data["files"], array($this->data["pendingURLs"][$item->OriginalURL]));
            }
        }
        //remove from pending URLs
        if(isset($item->OriginalURL)) {
            if(isset($this->data["pendingURLs"][$item->OriginalURL])) {
                unset($this->data["pendingURLs"][$item->OriginalURL]);
            }
            elseif(isset($this->data["urllist"]) && in_array($item->OriginalURL, $this->data["urllist"])) {
                $this->data["urllist"] = array_values(array_diff($this->data["urllist"], array($item->OriginalURL)));
            }
        }
    }
}
