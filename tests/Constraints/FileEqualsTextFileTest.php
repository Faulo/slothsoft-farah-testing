<?php
declare(strict_types = 1);

namespace Slothsoft\FarahTesting\Constraints;

use PHPUnit\Framework\TestCase;
use Slothsoft\Core\IO\FileInfoFactory;

/**
 * FileEqualsTextFileTest
 *
 * @see FileEqualsTextFile
 */
final class FileEqualsTextFileTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(FileEqualsTextFile::class), "Failed to load class 'Slothsoft\FarahTesting\Constraints\FileEqualsTextFile'!");
    }
    
    public function test_evaluate_true() {
        $a = FileInfoFactory::createTempFile();
        file_put_contents((string) $a, "a\r\nb");
        $b = FileInfoFactory::createTempFile();
        file_put_contents((string) $b, "a\nb");
        
        $sut = new FileEqualsTextFile($a, FILE_IGNORE_NEW_LINES);
        $actual = $sut->evaluate($b, '', true);
        
        $this->assertTrue($actual);
    }
    
    public function test_evaluate_false() {
        $a = FileInfoFactory::createTempFile();
        file_put_contents((string) $a, "a\r\nb");
        $b = FileInfoFactory::createTempFile();
        file_put_contents((string) $b, "a\nb");
        
        $sut = new FileEqualsTextFile($a);
        $actual = $sut->evaluate($b, '', true);
        
        $this->assertFalse($actual);
    }
}