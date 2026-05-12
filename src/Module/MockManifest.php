<?php
declare(strict_types = 1);

namespace Slothsoft\FarahTesting\Module;

use BadMethodCallException;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Manifest\ManifestInterface;
use SplFileInfo;

class MockManifest implements ManifestInterface {
    
    public function normalizeManifestElement(LeanElement $parent, LeanElement $child): void {
    }
    
    public function createUrl($path = null, $args = null, $fragment = null): FarahUrl {
        throw new BadMethodCallException();
    }
    
    public function lookupAsset($path): AssetInterface {
        throw new BadMethodCallException();
    }
    
    public function clearCachedAssets(): void {
    }
    
    public function createAsset(LeanElement $element): AssetInterface {
        throw new BadMethodCallException();
    }
    
    public function getId(): string {
        throw new BadMethodCallException();
    }
    
    public function normalizeManifestTree(LeanElement $root): void {
    }
    
    public function createManifestFile(string $fileName): SplFileInfo {
        throw new BadMethodCallException();
    }
    
    public function createCacheFile(string $fileName, $path = null, $args = null, $fragment = null): SplFileInfo {
        throw new BadMethodCallException();
    }
    
    public function createDataFile(string $fileName, $path = null, $args = null, $fragment = null): SplFileInfo {
        throw new BadMethodCallException();
    }
}

