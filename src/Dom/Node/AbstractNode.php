<?php

declare(strict_types=1);

namespace Haphp\HtmlParser\Dom\Node;

use stringEncode\Exception;
use Haphp\HtmlParser\Finder;
use Haphp\HtmlParser\Dom\Tag;
use Haphp\HtmlParser\Selector\Selector;
use stringEncode\Encode;
use Haphp\HtmlParser\Exceptions\ChildNotFoundException;
use Haphp\HtmlParser\Exceptions\ParentNotFoundException;
use Haphp\HtmlParser\Contracts\Selector\SelectorInterface;
use Haphp\HtmlParser\Exceptions\Tag\AttributeNotFoundException;
use function is_null;
use function is_string;
use function strtolower;

/**
 * Dom node object.
 *
 * @property-read string    $outerhtml
 * @property-read string    $innerhtml
 * @property-read string    $innerText
 * @property-read string    $text
 * @property-read Tag       $tag
 * @property-read InnerNode $parent
 */
abstract class AbstractNode
{
    public int $prev = 0;
    public int $next = 0;

    /**
     * Contains the tag name/type.
     *
     * @var ?Tag
     */
    protected ?Tag $tag;

    /**
     * Contains a list of attributes on this tag.
     *
     * @var array
     */
    protected array $attr = [];

    /**
     * Contains the parent Node.
     *
     * @var ?InnerNode
     */
    protected ?InnerNode $parent;

    /**
     * The unique id of the class. Given by PHP.
     *
     * @var int
     */
    protected int $id;

    /**
     * The encoding class used to encode strings.
     *
     * @var mixed
     */
    protected mixed $encode;

    /**
     * An array of all the children.
     *
     * @var array
     */
    protected array $children = [];

    /**
     * @var bool
     */
    protected bool $htmlSpecialCharsDecode = false;
    /**
     * @var int
     */
    private static int $count = 0;

    /**
     * Creates a unique id for this node.
     */
    public function __construct()
    {
        $this->id = self::$count;
        ++self::$count;
    }

    /**
     * Attempts to clear out any object references.
     */
    public function __destruct()
    {
        $this->tag = null;
        $this->parent = null;
        $this->attr = [];
        $this->children = [];
    }

    /**
     * Magic get method for attributes and certain methods.
     *
     * @return mixed
     */
    public function __get(string $key)
    {
        // check attribute first
        if ($this->getAttribute($key) !== null) {
            return $this->getAttribute($key);
        }
        return match (strtolower($key)) {
            'outerhtml' => $this->outerHtml(),
            'innerhtml' => $this->innerHtml(),
            'innertext' => $this->innerText(),
            'text' => $this->text(),
            'tag' => $this->getTag(),
            'parent' => $this->getParent(),
            default => null,
        };

    }

    /**
     * Simply calls the outer text method.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->outerHtml();
    }

    /**
     * @param  bool  $htmlSpecialCharsDecode
     */
    public function setHtmlSpecialCharsDecode(bool $htmlSpecialCharsDecode = false): void
    {
        $this->htmlSpecialCharsDecode = $htmlSpecialCharsDecode;
    }

    /**
     * Returns the id of this object.
     */
    public function id(): int
    {
        return $this->id;
    }

    /**
     * Returns the parent of node.
     */
    public function getParent(): ?InnerNode
    {
        return $this->parent;
    }

    /**
     * Sets the parent node.
     *
     */
    public function setParent(InnerNode $parent): AbstractNode
    {
        // remove from old parent
        if ($this->parent !== null) {
            if ($this->parent->id() == $parent->id()) {
                // already the parent
                return $this;
            }

            $this->parent->removeChild($this->id);
        }

        $this->parent = $parent;

        // assign child to parent
        $this->parent->addChild($this);

        return $this;
    }

    /**
     * Removes this node and all its children from the
     * DOM tree.
     *
     * @return void
     */
    public function delete(): void
    {
        $this->parent?->removeChild($this->id);
        $this->parent->clear();
        $this->clear();
    }

    /**
     * Sets the encoding class to this node.
     */
    public function propagateEncoding(Encode $encode): void
    {
        $this->encode = $encode;
        $this->tag->setEncoding($encode);
    }

    /**
     * Checks if the given node id is an ancestor of
     * the current node.
     */
    public function isAncestor(int $id): bool
    {
        if ($this->getAncestor($id) !== null) {
            return true;
        }

        return false;
    }

    /**
     * Attempts to get an ancestor node by the given id.
     */
    public function getAncestor(int $id): AbstractNode|InnerNode|null
    {
        if ($this->parent !== null) {
            if ($this->parent->id() == $id) {
                return $this->parent;
            }

            return $this->parent->getAncestor($id);
        }

        return null;
    }

    /**
     * Checks if the current node has a next sibling.
     */
    public function hasNextSibling(): bool
    {
        try {
            $this->nextSibling();

            // sibling found, return true;
            return true;
        } catch (ParentNotFoundException $e) {
            // no parent, no next sibling
            unset($e);

            return false;
        } catch (ChildNotFoundException $e) {
            // no sibling found
            unset($e);

            return false;
        }
    }

    /**
     * Attempts to get the next sibling.
     *
     * @throws ParentNotFoundException|ChildNotFoundException
     */
    public function nextSibling(): AbstractNode
    {
        if ($this->parent === null) {
            throw new ParentNotFoundException('Parent is not set for this node.');
        }

        return $this->parent->nextChild($this->id);
    }

    /**
     * Attempts to get the previous sibling.
     *
     * @throws ParentNotFoundException
     * @throws ChildNotFoundException
     */
    public function previousSibling(): AbstractNode
    {
        if ($this->parent === null) {
            throw new ParentNotFoundException('Parent is not set for this node.');
        }

        return $this->parent->previousChild($this->id);
    }

    /**
     * Gets the tag object of this node.
     */
    public function getTag(): Tag
    {
        return $this->tag;
    }

    /**
     * Replaces the tag for this node.
     */
    public function setTag(string|Tag $tag): AbstractNode
    {
        if (is_string($tag)) {
            $tag = new Tag($tag);
        }

        $this->tag = $tag;

        // clear any cache
        $this->clear();

        return $this;
    }

    /**
     * A wrapper method that simply calls the getAttribute method
     * on the tag of this node.
     * @throws Exception
     */
    public function getAttributes(): array
    {
        $attributes = $this->tag->getAttributes();
        foreach ($attributes as $name => $attributeDTO) {
            $attributes[$name] = $attributeDTO->getValue();
        }

        return $attributes;
    }

    /**
     * A wrapper method that simply calls the getAttribute method
     * on the tag of this node.
     */
    public function getAttribute(string $key): ?string
    {
        try {
            $attributeDTO = $this->tag->getAttribute($key);
        } catch (AttributeNotFoundException $e) {
            // no attribute with this key exists, returning null.
            unset($e);

            return null;
        }

        return $attributeDTO->getValue();
    }

    /**
     * A wrapper method that simply calls the hasAttribute method
     * on the tag of this node.
     */
    public function hasAttribute(string $key): bool
    {
        return $this->tag->hasAttribute($key);
    }

    /**
     * A wrapper method that simply calls the setAttribute method
     * on the tag of this node.
     */
    public function setAttribute(string $key, ?string $value, bool $doubleQuote = true): AbstractNode
    {
        $this->tag->setAttribute($key, $value, $doubleQuote);

        //clear any cache
        $this->clear();

        return $this;
    }

    /**
     * A wrapper method that simply calls the removeAttribute method
     * on the tag of this node.
     */
    public function removeAttribute(string $key): void
    {
        $this->tag->removeAttribute($key);

        //clear any cache
        $this->clear();
    }

    /**
     * A wrapper method that simply calls the removeAllAttributes
     * method on the tag of this node.
     */
    public function removeAllAttributes(): void
    {
        $this->tag->removeAllAttributes();

        //clear any cache
        $this->clear();
    }

    /**
     * Function to locate a specific ancestor tag in the path to the root.
     *
     * @throws ParentNotFoundException
     */
    public function ancestorByTag(string $tag): AbstractNode
    {
        // Start by including ourselves in the comparison.
        $node = $this;

        do {
            if ($node->tag->name() == $tag) {
                return $node;
            }

            $node = $node->getParent();
        } while ($node !== null);

        throw new ParentNotFoundException('Could not find an ancestor with "' . $tag . '" tag');
    }

    /**
     * Find elements by css selector.
     *
     * @throws ChildNotFoundException
     *
     * @return mixed|Collection|null
     */
    public function find(string $selectorString, ?int $nth = null, ?SelectorInterface $selector = null): mixed
    {
        if (is_null($selector)) {
            $selector = new Selector($selectorString);
        }

        $nodes = $selector->find($this);

        if ($nth !== null) {
            // return nth-element or array
            if (isset($nodes[$nth])) {
                return $nodes[$nth];
            }

            return null;
        }

        return $nodes;
    }

    /**
     * Find node by id.
     */
    public function findById(int $id): AbstractNode|bool
    {
        $finder = new Finder($id);

        return $finder->find($this);
    }

    /**
     * Gets the inner html of this node.
     */
    abstract public function innerHtml(): string;

    /**
     * Gets the html of this node, including its own
     * tag.
     */
    abstract public function outerHtml(): string;

    /**
     * Gets the text of this node (if there is any text).
     */
    abstract public function text(): string;

    /**
     * Check is node type textNode.
     */
    public function isTextNode(): bool
    {
        return false;
    }

    /**
     * Call this when something in the node tree has changed. Like a child has been added
     * or a parent has been changed.
     */
    abstract protected function clear(): void;
}
