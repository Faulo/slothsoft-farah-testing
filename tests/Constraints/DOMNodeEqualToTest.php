<?php
declare(strict_types = 1);
namespace Slothsoft\FarahTesting\Constraints;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use DOMDocument;
use PHPUnit\Framework\Constraint\IsEqual;
use Slothsoft\Core\DOMHelper;

/**
 * DOMNodeEqualToTest
 *
 * @see DOMNodeEqualTo
 */
final class DOMNodeEqualToTest extends TestCase {
    
    /**
     *
     * @dataProvider provideStringifiedXml
     */
    public function test_stringify(string $xml, string $expected): void {
        $dom = new DOMHelper();
        $node = $dom->parse($xml);
        
        $actual = DOMNodeEqualTo::stringify($node);
        
        self::assertThat($actual, new IsEqual($expected));
    }
    
    public static function provideStringifiedXml(): iterable {
        yield 'simple element' => [
            '<data a="b"><child c="d"/></data>',
            <<<EOT
<data
 a="b"
 >
 <child
  c="d"
  >
EOT
        ];
        
        yield 'namespaced element' => [
            '<x:data y:a="b" xmlns:x="urn:foo" xmlns:y="urn:bar"/>',
            <<<EOT
<urn:foo data
 urn:bar a="b"
 >
EOT
        ];
        
        yield 'text content' => [
            '<data>  a  b  c </data>',
            <<<EOT
<data
 >
 "a b c"
EOT
        ];
        
        yield 'multiple text nodes' => [
            <<<EOT
<data>
    abc
    <![CDATA[
d  e  f
]]>
    ghi
</data>
EOT,
            <<<EOT
<data
 >
 "abc d e f ghi"
EOT
        ];
        
        yield 'processing-instruction' => [
            <<<EOT
<?xml-stylesheet href="test.css"?>
<data><?php echo 'hello'; ?></data>
EOT,
            <<<EOT
<?xml-stylesheet href="test.css"?>
<data
 >
 <?php echo 'hello'; ?>
EOT
        ];
        
        yield 'entity content' => [
            '<data>&lt;&amp;&#x20;&amp;&gt;</data>',
            <<<EOT
<data
 >
 "<& &>"
EOT
        ];
    }
    
    /**
     *
     * @dataProvider provideXmlComparisons
     */
    public function test_matches(string $expectedXml, string $actualXml, ?string $expectedFailure = null): void {
        $expectedDoc = new DOMDocument();
        $expectedDoc->loadXML($expectedXml);
        $actualDoc = new DOMDocument();
        $actualDoc->loadXML($actualXml);
        
        $sut = new DOMNodeEqualTo($expectedDoc);
        
        if ($expectedFailure) {
            $this->expectException(ExpectationFailedException::class);
            $this->expectExceptionMessage($expectedFailure);
        }
        
        self::assertThat($actualDoc, $sut);
    }
    
    public static function provideXmlComparisons(): iterable {
        yield 'identical simple element' => [
            '<data a="b"/>',
            '<data a="b"/>'
        ];
        
        yield 'different namespace but same prefix ignored' => [
            '<x:data xmlns:x="urn:foo"/>',
            '<y:data xmlns:y="urn:foo"/>'
        ];
        
        yield 'missing attribute' => [
            '<data a="b"/>',
            '<data/>',
            <<<EOT
- a="b"
EOT
        ];
        
        yield 'extra attribute' => [
            '<data/>',
            '<data a="b"/>',
            <<<EOT
+ a="b"
EOT
        ];
        
        yield 'different attribute value' => [
            '<data a="b"/>',
            '<data a="c"/>',
            <<<EOT
- a="b"
+ a="c"
EOT
        ];
        
        yield 'different attribute order is irrelevant' => [
            '<data a="b" b="c" c="d"/>',
            '<data c="d" b="c" a="b"/>'
        ];
        
        yield 'different namespace but same prefix for attribute is ignored' => [
            '<data xmlns:x="urn:foo" x:a="b"/>',
            '<data xmlns:y="urn:foo" y:a="b"/>'
        ];
        
        yield 'multiple attributes in different namespaces are allowed' => [
            '<data xmlns:x="urn:foo" xmlns:y="urn:bar" x:a="2" y:a="3" a="1"/>',
            '<data/>',
            <<<EOT
- a="1"
- urn:bar a="3"
- urn:foo a="2"
EOT
        ];
        
        yield 'different namespace URI' => [
            '<x:data xmlns:x="urn:foo"/>',
            '<y:data xmlns:y="urn:bar"/>',
            <<<EOT
-<urn:foo data
+<urn:bar data
EOT
        ];
        
        yield 'ignores xmlns declaration difference' => [
            '<data xmlns:x="urn:foo"/>',
            '<data xmlns:y="urn:bar"/>'
        ];
        
        yield 'normalize-space text equality' => [
            '<data> hello   world </data>',
            '<data>hello world</data>'
        ];
        
        yield 'missing text' => [
            '<data>foo</data>',
            '<data></data>',
            <<<EOT
- "foo"
EOT
        ];
        
        yield 'extra text' => [
            '<data></data>',
            '<data>bar</data>',
            <<<EOT
+ "bar"
EOT
        ];
        
        yield 'cdata is identical to text' => [
            '<data> t e x t </data>',
            '<data><![CDATA[t  e  x  t]]></data>'
        ];
        
        yield 'ignores empty text nodes' => [
            '<root><data>foo</data></root>',
            '<root>   <data>foo</data>   </root>'
        ];
        
        yield 'different child elements causes failure' => [
            '<root><a/></root>',
            '<root><b/></root>',
            <<<EOT
- <a
+ <b
EOT
        ];
    }
}
