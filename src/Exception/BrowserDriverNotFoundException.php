<?php
declare(strict_types = 1);
namespace Slothsoft\FarahTesting\Exception;

use RuntimeException;

final class BrowserDriverNotFoundException extends RuntimeException {
    
    public static function forDirectory(string $directory, array $expected, array $found): self {
        return new self(sprintf('No compatible browser driver found in "%s". Expected any of [%s]; found [%s].', $directory, implode(', ', $expected), implode(', ', $found)));
    }
}
