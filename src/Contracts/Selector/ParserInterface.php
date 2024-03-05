<?php

declare(strict_types=1);

namespace Haphp\HtmlParser\Contracts\Selector;

use Haphp\HtmlParser\DTO\Selector\ParsedSelectorCollectionDTO;

interface ParserInterface
{
    public function parseSelectorString(string $selector): ParsedSelectorCollectionDTO;
}
