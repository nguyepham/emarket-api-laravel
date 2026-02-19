<?php

namespace App\Enums;

enum ExceptionName: string
{
    case BadCredential = 'BadCredentialException';
    case Unauthenticated = 'AuthenticationException';
    case ModelNotFound = 'ModelNotFoundException';
}
