<?php

namespace Phpactor\XmlEditor\Core;

use Countable;
use DOMNode;
use DOMNodeList;

final class NodeList implements NodeLike, Countable
{
    /**
     * @var Node[]
     */
    private $nodes;

    public function __construct(array $nodes)
    {
        $this->nodes = $nodes;
    }

    public static function fromDOMNodeList(DOMNodeList $nodeList)
    {
        return new self(array_map(function (DOMNode $node) {
            return Node::fromDOMNode($node);
        }, iterator_to_array($nodeList)));
    }

    public function first(): Node
    {
        foreach ($this->nodes as $node) {
            return $node;
        }
    }

    public function last(): Node
    {
    }

    public function child(int $index): Node
    {
    }

    /**
     * @param string $selector XPath selector
     * @param mixed $value Value
     */
    public function equals(string $selector, $value): NodeList
    {
    }

    /**
     * @param string $selector XPath selector
     * @param mixed $value Value
     */
    public function greaterThan(string $selector, $value): NodeList
    {
    }

    /**
     * @param string $selector XPath selector
     * @param mixed $value Value
     */
    public function greaterThanOrEqual(string $selector, $value): NodeList
    {
    }

    /**
     * @param string $selector XPath selector
     * @param mixed $value Value
     */
    public function lessThan(string $selector, $value): NodeList
    {
    }

    /**
     * @param string $selector XPath selector
     * @param mixed $value Value
     */
    public function lessThanOrEqual(string $selector, $value): NodeList
    {
    }

    /**
     * @param string $selector XPath selector
     * @param mixed $value Value
     */
    public function startsWith(string $selector, $value): NodeList
    {
    }

    /**
     * @param string $selector XPath selector
     * @param mixed $value Value
     */
    public function endsWith(string $selector, $value): NodeList
    {
    }

    /**
     * @param string $selector XPath selector
     * @param mixed $value Value
     */
    public function containing(string $selector, $value): NodeList
    {
    }

    /**
     * @return NodeList
     */
    public function after($node): NodeLike
    {
    }

    /**
     * @return NodeList
     */
    public function before($node): NodeLike
    {
    }

    /**
     * @return NodeList
     */
    public function append($node): NodeLike
    {
    }

    /**
     * @return NodeList
     */
    public function prepend($node): NodeLike
    {
    }

    public function find(string $xpathQuery): NodeList
    {
        $nodes = [];
        foreach ($this->nodes as $node) {
            foreach ($node->select($xpathQuery) as $newNode) {
                $nodes[] = $newNode;
            }
        }

        return new self($nodes);
    }

    public function nodeValue()
    {
        $values = [];
        foreach ($this->nodes as $node) {
            $values[] = $node->nodeValue();
        }

        return implode('', $values);
    }

    /**
     * @return NodeList
     */
    public function replace($node): NodeLike
    {
        foreach ($this->nodes as &$node) {
            $node = $node->replace($node);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @return int
     */
    public function count()
    {
        return count($this->nodes);
    }

    public function parents(): NodeList
    {
    }

    public function remove(): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function text(): string
    {
        $value = [];

        foreach ($this->nodes as $node) {
            $value[] = $node->text();
        }

        return implode('', $value);
    }

    public function clear(): NodeLike
    {
    }

    public function __toString()
    {
        return implode(PHP_EOL, array_map(function (Node $node) {
            return $node->__toString();
        }, $this->nodes));
    }

    public function children(): NodeList
    {
    }
}
