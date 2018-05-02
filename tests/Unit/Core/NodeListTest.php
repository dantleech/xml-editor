<?php

namespace Phpactor\XmlEditor\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use Phpactor\XmlEditor\Core\Exception\IndexOutOfRange;
use Phpactor\XmlEditor\Core\Exception\RequiresAtLeastOneNode;
use Phpactor\XmlEditor\Core\Node;
use Phpactor\XmlEditor\Core\NodeList;

class NodeListTest extends TestCase
{
    public function testReturnsFirstNode()
    {
        $list = $this->createList('<foobar/><barfoo/>');
        $node = $list->first();
        $this->assertEquals('foobar', $node->name());
    }

    public function testThrowsExceptionIfNoFirstNode()
    {
        $this->expectException(RequiresAtLeastOneNode::class);
        $list = $this->createList('');
        $list->first();
    }

    public function testReturnsLastNode()
    {
        $list = $this->createList('<foobar/><barfoo/>');
        $node = $list->last();
        $this->assertEquals('barfoo', $node->name());
    }

    public function testThrowsExceptionIfNoLastNode()
    {
        $this->expectException(RequiresAtLeastOneNode::class);
        $list = $this->createList('');
        $list->last();
    }

    public function testReturnsChildAtIndex()
    {
        $list = $this->createList('<foobar/><barfoo/>');
        $node = $list->child(1);
        $this->assertEquals('barfoo', $node->name());
    }

    public function testThrowsExceptionIfChildAtIndexIsOutOfRange()
    {
        $this->expectException(IndexOutOfRange::class);
        $list = $this->createList('<foobar/><barfoo/>');
        $node = $list->child(3);
    }


    private function createList(string $string): NodeList
    {
        return Node::fromXmlFirstChild('<list>' . $string . '</list>')->children();
    }
}
