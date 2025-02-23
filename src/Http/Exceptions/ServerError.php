<?php

namespace Brash\Framework\Http\Exceptions;

use RuntimeException;
use Throwable;

class ServerError extends RuntimeException
{
    public $message;

    public function __construct(
        protected Throwable $error,
        protected $code = 500,
        protected ?Throwable $previous = null
    ) {
        $this->message = $error->getMessage();
    }
}
