<?php

declare(strict_types=1);

namespace App\Utils;

class EnvChecker
{
    public function getEnvVars(): array
    {
        return [
            'APP_ENV' => $_ENV['APP_ENV'] ?? 'not set',
            'APP_DEBUG' => $_ENV['APP_DEBUG'] ?? 'not set',
            'DATABASE_NAME' => $_ENV['DATABASE_NAME'] ?? 'not set',
            'BIN_MYSQLDUMP' => $_ENV['BIN_MYSQLDUMP'] ?? 'not set',
            'BIN_MYSQL' => $_ENV['BIN_MYSQL'] ?? 'not set',
            'BIN_GZIP' => $_ENV['BIN_GZIP'] ?? 'not set',
            'BIN_PHP' => $_ENV['BIN_PHP'] ?? 'not set',
            'PATH_BACKUP' => $_ENV['PATH_BACKUP'] ?? 'not set',
        ];
    }
}
