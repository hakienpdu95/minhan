<?php

namespace Modules\Assessment\Exceptions;

use RuntimeException;

class CccdOcrException extends RuntimeException
{
    public function __construct(string $message, private readonly string $field = 'front_image')
    {
        parent::__construct($message);
    }

    public function getField(): string
    {
        return $this->field;
    }
}
