<?php

namespace MediaCloud\Vendor\Tinify;

class Exception extends \Exception {
    public $status;

    public static function create($message, $type, $status) {
        if ($status == 401 || $status == 429) {
            $klass = "MediaCloud\Vendor\Tinify\AccountException";
        } else if($status >= 400 && $status <= 499) {
            $klass = "MediaCloud\Vendor\Tinify\ClientException";
        } else if($status >= 500 && $status <= 599) {
            $klass = "MediaCloud\Vendor\Tinify\ServerException";
        } else {
            $klass = "MediaCloud\Vendor\Tinify\Exception";
        }

        if (empty($message)) $message = "No message was provided";
        return new $klass($message, $type, $status);
    }

    function __construct($message, $type = NULL, $status = NULL) {
        $this->status = $status;
        if ($status) {
            parent::__construct($message . " (HTTP " . $status . "/" . $type . ")");
        } else {
            parent::__construct($message);
        }
    }
}

class AccountException extends Exception {}
class ClientException extends Exception {}
class ServerException extends Exception {}
class ConnectionException extends Exception {}
