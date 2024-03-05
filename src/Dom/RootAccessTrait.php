<?php

declare(strict_types=1);

namespace Haphp\HtmlParser\Dom;

use Haphp\HtmlParser\Dom\Node\AbstractNode;
use Haphp\HtmlParser\Exceptions\LogicalException;
use Haphp\HtmlParser\Exceptions\CircularException;
use Haphp\HtmlParser\Exceptions\NotLoadedException;
use Haphp\HtmlParser\Exceptions\ChildNotFoundException;

trait RootAccessTrait
{
    /**
     * Contains the root node of this dom tree.
     */
    public AbstractNode $root;

    /**
     * A simple wrapper around the root node.
     *
     * @param  string  $name
     *
     * @return mixed
     *
     * @throws NotLoadedException
     */
    public function __get(string $name)
    {
        $this->isLoaded();

        return $this->root->$name;
    }

    /**
     * Simple wrapper function that returns the first child.
     *
     * @throws ChildNotFoundException|NotLoadedException
     */
    public function firstChild(): AbstractNode
    {
        $this->isLoaded();

        return $this->root->firstChild();
    }

    /**
     * Simple wrapper function that returns the last child.
     *
     * @throws ChildNotFoundException
     * @throws LogicalException|NotLoadedException
     */
    public function lastChild(): AbstractNode
    {
        $this->isLoaded();

        return $this->root->lastChild();
    }

    /**
     * Simple wrapper function that returns count of child elements.
     *
     * @throws NotLoadedException
     */
    public function countChildren(): int
    {
        $this->isLoaded();

        return $this->root->countChildren();
    }

    /**
     * Get an array of children.
     *
     * @throws CircularException|NotLoadedException
     */
    public function getChildren(): array
    {
        $this->isLoaded();

        return $this->root->getChildren();
    }

    /**
     * Check if node has children nodes.
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
