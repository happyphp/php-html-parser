<?php

declare(strict_types=1);

namespace Haphp\HtmlParser\Discovery;

use Haphp\HtmlParser\Dom\Parser;
use Haphp\HtmlParser\Contracts\Dom\ParserInterface;

class DomParserDiscovery
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
