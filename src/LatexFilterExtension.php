<?php

namespace PhpLatexRenderer;

use Twig\TwigFilter;

class LatexFilterExtension extends \Twig\Extension\AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('nl2tex', [$this, 'nl2tex'], ['pre_escape' => 'tex', 'is_safe' => ['tex']]),
        ];
    }

    public function nl2tex(string $in): string
    {
        return str_replace(PHP_EOL, '\\\\', $in);
    }
}
