<?php
declare(strict_types = 1);
namespace Slothsoft\FarahTesting;

use PHPUnit\Framework\Constraint\GreaterThan;
use PHPUnit\Framework\Constraint\StringContains;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

class DOMTest extends PantherTestCase {
    
    public static function setUpBeforeClass(): void {
        $options = self::$defaultOptions;
        $options['webServerDir'] = realpath('test-files/farah');
        $options['browser'] = self::FIREFOX;
        $options['port'] = self::findFreePort();
        
        self::startWebServer($options);
        
        $baseUri = sprintf('http://%s:%s', $options['hostname'], $options['port']);
        $options['port'] = self::findFreePort();
        
        self::$client = Client::createFirefoxClient(realpath('drivers/geckodriver.exe'), null, $options, $baseUri);
    }
    
    public static function tearDownAfterClass(): void {
        self::$client->quit();
        
        self::stopWebServer();
    }
    
    private static Client $client;
    
    private static function findFreePort(): int {
        $addr = '';
        $port = 0;
        $sock = socket_create_listen($port);
        socket_getsockname($sock, $addr, $port);
        socket_close($sock);
        
        return $port;
    }
    
    public function test_phpinfo(): void {
        self::$client->request('GET', '/slothsoft@farah/phpinfo');
        
        $actual = self::$client->executeScript(<<<EOT
return document.querySelector("h1").innerHTML;
EOT);
        
        $this->assertThat($actual, new StringContains('PHP Version'));
    }
    
    private function test_XPath(): void {
        self::$client->request('GET', '/slothsoft@farah/api/XPath');
        
        $actual = self::$client->executeScript(<<<EOT
return XPath.evaluate('count(//*)', document);
EOT);
        
        $this->assertThat($actual, new GreaterThan(1));
    }
}
