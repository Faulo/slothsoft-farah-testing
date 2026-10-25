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

Core 1.15 defaults to `.data` in the current working directory, so running tests from the package root needs no custom bootstrap. Applications can override the location with `ServerEnvironment::setDataDirectory()`.

Jenkins retains `.data/farah-testing` in its existing persistent workspaces. GitHub Actions restores and saves that entire directory, with separate cache keys for each runner OS and architecture. Cold parallel jobs may each download prerequisites; subsequent runs reuse the saved package data.
