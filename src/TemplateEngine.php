<?php

declare(strict_types=1);

namespace BuzzingPixel\Templating;

use RuntimeException;

use function array_merge;
use function extract;
use function ob_get_clean;
use function ob_start;

use const EXTR_SKIP;

// phpcs:disable Generic.PHP.ForbiddenFunctions.Found
// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class TemplateEngine
{
    private string $templatePath;

    public function templatePath(string $templatePath): self
    {
        $this->templatePath = $templatePath;

        return $this;
    }

    /** @var mixed[] $vars */
    private array $vars = [];

    /** @param mixed[] $vars */
    public function vars(array $vars): self
    {
        $this->vars = $vars;

        return $this;
    }

    public function addVar(string $key, mixed $value): self
    {
        $this->vars[$key] = $value;

        return $this;
    }

    private string|null $extends = null;

    public function extends(string|null $templatePath): self
    {
        $this->extends = $templatePath;

        return $this;
    }

    /** @var array<string, (string|null)> */
    private array $sections = [];

    private string|null $activeSection = null;

    public function sectionStart(string $sectionName): void
    {
        $this->activeSection = $sectionName;

        $this->sections[$sectionName] = null;

        ob_start();
    }

    public function sectionEnd(): void
    {
        $this->sections[$this->activeSection] = (string) ob_get_clean();

        $this->activeSection = null;
    }

    public function hasSection(string $section): bool
    {
        return isset($this->sections[$section]);
    }

    public function getSection(string $section): string
    {
        return (string) $this->sections[$section];
    }

    public function addSection(string $section, string $content): self
    {
        $this->sections[$section] = $content;

        return $this;
    }

    /** @param array<string, string|null> $sections */
    public function sections(array $sections): self
    {
        $this->sections = $sections;

        return $this;
    }

    /** @param mixed[] $vars */
    public function partial(string $templatePath, array $vars = []): string
    {
        $partialEngine = (new self())
            ->templatePath($templatePath)
            ->vars($vars);

        return $partialEngine->render();
    }

    public function render(): string
    {
        if (! isset($this->templatePath)) {
            throw new RuntimeException(
                '$templatePath must be set',
            );
        }

        $__vars__ = $this->vars;

        ob_start();

        extract($__vars__, EXTR_SKIP);

        require $this->templatePath;

        $content = (string) ob_get_clean();

        if ($this->extends === null) {
            return $content;
        }

        $extendEngine = (new self())
            ->templatePath($this->extends)
            ->vars($this->vars)
            ->sections(array_merge(
                $this->sections,
                ['layoutContent' => $content],
            ));

        return $extendEngine->render();
    }
}
