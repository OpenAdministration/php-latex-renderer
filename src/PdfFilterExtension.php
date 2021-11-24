<?php

namespace PhpLatexRenderer;

use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Twig\Environment;
use Twig\TwigFilter;

class PdfFilterExtension extends \Twig\Extension\AbstractExtension
{
    private bool $qpdf;


    public function __construct()
    {
        $finder = new ExecutableFinder();
        $this->qpdf = $finder->find('qpdf');
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('pages', [$this, 'pages'], ['is_safe' => ['tex', 'html', 'txt'], 'needs_context' => true]),
        ];
    }

    /**
     * @param array $context all variables
     * @param string $relFilePath only the filename, no path is needed. Directory is taken as _tex.dir/files/
     * @return int|null returns null if file does not exist
     */
    public function pages(array $context, $relFilePath): ?int
    {
        $dir = $context['_tex']['dir'];
        $file = $dir . $relFilePath;
        if (!file_exists($file)) {
            return null;
        }
        if (!empty($this->qpdf)) {
            $p = new Process(['qpdf', '--show-npages', $file]);
            $p->run();
            if ($p->isSuccessful()) {
                return (int) $p->getOutput();
            }
            return null;
        }
        return null;
    }
}
