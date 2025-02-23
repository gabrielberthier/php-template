<?php

namespace Brash\Framework\Http\Middlewares\Jwt\Exceptions;

final class TokenNotFoundException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Token not found.');
    }
}
