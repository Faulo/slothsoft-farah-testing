<?php
declare(strict_types = 1);
namespace Slothsoft\FarahTesting\Module;

use PHPUnit\Framework\TestCase;

/**
 * LinkCrawlerTest
 *
 * @see LinkCrawler
 *
 * @todo auto-generated
 */
class LinkCrawlerTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(LinkCrawler::class), "Failed to load class 'Slothsoft\Farah\ModuleTests\LinkCrawler'!");
    }
}