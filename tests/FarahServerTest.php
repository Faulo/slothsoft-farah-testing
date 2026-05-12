<?php
declare(strict_types = 1);

namespace Slothsoft\FarahTesting;

use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\Constraint\IsFalse;
use PHPUnit\Framework\Constraint\StringContains;
use PHPUnit\Framework\TestCase;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;
use Slothsoft\FarahTesting\Exception\BrowserDriverNotFoundException;

/**
 * FarahServerTest
 *
 * @see FarahServer
 */
class FarahServerTest extends TestCase {

    private static int $reporting;

    public static function setUpBeforeClass(): void {
        TestUtils::changeWorkingDirectoryToComposerRoot();

        self::$reporting = error_reporting(E_ERROR | E_WARNING | E_PARSE);
    }

    public static function tearDownAfterClass(): void {
        error_reporting(self::$reporting);
    }

    public function test_start() {
        $sut = new FarahServer();
        $sut->start();

        $actual = file_get_contents($sut->uri . '/slothsoft@farah/phpinfo');

        $this->assertThat($actual, new StringContains(PHP_VERSION));
    }

    public function test_setModule_isUsedByServer() {
        $sut = new FarahServer();
        $sut->setModule(FarahUrlAuthority::createFromVendorAndModule('slothsoft-testing', 'test'), realpath('test-files/module'));
        $sut->start();

        $actual = file_get_contents($sut->uri . '/slothsoft-testing@test/php-info');

        $this->assertThat($actual, new StringContains(PHP_VERSION));
    }

    public function test_setSitemap_isUsedByServer() {
        $sut = new FarahServer();
        $sut->setSitemap(FarahUrl::createFromReference('farah://slothsoft@farah/example-domain'));
        $sut->start();

        $actual = DOMHelper::loadDocument($sut->uri . '/sitemap');

        $this->assertThat($actual->documentElement->namespaceURI, new IsEqual(DOMHelper::NS_SITEMAP));
    }

    public function test_createClient_andQuit() {
        $sut = new FarahServer();
        $sut->start();

        try {
            $client = $sut->createClient();
            $client->request('GET', '/slothsoft@farah/phpinfo');
            $client->quit();
            $actual = $client->ping();

            $this->assertThat($actual, new IsFalse());
        } catch (BrowserDriverNotFoundException $e) {
            $this->markTestSkipped();
        }
    }

    public function test_createClient_executeScript() {
        $sut = new FarahServer();
        $sut->start();

        try {
            $client = $sut->createClient();
            $client->request('GET', '/slothsoft@farah/phpinfo');

            $actual = $client->executeScript(<<<EOT
const node = document.querySelector("h1");
if (!node) {
    return "ERROR: no h1 node. HTML:\\n" + document.body.outerHTML;
}
return node.innerHTML;
EOT
            );
            $client->quit();

            $this->assertThat($actual, new IsEqual(sprintf('PHP Version %s', PHP_VERSION)));
        } catch (BrowserDriverNotFoundException $e) {
            $this->markTestSkipped();
        }
    }
}
