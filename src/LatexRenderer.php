<?php

namespace PhpLatexRenderer;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Process\Process;
use Twig\Cache\FilesystemCache;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Extension\DebugExtension;
use Twig\Extension\EscaperExtension;
use Twig\Lexer;
use Twig\Loader\FilesystemLoader;

/**
 * Configure and execute the rendering
 */
class LatexRenderer
{
    private string $tmpDir;

    private string $latexExec;

    private \Twig\Environment $twig;

    private bool $debug;

    private LoggerInterface $logger;

    /**
     * @param $templateDirs array|string the path(s) where the .tex.twig templates are found
     * @param $tmpDir string the directory where latex code will be compiled
     * @param $latexExec string the path to the latex exec to use
     * @param $debug bool true the files will not be deleted after attempted rendering
     */
    public function __construct($templateDirs, string $tmpDir = '/tmp/', string $latexExec = 'pdflatex', bool $debug = false)
    {
        $this->debug = $debug;
        $this->latexExec = $latexExec;
        $this->tmpDir = $tmpDir;
        $loader = new FilesystemLoader($templateDirs);
        $this->twig = new Environment($loader, [
            'debug' => $debug,
            'strict_variables' => true,
            'autoescape' => 'tex',
            'cache' => false,
        ]);
        $this->twig->getExtension(EscaperExtension::class)->setEscaper('tex', [LatexEscape::class, 'escape']);
        $this->twig->addExtension(new LatexFilterExtension());
        $this->twig->addExtension(new PdfFilterExtension());
        if ($debug) {
            $this->twig->addExtension(new DebugExtension());
        }
        $this->twig->setLexer(new Lexer($this->twig, [
            'tag_block' => ['(%', '%)'],
            'tag_comment' => ['(!', '!)'],
            'tag_variable' => ['((', '))'],
        ]));
        $this->logger = new NullLogger();
    }

    /**
     * @param array $lexerOptions {@see \Twig\Lexer}
     */
    public function setTwigLexer(array $lexerOptions): void
    {
        $this->twig->setLexer(new Lexer($this->twig, $lexerOptions));
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @param $templateDir
     */
    public function setTemplateDir($templateDir): void
    {
        $this->twig->setLoader(new FilesystemLoader($templateDir));
    }

    /**
     * @param string $tmpDir the directory path where the latex runtime files will be located
     */
    public function setTmpDir(string $tmpDir): void
    {
        if (!str_ends_with($tmpDir, '/')) {
            $tmpDir .= '/';
        }
        $this->tmpDir = $tmpDir;
        if (!$this->debug) {
            $this->twig->setCache(new FilesystemCache($this->tmpDir . 'cache/'));
        }
    }

    /**
     * @param array $variables _tex will be added
     * @param array $files additional files which will be saved to ./files/<name> - format: key=name, value=fileContent
     * @return string|null returns pdf as string or null on failure
     */
    public function renderPdf(string $templateName, array $variables, array $files = []): ?string
    {
        try {
            $uid = uniqid('', true);
            $dir = $this->tmpDir . "tex/$templateName/$uid/files";
            // check if dir exists, if not create it and check if it was created successfully
            if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
                $this->logger->critical('Directory was not created', [$dir]);
                return null;
            }
            $fileNames = [];
            foreach ($files as $name => $content) {
                file_put_contents($this->tmpDir . "tex/$templateName/$uid/files/$name", $content);
                // write down filenames
                $fileNames[$name] = 'files/' . $name;
            }
            $latexVars = [
                '_tex' => [
                    'files' => $fileNames,
                    'dir' => $this->tmpDir . "tex/$templateName/$uid/",
                    'template' => $templateName,
                ],
            ];
            $this->twig->addGlobal('_tex', $latexVars);
            $tex = $this->twig->render($templateName . '.tex.twig', $variables);

            file_put_contents($this->tmpDir . "tex/$templateName/$uid/main.tex", $tex);
        } catch (Error $error) {
            $this->logger->alert($error->getMessage(), [$error->getFile(), $error->getTemplateLine()]);
            return null;
        }

        $proc = new Process([$this->latexExec, 'main.tex'], $this->tmpDir . "tex/$templateName/$uid/", getenv());
        $proc->run();
        // do a second time
        $proc2 = $proc->restart();
        $proc2->wait();
        $dir = $this->tmpDir . "tex/$templateName/$uid";
        $logPath = $dir . '/main.log';
        $auxPath = $dir . '/main.aux';
        $texPath = $dir . '/main.tex';

        if (!$proc2->isSuccessful()) {
            // try to filter tex log for most important parts (lines starting with ! and the line after it)
            $errors = [$proc2->getOutput(), $proc2->getErrorOutput()];
            if (file_exists($logPath)) {
                $logContent = file_get_contents($logPath);
                $logLines = explode(PHP_EOL, $logContent);
                $errorLineNumbers = array_keys(preg_grep('/^!.*$/', $logLines));
                $errors = array_filter(
                    $logLines,
                    static fn ($key) => in_array($key, $errorLineNumbers, true) || in_array($key - 1, $errorLineNumbers, true),
                    ARRAY_FILTER_USE_KEY);
            }
            $this->logger->error("Error in Template $templateName", $errors);
            $this->deleteFiles($dir, $files);
            return null;
        }
        // no error
        $pdfPath = $dir . '/main.pdf';
        $pdfContent = file_get_contents($pdfPath);

        $this->deleteFiles($dir, $files);
        $this->logger->debug('Created File', [$pdfPath]);
        return $pdfContent;
    }

    /*
     * Deletes all given files, if not in debug mode
     */
    private function deleteFiles(string $dir, array $files): void
    {
        if ($this->debug) {
            return;
        }
        foreach ($files as $fileName => $fileContent) {
            file_exists($dir . '/files/' . $fileName) && unlink($dir . '/files/' . $fileName);
        }
        rmdir($dir . '/files/');
        file_exists($dir . '/main.tex') && unlink($dir . '/main.tex');
        file_exists($dir . '/main.log') && unlink($dir . '/main.log');
        file_exists($dir . '/main.aux') && unlink($dir . '/main.aux');
        file_exists($dir . '/main.pdf') && unlink($dir . '/main.pdf');
        rmdir($dir);
    }
}
