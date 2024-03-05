<?php

namespace Haphp\HtmlParser\Contracts\Selector;

use Haphp\HtmlParser\DTO\Selector\RuleDTO;
use Haphp\HtmlParser\Exceptions\ChildNotFoundException;

interface SeekerInterface
{
    /**
     * Attempts to find all children that match the rule
     * given.
     *
     * @throws ChildNotFoundException
     */
    public function seek(array $nodes, RuleDTO $rule, array $options): array;
}
