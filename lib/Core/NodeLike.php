<?php

namespace Phpactor\XmlEditor\Core;

use Phpactor\XmlEditor\Core\NodeList;
use Phpactor\XmlEditor\Core\Node;
use DOMElement;

interface NodeLike
{
    public function find(string $xpathQuery): NodeList;

    public function remove(): void;

    /**
     * @return mixed
     */
    public function text(): string;

    /**
     * @param Node|string
     */
    public function replace($node): NodeLike;

    /**
     * @param Node|string
     */
    public function before($node): NodeLike;

    /**
     * @param Node|string
     */
    public function after($node): NodeLike;

    /**
     * @param Node|string
     */
    public function append($node): NodeLike;

    /**
     * @param Node|string
     */
    public function prepend($node): NodeLike;

    public function clear(): NodeLike;

    public function children(): NodeList;
}
