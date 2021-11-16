<?php

use PhpLatexRenderer\LatexRenderer;

define('ROOT', dirname(__FILE__, 2));

require ROOT . '/vendor/autoload.php';

$finder = new \Symfony\Component\Process\ExecutableFinder();
$pdfLatex = $finder->find('pdflatex');
$tex = new LatexRenderer(__DIR__, ROOT . '/runtime/', 'pdflatex', true); // <- debug true - files will not be deleted
$monolog = new \Monolog\Logger('Tex-Samples', [new \Monolog\Handler\StreamHandler('php://output')]);
$tex->setLogger($monolog);
$pdf = $tex->renderPdf('simple-report', [
    'title' => 'My Custom Title',
    'author' => 'Me!',
    'content' => [
        [
          'headline' => 'This is a Test Headline',
          'items' => [
              [
                  'label' => 'test',
                  'value' => 'value',
              ],
              [
                  'label' => 'test',
                  'value' => 'value',
              ],
          ],
        ],
        [
            'headline' => 'test2',
            'items' => [
                [
                    'label' => 'test',
                    'value' => 'value',
                ],
            ],
            'resume' => true,
        ],
        [
            'text' => 'Please sign only if all of the above data is correct',
            'signatures' => [
                [
                    'label' => 'Signature 1',
                    'name' => 'Person 1',
                ],
                [
                    'label' => 'Signature 2',
                    'name' => 'Person 2',
                ],
                [
                    'label' => 'Signature 3',
                    'name' => 'Person 3',
                ],
            ],
        ],
    ],
]);
echo $pdf !== null ? 'Success' . PHP_EOL : 'Failure' . PHP_EOL;
