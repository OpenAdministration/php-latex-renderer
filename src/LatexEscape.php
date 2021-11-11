<?php

namespace PhpLatexRenderer;

class LatexEscape
{
    public static function escape($twig, string $unsafe, $charset): string
    {
        $safer = str_replace(
            ['&', '%', '$', '#', '_', '{', '}', '~', '^', '\\'],
            ['\\&', '\\%', '\\$', '\\#', '\\_', '\\{', '\\}', '\\textasciitilde{}', '\\textasciicircum{}', '\\textbackslash{}'],
            $unsafe
        );
        $safer = str_replace(PHP_EOL, '\\\\', $safer);
        return $safer;
    }
}
