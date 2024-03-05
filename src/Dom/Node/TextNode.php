<?php

declare(strict_types=1);

namespace Haphp\HtmlParser\Dom\Node;

use Haphp\HtmlParser\Dom\Tag;
use Haphp\HtmlParser\Exceptions\LogicalException;
use function is_null;
use function str_replace;
use function mb_ereg_replace;
use function htmlspecialchars_decode;

/**
 * Class TextNode.
 *
 * @property-read string    $outerhtml
 * @property-read string    $innerhtml
 * @property-read string    $innerText
 * @property-read string    $text
 * @property-read Tag       $tag
 * @property-read InnerNode $parent
 */
class TextNode extends LeafNode
{
    protected ?Tag $tag = null;

    /**
     * This is the text in this node.
     *
     * @var string
     */
    protected string $text;

    /**
     * This is the converted version of the text.
     *
     * @var ?string
     */
    protected ?string $convertedText = null;

    /**
     * Sets the text for this node.
     *
     * @param  string  $text
     * @param  bool  $removeDoubleSpace
     * @throws LogicalException
     */
    public function __construct(string $text, bool $removeDoubleSpace = true)
    {
        if ($removeDoubleSpace) {
            // remove double spaces
            $replacedText = mb_ereg_replace('\s+', ' ', $text);
            if ($replacedText === false) {
                throw new LogicalException('mb_ereg_replace returns false when attempting to clean white space from "' . $text . '".');
            }
            $text = $replacedText;
        }

        // restore line breaks
        $text = str_replace('&#10;', "\n", $text);

        $this->text = $text;
        $this->tag = new Tag('text');
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
     * Returns the text of this node.
     */
    public function text(): string
    {
        if ($this->htmlSpecialCharsDecode) {
            $text = htmlspecialchars_decode($this->text);
        } else {
            $text = $this->text;
        }
        // convert charset
        if (!is_null($this->encode)) {
            if (!is_null($this->convertedText)) {
                // we already know the converted value
                return $this->convertedText;
            }
            $text = $this->encode->convert($text);

            // remember the conversion
            $this->convertedText = $text;

            return $text;
        }

        return $text;
    }

    /**
     * Sets the text for this node.
     *
     * @param  string  $text
     * @return void
     */
    public function setText(string $text): void
    {
        $this->text = $text;
        if (!is_null($this->encode)) {
            $text = $this->encode->convert($text);

            // remember the conversion
            $this->convertedText = $text;
        }
    }

    /**
     * This node has no html, return the text.
     *
     * @uses $this->text()
     */
    public function innerHtml(): string
    {
        return $this->text();
    }

    /**
     * This node has no html, return the text.
     *
     * @uses $this->text()
     */
    public function outerHtml(): string
    {
        return $this->text();
    }

    /**
     * Checks if the current node is a text node.
     */
    public function isTextNode(): bool
    {
        return true;
    }

    /**
     * Call this when something in the node tree has changed. Like a child has been added
     * or a parent has been changed.
     */
    protected function clear(): void
    {
        $this->convertedText = null;
    }
}
