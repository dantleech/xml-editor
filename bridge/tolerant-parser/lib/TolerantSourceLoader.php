<?php

namespace Phpactor\XmlQuery\Bridge\TolerantParser;

use DOMDocument;
use DOMElement;
use Microsoft\PhpParser\NamespacedNameInterface;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\Token;
use Phpactor\CodeQuery\Core\Loader;
use Phpactor\XmlQuery\Node as QueryNode;
use Phpactor\XmlQuery\SourceLoader;
use RuntimeException;

class TolerantSourceLoader implements SourceLoader
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var TolerantNodeEnhancer[]
     */
    private $nodeEnhancers;

    public function __construct(array $nodeEnhancers = [], Parser $parser = null)
    {
        $this->parser = $parser ?: new Parser();
        $this->nodeEnhancers = $nodeEnhancers;
    }

    public function loadSource(string $source): QueryNode
    {
        $start = microtime(true);
        $node = $this->parser->parseSourceFile($source);
        $dom = new DOMDocument();
        $element = $dom->createElement('Ast');
        $dom->appendChild($element);

        $this->walk($node, $node, $element);
        $end = microtime(true) - $start;

        $node = QueryNode::fromUnknown($dom);
        $node->children()->first()->attributes()->set('parse-time', $end);
        return $node;
    }

    private function walk($node, Node $parentNode, DOMElement $element)
    {
        if ($node instanceof Node) {
            return $this->walkNode($node, $element);
        }
        if (null === $node) {
            return;
        }

        if (is_array($node)) {
            foreach ($node as $node) {
                $this->walk($node, $parentNode, $element);
            }
            return;
        }

        if ($node instanceof Token) {
            $this->walkToken($node, $parentNode , $element);
            return;
        }

        if (is_scalar($node)) {
            $element->setAttribute($nodeName, $node);
            return;
        }

        throw new RuntimeException(sprintf(
            'Do not know what to do with "%s"',
            is_object($node) ? get_class($node) : gettype($node)
        ));
    }

    private function walkNode(Node $node, DOMElement $element)
    {
        $cruft = $node->getLeadingCommentAndWhitespaceText();
        $cruft = $element->ownerDocument->createElement('preamble', $cruft);

        $newElement = $element->ownerDocument->createElement($node->getNodeKindName());
        $element->appendChild($newElement);

        foreach ($this->nodeEnhancers as $nodeEnhancer) {
            $nodeEnhancer->enhance($node, $newElement);
        }

        foreach ($node->getChildNames() as $childName) {
            $childElement = $newElement->appendChild($element->ownerDocument->createElement(ucfirst($childName)));
            $this->walk($node->$childName, $node, $childElement);
        }
    }

    private function walkToken(Token $node, Node $parentNode, DOMElement $element)
    {
        $whitespaceAndComments = $node->getLeadingCommentsAndWhitespaceText($parentNode->getFileContents());

        if ($whitespaceAndComments) {
            $whitespaceAndCommentsElement = $element->ownerDocument->createElement('Preamble');
            $whitespaceAndCommentsElement->nodeValue = $whitespaceAndComments;
            $element->appendChild($whitespaceAndCommentsElement);
        }

        $tokenElement = $element->ownerDocument->createElement('Token');

        foreach ($node as $attrName => $attrValue) {
            $tokenElement->setAttribute($attrName, $attrValue);
        }
        $tokenElement->setAttribute('kind', Token::getTokenKindNameFromValue($node->kind));

        $textNode = $element->ownerDocument->createTextNode($node->getText($parentNode->getFileContents()));
        $tokenElement->appendChild($textNode);
        $element->appendChild($tokenElement);
    }
}
