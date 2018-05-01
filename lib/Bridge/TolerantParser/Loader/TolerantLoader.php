<?php

namespace Phpactor\XmlEditor\Bridge\TolerantParser\Loader;

use DOMDocument;
use DOMElement;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\Token;
use RuntimeException;

class TolerantLoader
{
    /**
     * @var Parser
     */
    private $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function load(string $source): DOMDocument
    {
        $node = $this->parser->parseSourceFile($source);
        $dom = new DOMDocument();
        $element = $dom->createElement('ast');
        $dom->appendChild($element);

        $this->walk($node, $node, $element);

        return $dom;
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

        foreach ($node->getChildNames() as $childName) {
            $this->walk($node->$childName, $node, $newElement);
        }
    }

    private function walkToken(Token $node, Node $parentNode, DOMElement $element)
    {
        $tokenElement = $element->ownerDocument->createElement('token');

        foreach ($node as $attrName => $attrValue) {
            $tokenElement->setAttribute($attrName, $attrValue);
        }
        $tokenElement->setAttribute('kind', Token::getTokenKindNameFromValue($node->kind));

        $textNode = $element->ownerDocument->createTextNode($node->getFullText($parentNode->getFileContents()));
        $tokenElement->appendChild($textNode);
        $element->appendChild($tokenElement);
    }
}
