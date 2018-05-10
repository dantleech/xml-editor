<?php

namespace Phpactor\XmlQuery\Bridge\TolerantParser\NodeEnhancer;

use DOMElement;
use Microsoft\PhpParser\NamespacedNameInterface;
use Microsoft\PhpParser\Node;
use Phpactor\XmlQuery\Bridge\TolerantParser\TolerantNodeEnhancer;

class NamespacedNameEnhancer implements TolerantNodeEnhancer
{
    public function enhance(Node $node, DOMElement $domElement): void
    {
        if (!$node instanceof NamespacedNameInterface) {
            return;
        }

        $domElement->setAttribute('namespaced-name', (string) $node->getNamespacedName());
    }
}
