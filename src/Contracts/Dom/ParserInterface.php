<?php

namespace Contracts\Dom;

use Content;
use Options;
use Dom\Node\AbstractNode;
use Exceptions\StrictException;
use Exceptions\LogicalException;
use Exceptions\CircularException;
use Exceptions\ContentLengthException;
use Exceptions\ChildNotFoundException;

interface ParserInterface
{
    /**
     * Attempts to parse the html in content.
     *
     * @throws \Exceptions\ChildNotFoundException
     * @throws \Exceptions\CircularException
     * @throws \Exceptions\ContentLengthException
     * @throws \Exceptions\LogicalException
     * @throws StrictException
     */
    public function parse(Options $options, Content $content, int $size): AbstractNode;

    /**
     * Attempts to detect the charset that the html was sent in.
     *
     * @throws \Exceptions\ChildNotFoundException
     */
    public function detectCharset(Options $options, string $defaultCharset, AbstractNode $root): bool;
}
