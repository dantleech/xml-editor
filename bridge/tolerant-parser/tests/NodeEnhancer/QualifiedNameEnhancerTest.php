<?php

namespace Phpactor\XmlQuery\Bridge\TolerantParser\Tests\NodeEnhancer;

use Phpactor\XmlQuery\Bridge\TolerantParser\NodeEnhancer\QualifiedNameEnhancer;

class QualifiedNameEnhancerTest extends TolerantEnhancerTestCase
{
    public function testEnhance()
    {
        $node = $this->enhance(new QualifiedNameEnhancer(), <<<'EOT'
<?php

UnqualifiedName;
\FullyQualifiedName;
Qualified\Name;
namespace\Relative\Name;
EOT
        );

        $names = $node->find('//QualifiedName');
        $this->assertEquals(
            'unqualified',
            $names->child(0)->attributes()->get('qualification')
        );
        $this->assertEquals(
            'full',
            $names->child(1)->attributes()->get('qualification')
        );
        $this->assertEquals(
            'qualified',
            $names->child(2)->attributes()->get('qualification')
        );
        $this->assertEquals(
            'relative',
            $names->child(3)->attributes()->get('qualification')
        );
    }

}
