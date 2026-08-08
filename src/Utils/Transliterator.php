<?php

declare(strict_types=1);

namespace App\Utils;

class Transliterator
{
    /**
     * Fix wrong keyboard layout (Cyrillic instead of English)
     */
    public static function fixKeyboardLayout(string $term): string
    {
        $layoutMap = [
            'й' => 'q',
            'ц' => 'w',
            'у' => 'e',
            'к' => 'r',
            'е' => 't',
            'н' => 'y',
            'г' => 'u',
            'ш' => 'i',
            'щ' => 'o',
            'з' => 'p',
            'ф' => 'a',
            'ы' => 's',
            'в' => 'd',
            'а' => 'f',
            'п' => 'g',
            'р' => 'h',
            'о' => 'j',
            'л' => 'k',
            'д' => 'l',
            'я' => 'z',
            'ч' => 'x',
            'с' => 'c',
            'м' => 'v',
            'и' => 'b',
            'т' => 'n',
            'ь' => 'm',
        ];
        foreach ($layoutMap as $cyr => $eng) {
            $layoutMap[mb_strtoupper($cyr)] = mb_strtoupper($eng);
        }

        return str_replace(array_keys($layoutMap), array_values($layoutMap), $term);
    }
}
