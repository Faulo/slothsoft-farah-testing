<?php
declare(strict_types = 1);

namespace Slothsoft\FarahTesting;

use DOMDocument;
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

    public function test_createClient_andReturn() {
        $sut = new FarahServer();
        $sut->start();

        try {
            $client = $sut->createClient();
            $client->request('GET', '/slothsoft@farah/phpinfo');
            $sut->returnClientQuietly($client);
            $actual = $client->ping();

            $this->assertThat($actual, new IsFalse());
        } catch (BrowserDriverNotFoundException $e) {
            $this->markTestSkipped();
        }
    }

    public function test_createClient_requestFromServer() {
        $sut = new FarahServer();
        $sut->start();

        try {
            $source = file_get_contents("$sut->uri/slothsoft@farah/phpinfo");

            $document = new DOMDocument();
            $actual = $document->loadXML($source);
            $this->assertTrue($actual, "Failed to retrieve /slothsoft@farah/phpinfo:" . PHP_EOL . $source);

            $xpath = DOMHelper::loadXPath($document);
            $actual = $xpath->evaluate('string(//html:title)');
            $this->assertThat($actual, new IsEqual(sprintf('PHP %s - phpinfo()', PHP_VERSION)), "Failed to retrieve <title> from /slothsoft@farah/phpinfo:" . PHP_EOL . $source);
        } catch (BrowserDriverNotFoundException $e) {
            $this->markTestSkipped();
        }
    }

    public function test_createClient_requestFromClient() {
        $sut = new FarahServer();
        $sut->start();

        try {
            $client = $sut->createClient();
            $client->request('GET', '/slothsoft@farah/phpinfo');
            $source = $client->getPageSource();
            $sut->returnClientQuietly($client);

            $document = new DOMDocument();
            $actual = $document->loadXML($source);
            $this->assertTrue($actual, "Failed to retrieve /slothsoft@farah/phpinfo:" . PHP_EOL . $source);

            $xpath = DOMHelper::loadXPath($document);
            $actual = $xpath->evaluate('string(//html:title)');
            $this->assertThat($actual, new IsEqual(sprintf('PHP %s - phpinfo()', PHP_VERSION)), "Failed to retrieve <title> from /slothsoft@farah/phpinfo:" . PHP_EOL . $source);
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
            $source = $client->getPageSource();

            $actual = $client->executeScript(<<<EOT
console.log(document.documentElement);
const node = document.querySelector("title");
if (!node) {
    return "ERROR: no title node. HTML:\\n" + document.documentElement;
}
return node.innerHTML;
EOT
            );
            $sut->returnClientQuietly($client);

            $this->assertThat($actual, new IsEqual(sprintf('PHP %s - phpinfo()', PHP_VERSION)), "Failed to retrieve <title> from /slothsoft@farah/phpinfo:" . PHP_EOL . $source);
        } catch (BrowserDriverNotFoundException $e) {
            $this->markTestSkipped();
        }
    }
}
