<?php

declare(strict_types=1);

namespace Discovery;

use Selector\Seeker;
use Contracts\Selector\SeekerInterface;

class SeekerDiscovery
{
    /**
     * @var SeekerInterface|null
     */
    private static $seeker = null;

    public static function find(): SeekerInterface
    {
        if (self::$seeker == null) {
            self::$seeker = new Seeker();
        }

        return self::$seeker;
    }
}
