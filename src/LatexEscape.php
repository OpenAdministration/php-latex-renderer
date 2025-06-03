<?php

namespace PhpLatexRenderer;

class LatexEscape
{
    // @see: https://tex.stackexchange.com/a/34586
    public const ESCAPES = [
        '\\' => '\\textbackslash{}',
        '&' => '\\&',
        '%' => '\\%',
        '$' => '\\$',
        '#' => '\\#',
        '_' => '\\_',
        '{' => '\\{',
        '}' => '\\}',
        '~' => '\\~{}',
        '^' => '\\^{}',
        "'" => "\\'{}",
        '"' => '\\"{}',
    ];

    public static function escape(?string $unsafe, $charset): string
    {
        if ($unsafe === null) {
            return '';
        }
        $safer = str_replace(
            array_keys(self::ESCAPES),
            array_values(self::ESCAPES),
            $unsafe
        );
        return trim($safer);
    }
}
