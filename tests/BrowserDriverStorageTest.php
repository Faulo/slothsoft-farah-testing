<?php
declare(strict_types = 1);

namespace Slothsoft\FarahTesting;

use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Slothsoft\Core\ServerEnvironment;
use Symfony\Component\Panther\ProcessManager\ChromeManager;
use Symfony\Component\Panther\ProcessManager\FirefoxManager;
use Symfony\Component\Process\Process;

final class BrowserDriverStorageTest extends TestCase {
    
    /**
     * @throws Exception
     * @dataProvider browserProvider
     * @runInSeparateProcess
     */
    public function test_reusesDataDirectoryDriverAfterCacheDirectoryChanges(string $driver, string $cachedDriver, string $managerClass): void {
        $dataDirectory = temp_dir(self::class);
        $driversDirectory = $dataDirectory . DIRECTORY_SEPARATOR . 'farah-testing' . DIRECTORY_SEPARATOR . 'bdi-drivers';
        mkdir($driversDirectory, 0777, true);
        $suffix = PHP_OS_FAMILY === 'Windows' ? '.exe' : '';
        $driverFile = $driversDirectory . DIRECTORY_SEPARATOR . $driver . $suffix;
        file_put_contents($driverFile, '');
        ServerEnvironment::setDataDirectory($dataDirectory);
        
        for ($i = 0; $i < 2; $i++) {
            $cacheDirectory = temp_dir(self::class);
            mkdir($cacheDirectory . DIRECTORY_SEPARATOR . 'bdi-drivers');
            file_put_contents($cacheDirectory . DIRECTORY_SEPARATOR . 'bdi-drivers' . DIRECTORY_SEPARATOR . $cachedDriver . $suffix, '');
            ServerEnvironment::setCacheDirectory($cacheDirectory);
            
            $server = new FarahServer();
            $server->start();
            try {
                // Panther is lazy: inspect the selected executable without starting a browser or invoking BDI.
                $client = $server->createClient();
                $manager = $client->getBrowserManager();
                self::assertInstanceOf($managerClass, $manager);
                $process = (new ReflectionProperty($manager, 'process'))->getValue($manager);
                self::assertInstanceOf(Process::class, $process);
                self::assertStringContainsString($driverFile, $process->getCommandLine());
                $server->returnClientQuietly($client);
            } finally {
                $server->quit();
            }
        }
    }
    
    public function browserProvider(): iterable {
        yield 'Firefox' => ['geckodriver', 'chromedriver', FirefoxManager::class];
        yield 'Chrome' => ['chromedriver', 'geckodriver', ChromeManager::class];
    }
}
