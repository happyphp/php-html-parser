<?php

namespace Haphp\HtmlParser\Contracts\Dom;

use Haphp\HtmlParser\Options;
use Haphp\HtmlParser\Exceptions\LogicalException;

interface CleanerInterface
{
    /**
     * Cleans the html of any none-html information.
     *
     * @throws LogicalException
     */
    public function clean(string $str, Options $options, string $defaultCharset): string;
}
