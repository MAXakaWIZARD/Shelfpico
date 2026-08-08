<?php

declare(strict_types=1);

namespace App\Enum;

enum LabelClass: string
{
    case Primary = 'primary';
    case Secondary = 'secondary';
    case Success = 'success';
    case Info = 'info';
    case Warning = 'warning';
    case Danger = 'danger';
    case Light = 'light';
    case Dark = 'dark';
}
