<?php

declare(strict_types=1);

namespace Haphp\HtmlParser\Discovery;

use Haphp\HtmlParser\Selector\Seeker;
use Haphp\HtmlParser\Contracts\Selector\SeekerInterface;

class SeekerDiscovery
{
    private static ?SeekerInterface $seeker = null;

    public static function find(): SeekerInterface
    {
        if (self::$seeker == null) {
            self::$seeker = new Seeker();
        }

        return self::$seeker;
    }
}
