Farah Testing Package
=====================
[![Packagist Version](https://img.shields.io/packagist/v/slothsoft/farah-testing)](https://packagist.org/packages/slothsoft/farah-testing)
[![PHP Version Support](https://img.shields.io/packagist/php-v/slothsoft/farah-testing)](https://www.php.net/)
[![Documentation](https://img.shields.io/badge/docs-reference-blue.svg)](https://faulo.github.io/slothsoft-farah-testing/)
[![Test Status](https://github.com/Faulo/slothsoft-farah-testing/actions/workflows/ci-tests.yml/badge.svg)](https://github.com/Faulo/slothsoft-farah-testing/actions/workflows/ci-tests.yml)
[![license badge](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

Constraints and test suite stubs for Farah modules.

## Persistent package data

Farah Testing stores its persistent data under `ServerEnvironment::getDataDirectory()/farah-testing`. This includes downloaded browser drivers. Preserve this entire directory between CI runs; callers do not need to manage its internal layout. Clearing Core's cache directory does not remove package data.

To retain package data between CI containers, configure Core's data directory in a development bootstrap such as `tests/bootstrap.php`:

```php
use Slothsoft\Core\ServerEnvironment;

ServerEnvironment::setDataDirectory(__DIR__ . '/../.data');
```

Register this file in Composer's `autoload-dev.files` and regenerate the autoloader with `ddev composer dump-autoload`. PHPUnit keeps using `vendor/autoload.php` as its bootstrap. This repository follows that setup; dependency installations do not load its development bootstrap.

Jenkins retains `.data/farah-testing` in its existing persistent workspaces. GitHub Actions restores and saves that entire directory, with separate cache keys for each runner OS and architecture. Cold parallel jobs may each download prerequisites; subsequent runs reuse the saved package data.
