<?php

declare(strict_types=1);

namespace Haphp\HtmlParser\Dom\Node;

use stringEncode\Exception;
use Haphp\HtmlParser\Dom\Tag;
use Haphp\HtmlParser\Exceptions\CircularException;
use Haphp\HtmlParser\Exceptions\ChildNotFoundException;
use Haphp\HtmlParser\Exceptions\UnknownChildTypeException;
use function is_null;
use function get_class;
use function strip_tags;

/**
 * Class HtmlNode.
 *
 * @property-read string    $outerhtml
 * @property-read string    $innerhtml
 * @property-read string    $innerText
 * @property-read string    $text
 * @property-read Tag       $tag
 * @property-read InnerNode $parent
 */
class HtmlNode extends InnerNode
{
    /**
     * Remembers what the innerHtml was if it was scanned previously.
     *
     * @var ?string
     */
    protected ?string $innerHtml;

    /**
     * Remembers what the outerHtml was if it was scanned previously.
     *
     * @var ?string
     */
    protected ?string $outerHtml;

    /**
     * Remembers what the innerText was if it was scanned previously.
     *
     * @var ?string
     */
    protected $innerText;

    /**
     * Remembers what the text was if it was scanned previously.
     *
     * @var ?string
     */
    protected $text;

    /**
     * Remembers what the text was when we looked into all our
     * children nodes.
     *
     * @var ?string
     */
    protected ?string $textWithChildren;

    protected ?Tag $tag;

    /**
     * Sets up the tag of this node.
     *
     * @param  string|Tag  $tag
     */
    public function __construct(string|Tag $tag)
    {
        if (!$tag instanceof Tag) {
            $tag = new Tag($tag);
        }
        $this->tag = $tag;
        parent::__construct();
    }

    /**
     * @param  bool  $htmlSpecialCharsDecode
     */
    public function setHtmlSpecialCharsDecode(bool $htmlSpecialCharsDecode = false): void
    {
        parent::setHtmlSpecialCharsDecode($htmlSpecialCharsDecode);
        $this->tag->setHtmlSpecialCharsDecode($htmlSpecialCharsDecode);
    }

    /**
     * Gets the inner html of this node.
     *
     * @throws ChildNotFoundException
     * @throws UnknownChildTypeException
     * @throws Exception
     */
    public function innerHtml(): string
    {
        if (!$this->hasChildren()) {
            // no children
            return '';
        }

        if ($this->innerHtml !== null) {
            // we already know the result.
            return $this->innerHtml;
        }

        $child = $this->firstChild();
        $string = '';

        // continue to loop until we are out of children
        while ($child !== null) {
            if ($child instanceof TextNode) {
                $string .= $child->text();
            } elseif ($child instanceof HtmlNode) {
                $string .= $child->outerHtml();
            } else {
                throw new UnknownChildTypeException('Unknown child type "' . get_class($child) . '" found in node');
            }

            try {
                $child = $this->nextChild($child->id());
            } catch (ChildNotFoundException $e) {
                // no more children
                unset($e);
                $child = null;
            }
        }

        // remember the results
        $this->innerHtml = $string;

        return $string;
    }

    /**
     * Gets the inner text of this node.
     *
     * @throws ChildNotFoundException
     * @throws UnknownChildTypeException
     * @throws Exception
     */
    public function innerText(): string
    {
        if (is_null($this->innerText)) {
            $this->innerText = strip_tags($this->innerHtml());
        }

        return $this->innerText;
    }

    /**
     * Gets the html of this node, including its own
     * tag.
     *
     * @throws ChildNotFoundException
     * @throws UnknownChildTypeException
     * @throws Exception
     */
    public function outerHtml(): string
    {
        // special handling for root
        if ($this->tag->name() == 'root') {
            return $this->innerHtml();
        }

        if ($this->outerHtml !== null) {
            // we already know the results.
            return $this->outerHtml;
        }

        $return = $this->tag->makeOpeningTag();
        if ($this->tag->isSelfClosing()) {
            // ignore any children... there should not be any though
            return $return;
        }

        // get the inner html
        $return .= $this->innerHtml();

        // add closing tag
        $return .= $this->tag->makeClosingTag();

        // remember the results
        $this->outerHtml = $return;

        return $return;
    }

    /**
     * Gets the text of this node (if there is any text). Or get all the text
     * in this node, including children.
     */
    public function text(bool $lookInChildren = false): string
    {
        if ($lookInChildren) {
            if ($this->textWithChildren !== null) {
                // we already know the results.
                return $this->textWithChildren;
            }
        } elseif ($this->text !== null) {
            // we already know the results.
            return $this->text;
        }

        // find out if this node has any text children
        $text = '';
        foreach ($this->children as $child) {
            /** @var AbstractNode $node */
            $node = $child['node'];
            if ($node instanceof TextNode) {
                $text .= $child['node']->text;
            } elseif (
                $lookInChildren &&
                $node instanceof HtmlNode
            ) {
                $text .= $node->text($lookInChildren);
            }
        }

        // remember our result
        if ($lookInChildren) {
            $this->textWithChildren = $text;
        } else {
            $this->text = $text;
        }

        return $text;
    }

    /**
     * Call this when something in the node tree has changed. Like a child has been added
     * or a parent has been changed.
     */
    protected function clear(): void
    {
        $this->innerHtml = null;
        $this->outerHtml = null;
        $this->text = null;
        $this->textWithChildren = null;

        if ($this->parent !== null) {
            $this->parent->clear();
        }
    }

    /**
     * Returns all children of this html node.
     * @throws CircularException
     */
    protected function getIteratorArray(): array
    {
        return $this->getChildren();
    }
}
