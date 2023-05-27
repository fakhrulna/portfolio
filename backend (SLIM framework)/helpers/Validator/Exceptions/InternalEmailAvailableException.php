<?php

namespace helpers\Validator\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class InternalEmailAvailableException extends ValidationException
{
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => '{{name}} already registered',
        ],
    ];
}