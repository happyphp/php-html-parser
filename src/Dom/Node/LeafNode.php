<?php

declare(strict_types=1);

namespace Dom\Node;

use Dom\Tag;

/**
 * Class LeafNode.
 *
 * @property-read string    $outerhtml
 * @property-read string    $innerhtml
 * @property-read string    $innerText
 * @property-read string    $text
 * @property-read Tag       $tag
 * @property-read InnerNode $parent
 */
abstract class LeafNode extends AbstractNode
{
}
