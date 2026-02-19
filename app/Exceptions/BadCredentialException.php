<?php

namespace App\Exceptions;

use Exception;

class BadCredentialException extends Exception
{
    public function __construct()
    {
        parent::__construct('Email hoặc mật khẩu không hợp lệ.');
    }
}
