<?php

declare(strict_types=1);

namespace Dom;

use Dom\Node\HtmlNode;
use Dom\Node\AbstractNode;
use Exceptions\NotLoadedException;
use Exceptions\ChildNotFoundException;

trait RootAccessTrait
{
    /**
     * Contains the root node of this dom tree.
     *
     * @var \Dom\Node\HtmlNode
     */
    public $root;

    /**
     * A simple wrapper around the root node.
     *
     * @param string $name
     *
     * @return mixed
     * @throws \Exceptions\NotLoadedException
     *
     */
    public function __get($name)
    {
        $this->isLoaded();

        return $this->root->$name;
    }

    /**
     * Simple wrapper function that returns the first child.
     *
     * @throws \Exceptions\ChildNotFoundException
     * @throws \Exceptions\NotLoadedException
     */
    public function firstChild(): AbstractNode
    {
        $this->isLoaded();

        return $this->root->firstChild();
    }

    /**
     * Simple wrapper function that returns the last child.
     *
     * @throws \Exceptions\ChildNotFoundException
     * @throws \Exceptions\NotLoadedException
     */
    public function lastChild(): AbstractNode
    {
        $this->isLoaded();

        return $this->root->lastChild();
    }

    /**
     * Simple wrapper function that returns count of child elements.
     *
     * @throws \Exceptions\NotLoadedException
     */
    public function countChildren(): int
    {
        $this->isLoaded();

        return $this->root->countChildren();
    }

    /**
     * Get array of children.
     *
     * @throws \Exceptions\NotLoadedException
     */
    public function getChildren(): array
    {
        $this->isLoaded();

        return $this->root->getChildren();
    }

    /**
     * Check if node have children nodes.
     *
     * @throws NotLoadedException
     */
    public function hasChildren(): bool
    {
        $this->isLoaded();

        return $this->root->hasChildren();
    }

    abstract public function isLoaded(): void;
}
