<?php
declare(strict_types = 1);
namespace Slothsoft\FarahTesting;

use Exception;

final class TestUtils {
    
    private static $composerRootFromProject = __DIR__ . '/..';
    
    private static $composerRootFromPackage = __DIR__ . '/../../../..';
    
    public static function changeWorkingDirectoryToComposerRoot(): void {
        if (self::isComposerDirectory(getcwd())) {
            return;
        }
        
        if (self::isComposerDirectory(self::$composerRootFromProject)) {
            chdir(realpath(self::$composerRootFromProject));
            return;
        }
        
        if (self::isComposerDirectory(self::$composerRootFromPackage)) {
            chdir(realpath(self::$composerRootFromPackage));
            return;
        }
        
        throw new Exception('Failed to find composer root directory');
    }
    
    private static function isComposerDirectory(string $directory): bool {
        return is_dir($directory . DIRECTORY_SEPARATOR . 'vendor');
    }
}
