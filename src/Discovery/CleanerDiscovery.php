<?php

declare(strict_types=1);

namespace Haphp\HtmlParser\Discovery;

use Haphp\HtmlParser\Dom\Cleaner;
use Haphp\HtmlParser\Contracts\Dom\CleanerInterface;

class CleanerDiscovery
{
    private static ?Cleaner $parser = null;

    public static function find(): CleanerInterface
    {
        if (self::$parser == null) {
            self::$parser = new Cleaner();
        }

        return self::$parser;
    }
}
