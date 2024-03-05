<?php

declare(strict_types=1);

namespace Discovery;

use Selector\Parser;
use Contracts\Selector\ParserInterface;

class SelectorParserDiscovery
{
    /**
     * @var \Contracts\Selector\ParserInterface|null
     */
    private static $parser = null;

    public static function find(): ParserInterface
    {
        if (self::$parser == null) {
            self::$parser = new Parser();
        }

        return self::$parser;
    }
}
