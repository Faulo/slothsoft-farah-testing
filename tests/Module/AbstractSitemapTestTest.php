<?php
declare(strict_types = 1);
namespace Slothsoft\FarahTesting\Module;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\Writable\Delegates\DOMWriterFromDocumentDelegate;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\Exception\EmptyTransformationException;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy\ExecutableBuilderStrategyInterface;
use Slothsoft\Farah\Module\DOMWriter\DOMDocumentDOMWriter;
use Slothsoft\Farah\Module\Executable\Executable;
use Slothsoft\Farah\Module\Executable\ExecutableStrategies;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\DOMWriterResultBuilder;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\NullResultBuilder;
use Slothsoft\Farah\Module\Manifest\Manifest;
use Slothsoft\Farah\Module\Manifest\ManifestInterface;
use Slothsoft\Farah\Module\Manifest\ManifestStrategies;
use Slothsoft\Farah\Module\Manifest\AssetBuilderStrategy\DefaultAssetBuilder;
use Slothsoft\Farah\Module\Manifest\TreeLoaderStrategy\TreeLoaderStrategyInterface;
use Slothsoft\Farah\Sites\Domain;
use DOMDocument;

/**
 * AbstractSitemapTestTest
 *
 * @see AbstractSitemapTest
 */
class AbstractSitemapTestTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(AbstractSitemapTest::class), "Failed to load class 'Slothsoft\Farah\ModuleTests\AbstractSitemapTest'!");
    }
    
    private DOMDocument $sitesDocument;
    
    private function createSuT(): AbstractSitemapTest {
        $siteXML = <<<EOT
        <?xml version="1.0" encoding="UTF-8"?>
        <domain xmlns="http://schema.slothsoft.net/farah/sitemap" xmlns:sfd="http://schema.slothsoft.net/farah/dictionary" name="test.slothsoft.net" vendor="slothsoft" module="test"
        	ref="domain-asset" status-active="" status-public="" sfd:languages="de-de en-us" version="1.1">        
        	<page name="test-page" ref="page-asset">
        		<file name="test-file" ref="file-asset" />
        	</page>
        </domain>
        EOT;
        
        $this->sitesDocument = new DOMDocument();
        $this->sitesDocument->loadXML($siteXML);
        
        $sitesAsset = $this->createStub(AssetInterface::class);
        Kernel::setCurrentSitemap($sitesAsset);
        
        $sitesBuilder = new DOMWriterResultBuilder(new DOMDocumentDOMWriter($this->sitesDocument));
        $sitesExecutable = new Executable($sitesAsset, FarahUrlArguments::createEmpty(), new ExecutableStrategies($sitesBuilder));
        
        $sitesAsset->method('lookupExecutable')->willReturn($sitesExecutable);
        
        StubSitemapTest::$sitesAsset = $sitesAsset;
        
        TestCache::instance(StubSitemapTest::class)->clear();
        
        $treeLoader = $this->createStub(TreeLoaderStrategyInterface::class);
        $treeLoader->method('loadTree')->willReturnCallback(function (ManifestInterface $context): LeanElement {
            $root = LeanElement::createOneFromArray('assets', [], [
                LeanElement::createOneFromArray('custom-asset', [
                    Manifest::ATTR_NAME => 'sitemap',
                    Manifest::ATTR_EXECUTABLE_BUILDER => StubExecutableBuilder::class
                ]),
                LeanElement::createOneFromArray('custom-asset', [
                    Manifest::ATTR_NAME => 'domain-asset',
                    Manifest::ATTR_EXECUTABLE_BUILDER => StubExecutableBuilder::class
                ]),
                LeanElement::createOneFromArray('fragment', [
                    Manifest::ATTR_NAME => 'page-asset',
                    Manifest::ATTR_EXECUTABLE_BUILDER => StubExecutableBuilder::class
                ]),
                LeanElement::createOneFromArray('fragment', [
                    Manifest::ATTR_NAME => 'file-asset',
                    Manifest::ATTR_EXECUTABLE_BUILDER => StubExecutableBuilder::class
                ])
            ]);
            $context->normalizeManifestTree($root);
            return $root;
        });
        
        $module = FarahUrlAuthority::createFromVendorAndModule('slothsoft', 'test');
        
        $assetBuilder = new DefaultAssetBuilder($module);
        
        $manifest = new ManifestStrategies($treeLoader, $assetBuilder);
        
        Module::register($module, '.', $manifest);
        
        return new StubSitemapTest();
    }
    
    /**
     *
     * @runInSeparateProcess
     * @dataProvider pageNodeProvider
     */
    public function test_testPageMustHaveOneOfRefOrRedirectOrExt(string $xml, bool $isValid): void {
        $dom = new DOMHelper();
        $fragment = $dom->parse($xml);
        $pageNode = $fragment->firstChild;
        
        $sut = $this->createSuT();
        
        if (! $isValid) {
            $this->expectException(AssertionFailedError::class);
        }
        
        $sut->testPageMustHaveOneOfRefOrRedirectOrExt($pageNode);
    }
    
    public function pageNodeProvider(): iterable {
        yield 'page ref pass' => [
            '<page ref="/" />',
            true
        ];
        yield 'page redirect pass' => [
            '<page redirect="/" />',
            true
        ];
        yield 'page ext pass' => [
            '<page ext="/" />',
            true
        ];
        yield 'empty page raises error' => [
            '<page />',
            false
        ];
        yield 'page ref redirect raise error' => [
            '<page ref="/" redirect="/" />',
            false
        ];
        yield 'page ref ext raise error' => [
            '<page ref="/" ext="/" />',
            false
        ];
        yield 'page ext redirect raise error' => [
            '<page ext="/" redirect="/" />',
            false
        ];
    }
    
    /**
     *
     * @runInSeparateProcess
     */
    public function test_getSitesDocument() {
        $sut = $this->createSuT();
        
        $this->assertEquals($this->sitesDocument, $sut->getSitesDocumentProtected());
    }
    
    /**
     *
     * @runInSeparateProcess
     */
    public function test_pageNodeProvider() {
        $sut = $this->createSuT();
        
        $actual = $sut->pageNodeProvider();
        
        $this->assertEquals([
            '/',
            '/test-page/',
            '/test-page/test-file'
        ], array_keys($actual));
    }
    
    /**
     *
     * @runInSeparateProcess
     * @dataProvider pageAssetAndLinkProvider
     */
    public function test_pageLinkProvider(string $assetPath, string $assetXML, array $assetLinks) {
        $pageDocument = new DOMDocument();
        $pageDocument->loadXML($assetXML);
        StubExecutableBuilder::$executables[$assetPath] = new DOMWriterResultBuilder(new DOMDocumentDOMWriter($pageDocument));
        
        $sut = $this->createSuT();
        
        $actual = $sut->pageLinkProvider();
        
        $this->assertEquals($assetLinks, $actual);
    }
    
    /**
     *
     * @runInSeparateProcess
     */
    public function test_pageLinkProvider_canWorkWithEmptyDocuments() {
        StubExecutableBuilder::$executables['/domain-asset'] = new DOMWriterResultBuilder(new DOMWriterFromDocumentDelegate(function (): DOMDocument {
            throw new EmptyTransformationException('/domain-asset');
        }));
        StubExecutableBuilder::$executables['/page-asset'] = new DOMWriterResultBuilder(new DOMWriterFromDocumentDelegate(function (): DOMDocument {
            return new DOMDocument();
        }));
        
        $sut = $this->createSuT();
        
        $actual = $sut->pageLinkProvider();
        
        $this->assertEquals([], $actual);
    }
    
    public function pageAssetAndLinkProvider(): iterable {
        yield 'Skip links that are pages' => [
            '/page-asset',
            <<<EOT
            <html xmlns="http://www.w3.org/1999/xhtml">
            	<body>
            		<a href="/" />
            		<a href="/test-page/" />
            		<a href="/test-page/test-file" />
            	</body>
            </html>
            EOT,
            []
        ];
        
        yield 'Use XML namespace' => [
            '/file-asset',
            <<<EOT
            <html>
                <img src="." />
                <import href="." />
                <include schemaLocation="." />
            </html>
            EOT,
            []
        ];
        
        yield 'Find HTML header links' => [
            '/page-asset',
            <<<EOT
            <html xmlns="http://www.w3.org/1999/xhtml">
            	<head>
            		<link href="." />
            		<link href="" />
            		<script src="." />
            		<script src="" />
            	</head>
            </html>
            EOT,
            [
                "/test-page/ link href '.'" => [
                    '/test-page/',
                    '.'
                ],
                "/test-page/ link href ''" => [
                    '/test-page/',
                    ''
                ],
                "/test-page/ script src '.'" => [
                    '/test-page/',
                    '.'
                ]
            ]
        ];
        
        yield 'Find HTML body elements with required links' => [
            '/page-asset',
            <<<EOT
            <html xmlns="http://www.w3.org/1999/xhtml">
            	<body>
            		<a href="." />
            		<a href="" />
            		<img src="." />
            		<img src="" />
            		<iframe src="." />
            		<iframe src="" />
            		<source src="." />
            		<source src="" />
            		<track src="." />
            		<track src="" />
                    <embed src="." />
            		<embed src="" />
            	</body>
            </html>
            EOT,
            [
                "/test-page/ a href '.'" => [
                    '/test-page/',
                    '.'
                ],
                "/test-page/ a href ''" => [
                    '/test-page/',
                    ''
                ],
                "/test-page/ img src '.'" => [
                    '/test-page/',
                    '.'
                ],
                "/test-page/ img src ''" => [
                    '/test-page/',
                    ''
                ],
                "/test-page/ iframe src '.'" => [
                    '/test-page/',
                    '.'
                ],
                "/test-page/ iframe src ''" => [
                    '/test-page/',
                    ''
                ],
                "/test-page/ source src '.'" => [
                    '/test-page/',
                    '.'
                ],
                "/test-page/ source src ''" => [
                    '/test-page/',
                    ''
                ],
                "/test-page/ track src '.'" => [
                    '/test-page/',
                    '.'
                ],
                "/test-page/ track src ''" => [
                    '/test-page/',
                    ''
                ],
                "/test-page/ embed src '.'" => [
                    '/test-page/',
                    '.'
                ],
                "/test-page/ embed src ''" => [
                    '/test-page/',
                    ''
                ]
            ]
        ];
        
        yield 'Find HTML body elements with optional links' => [
            '/page-asset',
            <<<EOT
            <html xmlns="http://www.w3.org/1999/xhtml">
            	<body>
            		<form action="." />
            		<form action="" />
            		<video src="." />
            		<video src="" />
            		<audio src="." />
            		<audio src="" />
            	</body>
            </html>
            EOT,
            [
                "/test-page/ form action '.'" => [
                    '/test-page/',
                    '.'
                ],
                "/test-page/ video src '.'" => [
                    '/test-page/',
                    '.'
                ],
                "/test-page/ audio src '.'" => [
                    '/test-page/',
                    '.'
                ]
            ]
        ];
        
        yield 'Include HTML special URIs' => [
            '/page-asset',
            <<<EOT
            <html xmlns="http://www.w3.org/1999/xhtml">
            	<body>
            		<a href="mailto:test@email" />
            		<img src="data:image/png;base64, iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg==" />
            	</body>
            </html>
            EOT,
            [
                "/test-page/ a href 'mailto:test@email'" => [
                    '/test-page/',
                    'mailto:test@email'
                ],
                "/test-page/ img src 'data:image/png;base64, iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg=='" => [
                    '/test-page/',
                    'data:image/png;base64, iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg=='
                ]
            ]
        ];
        
        yield 'Find XSL links' => [
            '/file-asset',
            <<<EOT
            <stylesheet xmlns="http://www.w3.org/1999/XSL/Transform">
                <include href="." />
                <include href="" />
                <import href="." />
                <import href="" />
            </stylesheet>
            EOT,
            [
                "/test-page/test-file include href '.'" => [
                    '/test-page/test-file',
                    '.'
                ],
                "/test-page/test-file include href ''" => [
                    '/test-page/test-file',
                    ''
                ],
                "/test-page/test-file import href '.'" => [
                    '/test-page/test-file',
                    '.'
                ],
                "/test-page/test-file import href ''" => [
                    '/test-page/test-file',
                    ''
                ]
            ]
        ];
        
        yield 'Find XSD links' => [
            '/file-asset',
            <<<EOT
            <schema xmlns="http://www.w3.org/2001/XMLSchema">
                <include schemaLocation="." />
                <include schemaLocation="" />
                <import schemaLocation="." />
                <import schemaLocation="" />
            </schema>
            EOT,
            [
                "/test-page/test-file include schemaLocation '.'" => [
                    '/test-page/test-file',
                    '.'
                ],
                "/test-page/test-file include schemaLocation ''" => [
                    '/test-page/test-file',
                    ''
                ],
                "/test-page/test-file import schemaLocation '.'" => [
                    '/test-page/test-file',
                    '.'
                ]
            ]
        ];
        
        yield 'Find XInclude links' => [
            '/file-asset',
            <<<EOT
            <html xmlns="http://www.w3.org/1999/xhtml" xmlns:xi="http://www.w3.org/2001/XInclude">
                <xi:include href="." />
                <xi:include href="" />
            </html>
            EOT,
            [
                "/test-page/test-file include href '.'" => [
                    '/test-page/test-file',
                    '.'
                ],
                "/test-page/test-file include href ''" => [
                    '/test-page/test-file',
                    ''
                ]
            ]
        ];
        
        yield 'Find data-* links' => [
            '/domain-asset',
            <<<EOT
            <html xmlns="http://www.w3.org/1999/xhtml">
                <a data-href="." />
                <img data-src="." />
            </html>
            EOT,
            [
                "/ a href '.'" => [
                    '/',
                    '.'
                ],
                "/ img src '.'" => [
                    '/',
                    '.'
                ]
            ]
        ];
    }
    
    /**
     *
     * @runInSeparateProcess
     */
    public function test_pageLinkProvider_doesNotTouchSitemap() {
        $sut = $this->createSuT();
        
        $expected = file_get_contents(Domain::CURRENT_SITEMAP);
        
        $actual = $this->sitesDocument->saveXML();
        
        $this->assertEquals($expected, $actual, "Setting up test sitemap failed!");
        
        $sut->pageLinkProvider();
        
        $actual = $this->sitesDocument->saveXML();
        
        $this->assertEquals($expected, $actual, "pageLinkProvider changed the sitemap!");
    }
    
    /**
     *
     * @runInSeparateProcess
     * @dataProvider pageLinkProvider
     */
    public function test_testPageHasValidLink(string $context, string $link, bool $isValid) {
        $sut = $this->createSuT();
        
        if (! $isValid) {
            $this->expectException(AssertionFailedError::class);
        }
        
        $sut->testPageHasValidLink($context, $link);
    }
    
    public function pageLinkProvider(): iterable {
        yield '/ does exist' => [
            '/',
            '/',
            true
        ];
        yield '/test-page/ does exist' => [
            '/',
            '/test-page/',
            true
        ];
        yield '/test-page/test-file does exist' => [
            '/',
            '/test-page/test-file',
            true
        ];
        yield '/missing does not exist' => [
            '/',
            '/missing',
            false
        ];
        yield 'empty string does not exist' => [
            '/',
            '',
            false
        ];
        yield 'farah://slothsoft@test/ does exist' => [
            '/',
            'farah://slothsoft@test/',
            true
        ];
        yield 'farah://slothsoft@test/missing does not exist' => [
            '/',
            'farah://slothsoft@test/missing',
            false
        ];
        yield 'farah://slothsoft@test-missing/ does not exist' => [
            '/',
            'farah://slothsoft@test-missing/',
            false
        ];
        yield 'external host does exist' => [
            '/',
            'https://slothsoft.net/',
            true
        ];
        yield '/test-page has a missing slash' => [
            '/',
            '/test-page',
            false
        ];
        yield '/ => test-page does exist' => [
            '/',
            'test-page/',
            true
        ];
        yield '/test-page => test-file does exist' => [
            '/test-page/',
            'test-file',
            true
        ];
        yield '/test-page => .. does exist' => [
            '/test-page/',
            '..',
            true
        ];
        yield '/test-page/. does exist' => [
            '/test-page/',
            '.',
            true
        ];
        yield 'short mailto works' => [
            '/',
            'mailto:daniel.lio.schulz@gmail.com',
            true
        ];
        yield 'long mailto works' => [
            '/',
            'mailto:Daniel Schulz <daniel.lio.schulz@gmail.com>',
            true
        ];
    }
}

class StubExecutableBuilder implements ExecutableBuilderStrategyInterface {
    
    public static array $executables = [];
    
    public function buildExecutableStrategies(AssetInterface $context, FarahUrlArguments $args): ExecutableStrategies {
        return new ExecutableStrategies(self::$executables[(string) $context->getUrlPath()] ?? new NullResultBuilder());
    }
}

class StubSitemapTest extends AbstractSitemapTest {
    
    public static AssetInterface $sitesAsset;
    
    protected static function loadSitesAsset(): AssetInterface {
        return self::$sitesAsset;
    }
    
    public function getSitesDocumentProtected(): DOMDocument {
        return $this->getSitesDocument();
    }
}