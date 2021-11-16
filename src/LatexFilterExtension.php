<?php

namespace PhpLatexRenderer;

use Twig\TwigFunction;

class LatexFilterExtension extends \Twig\Extension\AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFunction('nl2tex', [$this, 'nl2tex']),
        ];
    }

    public function nl2tex(?string $in): string
    {
        if ($in === null) {
            return '';
        }
        return str_replace(PHP_EOL, '\\\\', $in);
    }
}
