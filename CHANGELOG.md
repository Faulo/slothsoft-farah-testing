# Changelog

## Unreleased

- Store package data, including downloaded browser drivers, under `ServerEnvironment::getDataDirectory()/farah-testing` so it survives cache cleanup and can be reused between CI runs. The development bootstrap uses workspace-local `.data`. GitHub Actions caches the entire package data directory and Jenkins retains it in its existing workspaces.
