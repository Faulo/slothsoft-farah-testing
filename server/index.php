<?php
use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\Http\MessageFactory;
use Slothsoft\Farah\RequestStrategy\LookupAssetStrategy;
use Slothsoft\Farah\RequestStrategy\LookupPageStrategy;
use Slothsoft\Farah\ResponseStrategy\SendHeaderAndBodyStrategy;
use Slothsoft\Core\ServerEnvironment;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;
use Slothsoft\Farah\Module\Module;

require_once $_ENV['FARAH_AUTOLOAD'];

if (isset($_ENV['FARAH_MODULE_VENDOR'], $_ENV['FARAH_MODULE_NAME'], $_ENV['FARAH_MODULE_MANIFEST'])) {
    Module::registerWithXmlManifestAndDefaultAssets(FarahUrlAuthority::createFromVendorAndModule($_ENV['FARAH_MODULE_VENDOR'], $_ENV['FARAH_MODULE_NAME']), $_ENV['FARAH_MODULE_MANIFEST']);
}

if (isset($_ENV['FARAH_SITEMAP'])) {
    Kernel::setCurrentSitemap($_ENV['FARAH_SITEMAP']);
}

$request = MessageFactory::createServerRequest($_SERVER, $_REQUEST, $_FILES);
if (preg_match('~^/[^/]+@[^/]+~', $request->getUri()->getPath())) {
    $requestStrategy = new LookupAssetStrategy();
} else {
    $requestStrategy = new LookupPageStrategy();
}
$responseStrategy = new SendHeaderAndBodyStrategy();

$kernel = Kernel::getInstance();
$kernel->handle($requestStrategy, $responseStrategy, $request);