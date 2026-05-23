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
final class TestCacheTest extends TestCase {
    
    /**
     * @test
     */
    public function testClassExists(): void {
        $this->assertTrue(class_exists(TestCache::class), "Failed to load class 'Slothsoft\FarahTesting\Module\TestCache'!");
    }
}