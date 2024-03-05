<?php

declare(strict_types=1);

namespace Haphp\HtmlParser;

use Haphp\HtmlParser\Enum\StringToken;
use Haphp\HtmlParser\Exceptions\LogicalException;
use Haphp\HtmlParser\Exceptions\ContentLengthException;
use function strlen;
use function strpos;
use function substr;
use function strspn;
use function strcspn;

/**
 * Class Content.
 */
class Content
{
    /**
     * The content string.
     *
     * @var string
     */
    protected string $content;

    /**
     * The size of the content.
     *
     * @var int
     */
    protected int $size;

    /**
     * The current position we are in the content.
     *
     * @var int
     */
    protected int $pos;

    /**
     * The following four strings are tags that are important to us.
     *
     * @var string
     */
    protected string $blank = " \t\r\n";
    protected string $equal = ' =/>';
    protected string $slash = " />\r\n\t";
    protected string $attr = ' >';

    /**
     * Content constructor.
     */
    public function __construct(string $content = '')
    {
        $this->content = $content;
        $this->size = strlen($content);
        $this->pos = 0;
    }

    /**
     * Returns the current position of the content.
     */
    public function getPosition(): int
    {
        return $this->pos;
    }

    public function char(?int $char = null): string
    {
        return $this->content[$char ?? $this->pos] ?? '';
    }

    /**
     * Gets a string from the current character position.
     *
     * @param int $length
     * @return string
     */
    public function string(int $length = 1): string
    {
        $string = '';
        $position = $this->pos;
        do {
            $string .= $this->char($position++);
        } while ($position < $this->pos + $length);
        return $string;
    }

    /**
     * Moves the current position forward.
     *
     * @throws ContentLengthException
     */
    public function fastForward(int $count): Content
    {
        if (!$this->canFastForward($count)) {
            // trying to go over the content length, throw exception
            throw new ContentLengthException('Attempt to fastForward pass the length of the content.');
        }
        $this->pos += $count;

        return $this;
    }

    /**
     * Checks if we can move the position forward.
     */
    public function canFastForward(int $count): bool
    {
        return strlen($this->content) >= $this->pos + $count;
    }

    /**
     * Moves the current position backward.
     */
    public function rewind(int $count): Content
    {
        $this->pos -= $count;
        if ($this->pos < 0) {
            $this->pos = 0;
        }

        return $this;
    }

    /**
     * Copy the content until we find the given string.
     * @throws LogicalException
     */
    public function copyUntil(string $string, bool $char = false, bool $escape = false): string
    {
        if ($this->pos >= $this->size) {
            // nothing left
            return '';
        }

        if ($escape) {
            $position = $this->pos;
            $found = false;
            while (!$found) {
                $position = strpos($this->content, $string, $position);
                if ($position === false) {
                    // reached the end
                    break;
                }

                if ($this->char($position - 1) == '\\') {
                    // this character is escaped
                    ++$position;
                    continue;
                }

                $found = true;
            }
        } elseif ($char) {
            $position = strcspn($this->content, $string, $this->pos);
            $position += $this->pos;
        } else {
            $position = strpos($this->content, $string, $this->pos);
        }

        if ($position === false) {
            // could not find character, return the remaining of the content
            $return = substr($this->content, $this->pos, $this->size - $this->pos);
            if ($return === '') {
                throw new LogicalException('Substr returned false with position ' . $this->pos . '.');
            }
            $this->pos = $this->size;

            return $return;
        }

        if ($position == $this->pos) {
            // we are at the right place
            return '';
        }

        $return = substr($this->content, $this->pos, $position - $this->pos);
        if ($return === '') {
            throw new LogicalException('Substr returned false with position ' . $this->pos . '.');
        }
        // set the new position
        $this->pos = $position;

        return $return;
    }

    /**
     * Copies the content until the string is found and return it
     * unless the 'unless' is found in the substring.
     * @throws ContentLengthException|LogicalException
     */
    public function copyUntilUnless(string $string, string $unless): string
    {
        $lastPos = $this->pos;
        $this->fastForward(1);
        $foundString = $this->copyUntil($string, true, true);

        $position = strcspn($foundString, $unless);
        if ($position == strlen($foundString)) {
            return $string . $foundString;
        }
        // rewind changes and return nothing
        $this->pos = $lastPos;

        return '';
    }

    /**
     * Copies the content until it reaches the token string.
     *
     * @throws LogicalException
     * @uses $this->copyUntil()
     */
    public function copyByToken(StringToken $stringToken, bool $char = false, bool $escape = false): string
    {
        $string = $stringToken->getValue();

        return $this->copyUntil($string, $char, $escape);
    }

    /**
     * Skip a given set of characters.
     *
     */
    public function skip(string $string, bool $copy = false): string
    {
        $len = strspn($this->content, $string, $this->pos);
        $return = '';
        if ($copy) {
            $return = substr($this->content, $this->pos, $len);
        }

        // update the position
        $this->pos += $len;

        return $return;
    }

    /**
     * Skip a given token of pre-defined characters.
     *
     * @uses $this->skip()
     */
    public function skipByToken(StringToken $skipToken, bool $copy = false): string
    {
        $string = $skipToken->getValue();

        return $this->skip($string, $copy);
    }
}
