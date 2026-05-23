<?php
declare(strict_types = 1);

namespace Slothsoft\FarahTesting\Module;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

/**
 * AbstractModuleTestTest
 *
 * @see AbstractModuleTest
 */
final class AbstractModuleTestTest extends TestCase {
    
    /**
     * @test
     */
    public function testClassExists(): void {
        $this->assertTrue(class_exists(AbstractModuleTest::class), "Failed to load class 'Slothsoft\FarahTesting\Module\AbstractModuleTest'!");
    }
    
    /**
     * @test
     */
    public function testAssetHasValidLinkPercentDecodesFragmentId(): void {
        $context = temp_file(__NAMESPACE__);
        
        file_put_contents($context, <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<document>
    <section id="foo/bar" />
</document>
XML
        );
        
        try {
            $sut = $this->getMockForAbstractClass(AbstractModuleTest::class);
            $sut->testAssetHasValidLink($context, '#foo%2Fbar');
        } catch (ExpectationFailedException $e) {
            $this->fail("Should not have thrown: " . PHP_EOL . $e);
        } finally {
            unlink($context);
        }
    }
}