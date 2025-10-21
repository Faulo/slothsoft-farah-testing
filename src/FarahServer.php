<?php
declare(strict_types = 1);
namespace Slothsoft\FarahTesting;

use Slothsoft\Core\CLI;
use Slothsoft\Core\FileSystem;
use Slothsoft\Core\ServerEnvironment;
use Slothsoft\FarahTesting\Exception\BrowserDriverNotFoundException;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\ProcessManager\WebServerManager;

class FarahServer {
    
    private static function findFreePort(): int {
        $addr = '';
        $port = 0;
        $sock = socket_create_listen($port);
        socket_getsockname($sock, $addr, $port);
        socket_close($sock);
        
        return $port;
    }
    
    private array $env = [];
    
    private WebServerManager $manager;
    
    public string $uri;
    
    public function __construct() {}
    
    public function setModule(FarahUrlAuthority $module, string $assetsDirectory): void {
        $this->env['FARAH_MODULE_VENDOR'] = $module->getVendor();
        $this->env['FARAH_MODULE_NAME'] = $module->getModule();
        $this->env['FARAH_MODULE_MANIFEST'] = $assetsDirectory;
    }
    
    public function setSitemap(FarahUrl $url): void {
        $this->env['FARAH_SITEMAP'] = (string) $url;
    }
    
    public function start(): void {
        $documentRoot = realpath(__DIR__ . '/../server');
        $hostname = '127.0.0.1';
        $port = self::findFreePort();
        $router = '';
        $readinessPath = '';
        $this->env['FARAH_AUTOLOAD'] = realpath('vendor/autoload.php');
        
        $this->uri = sprintf('http://%s:%d', $hostname, $port);
        
        $this->manager = new WebServerManager($documentRoot, $hostname, $port, $router, $readinessPath, $this->env);
        $this->manager->start();
    }
    
    public function __destruct() {
        $this->manager->quit();
    }
    
    private static array $firefoxExecutables = [
        'geckodriver.exe',
        'geckodriver'
    ];
    
    private static array $chromeExecutables = [
        'chromedriver.exe',
        'chromedriver'
    ];
    
    public function createClient(): Client {
        $driversDirectory = ServerEnvironment::getCacheDirectory() . DIRECTORY_SEPARATOR . 'bdi-drivers';
        
        $options = [];
        $options['port'] = self::findFreePort();
        $options['request_timeout_in_ms'] = 300_000;
        
        for ($i = 0; $i < 2; $i ++) {
            foreach (self::$firefoxExecutables as $executable) {
                if (file_exists($driversFile = $driversDirectory . DIRECTORY_SEPARATOR . $executable)) {
                    return Client::createFirefoxClient($driversFile, null, $options, $this->uri);
                }
            }
            
            foreach (self::$chromeExecutables as $executable) {
                if (file_exists($driversFile = $driversDirectory . DIRECTORY_SEPARATOR . $executable)) {
                    return Client::createChromeClient($driversFile, null, $options, $this->uri);
                }
            }
            
            if (! $this->detectDrivers($driversDirectory)) {
                break;
            }
        }
        
        throw BrowserDriverNotFoundException::forDirectory($driversDirectory, [
            ...self::$firefoxExecutables,
            ...self::$chromeExecutables
        ], FileSystem::scanDir($driversDirectory));
    }
    
    private function detectDrivers(string $driversDirectory): bool {
        $rootDirectory = __DIR__;
        $executableDirectory = DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'dbrekelmans' . DIRECTORY_SEPARATOR . 'bdi' . DIRECTORY_SEPARATOR . 'bdi';
        for ($i = 0; $i < 3; $i ++) {
            $rootDirectory .= DIRECTORY_SEPARATOR . '..';
            if ($executable = realpath($rootDirectory . $executableDirectory)) {
                $command = sprintf('%s %s detect %s', escapeshellarg(PHP_BINARY), escapeshellarg($executable), escapeshellarg($driversDirectory));
                return CLI::execute($command) === 0;
            }
        }
        return false;
    }
}
