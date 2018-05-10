<?php

namespace Phpactor\XmlQuery\Bridge\TolerantParser\Tests\NodeEnhancer;

use PHPUnit\Framework\TestCase;
use Phpactor\XmlQuery\Bridge\TolerantParser\TolerantNodeEnhancer;
use Phpactor\XmlQuery\Bridge\TolerantParser\TolerantSourceLoader;
use Phpactor\XmlQuery\Node;

class TolerantEnhancerTestCase extends TestCase
{
    protected function enhance(TolerantNodeEnhancer $enhancer, string $source): Node
    {
        $tolerantLoader = new TolerantSourceLoader([ $enhancer ]);
        return $tolerantLoader->loadSource($source);
    }
}
