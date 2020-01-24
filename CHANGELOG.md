# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.0.4 - TBD

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.0.3 - 2020-01-24

### Added

- [#2](https://github.com/mezzio/mezzio-authorization-rbac/pull/2) adds support for PHP 7.4.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#1](https://github.com/mezzio/mezzio-authorization-rbac/pull/1) fixes RBAC authorization adapter to grant access on request with failed routing.

## 1.0.2 - 2019-06-18

### Added

- [zendframework/zend-expressive-authorization-rbac#18](https://github.com/zendframework/zend-expressive-authorization-rbac/pull/18) adds support for PHP 7.3.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.0.1 - 2018-09-17

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-expressive-authorization-rbac#17](https://github.com/zendframework/zend-expressive-authorization-rbac/pull/17) fixes exception messages on invalid configuration to refer to the
  configuration key `mezzio-authorization-rbac` instead of `authorization`.

## 1.0.0 - 2018-09-13

### Added

- Nothing.

### Changed

- [zendframework/zend-expressive-authorization-rbac#16](https://github.com/zendframework/zend-expressive-authorization-rbac/pull/16)
  pins to the 1.0 series of zendframework/zend-expressive-authorization.
  Changed the service configuration key to `zend-expressive-authorization-rbac`
  instead of `authorization`.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.3.1 - 2018-05-15

### Added

- [zendframework/zend-expressive-authorization-rbac#15](https://github.com/zendframework/zend-expressive-authorization-rbac/pull/15) adds support for the v3 release tree of laminas-permissions-rbac, as the
  API consumed by this package is unchanged.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.3.0 - 2018-03-15

### Added

- Nothing.

### Changed

- Locks mezzio-authorization to the 0.4.0 series.

- Updates the mezzio-router constraint to `^3.0`.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.2.0 - 2018-02-27

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-expressive-authorization-rbac#12](https://github.com/zendframework/zend-expressive-authorization-rbac/pull/12)
  removes all exceptions from the package, in favor of using exceptions defined
  in mezzio-authorization.

### Fixed

- Nothing.

## 0.1.4 - 2018-02-20

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-expressive-authorization-rbac#11](https://github.com/zendframework/zend-expressive-authorization-rbac/pull/11)
  fixes missing configuration factories key.

## 0.1.3 - 2017-12-13

### Added

- [zendframework/zend-expressive-authorization-rbac#9](https://github.com/zendframework/zend-expressive-authorization-rbac/pull/9)
  adds support for the 1.0.0-dev branch of mezzio-authorization.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.1.2 - 2017-11-28

### Added

- Adds support for mezzio-authorization 0.2 and 0.3.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.1.1 - 2017-09-28

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Updates the minimum PHPUnit version required, to ensure tests run under lowest
  supported versions.

## 0.1.0 - 2017-09-28

Initial release.

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
