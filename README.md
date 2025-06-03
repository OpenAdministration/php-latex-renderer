# php-latex-renderer
wraps latex rendering and generating with twig templates. This library does
  * using twig for latex templating
  * inserting user data into latex templates 
  * escaping user data, so no (new) latex commands can be introduced by userdata 
  * renders latex file and returns pdf 
  * has compact latex error logs 
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
// output / save the pdf with
file_put_contents('main.pdf', $pdf);
// or echo with fitting header 
header("Content-type:application/pdf");
echo $pdf;
```
### Twig options
The following symbols are used for twig templating 
```php
$options = [
    'tag_block' => ['(%', '%)'],
    'tag_comment' => ['(!', '!)'],
    'tag_variable' => ['((', '))'],
];
```
due to `{{`, `{#` and `{%` are too common in regular latex code. Be carefull with `((` it is easy to use it in calculations as well. You can use 
```php 
$tex->setTwigLexer($options)
```
for custom variants. 
### Meta Twig Context 
There is a new introduced global variable `_tex`, which can be used everywhere and is defined like: 
```php 
$this->twig->addGlobal('_tex', [
    'files' => $fileNames, // with name.pdf => files/name.pdf (local path in dir) 
    'dir' => $tmpDir . "tex/$templateName/$uid/",
    'template' => $twigTemplateName,
]);
```
Example: `_tex.dir` 
### Try the sample 
```
php -f samples/simple-report.php
```

# Examples 

You can find some real world examples in the `samples` Folder

# Contribute 
Please run 
```
composer cs-fix 
```
and commit the changes in an extra commit before doing a pull request
