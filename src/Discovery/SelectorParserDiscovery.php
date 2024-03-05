<?php

declare(strict_types=1);

namespace Haphp\HtmlParser\Discovery;

use Haphp\HtmlParser\Selector\Parser;
use Haphp\HtmlParser\Contracts\Selector\ParserInterface;

class SelectorParserDiscovery
{
    private static ?ParserInterface $parser = null;

    public static function find(): ParserInterface
    {
        if (self::$parser == null) {
            self::$parser = new Parser();
        }

        return self::$parser;
    }
}
