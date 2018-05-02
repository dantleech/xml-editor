<?php

namespace Phpactor\XmlEditor\Core;

use ArrayIterator;
use Countable;
use DOMDocument;
use DOMNode;
use DOMNodeList;
use IteratorAggregate;
use Phpactor\XmlEditor\Core\Exception\IndexOutOfRange;
use Phpactor\XmlEditor\Core\Exception\RequiresAtLeastOneNode;

final class NodeList implements NodeLike, Countable, IteratorAggregate
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

        throw new RequiresAtLeastOneNode(
            'Node list "%s" is empty, cannot retrieve the first'
        );
    }

    public function last(): Node
    {
        if (count($this->nodes) === 0) {
            throw new RequiresAtLeastOneNode(
                'Node list "%s" is empty, cannot retrieve the first'
            );
        }

        return end($this->nodes);
    }

    public function child(int $index): Node
    {
        if (isset($this->nodes[$index])) {
            return $this->nodes[$index];
        }

        throw new IndexOutOfRange(sprintf(
            'Index "%s" does not exist, valid indexes: "%s"',
            $index, implode('", "', array_keys($this->nodes))
        ));
    }

    /**
     * @param string $selector XPath selector
     * @param mixed $value Value
     */
    public function filter(string $expression): NodeList
    {
        return new self(array_filter($this->nodes, function (Node $node) use ($expression) {
            return $node->evaluate($expression);

        }));
    }

    /**
     * @return NodeList
     */
    public function after($node): NodeLike
    {
        $newNode = Node::fromUnknown($node);
        return $this->apply(function (Node $node) use ($newNode) {
            $node->after($newNode);
        });
    }

    /**
     * @return NodeList
     */
    public function before($node): NodeLike
    {
        $newNode = Node::fromUnknown($node);
        return $this->apply(function (Node $node) use ($newNode) {
            $node->before($newNode);
        });
    }

    /**
     * @return NodeList
     */
    public function append($node): NodeLike
    {
        $newNode = Node::fromUnknown($node);
        return $this->apply(function (Node $node) use ($newNode) {
            $node->append($newNode);
        });
    }

    /**
     * @return NodeList
     */
    public function prepend($node): NodeLike
    {
        $newNode = Node::fromUnknown($node);
        return $this->apply(function (Node $node) use ($newNode) {
            $node->prepend($newNode);
        });
    }

    public function apply(callable $callable): NodeList
    {
        foreach ($this->nodes as $node) {
            $callable($node);
        }

        return $this;
    }

    public function find(string $xpathQuery): NodeList
    {
        $nodes = [];
        foreach ($this->nodes as $node) {
            foreach ($node->find($xpathQuery) as $newNode) {
                assert($newNode instanceof Node);
                $nodes[] = $newNode;
            }
        }

        return new self($nodes);
    }

    /**
     * @return NodeList
     */
    public function replace($newNode): NodeLike
    {
        $newNode = Node::fromUnknown($newNode);
        foreach ($this->nodes as &$node) {
            $node->replace($newNode);
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
        $this->apply(function (Node $node) {
            $node->clear();
        });

        return $this;
    }

    public function children(): NodeList
    {
        $children = [];
        foreach ($this->nodes as $node) {
            foreach ($node->children() as $child) {
                $children[] = $child;
            }
        }

        return new self($children);
    }

    public function dump(): string
    {
        $document = new \DOMDocument('1.0');
        foreach ($this->nodes as $node) {
            $node = $node->attachTo($document);
            $document->appendChild($node);
        }
        return $document->saveXML();
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->nodes);
    }
}
