<?php
declare(strict_types = 1);

namespace Slothsoft\FarahTesting\Module;

use PHPUnit\Framework\TestCase;

/**
 * AbstractModuleTestTest
 *
 * @see AbstractModuleTest
 *
 * @todo auto-generated
 */
final class AbstractModuleTestTest extends TestCase {
    
    /**
     *
     * @test
     */
    public function testClassExists(): void {
        $this->assertTrue(class_exists(AbstractModuleTest::class), "Failed to load class 'Slothsoft\FarahTesting\Module\AbstractModuleTest'!");
    }
}