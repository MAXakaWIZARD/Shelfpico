<?php

declare(strict_types=1);

namespace App;

use Exception;
use Symfony\Component\Process\Process;

class Utils
{
    public const B = 1;
    public const KB = 1024;
    public const MB = self::KB ** 2;
    public const GB = self::KB ** 3;
    public const TB = self::KB ** 4;
    public const PB = self::KB ** 5;

    private const UNITS = [
        'PB' => self::PB,
        'P' => self::PB,
        'TB' => self::TB,
        'T' => self::TB,
        'GB' => self::GB,
        'G' => self::GB,
        'MB' => self::MB,
        'M' => self::MB,
        'KB' => self::KB,
        'K' => self::KB,
        'B' => self::B,
    ];

    private const DIVIDERS = [
         self::PB => 'PB',
         self::TB => 'TB',
         self::GB => 'GB',
         self::MB => 'MB',
         self::KB => 'KB',
         self::B => 'B',
    ];

    public static function fileSizeStrToBytes(string $str): int
    {
        $pattern = '/^([0-9\.]+)[\s]*([A-Z]{0,2})$/i';
        $matches = [];
        if (preg_match($pattern, $str, $matches)) {
            $unit = $matches[2] ? $matches[2] : 'B';
            if (isset(self::UNITS[$unit])) {
                return (int) round($matches[1] * self::UNITS[$unit]);
            }
        }

        throw new Exception('Incorrect file size string format');
    }

    public static function fileSizeStrToMBytes(string $str): int
    {
        return intval(self::fileSizeStrToBytes($str) / self::MB);
    }

    public static function formatBytes(int $bytes, int $floatDigits = 2): string
    {
        foreach (self::UNITS as $unit => $divisor) {
            $result = $bytes / $divisor;
            if ($result >= 1) {
                if ($divisor == 1) {
                    $floatDigits = 0;
                }

                return sprintf("%.{$floatDigits}f %s", $result, $unit);
            }
        }

        return '0 B';
    }

    public static function formatBytesEx(
        int $bytes,
        int $divider,
        int $floatDigits = 1
    ): string {
        $label = self::DIVIDERS[$divider];

        return sprintf("%.{$floatDigits}f %s", $bytes / $divider, $label);
    }

    public static function formatGbytes(
        int $bytes,
        int $floatDigits = 1
    ): string {
        return self::formatBytesEx($bytes, static::GB, $floatDigits);
    }

    public static function formatMbytes(
        int $bytes,
        int $floatDigits = 1
    ): string {
        return self::formatBytesEx($bytes, static::MB, $floatDigits);
    }

    /**
     * multidimensional arrays sorting function, sort fields in format "subarray.field" supported
     */
    public static function multiSort(
        iterable &$data,
        string $field,
        string $direction = 'ASC',
        bool $preserveKeys = false
    ): void {
        $direction = strtoupper($direction);
        $array = self::prepareDataForSorting($data, $field, $direction);

        $funcName = $preserveKeys ? 'uasort' : 'usort';
        $funcName($array, function (array $first, array $second) {
            return $first['sort_dir'] == 'DESC'
                   ? $second['sort_value'] <=> $first['sort_value']
                   : $first['sort_value'] <=> $second['sort_value'];
        });

        $data = [];
        foreach ($array as $key => $row) {
            $data[$key] = $row["row"];
        }
    }

    protected static function prepareDataForSorting(iterable $data, string $field, string $direction): iterable
    {
        if (mb_strpos($field, '.') !== false) {
            list($sortSection, $sortField) = explode('.', $field);
        } else {
            $sortField = $field;
        }

        $isMethod = strpos($sortField, '()') !== false;
        $sortField = str_replace('()', '', $sortField);

        $array = [];
        foreach ($data as $key => $row) {
            $item = isset($sortSection) ? $row[$sortSection] : $row;

            if ($isMethod) {
                $sortFieldValue = $item->$sortField();
            } elseif (is_object($item)) {
                $sortFieldValue = $item->$sortField;
            } else {
                $sortFieldValue = $item[$sortField];
            }

            $array[$key] = [
                "sort_value" => $sortFieldValue,
                "sort_dir" => $direction,
                "row" => $row,
            ];
        }

        return $array;
    }

    public static function getHtmlFriendlyProcessOutput(Process $process): string
    {
        $output = $process->getOutput();
        if ($process->getErrorOutput()) {
            $output .= '<br />' . $process->getErrorOutput();
        }

        $output = nl2br($output, true);
        $output = str_replace(PHP_EOL, '', $output);

        if (strpos($output, '<br />') === 0) {
            $output = substr($output, 6);
        }

        $output = str_replace('[CAUTION]', '<strong style="color: red;">[CAUTION]</strong>', $output);
        $output = str_replace('[NOTE]', '<strong>[NOTE]</strong>', $output);

        return $output;
    }

    public static function isOneWord(string $term)
    {
        $term = trim($term);

        return count(explode(' ', $term)) === 1;
    }
}
