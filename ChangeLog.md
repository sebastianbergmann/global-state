# Changes in sebastian/global-state

All notable changes in `sebastian/global-state` are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [5.0.1] - 2020-09-28

### Changed

* Changed PHP version constraint in `composer.json` from `^7.3 || ^8.0` to `>=7.3`

## [5.0.0] - 2020-08-07

### Changed

* The `SebastianBergmann\GlobalState\Blacklist` class has been renamed to `SebastianBergmann\GlobalState\ExcludeList`

## [4.0.0] - 2020-02-07

### Removed

* This component is no longer supported on PHP 7.2

## [3.0.0] - 2019-02-01

### Changed

* `Snapshot::canBeSerialized()` now recursively checks arrays and object graphs for variables that cannot be serialized

### Removed

* This component is no longer supported on PHP 7.0 and PHP 7.1

[5.0.1]: https://github.com/sebastianbergmann/global-state/compare/5.0.0...5.0.1
[5.0.0]: https://github.com/sebastianbergmann/global-state/compare/4.0.0...5.0.0
[4.0.0]: https://github.com/sebastianbergmann/global-state/compare/3.0.0...4.0.0
[3.0.0]: https://github.com/sebastianbergmann/global-state/compare/2.0.0...3.0.0

