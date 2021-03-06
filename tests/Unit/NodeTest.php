<?php

namespace Phpactor\XmlQuery\Tests\Unit;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use Phpactor\XmlQuery\Exception\InvalidNodeType;
use Phpactor\XmlQuery\Exception\NodeHasNoParent;
use Phpactor\XmlQuery\Exception\CannotReplaceRoot;
use Phpactor\XmlQuery\Node;
use RuntimeException;

class NodeTest extends TestCase
{
    public function testCanBeInstantiatedFromNodeDomNodeOrXmlString()
    {
        $node = Node::fromUnknown('<hello>Foobar</hello>');
        $this->assertEquals('Foobar', $node->text());

        $node = Node::fromUnknown(Node::fromXml('<hello>Foobar</hello>'));
        $this->assertEquals('Foobar', $node->text());

        $dom = new DOMDocument('1.0');
        $dom->loadXML('<hello>Foobar</hello>');

        $node = Node::fromUnknown($dom->firstChild);
        $this->assertEquals('Foobar', $node->text());
    }

    public function testThrowsExceptionIfItCannotCreateFromUnknown()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('be either a Node, an XML string or a DOMNode');
        Node::fromUnknown(new \stdClass());
    }

    public function testFind()
    {
        $node = Node::fromXml('<orders><order><product>Hello</product></order></orders>');
        $nodes = $node->find('//product');

        $this->assertCount(1, $nodes);
    }

    public function testReplace()
    {
        $node = Node::fromXml('<product>Hello</product>')->children()->first();
        $node = $node->replace('<product>Foobar</product>');

        $this->assertEquals('Foobar', $node->find('//product')->text());
        $this->assertEquals('product', $node->name());
    }

    public function testReplaceText()
    {
        $node = Node::fromXml('<product>Hello</product>')->children()->first();
        $nodes = $node->replaceText('Foobar');

        $this->assertContains('<product>Foobar</product>', $nodes->find('//product')->dump());
    }

    public function testThrowsExceptionIfTryingToReplaceDocument()
    {
        $this->expectException(CannotReplaceRoot::class);
        $node = Node::fromXml('<product>Hello</product>');
        $node->replace('<product>Foobar</product>');
    }

    public function testReturnsParent()
    {
        $node = Node::fromXml('<product><foobar>Hello</foobar></product>');
        $node = $node->find('//foobar')->first();

        $this->assertEquals('product', $node->parent()->name());
    }

    public function testThrowsExceptionIfGetParentCalledOnRoot()
    {
        $this->expectException(NodeHasNoParent::class);
        $node = Node::fromXml('<foo/>');
        $this->assertEquals('product', $node->parent()->parent()->name());
    }

    public function testReturnsName()
    {
        $node = Node::fromXml('<product/>')->children()->first();
        $this->assertEquals('product', $node->name());
    }

    public function testRemovesItself()
    {
        $products = Node::fromXmlFirstChild('<products><product/></products>');
        $this->assertCount(1, $products->children());
        $product = $products->children()->first()->remove();
        $this->assertCount(0, $products->children());
    }

    public function testReturnsTextContentFromDocument()
    {
        $text = Node::fromXml('<foobar>Hello</foobar>')->text();
        $this->assertEquals('Hello', $text);
    }

    public function testReturnsTextContentFromNode()
    {
        $text = Node::fromXmlFirstChild('<foobar>Hello</foobar>')->text();
        $this->assertEquals('Hello', $text);
    }

    public function testInsertsNodeBefore()
    {
        $node = Node::fromXmlFirstChild('<foobar>Hello</foobar>');
        $node = $node->before('<barbar>foo</barbar>');
        $this->assertEquals(<<<'EOT'
<?xml version="1.0"?>
<barbar>foo</barbar>
<foobar>Hello</foobar>

EOT
        , $node->root()->dump());
        $this->assertEquals('barbar', $node->name());
    }

    public function testInsertsNodeAfter()
    {
        $node = Node::fromXmlFirstChild('<foobar>Hello</foobar>');
        $node = $node->after('<barbar>foo</barbar>');
        $this->assertEquals(<<<'EOT'
<?xml version="1.0"?>
<foobar>Hello</foobar>
<barbar>foo</barbar>

EOT
        , $node->root()->dump());
        $this->assertEquals('barbar', $node->name());
    }

    public function testAppendsNodeAsLastChild()
    {
        $node = Node::fromXmlFirstChild('<foobar><barfoo/></foobar>');
        $node = $node->append('<barbar>foo</barbar>');
        $this->assertEquals(<<<'EOT'
<?xml version="1.0"?>
<foobar><barfoo/><barbar>foo</barbar></foobar>

EOT
        , $node->root()->dump());
        $this->assertEquals('barbar', $node->name());
    }

    public function testPrependsNodeAsFirstChild()
    {
        $node = Node::fromXmlFirstChild('<foobar><barfoo/></foobar>');
        $node = $node->prepend('<barbar>foo</barbar>');
        $this->assertEquals(<<<'EOT'
<?xml version="1.0"?>
<foobar><barbar>foo</barbar><barfoo/></foobar>

EOT
        , $node->root()->dump());
        $this->assertEquals('barbar', $node->name());
    }

    public function testPrependsNodeAsFirstChildWhenThereIsNoFirstChild()
    {
        $node = Node::fromXmlFirstChild('<foobar></foobar>');
        $node = $node->prepend('<barbar>foo</barbar>');
        $this->assertEquals(<<<'EOT'
<?xml version="1.0"?>
<foobar><barbar>foo</barbar></foobar>

EOT
        , $node->root()->dump());
        $this->assertEquals('barbar', $node->name());
    }

    public function testClearsAllChildrenNodes()
    {
        $node = Node::fromXmlFirstChild('<foobar><bar/><foo/></foobar>');
        $node = $node->clear();
        $this->assertEquals(<<<'EOT'
<?xml version="1.0"?>
<foobar/>

EOT
        , $node->root()->dump());
    }

    public function testReturnsRootNode()
    {
        $node = Node::fromXmlFirstChild('<foobar><bar/><foo/></foobar>');
        $root = $node->root();
        $this->assertEquals('#document', $root->name());
    }

    public function testReturnsRootNodeForRootNode()
    {
        $node = Node::fromXml('<foobar></foobar>');
        $root = $node->root();
        $this->assertEquals('#document', $root->name());
    }

    public function testDumpsXmlFromTheCurrentNode()
    {
        $node = Node::fromXmlFirstChild('<foobar><hello>HI</hello></foobar>');
        $this->assertEquals(<<<'EOT'
<?xml version="1.0"?>
<foobar><hello>HI</hello></foobar>

EOT
, $node->dump());
    }

    public function testPrettyPrintsXmlDump()
    {
        $node = Node::fromXmlFirstChild('<foobar><hello>HI</hello></foobar>');
        $this->assertEquals(<<<'EOT'
<?xml version="1.0"?>
<foobar>
  <hello>HI</hello>
</foobar>

EOT
, $node->dump(true));
    }

    public function testEvaluatesExpression()
    {
        $node = Node::fromXmlFirstChild('<foobar foo="bar"/>');
        $this->assertTrue($node->evaluate('./@foo="bar"'));
        $this->assertFalse($node->evaluate('./@foo="foo"'));
    }

    public function testReturnsAttributes()
    {
        $node = Node::fromXmlFirstChild('<foobar foobar="bar"/>');
        $attributes = $node->attributes();
        $this->assertEquals('bar', $attributes->get('foobar'));
    }

    public function testSetsAttributes()
    {
        $node = Node::fromXmlFirstChild('<foobar/>');
        $node->attributes()->set('bar', 'foobar');
        $this->assertEquals('foobar', $node->attributes()->get('bar'));
    }

    public function testThrowsExceptionWhenGettingAttributesOnInvalidNode()
    {
        $this->expectException(InvalidNodeType::class);
        $node = Node::fromXmlFirstChild('<foobar foobar="bar"/>');
        $node->root()->attributes();
    }
}
