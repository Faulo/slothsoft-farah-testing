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
class AbstractModuleTestTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(AbstractModuleTest::class), "Failed to load class 'Slothsoft\Farah\ModuleTests\AbstractModuleTest'!");
    }
}