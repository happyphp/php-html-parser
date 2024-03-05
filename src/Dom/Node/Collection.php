<?php

declare(strict_types=1);

namespace Haphp\HtmlParser\Dom\Node;

use Countable;
use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use ReturnTypeWillChange;
use Haphp\HtmlParser\Exceptions\EmptyCollectionException;
use function reset;
use function count;
use function is_null;
use function call_user_func_array;

/**
 * Class Collection.
 */
class Collection implements IteratorAggregate, ArrayAccess, Countable
{
    /**
     * The collection of Nodes.
     *
     * @var array
     */
    protected array $collection = [];

    /**
     * Attempts to call the method on the first node in
     * the collection.
     *
     * @return mixed
     * @throws EmptyCollectionException
     *
     */
    public function __call(string $method, array $arguments)
    {
        $node = reset($this->collection);
        if ($node instanceof AbstractNode) {
            return call_user_func_array([$node, $method], $arguments);
        }
        throw new EmptyCollectionException('The collection does not contain any Nodes.');
    }

    /**
     * Attempts to apply the magic get to the first node
     * in the collection.
     *
     * @param mixed $key
     *
     * @return mixed
     * @throws EmptyCollectionException
     *
     */
    public function __get(mixed $key)
    {
        $node = reset($this->collection);
        if ($node instanceof AbstractNode) {
            return $node->$key;
        }
        throw new EmptyCollectionException('The collection does not contain any Nodes.');
    }

    /**
     * Applies the magic string method to the first node in
     * the collection.
     */
    public function __toString(): string
    {
        $node = reset($this->collection);
        if ($node instanceof AbstractNode) {
            return (string) $node;
        }

        return '';
    }

    /**
     * Returns the count of the collection.
     */
    public function count(): int
    {
        return count($this->collection);
    }

    /**
     * Returns an iterator for the collection.
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->collection);
    }

    /**
     * Set an attribute by the given offset.
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->collection[] = $value;
        } else {
            $this->collection[$offset] = $value;
        }
    }

    /**
     * Checks if an offset exists.
     *
     * @param  mixed  $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->collection[$offset]);
    }

    /**
     * Unset a collection Node.
     *
     * @param mixed $offset
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->collection[$offset]);
    }

    /**
     * Gets a node at the given offset, or null.
     *
     * @param mixed $offset
     *
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function offsetGet(mixed $offset): mixed
    {
        return $this->collection[$offset] ?? null;
    }

    /**
     * Returns this collection as an array.
     */
    public function toArray(): array
    {
        return $this->collection;
    }

    /**
     * Similar to jQuery "each" method. Calls the callback with each
     * Node in this collection.
     */
    public function each(callable $callback): void
    {
        foreach ($this->collection as $key => $value) {
            $callback($value, $key);
        }
    }
}
