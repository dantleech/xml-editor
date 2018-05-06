<?php

namespace Phpactor\XmlQuery\Tests\Unit\Helper;

use PHPUnit\Framework\TestCase;
use Phpactor\XmlQuery\Exception\InvalidQueryValue;
use Phpactor\XmlQuery\Exception\MissingQueryParameter;
use Phpactor\XmlQuery\Helper\XPathHelper;

class XPathHelperTest extends TestCase
{
    /**
     * @dataProvider provideParameterizesQuery
     */
    public function testParameterizesQueryWith(string $query, array $params, string $expectedQuery)
    {
        $result = XPathHelper::parameterizeQuery($query, $params);
        $this->assertEquals($expectedQuery, $result);
    }

    public function provideParameterizesQuery()
    {
        yield 'no params' => [
            '//hello',
            [],
            '//hello'
        ];

        yield 'single integer param' => [
            '//hello[@foo=?]',
            [1],
            '//hello[@foo=1]'
        ];

        yield 'single string param' => [
            '//hello[@foo=?]',
            ['1'],
            '//hello[@foo="1"]'
        ];

        yield 'ignores placeholder in single quotes' => [
            "//hello[@foo='?']",
            [],
            "//hello[@foo='?']"
        ];

        yield 'ignores placeholder in single quotes but uses subsequent ones' => [
            "//hello[@foo='?' and @bar=? and @baz='?' and @boo=?]",
            ['one', 'two'],
            "//hello[@foo='?' and @bar=\"one\" and @baz='?' and @boo=\"two\"]",
        ];

        yield 'multiple params' => [
            '//hello[@foo=? and @bar=?]',
            ['one', 'two'],
            '//hello[@foo="one" and @bar="two"]'
        ];
    }

    public function testThrowsExceptionIfParameterIsMissing()
    {
        $this->expectException(MissingQueryParameter::class);
        XPathHelper::parameterizeQuery('hello=?', []);
    }

    public function testThrowsExceptionIfValueNotSupported()
    {
        $this->expectException(InvalidQueryValue::class);
        XPathHelper::parameterizeQuery('hello=?', [ new \stdClass() ]);
    }


}
