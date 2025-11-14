<?php
declare(strict_types = 1);
namespace Slothsoft\FarahTesting;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\IsEqual;

/**
 * TestUtilsTest
 *
 * @see TestUtils
 */
final class TestUtilsTest extends TestCase {
    
    /**
     *
     * @dataProvider provideDirectories
     */
    public function test_changeWorkingDirectoryToComposerRoot(string $directory): void {
        chdir($directory);
        
        TestUtils::changeWorkingDirectoryToComposerRoot();
        
        $expected = realpath(__DIR__ . '/..');
        
        $this->assertThat(getcwd(), new IsEqual($expected));
    }
    
    public function provideDirectories(): iterable {
        yield 'do nothing in root' => [
            __DIR__ . '/..'
        ];
        
        yield 'find from package' => [
            __DIR__
        ];
    }
}
