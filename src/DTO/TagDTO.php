<?php

declare(strict_types=1);

namespace Haphp\HtmlParser\DTO;

use Haphp\HtmlParser\Dom\Node\HtmlNode;

final class TagDTO
{
    /**
     * @var bool
     */
    private mixed $status;

    /**
     * @var bool
     */
    private mixed $closing;

    /**
     * @var ?HtmlNode
     */
    private mixed $node;

    /**
     * @var ?string
     */
    private mixed $tag;

    private function __construct(array $values = [])
    {
        $this->status = $values['status'] ?? false;
        $this->closing = $values['closing'] ?? false;
        $this->node = $values['node'] ?? null;
        $this->tag = $values['tag'] ?? null;
    }

    public static function makeFromPrimitives(bool $status = false, bool $closing = false, ?HtmlNode $node = null, ?string $tag = null): TagDTO
    {
        return new TagDTO([
            'status'  => $status,
            'closing' => $closing,
            'node'    => $node,
            'tag'     => $tag,
        ]);
    }

    public function isStatus(): bool
    {
        return $this->status;
    }

    public function isClosing(): bool
    {
        return $this->closing;
    }

    /**
     * @return HtmlNode|null
     */
    public function getNode(): ?HtmlNode
    {
        return $this->node;
    }

    /**
     * @return string|null
     */
    public function getTag(): ?string
    {
        return $this->tag;
    }
}
