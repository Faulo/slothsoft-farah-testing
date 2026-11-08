<?php
declare(strict_types = 1);

namespace Slothsoft\FarahTesting\Module;

final class TestCache {
    
    /**
     * @var array<string, self>
     */
    private static array $caches = [];
    
    public static function instance(string $key): TestCache {
        return self::$caches[$key] ??= new self();
    }
    
    private function __construct() {
    }
    
    /**
     * @var array<string, mixed>
     */
    private array $cache = [];
    
    /**
     * @template T
     * @param callable(): T $generator
     * @return T
     */
    public function retrieve(string $key, callable $generator) {
        return $this->cache[$key] ??= $generator();
    }
    
    public function clear(): void {
        $this->cache = [];
    }
}

