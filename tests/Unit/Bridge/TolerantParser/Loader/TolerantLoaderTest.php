<?php

namespace Phpactor\XmlEditor\Tests\Unit\Bridge\TolerantParser\Loader;

use Microsoft\PhpParser\Parser;
use PHPUnit\Framework\TestCase;
use Phpactor\XmlEditor\Bridge\TolerantParser\Loader\TolerantLoader;

class TolerantLoaderTest extends TestCase
{
    /**
     * Hello
     */
    public function testConvertsSourceToXml()
    {
        $source = file_get_contents(__FILE__);
        $loader = new TolerantLoader(new Parser());
        $xml = $loader->load($source);
        $xml->formatOutput = true;
        $xml->preserveWhiteSpace = true;
        $this->assertEquals($source, $xml->firstChild->nodeValue);
    }
}
