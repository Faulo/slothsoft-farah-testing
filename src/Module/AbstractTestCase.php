<?php
declare(strict_types = 1);
namespace Slothsoft\FarahTesting\Module;

use PHPUnit\Framework\TestCase;
use Slothsoft\FarahTesting\TestUtils;
use Slothsoft\FarahTesting\Constraints\DOMDocumentIsValidAccordingToSchema;
use Slothsoft\Farah\Schema\SchemaLocator;
use DOMDocument;
use Throwable;

class AbstractTestCase extends TestCase {
    
    public static function setUpBeforeClass(): void {
        TestUtils::changeWorkingDirectoryToComposerRoot();
    }
    
    protected function failException(Throwable $e): void {
        $this->fail(sprintf('%s:%s%s%s%s', get_class($e), PHP_EOL, $e->getMessage(), PHP_EOL, $e->getTraceAsString()));
    }
    
    protected function getObjectProperty(object $target, string $name) {
        $getProperty = function (string $name) {
            return $this->$name;
        };
        $getProperty = $getProperty->bindTo($target, get_class($target));
        return $getProperty($name);
    }
    
    protected function getObjectMethod(object $target, string $name, ...$args) {
        $getProperty = function (string $name, $args) {
            return $this->$name(...$args);
        };
        $getProperty = $getProperty->bindTo($target, get_class($target));
        return $getProperty($name, $args);
    }
    
    protected function findSchemaLocation(DOMDocument $document): ?string {
        $locator = new SchemaLocator();
        return $locator->findSchemaLocation($document);
    }
    
    protected function assertSchema(DOMDocument $document, string $schema): void {
        $this->assertThat($document, new DOMDocumentIsValidAccordingToSchema($schema));
    }
}

