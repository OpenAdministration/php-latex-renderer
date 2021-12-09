<?php

namespace PhpLatexRenderer;

use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Twig\Error\RuntimeError;
use Twig\TwigFilter;

class PdfFilterExtension extends \Twig\Extension\AbstractExtension
{
    private string $qpdfPath;
    private string $pdfinfoPath;

    public function __construct()
    {
        $finder = new ExecutableFinder();
        $this->qpdfPath = $finder->find('qpdf', '');
        $this->pdfinfoPath = $finder->find('pdfinfo', '');
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
     * @return int|null returns null if file does not exist, int with number of pages otherwise
     * @throws RuntimeError
     */
    public function pages(array $context, $relFilePath): ?int
    {
        if (!isset($context['_tex']['dir'])) {
            throw new RuntimeError('_tex is not visible in this context');
        }
        $dir = $context['_tex']['dir'];
        $file = $dir . $relFilePath;
        if (!file_exists($file)) {
            throw new RuntimeError('File does not exist');
        }

        switch (false) {
            case empty($this->qpdfPath):
                $process = new Process([$this->qpdfPath, '--show-npages', $file]);
                $callback = static fn ($output) => (int) $output;
                break;
            case empty($this->pdfinfoPath):
                $process = new Process([$this->pdfinfoPath, $file]);
                $callback = static function ($output) {
                    preg_match("/Pages:\s*(\d+)/i", $output, $matches);
                    return (int) $matches[1];
                };
                break;
            default:
                throw new RuntimeError('No executable found on the system to determine the pdf page count');
        }
        $process->run();
        if ($process->isSuccessful()) {
            return $callback($process->getOutput());
        }
        return null;
    }
}
