<?php
declare(strict_types = 1);
namespace Slothsoft\FarahTesting\Module;

final class TestCache {
    
    private static array $caches = [];
    
    public static function instance(string $key): TestCache {
        return self::$caches[$key] ??= new self();
    }
    
    private function __construct() {}
    
    private array $cache = [];
    
    public function retrieve(string $key, callable $generator) {
        return $this->cache[$key] ??= $generator();
    }
    
    public function clear() {
        $this->cache = [];
    }
}

