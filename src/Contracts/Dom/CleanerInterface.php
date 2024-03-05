<?php

namespace Contracts\Dom;

use Options;
use Exceptions\LogicalException;

interface CleanerInterface
{
    /**
     * Cleans the html of any none-html information.
     *
     * @throws \Exceptions\LogicalException
     */
    public function clean(string $str, Options $options, string $defaultCharset): string;
}
