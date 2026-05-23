<?php
declare(strict_types = 1);

namespace Slothsoft\FarahTesting\Module;

use DOMDocument;
use Exception;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Schema\SchemaLocator;

abstract class AbstractXmlManifestTest extends AbstractManifestTest {
    
    abstract protected static function getManifestDirectory(): string;
    
    protected static function getManifestFile(): string {
        return static::getManifestDirectory() . DIRECTORY_SEPARATOR . 'manifest.xml';
    }
    
    protected static function loadTree(): LeanElement {
        return LeanElement::createTreeFromDOMDocument(DOMHelper::loadDocument(static::getManifestFile()));
    }
    
    /**
     * @test
     * @depends testManifestIsValidXml
     * @throws Exception
     */
    public function testSchemaExists(DOMDocument $manifestDocument): string {
        $locator = new SchemaLocator();
        $path = $locator->findSchemaLocation($manifestDocument);
        $this->assertNotNull($path, 'Failed to determine schema!');
        $this->assertFileExists($path, 'Schema file not found!');
        return $path;
    }
    
    /**
     * @test
     * @depends testSchemaExists
     */
    public function testSchemaIsValidXml(string $path): DOMDocument {
        $dom = new DOMHelper();
        $document = $dom->load($path);
        $this->assertNotNull($document);
        return $document;
    }
    
    /**
     * @test
     */
    public function testManifestExists(): string {
        $path = $this->getManifestFile();
        $this->assertFileExists($path, 'Asset file not found!');
        return $path;
    }
    
    /**
     * @test
     * @depends testManifestExists
     */
    public function testManifestIsValidXml(string $path): DOMDocument {
        $dom = new DOMHelper();
        $document = $dom->load($path);
        $this->assertNotNull($document);
        return $document;
    }
    
    /**
     * @test
     * @depends testManifestIsValidXml
     * @depends testSchemaIsValidXml
     */
    public function testManifestIsValidAccordingToSchema(DOMDocument $manifestDocument, DOMDocument $schemaDocument): DOMDocument {
        $this->assertSchema($manifestDocument, $schemaDocument->documentURI);
        
        return $manifestDocument;
    }
}

