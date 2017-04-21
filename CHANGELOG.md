Changelog
=========

The format is based on [Keep a Changelog][1]
and this project adheres to [Semantic Versioning][2].

## [Unreleased]
### Added
- Add support for nginx

## [1.4.1] - 2017-04-21
### Fixed
- Fix `autoopen` bug, which caused problems on sites which did not use the
  default port 80. Now `autoopen` uses right port number and right protocol
  (e.g. https instead of http if SSL is enabled).

## [1.4.0] - 2017-04-20
### Added
- Add this Changelog so you can see how the application gets better.
- Add `autoopen` feature. With `autoopen` enabled clamp will open the
  [URI][3] configured in `servername` of apache options.

## [1.3.3] - 2016-11-10
### Fixed
- Fix apache configuration issues for MacOS Sierra.

## [1.3.2] - 2015-10-29
### Fixed
- Fix apache configuration issues for MacOS El Capitan.

## [1.3.1] - 2014-11-20
### Added
- Add support for homebrew tap.
### Changed
- Installation process now depends on homebrew tap.

### Fixed
- Fix apache configuration issues for MacOS El Capitan.

## [1.2] - 2014-11-20
### Fixed
- Fix Homebrew formula.

## [1.1] - 2014-11-20
### Added
- Add Homebrew support and adapt installation process to it.

## [1.0] - 2014-10-23

[1]: http://keepachangelog.com/
[2]: http://semver.org/
[3]: https://danielmiessler.com/study/url-uri/
