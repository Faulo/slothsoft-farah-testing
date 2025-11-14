<?php
declare(strict_types = 1);
namespace Slothsoft\FarahTesting;

use PHPUnit\Framework\TestCase;
use Slothsoft\FarahTesting\Exception\BrowserDriverNotFoundException;
use Symfony\Component\Panther\Client;

abstract class FarahServerTestCase extends TestCase {
    
    protected static FarahServer $server;
    
    protected Client $client;
    
    public static function setUpBeforeClass(): void {
        TestUtils::changeWorkingDirectoryToComposerRoot();
        
        static::$server = new FarahServer();
        static::setUpServer();
        static::$server->start();
    }
    
    protected static function setUpServer(): void {}
    
    public static function tearDownAfterClass(): void {
        static::$server->quit();
        static::$server = new FarahServer();
    }
    
    protected function setUp(): void {
        try {
            $this->client = static::$server->createClient();
        } catch (BrowserDriverNotFoundException $e) {
            self::markTestSkipped($e->getMessage());
        }
        
        $this->setUpClient();
    }
    
    protected function setUpClient(): void {}
    
    protected function tearDown(): void {
        $this->client->quit();
        unset($this->client);
    }
}