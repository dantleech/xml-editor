<?php

namespace Phpactor\XmlQuery\Bridge\TolerantParser\Tests\NodeEnhancer;

use Phpactor\XmlQuery\Bridge\TolerantParser\NodeEnhancer\NamespacedNameEnhancer;

class NamespacedNameEnhancerTest extends TolerantEnhancerTestCase
{
    public function testEnhancer()
    {
        $node = $this->enhance(new NamespacedNameEnhancer(), <<<'EOT'
<?php

namespace Foobar;

class Foobar
{
    }
EOT
        );
        $this->assertEquals('Foobar\Foobar', $node->find('//ClassDeclaration')->first()->attributes()->get('namespaced-name'));
    }
}
