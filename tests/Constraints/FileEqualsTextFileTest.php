<?php
declare(strict_types = 1);

namespace Slothsoft\FarahTesting\Constraints;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Slothsoft\Core\IO\FileInfoFactory;
use SplFileInfo;

/**
 * FileEqualsTextFileTest
 *
 * @see FileEqualsTextFile
 */
final class FileEqualsTextFileTest extends TestCase {
    
    /**
     * @dataProvider unreadableFileProvider
     */
    public function test_evaluate_handlesFilesDisappearingBeforeRead(bool $expectedUnreadable, bool $returnResult): void {
        $existing = FileInfoFactory::createTempFile();
        file_put_contents((string) $existing, 'content');
        $missing = FileInfoFactory::createTempFile();
        // Model a file disappearing between isFile() and file(), without relying
        // on OS permissions (the DDEV test user may have elevated privileges).
        $stale = new class((string) $missing) extends SplFileInfo {
            public function isFile(): bool {
                return true;
            }
            
            public function getRealPath(): string {
                return $this->getPathname();
            }
        };
        $expected = $expectedUnreadable ? $stale : $existing;
        $actual = $expectedUnreadable ? $existing : $stale;
        $sut = new FileEqualsTextFile($expected);
        
        if (! $returnResult) {
            $this->expectException(ExpectationFailedException::class);
            $this->expectExceptionMessage(sprintf("%s file '%s' could not be read.", $expectedUnreadable ? 'Expected' : 'Actual', $stale));
        }
        $result = $sut->evaluate($actual, '', $returnResult);
        $this->assertFalse($result);
    }
    
    /** @return iterable<string, array{bool, bool}> */
    public function unreadableFileProvider(): iterable {
        yield 'expected, boolean' => [true, true];
        yield 'actual, boolean' => [false, true];
        yield 'expected, exception' => [true, false];
        yield 'actual, exception' => [false, false];
    }
    
    /**
     * @test
     */
    public function testClassExists(): void {
        $this->assertTrue(class_exists(FileEqualsTextFile::class), "Failed to load class 'Slothsoft\FarahTesting\Constraints\FileEqualsTextFile'!");
    }
    
    /**
     * @test
     */
    public function test_evaluate_true(): void {
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
    public function test_evaluate_false(): void {
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
