<?php

declare(strict_types=1);

namespace Discovery;

use Dom\Cleaner;
use Contracts\Dom\CleanerInterface;

class CleanerDiscovery
{
    /**
     * @var Cleaner|null
     */
    private static $parser = null;

    public static function find(): CleanerInterface
    {
        if (self::$parser == null) {
            self::$parser = new Cleaner();
        }

        return self::$parser;
    }
}
