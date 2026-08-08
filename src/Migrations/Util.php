<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\Migrations\AbstractMigration;

trait Util
{
    protected function nullToStringValue($tableNames, $columnNames, string $value = '')
    {
        /** @var AbstractMigration $this */

        if (!is_array($tableNames)) {
            $tableNames = [$tableNames];
        }

        if (!is_array($columnNames)) {
            $columnNames = [$columnNames];
        }

        foreach ($tableNames as $tableName) {
            foreach ($columnNames as $columnName) {
                $this->addSql(
                    "UPDATE $tableName 
                    SET $columnName = '{$value}'
                    WHERE ISNULL($columnName)"
                );
            }
        }
    }

    protected function stringValueToNull($tableNames, $columnNames, string $value = '')
    {
        /** @var AbstractMigration $this */

        if (!is_array($tableNames)) {
            $tableNames = [$tableNames];
        }

        if (!is_array($columnNames)) {
            $columnNames = [$columnNames];
        }

        foreach ($tableNames as $tableName) {
            foreach ($columnNames as $columnName) {
                $this->addSql(
                    "UPDATE $tableName 
                    SET $columnName = NULL
                    WHERE $columnName = '{$value}'"
                );
            }
        }
    }
}
