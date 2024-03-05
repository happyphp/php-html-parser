<?php

declare(strict_types=1);

namespace Haphp\HtmlParser\DTO\Selector;

final class RuleDTO
{
    /**
     * @var string
     */
    private mixed $tag;

    /**
     * @var string
     */
    private mixed $operator;

    /**
     * @var string|array|null
     */
    private mixed $key;

    /**
     * @var string|array|null
     */
    private mixed $value;

    /**
     * @var bool
     */
    private mixed $noKey;

    /**
     * @var bool
     */
    private mixed $alterNext;

    private function __construct(array $values)
    {
        $this->tag = $values['tag'];
        $this->operator = $values['operator'];
        $this->key = $values['key'];
        $this->value = $values['value'];
        $this->noKey = $values['noKey'];
        $this->alterNext = $values['alterNext'];
    }

    /**
     * @param  string  $tag
     * @param  string  $operator
     * @param  array|string|null  $key
     * @param  array|string|null  $value
     * @param  bool  $noKey
     * @param  bool  $alterNext
     * @return RuleDTO
     */
    public static function makeFromPrimitives(string $tag, string $operator, array|string|null $key, array|string|null $value, bool $noKey, bool $alterNext): RuleDTO
    {
        return new RuleDTO([
            'tag'       => $tag,
            'operator'  => $operator,
            'key'       => $key,
            'value'     => $value,
            'noKey'     => $noKey,
            'alterNext' => $alterNext,
        ]);
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * @return string|array|null
     */
    public function getKey(): array|string|null
    {
        return $this->key;
    }

    /**
     * @return string|array|null
     */
    public function getValue(): array|string|null
    {
        return $this->value;
    }

    public function isNoKey(): bool
    {
        return $this->noKey;
    }

    public function isAlterNext(): bool
    {
        return $this->alterNext;
    }
}
