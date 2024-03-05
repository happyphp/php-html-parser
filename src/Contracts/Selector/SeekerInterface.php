<?php

namespace Contracts\Selector;

use Exceptions\ChildNotFoundException;

interface SeekerInterface
{
    /**
     * Attempts to find all children that match the rule
     * given.
     *
     * @throws \Exceptions\ChildNotFoundException
     */
    public function seek(array $nodes, \DTO\Selector\RuleDTO $rule, array $options): array;
}
