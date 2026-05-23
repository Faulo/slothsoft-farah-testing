<?php
declare(strict_types = 1);

namespace Slothsoft\FarahTesting\Module;

use DOMDocument;
use Ds\Set;
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\TestCase;

/**
 * LinkCrawlerTest
 *
 * @see LinkCrawler
 */
class LinkCrawlerTest extends TestCase {
    
    /**
     * @test
     */
    public function testClassExists(): void {
        $this->assertTrue(class_exists(LinkCrawler::class), "Failed to load class 'Slothsoft\Farah\ModuleTests\LinkCrawler'!");
    }
    
    /**
     * @test
     * @dataProvider provideExamples
     */
    public function test_crawl(string $xml, array $expected): void {
        $document = new DOMDocument();
        $document->loadXML($xml);
        
        $sut = new LinkCrawler();
        
        $actual = [];
        foreach ($sut->crawlDocument($document) as $key => $value) {
            $actual[$key] = $value;
        }
        
        $this->assertThat($actual, new IsEqual($expected));
    }
    
    /**
     * @test
     */
    public function test_crawl_skipsWhitelistedLinks(): void {
        $document = new DOMDocument();
        $document->loadXML(<<<EOT
<html xmlns="http://www.w3.org/1999/xhtml" lang="">
    <a href="/known"/>
    <a href="/unknown"/>
</html>
EOT
        );
        
        $sut = new LinkCrawler(new Set([
            '/known'
        ]));
        
        $actual = [];
        foreach ($sut->crawlDocument($document) as $key => $value) {
            $actual[$key] = $value;
        }
        
        $this->assertThat($actual, new IsEqual([
            "a href '/unknown'" => '/unknown'
        ]));
    }
    
    public function provideExamples(): iterable {
        yield 'html a' => [
            <<<EOT
<a xmlns="http://www.w3.org/1999/xhtml" href="test"/>
EOT,
            [
                "a href 'test'" => 'test'
            ]
        ];
        
        yield 'xsl link' => [
            <<<EOT
<include xmlns="http://www.w3.org/1999/XSL/Transform" href="test"/>
EOT,
            [
                "include href 'test'" => 'test'
            ]
        ];
        
        yield 'xsd link' => [
            <<<EOT
<include xmlns="http://www.w3.org/2001/XMLSchema" schemaLocation="test"/>
EOT,
            [
                "include schemaLocation 'test'" => 'test'
            ]
        ];
        
        yield 'deep link' => [
            <<<EOT
<html xmlns="http://www.w3.org/1999/xhtml"><body><a href="test"/></body></html>
EOT,
            [
                "a href 'test'" => 'test'
            ]
        ];
        
        yield 'template link' => [
            <<<EOT
<html xmlns="http://www.w3.org/1999/xhtml"><template><xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><a href="test"/></xsl:stylesheet></template></html>
EOT,
            []
        ];
        
        yield 'svg links' => [
            <<<EOT
<svg xmlns="http://www.w3.org/2000/svg">
    <a href="/a"/>
    <use href="/use"/>
    <script href="/script"/>
    <image href="/image"/>
    <feImage href="/feImage"/>
</svg>
EOT,
            [
                "a href '/a'" => '/a',
                "use href '/use'" => '/use',
                "script href '/script'" => '/script',
                "image href '/image'" => '/image',
                "feImage href '/feImage'" => '/feImage'
            ]
        ];
        
        yield 'svg other-namespace links' => [
            <<<EOT
<svg xmlns="http://www.w3.org/2000/svg">
    <link xmlns="http://www.w3.org/1999/xhtml" href="/link"/>
</svg>
EOT,
            [
                "link href '/link'" => '/link'
            ]
        ];
        
        yield 'data attribute fallback' => [
            <<<EOT
<html xmlns="http://www.w3.org/1999/xhtml">
    <a data-href="/fallback"/>
    <img data-src="/image" alt=""/>
</html>
EOT,
            [
                "a href '/fallback'" => '/fallback',
                "img src '/image'" => '/image'
            ]
        ];
        
        yield 'ignore dictionary replacement links' => [
            <<<EOT
<html xmlns="http://www.w3.org/1999/xhtml">
    <a href="/ignored" data-dict-replace="href"/>
    <a href="/included"/>
</html>
EOT,
            [
                "a href '/included'" => '/included'
            ]
        ];
        
        yield 'skip empty optional links' => [
            <<<EOT
<html xmlns="http://www.w3.org/1999/xhtml">
    <script src=""/>
    <video src=""/>
    <form action=""/>
</html>
EOT,
            []
        ];
        
        yield 'report empty required links' => [
            <<<EOT
<html xmlns="http://www.w3.org/1999/xhtml">
    <a href=""/>
    <img src="" alt=""/>
</html>
EOT,
            [
                "a href ''" => '',
                "img src ''" => ''
            ]
        ];
        
        yield 'xinclude links are crawled in other namespaces' => [
            <<<EOT
<data xmlns:xi="http://www.w3.org/2001/XInclude">
    <xi:include href="/include"/>
</data>
EOT,
            [
                "include href '/include'" => '/include'
            ]
        ];
    }
}
