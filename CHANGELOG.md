# Changelog
All notable changes to this project will be documented in this file.

## [2.3.8] - 2018-02-20

### Fixed
- fixed dlh_marker for hyphenator

## [2.3.7] - 2018-01-31

### Fixed
- composer.json

## [2.3.6] - 2018-01-31

### Fixed
- map size

## [2.3.5] - 2017-12-18

### Fixed
- contao 3 compatibility, `\Contao\StringUtil::specialchars()` not available in contao 3

## [2.3.4] - 2017-12-11

### Fixed
- changelog

## [2.3.3] - 2017-12-11

### Changed
- replace `delahaye/dlh_googlemaps` with this package

## [2.3.2] - 2017-12-11

### Fixed
- contao 3 requires `contao-community-alliance/composer-plugin` in version `~2.4`

## [2.3.1] - 2017-07-18

### Fixed
- `gmap*_markers is not defined` js error

## [2.3.0] - 2017-07-18

### Fixed
- `contao 4.x` compatibility
 
### Changed

- `imgSize` now always uses `box` option. For full with add % or pcnt to with dimension
- make always usage of api key, global api key can now added to `tl_settings.dlh_googlemaps_apikey`, required for pageless context
- requires now `heimrichhannot/dlh_geocode` and created independent `composer` package within namespace `heimrichhannot/dlh_googlemaps`
