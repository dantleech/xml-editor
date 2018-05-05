<?php

namespace Phpactor\XmlQuery;

use DOMElement;
use DOMNamedNodeMap;

class Attributes
{
    /**
     * @var DOMElement
     */
    private $node;

    public function __construct(DOMElement $node)
    {
        $this->node = $node;
    }

    public function get(string $name)
    {
        return $this->node->getAttribute($name);
    }

    public function set(string $name, $value)
    {
        $this->node->setAttribute($name, $value);

        return $this;
    }
}
