<?php

declare(strict_types=1);

namespace Haphp\HtmlParser\Contracts\Selector;

use Haphp\HtmlParser\Dom\Node\Collection;
use Haphp\HtmlParser\Dom\Node\AbstractNode;
use Haphp\HtmlParser\Exceptions\ChildNotFoundException;
use Haphp\HtmlParser\DTO\Selector\ParsedSelectorCollectionDTO;

interface SelectorInterface
{
    /**
     * Constructs with the selector string.
     */
    public function __construct(string $selector, ?ParserInterface $parser = null, ?SeekerInterface $seeker = null);

    /**
     * Returns the selectors that where found.
     */
    public function getParsedSelectorCollectionDTO(): ParsedSelectorCollectionDTO;

    /**
     * Attempts to find the selectors starting from the given
     * node object.
     *
     * @throws ChildNotFoundException
     */
    public function find(AbstractNode $node): Collection;
}
