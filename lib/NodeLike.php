<?php

namespace Phpactor\XmlEditor;

use Phpactor\XmlEditor\NodeList;
use Phpactor\XmlEditor\Node;
use DOMElement;
use Phpactor\XmlEditor\NodeLike;

interface NodeLike
{
    public function find(string $xpathQuery): NodeList;

    public function remove(): void;

    public function text(): string;

    /**
     * @param Node|string $node
     */
    public function replace($node): NodeLike;

    /**
     * @param Node|string $node
     */
    public function before($node): NodeLike;

    /**
     * @param Node|string $node
     */
    public function after($node): NodeLike;

    /**
     * @param Node|string $node
     */
    public function append($node): NodeLike;

    /**
     * @param Node|string $node
     */
    public function prepend($node): NodeLike;

    public function clear(): NodeLike;

    public function children(): NodeList;
}
