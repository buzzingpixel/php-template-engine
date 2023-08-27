Just simple, basic, PHP templating.

## Usage

### Installation

```bash
composer require buzzingpixel/php-template-engine
```

### Render a template

Get an instance of the `TemplateEngine` from the `TemplateEngineFactory`.

```php
use BuzzingPixel\Templating\TemplateEngineFactory;

$templateEngine = (new TemplateEngineFactory())->create();
```

The only required item to set is the template path.

```php
$templateEngine->templatePath('/path/to/template.phtml');
```

(note that `pthml` is just a convention. The TemplateEngine doesn't care what the extension is)

Variables are optional. If you have any to set, you can add them in one go:

```php
$templateEngine->vars([
    'foo' => 'bar',
    'baz' => 'foo',
]);
```

…or you can add them one at a time:

```php
$templateEngine->addVar('foo', 'bar')->addVar('baz', 'foo');
```

And when you're ready, call `render()` on the `TemplateEngine` instance, and your template will be rendered and the content returned as a string. Here's a full example.


```php
use BuzzingPixel\Templating\TemplateEngineFactory;

$renderedContent = (new TemplateEngineFactory())->create()
    ->templatePath(__DIR__ . '/my/template.phtml')
    ->addVar('foo', 'bar')
    ->render();
```

### Templating

To get auto-completing in your IDE for your PHP templates, you can assert that `$this` is an instance of `\BuzzingPixel\Templating\TemplateEngine`. You can also assert any variables you're expecting in your template:

```php
<?php
assert($this instanceof \BuzzingPixel\Templating\TemplateEngine);
assert(is_string($foo));
?>
```

#### Template Inheritance, Extending, and Sections

Extending another template is fairly simple. Just run `$this->extends('/path/to/some/template.phtml')` somewhere in your template (I like to do it at the top). All rendered content in the template is assigned to a section named `layoutContent`.

As noted, by default, when extending, all content not placed in a section gets assigned to the `layoutContent` section. This is nice in that you don't have to do a lot of work if you just have one thing you're rendering and pushing up the stack. So a simple extension might look something like this:

`/my-template.phtml`:
```php
<?php
assert($this instanceof \BuzzingPixel\Templating\TemplateEngine);
assert(is_string($pageTitle));
$this->extends(__DIR__ . '/layout.phtml');
?>

Hello World!
```

`/layout.phtml`:
```php
<?php
assert($this instanceof \BuzzingPixel\Templating\TemplateEngine);
assert(is_string($pageTitle));
?>
<html>
<head>
    <title><?= $this->html($pageTitle) ?></title>
</head>
<body>
    <?= $this->getSection('layoutContent') ?>
</body>
</html>
```

There is no limit on how deep you can go on extending. An extended template can, in turn, extend another template.

But you may also want to set other sections, say, a sidebar, for your extended template. That might look something like this:

`/my-template.phtml`:
```php
<?php
assert($this instanceof \BuzzingPixel\Templating\TemplateEngine);
assert(is_string($pageTitle));
$this->extends(__DIR__ . '/layout.phtml');
?>

<?php $this->sectionStart('sidebar') ?>

<ul>
    <li>Foo</li>
    <li>Bar</li>
</ul>

<?php $this->sectionEnd() ?>

Hello World!
```

`/layout.phtml`:
```php
<?php
assert($this instanceof \BuzzingPixel\Templating\TemplateEngine);
assert(is_string($pageTitle));
?>
<html>
<head>
    <title><?= $this->html($pageTitle) ?></title>
</head>
<body>
    <?php if ($this->hasSection('sidebar')): ?>
        <?= $this->getSection('sidebar') ?>
    <?php endif ?>
    <?= $this->getSection('layoutContent') ?>
</body>
</html>
```

#### Partials

Reusability of code is a big concern, which is why partials are of interest. Partials work a little something like this:

`/my-partial.phtml`
```php
<?php
assert($this instanceof \BuzzingPixel\Templating\TemplateEngine);
assert(is_array($items));
?>

<?php foreach ($items as $item): ?>
    <li><?= $item ?></li>
<?php endforeach ?>

```

`/my-template.phtml`:
```php
<?php
assert($this instanceof \BuzzingPixel\Templating\TemplateEngine);
assert(is_string($pageTitle));
$this->extends(__DIR__ . '/layout.phtml');
?>

<?php $this->sectionStart('sidebar') ?>

<ul>
    <?= $this->partial(__DIR_ '/my-partial.phtml', [
        'Foo',
        'Bar',
    ]) ?>
</ul>

<?php $this->sectionEnd() ?>

Hello World!
```

Partials are a full instance of the `TemplateEngine` so they have every feature any other template does. They do NOT inherit the variable context of the calling template.

#### Escaping

The `TemplateEngine` has escaping helper methods which you should use whenever you have output from untrusted sources.

```php
<?php
assert($this instanceof \BuzzingPixel\Templating\TemplateEngine);
assert(is_string($id));
assert(is_string($js));
assert(is_string($css));
assert(is_string($url));
assert(is_string($pageTitle));
$this->extends(__DIR__ . '/layout.phtml');
?>

<style>
<?= $this->css($css) ?>
</style>

<h1><?= $this->html($pageTitle) ?></h1>

<a
    id="<?= $this->attr($id) ?>"
    href="<?= $this->url($url) ?>"
>
    My Link
</a>

<script>
    <?= $this->js($js) ?>
</script>
```
