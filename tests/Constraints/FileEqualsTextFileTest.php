<?php
declare(strict_types = 1);

namespace Slothsoft\FarahTesting\Constraints;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Slothsoft\Core\IO\FileInfoFactory;

/**
 * FileEqualsTextFileTest
 *
 * @see FileEqualsTextFile
 */
final class FileEqualsTextFileTest extends TestCase {
    
    /**
     * @test
     */
    public function testClassExists(): void {
        $this->assertTrue(class_exists(FileEqualsTextFile::class), "Failed to load class 'Slothsoft\FarahTesting\Constraints\FileEqualsTextFile'!");
    }
    
    /**
     * @test
     */
    public function test_evaluate_true() {
        $a = FileInfoFactory::createTempFile();
        file_put_contents((string) $a, "a\r\nb");
        $b = FileInfoFactory::createTempFile();
        file_put_contents((string) $b, "a\nb");
        
        $sut = new FileEqualsTextFile($a, FILE_IGNORE_NEW_LINES);
        $actual = $sut->evaluate($b, '', true);
        
        $this->assertTrue($actual);
    }
    
    /**
     * @test
     */
    public function test_evaluate_false() {
        $a = FileInfoFactory::createTempFile();
        file_put_contents((string) $a, "a\r\nb");
        $b = FileInfoFactory::createTempFile();
        file_put_contents((string) $b, "a\nb");
        
        $sut = new FileEqualsTextFile($a);
        $actual = $sut->evaluate($b, '', true);
        
        $this->assertFalse($actual);
    }
    
    /**
     * @test
     */
    public function test_evaluate_returnsFalseWhenExpectedFileDoesNotExist(): void {
        $expected = FileInfoFactory::createTempFile();
        $actual = FileInfoFactory::createTempFile();
        file_put_contents((string) $actual, 'a');
        
        $sut = new FileEqualsTextFile($expected);
        
        $this->assertFalse($sut->evaluate($actual, '', true));
    }
    
    /**
     * @test
     */
    public function test_evaluate_reportsMissingExpectedFile(): void {
        $expected = FileInfoFactory::createTempFile();
        $actual = FileInfoFactory::createTempFile();
        file_put_contents((string) $actual, 'a');
        
        $sut = new FileEqualsTextFile($expected);
        
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage(sprintf("Expected file '%s' does not exist.", $expected));
        
        $sut->evaluate($actual);
    }
    
    /**
     * @test
     */
    public function test_evaluate_reportsMissingActualFile(): void {
        $expected = FileInfoFactory::createTempFile();
        $actual = FileInfoFactory::createTempFile();
        file_put_contents((string) $expected, 'a');
        
        $sut = new FileEqualsTextFile($expected);
        
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage(sprintf("Actual file '%s' does not exist.", $actual));
        
        $sut->evaluate($actual);
    }
}
