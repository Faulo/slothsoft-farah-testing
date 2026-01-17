<?php
declare(strict_types = 1);
namespace Slothsoft\FarahTesting\Constraints;

use PHPUnit\Framework\TestCase;

/**
 * FileEqualsFileTest
 *
 * @see FileEqualsFile
 *
 * @todo auto-generated
 */
final class FileEqualsFileTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(FileEqualsFile::class), "Failed to load class 'Slothsoft\FarahTesting\Constraints\FileEqualsFile'!");
    }
}