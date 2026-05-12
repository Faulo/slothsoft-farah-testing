<?php
declare(strict_types = 1);

namespace Slothsoft\FarahTesting\Module;

use PHPUnit\Framework\TestCase;

/**
 * MockManifestTest
 *
 * @see MockManifest
 *
 * @todo auto-generated
 */
final class MockManifestTest extends TestCase {
    
    /**
     *
     * @test
     */
    public function testClassExists(): void {
        $this->assertTrue(class_exists(MockManifest::class), "Failed to load class 'Slothsoft\FarahTesting\Module\MockManifest'!");
    }
}