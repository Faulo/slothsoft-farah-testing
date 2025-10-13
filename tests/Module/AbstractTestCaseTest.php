<?php
declare(strict_types = 1);
namespace Slothsoft\FarahTesting\Module;

use PHPUnit\Framework\TestCase;

/**
 * AbstractTestCaseTest
 *
 * @see AbstractTestCase
 *
 * @todo auto-generated
 */
class AbstractTestCaseTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(AbstractTestCase::class), "Failed to load class 'Slothsoft\Farah\ModuleTests\AbstractTestCase'!");
    }
}