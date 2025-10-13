<?php
declare(strict_types = 1);
namespace Slothsoft\FarahTesting;

use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\ProcessManager\WebServerManager;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;
use Slothsoft\Core\ServerEnvironment;
use Slothsoft\Core\FileSystem;
use RuntimeException;
use Slothsoft\Core\CLI;

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
        $hostname = 'localhost';
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
    
    public function createClient(): Client {
        $driversDirectory = ServerEnvironment::getCacheDirectory() . DIRECTORY_SEPARATOR . 'bdi-drivers';
        
        $options = [];
        $options['port'] = self::findFreePort();
        
        for ($i = 0; $i < 2; $i ++) {
            if (file_exists($driversFile = $driversDirectory . DIRECTORY_SEPARATOR . 'geckodriver.exe')) {
                return Client::createFirefoxClient($driversFile, null, $options, $this->uri);
            }
            
            $command = sprintf('composer exec bdi detect %s', escapeshellarg($driversDirectory));
            if (CLI::execute($command) !== 0) {
                break;
            }
        }
        
        throw new RuntimeException(sprintf('Failed to find valid browser driver. Drivers available are: [%s]', implode(FileSystem::scanDir($driversDirectory))));
    }
}
