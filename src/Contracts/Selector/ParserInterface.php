<?php

declare(strict_types=1);

namespace Contracts\Selector;

use DTO\Selector\ParsedSelectorCollectionDTO;

interface ParserInterface
{
    public function parseSelectorString(string $selector): ParsedSelectorCollectionDTO;
}
