<?php

declare(strict_types=1);

namespace BuzzingPixel\Templating;

use Laminas\Escaper\Escaper;

readonly class TemplateEngineFactory
{
    public function __construct(
        private Escaper|null $escaper = null,
    ) {
    }

    public function create(): TemplateEngine
    {
        return new TemplateEngine(
            $this->escaper ?? new Escaper(),
        );
    }
}
