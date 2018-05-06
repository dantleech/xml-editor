<?php

namespace Phpactor\XmlQuery;

use ArrayIterator;
use Countable;
use DOMDocument;
use DOMNode;
use DOMNodeList;
use IteratorAggregate;
use Phpactor\XmlQuery\Exception\IndexOutOfRange;
use Phpactor\XmlQuery\Exception\RequiresAtLeastOneNode;
use Phpactor\XmlQuery\Node;
use Phpactor\XmlQuery\NodeLike;
use Phpactor\XmlQuery\NodeList;

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

    /**
     * @return NodeList<Node>
     */
    public static function fromDOMNodeList(DOMNodeList $nodeList): NodeList
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
     * @param string|Callable $expressionOrCallable
     * @return NodeList<Node>
     */
    public function filter($expressionOrCallable): NodeList
    {
        if (is_callable($expressionOrCallable)) {
            return new self(array_filter($this->nodes, $expressionOrCallable));
        }

        assert(is_string($expressionOrCallable));

        return new self(array_filter($this->nodes, function (Node $node) use ($expressionOrCallable) {
            return $node->evaluate($expressionOrCallable);

        }));
    }

    /**
     * @return NodeList<Node>
     */
    public function after($node): NodeLike
    {
        $newNode = Node::fromUnknown($node);
        return $this->apply(function (Node $node) use ($newNode) {
            $node->after($newNode);
        });
    }

    /**
     * @return NodeList<Node>
     */
    public function before($node): NodeLike
    {
        $newNode = Node::fromUnknown($node);
        return $this->apply(function (Node $node) use ($newNode) {
            $node->before($newNode);
        });
    }

    /**
     * @return NodeList<Node>
     */
    public function append($node): NodeLike
    {
        $newNode = Node::fromUnknown($node);
        return $this->apply(function (Node $node) use ($newNode) {
            $node->append($newNode);
        });
    }

    /**
     * @return NodeList<Node>
     */
    public function prepend($node): NodeLike
    {
        $newNode = Node::fromUnknown($node);
        return $this->apply(function (Node $node) use ($newNode) {
            $node->prepend($newNode);
        });
    }

    /**
     * @return NodeList<Node>
     */
    public function apply(callable $callable): NodeList
    {
        foreach ($this->nodes as $node) {
            $callable($node);
        }

        return $this;
    }

    /**
     * @return NodeList<Node>
     */
    public function find(string $xpathQuery, ...$params): NodeList
    {
        $nodes = [];
        foreach ($this->nodes as $node) {
            foreach ($node->find($xpathQuery, ...$params) as $newNode) {
                assert($newNode instanceof Node);
                $nodes[] = $newNode;
            }
        }

        return new self($nodes);
    }

    /**
     * @return NodeList<Node>
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
        foreach ($this->nodes as $node) {
            $node->remove();
        }
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

    /**
     * @return NodeList<Node>
     */
    public function clear(): NodeLike
    {
        $this->apply(function (Node $node) {
            $node->clear();
        });

        return $this;
    }

    /**
     * @return NodeList<Node>
     */
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

    public function dump(bool $pretty = false): string
    {
        $document = new \DOMDocument('1.0');

        if ($pretty) {
            $document->formatOutput = true;
            $document->preserveWhiteSpace = true;
        }

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
