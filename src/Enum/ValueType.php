<?php

declare(strict_types=1);

namespace App\Enum;

enum ValueType: string
{
    case String = 'string';
    case Int = 'int';
    case Float = 'float';
}
