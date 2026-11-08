<?php
declare(strict_types = 1);

namespace Slothsoft\FarahTesting;

use Exception;

final class TestUtils {
    
    private static string $composerRootFromProject = __DIR__ . '/..';
    
    private static string $composerRootFromPackage = __DIR__ . '/../../../..';
    
    /**
     * @throws Exception
     */
    public static function changeWorkingDirectoryToComposerRoot(): void {
        $directory = getcwd();
        if ($directory !== false and self::isComposerDirectory($directory)) {
            return;
        }
        
        $directory = realpath(self::$composerRootFromProject);
        if ($directory !== false and self::isComposerDirectory($directory)) {
            chdir($directory);
            return;
        }
        
        $directory = realpath(self::$composerRootFromPackage);
        if ($directory !== false and self::isComposerDirectory($directory)) {
            chdir($directory);
            return;
        }
        
        throw new Exception('Failed to find composer root directory');
    }
    
    private static function isComposerDirectory(string $directory): bool {
        return is_dir($directory . DIRECTORY_SEPARATOR . 'vendor');
    }
}
