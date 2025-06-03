<?php

use PhpLatexRenderer\LatexRenderer;

define('ROOT', dirname(__FILE__, 2));

require ROOT . '/vendor/autoload.php';

$finder = new Symfony\Component\Process\ExecutableFinder();
$pdfLatex = $finder->find('pdflatex');
$tex = new LatexRenderer(__DIR__, ROOT . '/runtime/', 'pdflatex', true); // <- debug true - files will not be deleted
$monolog = new Monolog\Logger('Tex-Samples', [new Monolog\Handler\StreamHandler('php://output')]);
$tex->setLogger($monolog);
$pdf = $tex->renderPdf('simple-report', [
    'title' => 'My Custom Title',
    'author' => 'Me!',
    'content' => [
        [
            'headline' => 'This is a Test Headline',
            'items' => [
                [
                    'label' => 'Euro',
                    'value' => '€',
                ], [
                    'label' => 'Dollar',
                    'value' => '$',
                ], [
                    'label' => 'Tilde',
                    'value' => '~',
                ], [
                    'label' => 'Circumflex',
                    'value' => '^',
                ], [
                    'label' => 'And',
                    'value' => '&',
                ], [
                    'label' => 'Percent',
                    'value' => '%',
                ], [
                    'label' => 'Hashtag',
                    'value' => '#',
                ], [
                    'label' => 'Underscore',
                    'value' => '_',
                ], [
                    'label' => 'Curlybraces',
                    'value' => '{}',
                ], [
                    'label' => 'Hyphens',
                    'value' => '"\'',
                ],
            ],
        ],
    ]
]);
echo $pdf !== null ? 'Success' . PHP_EOL : 'Failure' . PHP_EOL;
