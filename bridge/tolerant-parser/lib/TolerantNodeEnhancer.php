<?php

namespace Phpactor\XmlQuery\Bridge\TolerantParser;

use DOMElement;
use Microsoft\PhpParser\Node;

interface TolerantNodeEnhancer
{
    public function enhance(Node $node, DOMElement $domElement);
}
