# php-latex-renderer
wraps latex rendering and generating with twig templates 
# Installation
Installation via composer is suggested  
```
composer require open-administration/php-latex-renderer
```
# Usage

```php
require './vendor/autoload.php';

$tex = new LatexRenderer('./templates/'); // <- dir where to search the templates
$tex->setTmpDir('./runtime/'); // <- where to build the latex files
$pdf = $tex->renderPdf('simple-report', [ // <- which template to use (file ending .tex.twig)
    'title' => 'My Custom Title', // <- variables to set 
    'author' => 'Me!',
]); 
```

# Contribute 
Please run 
```
composer cs-fix 
```
before doing a pull request