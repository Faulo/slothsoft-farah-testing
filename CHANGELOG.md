# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Persistent package data caching in GitHub Actions, separated by runner OS and architecture. Jenkins reuses package data in its existing workspaces.
- PHPStan validation and a DDEV development environment.
- A complete historical changelog reconstructed from release tags and Git history.

### Changed

- Store downloaded browser drivers under `ServerEnvironment::getDataDirectory()/farah-testing/bdi-drivers` instead of Core's cache directory, allowing reuse after cache cleanup and across CI containers ([#1](https://github.com/Faulo/slothsoft-farah-testing/issues/1)).
- Require Core 1.15 and Farah 1.29.6, and raise the minimum BrowserKit and DomCrawler versions. Core now defaults to `.data` in the current working directory, so no custom development bootstrap is needed.
- Validate PHP 8.2 through 8.5 and lowest/stable dependencies in the CI matrices, including Linux and Windows integration tests.

### Fixed

- Resolve static analysis findings in constraints, module tests, and link crawling while preserving their behavior.
- Normalize Windows temporary paths in the driver storage regression tests.

## [1.3.13] - 2026-05-23

### Added

- Explicit `LogicException` diagnostics when starting an already running `FarahServer` or creating a browser client before starting the server.
- Expanded regression tests and API documentation for constraints, server lifecycle, and link crawling.

### Fixed

- Report the actual missing file path in binary and text file comparison failures.
- Compare existing files by their resolved paths before reading contents, including when different file objects reference the same file.
- Avoid accessing an uninitialized browser client during test teardown after setup is skipped.

## [1.3.12] - 2026-05-18

### Fixed

- Decode percent-encoded page IDs when generating sitemap page tests.

## [1.3.11] - 2026-05-18

### Fixed

- Decode percent-encoded asset IDs when generating module asset tests.

## [1.3.10] - 2026-05-15

### Added

- `FarahServer::returnClientQuietly()` to release browser clients without letting WebDriver shutdown failures obscure test results.

### Changed

- Use quiet client shutdown in `FarahServerTestCase` teardown.
- Run automatic browser driver discovery non-interactively with verbose diagnostics.
- Expand real-browser integration tests and GitHub Actions coverage.

## [1.3.9] - 2026-05-12

### Changed

- Declare the DOM, libxml, and sockets extension requirements explicitly.
- Modernize typing and exception handling throughout the package.

### Fixed

- Throw `BadMethodCallException` for unsupported `MockManifest` operations instead of returning no value from methods with required return types.
- Correct shell script line endings for execution on Linux.

## [1.3.8] - 2026-04-02

### Fixed

- Require Farah 1.28.2 or later in the 1.x series for the PHP 8.2 dependency combination.

## [1.3.7] - 2026-04-02

### Changed

- Restore PHP 8.2 support and allow Farah 1.x dependency resolution.

## [1.3.6] - 2026-04-02

### Fixed

- Declare BrowserKit 7.4.8 or later in the 7.x series as a direct dependency.

## [1.3.5] - 2026-04-02

### Fixed

- Constrain Panther to 2.2.x-compatible releases, DomCrawler to 7.4.x-compatible releases, and WebDriver to 1.16.x-compatible releases.

## [1.3.4] - 2026-04-02

### Changed

- Add PHP 8.5 CI coverage and raise the minimum PHP version to 8.3.
- Raise the minimum Core, Farah, and BDI versions for the updated runtime and browser tooling.

## [1.3.3] - 2026-03-12

### Changed

- Allow Panther 2.x and later versions satisfying the existing minimum version, rather than restricting installations to Panther 1.x.

## [1.3.2] - 2026-02-24

### Changed

- Simplify DOM comparison serialization with `iterator_to_array()`.

## [1.3.1] - 2026-02-08

### Fixed

- Replace the deprecated `FILE_TEXT` default with `0` in `FileEqualsTextFile`.

## [1.3.0] - 2026-02-08

### Added

- `FileEqualsTextFile` for line-by-line text file comparisons with configurable PHP `file()` options.

### Changed

- Expose the default binary comparison row length as `FileEqualsFile::DEFAULT_ROW_LENGTH`.

### Fixed

- Honor boolean-result mode for binary comparison failures instead of allowing comparison exceptions to escape.

## [1.2.0] - 2026-01-17

### Added

- `FileEqualsFile` for byte-for-byte file comparisons with readable binary difference output.

## [1.1.8] - 2026-01-07

### Changed

- Raise minimum dependencies to Core 1.14.14, Farah 1.26.18, WebDriver 1.16.0, and PHPUnit 9.6.31.
- Remove the redundant development-only PHPUnit requirement; PHPUnit remains a runtime dependency for consumers of the testing helpers.

## [1.1.7] - 2025-12-23

### Fixed

- Use more specific assertions when checking sitemap page redirects.

## [1.1.6] - 2025-12-23

### Fixed

- Accept HTTP 3xx responses when testing asset and sitemap links.

## [1.1.5] - 2025-12-01

### Added

- SVG link discovery in `LinkCrawler`.

## [1.1.4] - 2025-12-01

### Fixed

- Only interpret link elements and attributes in their expected XML namespaces, avoiding unrelated same-name elements.

## [1.1.3] - 2025-11-14

### Added

- `TestUtils::changeWorkingDirectoryToComposerRoot()` for locating the project or installed package Composer root.

### Changed

- Initialize module and browser-server test cases from the Composer root directory.

## [1.1.2] - 2025-10-23

### Fixed

- Make `FarahServer::quit()` safe before startup and after an earlier shutdown by checking and clearing the server manager.

## [1.1.1] - 2025-10-23

### Fixed

- Validate module asset directories and pass their resolved absolute paths to the test server.

## [1.1.0] - 2025-10-23

### Added

- `FarahServerTestCase` with server and client setup hooks and automatic lifecycle management.
- Automatic `FarahServer` shutdown on destruction.

## [1.0.6] - 2025-10-22

### Fixed

- Declare DomCrawler as a direct dependency and allow newer DomCrawler and WebDriver major versions satisfying their minimum versions.

## [1.0.5] - 2025-10-22

### Fixed

- Declare WebDriver 1.15.2 or later in the 1.x series as a direct dependency.

## [1.0.4] - 2025-10-22

### Fixed

- Find the BDI executable when this package is installed deeper in a consumer's `vendor` directory.

## [1.0.3] - 2025-10-22

### Fixed

- Supply explicit headless Firefox and Chrome arguments, including Windows Firefox options and a consistent browser window size.

## [1.0.2] - 2025-10-21

### Added

- `BrowserDriverNotFoundException` with expected executable names and available directory contents.

### Fixed

- Locate and invoke BDI directly using the current PHP executable.
- Improve DOM comparison failure reporting with the expected and actual XML representations.

## [1.0.1] - 2025-10-21

### Changed

- Require Core 1.14 and Farah 1.23.

## [1.0.0] - 2025-10-21

### Added

- Initial public release of constraints and PHPUnit test suite helpers for Farah modules, manifests, XML assets, sitemaps, and links.
- `DOMNodeEqualTo` and `DOMDocumentIsValidAccordingToSchema` constraints.
- `FarahServer` for local HTTP integration tests with Firefox or Chrome through Panther and automatic BDI driver installation.
- Module test caching and a mock manifest implementation.

[Unreleased]: https://github.com/Faulo/slothsoft-farah-testing/compare/1.3.13...HEAD
[1.3.13]: https://github.com/Faulo/slothsoft-farah-testing/compare/1.3.12...1.3.13
[1.3.12]: https://github.com/Faulo/slothsoft-farah-testing/compare/1.3.11...1.3.12
[1.3.11]: https://github.com/Faulo/slothsoft-farah-testing/compare/1.3.10...1.3.11
[1.3.10]: https://github.com/Faulo/slothsoft-farah-testing/compare/1.3.9...1.3.10
[1.3.9]: https://github.com/Faulo/slothsoft-farah-testing/compare/1.3.8...1.3.9
[1.3.8]: https://github.com/Faulo/slothsoft-farah-testing/compare/1.3.7...1.3.8
[1.3.7]: https://github.com/Faulo/slothsoft-farah-testing/compare/1.3.6...1.3.7
[1.3.6]: https://github.com/Faulo/slothsoft-farah-testing/compare/1.3.5...1.3.6
[1.3.5]: https://github.com/Faulo/slothsoft-farah-testing/compare/1.3.4...1.3.5
[1.3.4]: https://github.com/Faulo/slothsoft-farah-testing/compare/1.3.3...1.3.4
[1.3.3]: https://github.com/Faulo/slothsoft-farah-testing/compare/1.3.2...1.3.3
[1.3.2]: https://github.com/Faulo/slothsoft-farah-testing/compare/1.3.1...1.3.2
[1.3.1]: https://github.com/Faulo/slothsoft-farah-testing/compare/1.3.0...1.3.1
[1.3.0]: https://github.com/Faulo/slothsoft-farah-testing/compare/1.2.0...1.3.0
[1.2.0]: https://github.com/Faulo/slothsoft-farah-testing/compare/1.1.8...1.2.0
[1.1.8]: https://github.com/Faulo/slothsoft-farah-testing/compare/1.1.7...1.1.8
[1.1.7]: https://github.com/Faulo/slothsoft-farah-testing/compare/1.1.6...1.1.7
[1.1.6]: https://github.com/Faulo/slothsoft-farah-testing/compare/1.1.5...1.1.6
[1.1.5]: https://github.com/Faulo/slothsoft-farah-testing/compare/1.1.4...1.1.5
[1.1.4]: https://github.com/Faulo/slothsoft-farah-testing/compare/1.1.3...1.1.4
[1.1.3]: https://github.com/Faulo/slothsoft-farah-testing/compare/1.1.2...1.1.3
[1.1.2]: https://github.com/Faulo/slothsoft-farah-testing/compare/1.1.1...1.1.2
[1.1.1]: https://github.com/Faulo/slothsoft-farah-testing/compare/1.1.0...1.1.1
[1.1.0]: https://github.com/Faulo/slothsoft-farah-testing/compare/1.0.6...1.1.0
[1.0.6]: https://github.com/Faulo/slothsoft-farah-testing/compare/1.0.5...1.0.6
[1.0.5]: https://github.com/Faulo/slothsoft-farah-testing/compare/1.0.4...1.0.5
[1.0.4]: https://github.com/Faulo/slothsoft-farah-testing/compare/1.0.3...1.0.4
[1.0.3]: https://github.com/Faulo/slothsoft-farah-testing/compare/1.0.2...1.0.3
[1.0.2]: https://github.com/Faulo/slothsoft-farah-testing/compare/1.0.1...1.0.2
[1.0.1]: https://github.com/Faulo/slothsoft-farah-testing/compare/1.0.0...1.0.1
[1.0.0]: https://github.com/Faulo/slothsoft-farah-testing/releases/tag/1.0.0
