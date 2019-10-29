<?php

namespace App;

use \Exception;

class HttpException extends Exception
{
    public $statusCode;

    public function __construct($statusCode)
    {
        $this->statusCode = $statusCode;
        $message = "{$statusCode}";

        switch ($statusCode) {
            case 401:
                $message = "401 Unauthorized";
                break;
            case 403:
                $message = "403 Forbidden";
                break;
            case 404:
                $message = "404 Not Found";
                break;
            case 405:
                $message = "405 Method Not Allowed";
                break;
        }

        parent::__construct($message);
    }
}
