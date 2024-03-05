<?php

declare(strict_types=1);

namespace Discovery;

use Dom\Parser;
use Contracts\Dom\ParserInterface;

class DomParserDiscovery
{
    /**
     * @var \Contracts\Dom\ParserInterface|null
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
