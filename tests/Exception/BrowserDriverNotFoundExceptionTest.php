<?php
declare(strict_types = 1);
namespace Slothsoft\FarahTesting\Exception;

use PHPUnit\Framework\TestCase;

/**
 * BrowserDriverNotFoundExceptionTest
 *
 * @see BrowserDriverNotFoundException
 *
 * @todo auto-generated
 */
class BrowserDriverNotFoundExceptionTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(BrowserDriverNotFoundException::class), "Failed to load class 'Slothsoft\FarahTesting\Exception\BrowserDriverNotFoundException'!");
    }
}