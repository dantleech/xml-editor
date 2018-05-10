<?php

namespace Phpactor\XmlQuery\Bridge\TolerantParser\NodeEnhancer;

use DOMElement;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\QualifiedName;
use Phpactor\XmlQuery\Bridge\TolerantParser\TolerantNodeEnhancer;

class QualifiedNameEnhancer implements TolerantNodeEnhancer
{
    public function enhance(Node $node, DOMElement $domElement): void
    {
        if (!$node instanceof QualifiedName) {
            return;
        }

        $qualification = [];
        if ($node->isFullyQualifiedName()) {
            $qualification[] = 'full';
        }

        if ($node->isQualifiedName()) {
            $qualification[] = 'qualified';
        }

        if ($node->isRelativeName()) {
            $qualification[] = 'relative';
        }

        if ($node->isUnqualifiedName()) {
            $qualification[] = 'unqualified';
        }

        $domElement->setAttribute('qualification', implode(',', $qualification));

    }
}
