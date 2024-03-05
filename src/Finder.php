<?php

declare(strict_types=1);

namespace Haphp\HtmlParser;

use Haphp\HtmlParser\Dom\Node\InnerNode;
use Haphp\HtmlParser\Dom\Node\AbstractNode;
use Haphp\HtmlParser\Exceptions\ChildNotFoundException;
use Haphp\HtmlParser\Exceptions\ParentNotFoundException;

class Finder
{
    private int $id;

    /**
     * Finder constructor.
     *
     * @param $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Find node in a tree by id.
     *
     * @param  AbstractNode  $node
     * @return bool|AbstractNode
     * @throws ChildNotFoundException
     * @throws ParentNotFoundException
     */
    public function find(AbstractNode $node): AbstractNode|bool
    {
        if (!$node->id() && $node instanceof InnerNode) {
            return $this->find($node->firstChild());
        }

        if ($node->id() == $this->id) {
            return $node;
        }

        if ($node->hasNextSibling()) {
            $nextSibling = $node->nextSibling();
            if ($nextSibling->id() == $this->id) {
                return $nextSibling;
            }
            if ($nextSibling->id() > $this->id && $node instanceof InnerNode) {
                return $this->find($node->firstChild());
            }
            if ($nextSibling->id() < $this->id) {
                return $this->find($nextSibling);
            }
        } elseif (!$node->isTextNode() && $node instanceof InnerNode) {
            return $this->find($node->firstChild());
        }

        return false;
    }
}
