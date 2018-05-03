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

    /**
     * @dataProvider provideFiltersNodesMatchingExpression
     */
    public function testFiltersNodesMatchingExpression(string $expression, int $expectedCount)
    {
        $nodeList = $this->createList('<foobar foo="bar"/><barfoo bar="baz"/><foobar foo="bar"/><foobar foo="baz"/>');
        $nodeList = $nodeList->filter($expression);
        $this->assertCount($expectedCount, $nodeList);
    }

    public function provideFiltersNodesMatchingExpression()
    {
        yield 'attribute only' => [
            '@foo="bar"',
            2
        ];

        yield 'relative attribute only' => [
            './@foo="bar"',
            2
        ];

        yield 'relative attribute only' => [
            'name()="foobar" and @foo="bar"',
            2
        ];
    }

    public function testInsertsAfterAllNodes()
    {
        $nodeList = $this->createList('<one/><two/><three/>');
        $nodeList = $nodeList->after('<potato/>');
        $this->assertEquals(<<<'EOT'
<?xml version="1.0"?>
<list><one/><potato/><two/><potato/><three/><potato/></list>

EOT
        , $nodeList->first()->root()->dump());
    }

    public function testInsertsBeforeAllNodes()
    {
        $nodeList = $this->createList('<one/><two/><three/>');
        $nodeList = $nodeList->before('<potato/>');
        $this->assertEquals(<<<'EOT'
<?xml version="1.0"?>
<list><potato/><one/><potato/><two/><potato/><three/></list>

EOT
        , $nodeList->first()->root()->dump());
    }

    public function testAppendsToAllNodes()
    {
        $nodeList = $this->createList('<one/><two/>');
        $nodeList = $nodeList->append('<potato/>');
        $this->assertEquals(<<<'EOT'
<?xml version="1.0"?>
<list><one><potato/></one><two><potato/></two></list>

EOT
        , $nodeList->first()->root()->dump());
    }

    public function testPrependsToAllNodes()
    {
        $nodeList = $this->createList('<one><hot/></one><two><cold/></two>');
        $nodeList = $nodeList->prepend('<potato/>');
        $this->assertEquals(<<<'EOT'
<?xml version="1.0"?>
<list><one><potato/><hot/></one><two><potato/><cold/></two></list>

EOT
        , $nodeList->first()->root()->dump());
    }

    public function testAggregatesFindFromAllNodes()
    {
        $nodeList = $this->createList('<one><potato/></one><two><potato/></two><three><cabbage/></three>');
        $nodeList = $nodeList->find('.//potato');
        $this->assertCount(2, $nodeList);
    }

    public function testReturnsTextContentOfAllNodes()
    {
        $nodeList = $this->createList('<one>foobar</one><two>barfoo</two>');
        $this->assertEquals('foobarbarfoo', $nodeList->text());
    }

    public function testClearsChildNodesOfAllNodes()
    {
        $nodeList = $this->createList('<one><bar/></one><two><bar/></two>');
        $nodeList->clear();
        $this->assertEquals(<<<'EOT'
<?xml version="1.0"?>
<list><one/><two/></list>

EOT
        , $nodeList->first()->root()->dump());
    }

    public function testReturnsAggregateChildrenOfAllNodes()
    {
        $nodeList = $this->createList('<one><bar/></one><two><bar/></two>');
        $nodeList = $nodeList->children();
        $this->assertEquals(<<<'EOT'
<?xml version="1.0"?>
<bar/>
<bar/>

EOT
        , $nodeList->dump());
    }

    public function testRepacesAllNodes()
    {
        $nodeList = $this->createList('<one><bar/></one><two><bar/></two>');
        $nodeList = $nodeList->replace('<haha/>');
        $this->assertEquals(<<<'EOT'
<?xml version="1.0"?>
<haha/>
<haha/>

EOT
        , $nodeList->dump());
    }


    private function createList(string $string): NodeList
    {
        return Node::fromXmlFirstChild('<list>' . $string . '</list>')->children();
    }
}
