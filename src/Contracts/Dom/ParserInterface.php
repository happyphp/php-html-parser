<?php

namespace Haphp\HtmlParser\Contracts\Dom;

use Haphp\HtmlParser\Content;
use Haphp\HtmlParser\Options;
use Haphp\HtmlParser\Dom\Node\AbstractNode;
use Haphp\HtmlParser\Exceptions\StrictException;
use Haphp\HtmlParser\Exceptions\LogicalException;
use Haphp\HtmlParser\Exceptions\CircularException;
use Haphp\HtmlParser\Exceptions\ContentLengthException;
use Haphp\HtmlParser\Exceptions\ChildNotFoundException;

interface ParserInterface
{
    /**
     * Attempts to parse the html in content.
     *
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws ContentLengthException
     * @throws LogicalException
     * @throws StrictException
     */
    public function parse(Options $options, Content $content, int $size): AbstractNode;

    /**
     * Attempts to detect the charset that the html was sent in.
     *
     * @throws ChildNotFoundException
     */
    public function detectCharset(Options $options, string $defaultCharset, AbstractNode $root): bool;
}
