<?php

namespace App\Exception;

use Exception;

class KeyNotFoundException extends Exception {
    public function __construct(string $key, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct("Cannot find a value by the name of '$key'", $code, $previous);
    }
}