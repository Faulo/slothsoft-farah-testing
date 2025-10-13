<?php
declare(strict_types = 1);
namespace Slothsoft\FarahTesting\Module;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy\ExecutableBuilderStrategyInterface;
use Slothsoft\Farah\Module\Asset\InstructionStrategy\InstructionStrategyInterface;
use Slothsoft\Farah\Module\Asset\ParameterFilterStrategy\ParameterFilterStrategyInterface;
use Slothsoft\Farah\Module\Asset\PathResolverStrategy\PathResolverStrategyInterface;
use Slothsoft\Farah\Module\Manifest\Manifest;
use DOMDocument;

abstract class AbstractManifestTest extends AbstractTestCase {
    
    abstract protected static function loadTree(): LeanElement;
    
    protected function getManifestRoot(): LeanElement {
        $cache = TestCache::instance(get_class($this));
        
        return $cache->retrieve('getManifestRoot', function () {
            return static::loadTree();
        });
    }
    
    protected function getManifestDocument(): DOMDocument {
        return $this->getManifestRoot()->toDocument();
    }
    
    public function testHasRootElement(): void {
        $this->assertInstanceOf(LeanElement::class, $this->getManifestRoot());
    }
    
    /**
     *
     * @depends testHasRootElement
     */
    public function testRootElementIsAssets(): void {
        $this->assertEquals($this->getManifestRoot()
            ->getTag(), Manifest::TAG_ASSET_ROOT);
    }
    
    /**
     *
     * @dataProvider customPathResolverProvider
     */
    public function testClassImplementsPathResolverStrategy(string $className): void {
        $this->assertNotNull($className);
        $this->assertTrue(class_exists($className));
        $this->assertInstanceOf(PathResolverStrategyInterface::class, new $className());
    }
    
    public function customPathResolverProvider(): array {
        return $this->getAllAttributeValuesProvider(Manifest::ATTR_PATH_RESOLVER);
    }
    
    /**
     *
     * @dataProvider customExecutableBuilderProvider
     */
    public function testClassImplementsExecutableBuilderStrategy(string $className): void {
        $this->assertNotNull($className);
        $this->assertTrue(class_exists($className));
        $this->assertInstanceOf(ExecutableBuilderStrategyInterface::class, new $className());
    }
    
    public function customExecutableBuilderProvider(): array {
        return $this->getAllAttributeValuesProvider(Manifest::ATTR_EXECUTABLE_BUILDER);
    }
    
    /**
     *
     * @dataProvider customInstructionProvider
     */
    public function testClassImplementsInstructionStrategy(string $className): void {
        $this->assertNotNull($className);
        $this->assertTrue(class_exists($className));
        $this->assertInstanceOf(InstructionStrategyInterface::class, new $className());
    }
    
    public function customInstructionProvider(): array {
        return $this->getAllAttributeValuesProvider(Manifest::ATTR_INSTRUCTION);
    }
    
    /**
     *
     * @dataProvider customParameterFilterProvider
     */
    public function testClassImplementsParameterFilterStrategy(string $className): void {
        $this->assertNotNull($className);
        $this->assertTrue(class_exists($className));
        $this->assertInstanceOf(ParameterFilterStrategyInterface::class, new $className());
    }
    
    public function customParameterFilterProvider(): array {
        return $this->getAllAttributeValuesProvider('parameter-filte');
    }
    
    private function getAllAttributeValues(string $attributeName): iterable {
        $manifestDocument = $this->getManifestDocument();
        $nodeList = $manifestDocument->getElementsByTagName('*');
        foreach ($nodeList as $node) {
            if ($node->hasAttribute($attributeName)) {
                yield $node->getAttribute($attributeName);
            }
        }
    }
    
    private function getAllAttributeValuesProvider(string $attributeName): array {
        $cache = TestCache::instance(get_class($this));
        
        return $cache->retrieve("getAllAttributeValuesProvider $attributeName", function () use ($attributeName) {
            $provider = [];
            foreach ($this->getAllAttributeValues($attributeName) as $className) {
                $provider[$className] ??= [
                    $className
                ];
            }
            return $provider;
        });
    }
}

