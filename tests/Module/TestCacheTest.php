<?php
declare(strict_types = 1);
namespace Slothsoft\FarahTesting\Module;

use PHPUnit\Framework\TestCase;

/**
 * TestCacheTest
 *
 * @see TestCache
 *
 * @todo auto-generated
 */
class TestCacheTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(TestCache::class), "Failed to load class 'Slothsoft\Farah\ModuleTests\TestCache'!");
    }
}