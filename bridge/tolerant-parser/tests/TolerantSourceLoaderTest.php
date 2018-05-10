<?php

namespace Phpactor\XmlQuery\Bridge\TolerantParser\Tests;

use Microsoft\PhpParser\Parser;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeQuery\Bridge\TolerantParser\Loader\TolerantLoader;
use Phpactor\XmlQuery\Bridge\TolerantParser\TolerantSourceLoader;

class TolerantSourceLoaderTest extends TestCase
{
    public function testConvertsSourceToXml()
    {
        $source = file_get_contents(__DIR__ . '/data/example.php');
        $loader = new TolerantSourceLoader(new Parser());
        $xml = $loader->loadSource($source);
        $this->assertEquals($source, $xml->text());
    }
}
