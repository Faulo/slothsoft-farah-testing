<?php
declare(strict_types = 1);
namespace Slothsoft\FarahTesting\Module;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Manifest\ManifestInterface;
use SplFileInfo;

class MockManifest implements ManifestInterface {
    
    public function normalizeManifestElement(LeanElement $parent, LeanElement $child): void {}
    
    public function createUrl($path = null, $args = null, $fragment = null): FarahUrl {}
    
    public function lookupAsset($path): AssetInterface {}
    
    public function clearCachedAssets(): void {}
    
    public function createAsset(LeanElement $element): AssetInterface {}
    
    public function getId(): string {}
    
    public function normalizeManifestTree(LeanElement $root): void {}
    
    public function createManifestFile(string $fileName): SplFileInfo {}
    
    public function createCacheFile(string $fileName, $path = null, $args = null, $fragment = null): SplFileInfo {}
    
    public function createDataFile(string $fileName, $path = null, $args = null, $fragment = null): SplFileInfo {}
}

