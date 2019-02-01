# Changes in sebastian/global-state

All notable changes in `sebastian/global-state` are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [3.0.0] - 2019-02-01

### Changed

* `Snapshot::canBeSerialized()` now recursively checks arrays and object graphs for variables that cannot be serialized

### Removed

* This component is no longer supported on PHP 7.0 and PHP 7.1

[3.0.0]: https://github.com/sebastianbergmann/phpunit/compare/2.0.0...3.0.0

