<?php

require dirname(__FILE__, 2) . '/vendor/autoload.php';
$p = new Symfony\Component\Process\Process(['pdflatex'], __DIR__, getenv());
$p->run();
echo $p->getOutput();
echo PHP_EOL . 'error:';
echo $p->getErrorOutput();
