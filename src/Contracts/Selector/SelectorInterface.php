<?php

declare(strict_types=1);

namespace Contracts\Selector;

use Dom\Node\Collection;
use Dom\Node\AbstractNode;
use Exceptions\ChildNotFoundException;
use DTO\Selector\ParsedSelectorCollectionDTO;

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
     * @throws \Exceptions\ChildNotFoundException
     */
    public function find(AbstractNode $node): Collection;
}
