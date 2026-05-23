<?php
declare(strict_types = 1);

namespace Slothsoft\FarahTesting\Constraints;

use PHPUnit\Framework\TestCase;
use Slothsoft\Core\IO\FileInfoFactory;

/**
 * FileEqualsFileTest
 *
 * @see FileEqualsFile
 */
final class FileEqualsFileTest extends TestCase {
    
    /**
     * @test
     */
    public function testClassExists(): void {
        $this->assertTrue(class_exists(FileEqualsFile::class), "Failed to load class 'Slothsoft\FarahTesting\Constraints\FileEqualsFile'!");
    }
    
    /**
     * @test
     */
    public function test_evaluate_true() {
        $a = FileInfoFactory::createTempFile();
        file_put_contents((string) $a, 'a');
        $b = FileInfoFactory::createTempFile();
        file_put_contents((string) $b, 'a');
        
        $sut = new FileEqualsFile($a);
        $actual = $sut->evaluate($b, '', true);
        
        $this->assertTrue($actual);
    }
    
    /**
     * @test
     */
    public function test_evaluate_false() {
        $a = FileInfoFactory::createTempFile();
        file_put_contents((string) $a, 'a');
        $b = FileInfoFactory::createTempFile();
        file_put_contents((string) $b, 'b');
        
        $sut = new FileEqualsFile($a);
        $actual = $sut->evaluate($b, '', true);
        
        $this->assertFalse($actual);
    }
}