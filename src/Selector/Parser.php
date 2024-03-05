<?php

declare(strict_types=1);

namespace Haphp\HtmlParser\Selector;

use Haphp\HtmlParser\DTO\Selector\RuleDTO;
use Haphp\HtmlParser\DTO\Selector\ParsedSelectorDTO;
use Haphp\HtmlParser\Contracts\Selector\ParserInterface;
use Haphp\HtmlParser\DTO\Selector\ParsedSelectorCollectionDTO;
use function trim;
use function count;
use function substr;
use function explode;
use function is_string;
use function strtolower;
use function preg_match_all;

/**
 * This is the default parser for the selector.
 */
class Parser implements ParserInterface
{
    /**
     * Pattern of CSS selectors, modified from 'mootools'.
     *
     * @var string
     */
    private string $pattern = "/([\w\-:*>]*)(?:#([\w\-]+)|\.([\w.\-]+))?(?:\[@?(!?[\w\-:]+)(?:([!*^$]?=)[\"']?(.*?)[\"']?)?])?([\/, ]+)/is";

    /**
     * Parses the selector string.
     */
    public function parseSelectorString(string $selector): ParsedSelectorCollectionDTO
    {
        $selectors = [];
        $matches = [];
        $rules = [];
        preg_match_all($this->pattern, trim($selector) . ' ', $matches, PREG_SET_ORDER);

        // skip tbody
        foreach ($matches as $match) {
            // default values
            $tag = strtolower(trim($match[1]));
            $operator = '=';
            $key = null;
            $value = null;
            $noKey = false;
            $alterNext = false;

            // check for elements that alter the behavior of the next element
            if ($tag == '>') {
                $alterNext = true;
            }

            // check for id selector
            if (!empty($match[2])) {
                $key = 'id';
                $value = $match[2];
            }

            // check for class selector
            if (!empty($match[3])) {
                $key = 'class';
                $value = explode('.', $match[3]);
            }

            // and final attribute selector
            if (!empty($match[4])) {
                $key = strtolower($match[4]);
            }
            if (!empty($match[5])) {
                $operator = $match[5];
            }
            if (!empty($match[6])) {
                $value = $match[6];
                if (str_contains($value, '][')) {
                    // we have multiple type selectors
                    $keys = [];
                    $keys[] = $key;
                    $key = $keys;
                    $parts = explode('][', $value);
                    $value = [];
                    foreach ($parts as $part) {
                        if (str_contains($part, '=')) {
                            list($first, $second) = explode('=', $part);
                            $key[] = $first;
                            $value[] = $second;
                        } else {
                            $value[] = $part;
                        }
                    }
                }
            }

            // check for elements that do not have a specified attribute
            if (is_string($key) && isset($key[0]) && $key[0] == '!') {
                $key = substr($key, 1);
                $noKey = true;
            }

            $rules[] = RuleDTO::makeFromPrimitives(
                $tag,
                $operator,
                $key,
                $value,
                $noKey,
                $alterNext
            );
            if (isset($match[7]) && is_string($match[7]) && trim($match[7]) == ',') {
                $selectors[] = ParsedSelectorDTO::makeFromRules($rules);
                $rules = [];
            }
        }

        // save last results
        if (count($rules) > 0) {
            $selectors[] = ParsedSelectorDTO::makeFromRules($rules);
        }

        return ParsedSelectorCollectionDTO::makeCollection($selectors);
    }
}
