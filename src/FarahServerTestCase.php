<?php
declare(strict_types = 1);
namespace Slothsoft\FarahTesting;

use PHPUnit\Framework\TestCase;
use Slothsoft\FarahTesting\Exception\BrowserDriverNotFoundException;
use Symfony\Component\Panther\Client;

abstract class FarahServerTestCase extends TestCase {
    
    private static ?FarahServer $server;
    
    protected Client $client;
    
    public static function setUpBeforeClass(): void {
        self::$server = new FarahServer();
        static::setUpServer(self::$server);
        self::$server->start();
    }
    
    protected static function setUpServer(FarahServer $server): void {}
    
    public static function tearDownAfterClass(): void {
        self::$server->quit();
        self::$server = null;
    }
    
    protected function setUp(): void {
        try {
            $this->client = self::$server->createClient();
        } catch (BrowserDriverNotFoundException $e) {
            self::markTestSkipped($e->getMessage());
        }
        
        $this->setUpClient($this->client);
    }
    
    protected function setUpClient(Client $client): void {}
    
    protected function tearDown(): void {
        $this->client->quit();
    }
}