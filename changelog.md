# Change log

## [[1.4.2]](https://github.com/lightspeeddevelopment/wetu-importer/releases/tag/1.4.2) - 2023-08-09

### Security
- General testing to ensure compatibility with latest WordPress version (6.3).

## [[1.4.1]](https://github.com/lightspeeddevelopment/wetu-importer/releases/tag/1.4.1) - 2023-04-20

### Added
- Allowing the itineraries to retain any custom images set
- Added in an option to replace any custom itierary images during import.

### Update
- Adding in a verbose error message for failed tours.

### Security
- General testing to ensure compatibility with latest WordPress version (6.2).

## [[1.4.0]](https://github.com/lightspeeddevelopment/wetu-importer/releases/tag/1.4.0) - 2022-12-23

### New Features
- Allowing the import of "mobile" tours and their associated accommodation and destinations.

### Minor Feature Additions
- Added in the option to disable the tours featured image and banner image, to allow for manually assigned images on the site.
- A filter to allow the altering of the tours list from the API `lsx_wetu_tour_refresh_url`

### Updated
- Passing the `lsx_wetu_importer_tour_travel_styles` filter with the current tour ID and WETU Importer object.

### Security
- General testing to ensure compatibility with latest WordPress version (6.1.1).

## [[1.3.8]](https://github.com/lightspeeddevelopment/wetu-importer/releases/tag/1.3.8) - 2022-02-02

### Updated
- Updated the https://datatables.net/ JS an CSS to the latest version.

### Security
- General testing to ensure compatibility with latest WordPress version (5.9).
- Converted the changelog.txt to changelog.md

## [[1.3.7]](https://github.com/lightspeeddevelopment/wetu-importer/releases/tag/1.3.7) - 2021-06-23

### Added 
- An exclusion for the "Missing Point" accommodation. 

## [[1.3.6]](https://github.com/lightspeeddevelopment/wetu-importer/releases/tag/1.3.6) - 2021-05-05

### Fixed 
- Fixing the importing of the accommodation banner.

### Code Quality
- Updating the code structure using PHPCS and PHPCBF

## [[1.3.5]](https://github.com/lightspeeddevelopment/wetu-importer/releases/tag/1.3.5) - 2021-04-21

### Fixed 
- Removed the conditional statements checking for the LSX Banners plugin.

### Security
- General testing to ensure compatibility with latest WordPress version (5.7).

## [[1.3.4]](https://github.com/lightspeeddevelopment/wetu-importer/releases/tag/1.3.4) - 2021-01-15

### Updated
- Documentation and support links

### Security
- General testing to ensure compatibility with latest WordPress version (5.6).

## [[1.3.3]](https://github.com/lightspeeddevelopment/wetu-importer/releases/tag/1.3.3) - 2020-03-26

### Fixed
- Importer input filter styling.

### Security
- General testing to ensure compatibility with latest WordPress version (5.4).
- General testing to ensure compatibility with latest LSX Theme version (2.7).


## [[1.3.2]](https://github.com/lightspeeddevelopment/wetu-importer/releases/tag/1.3.2) - 2020-02-19

### Added
- Allowing the destinations to import and search for "Areas" as well. 
- Separated the `departs_from` and `ends_in` fields into their own function.
- Moving the API info tooltip to a better position.

### Changed
- Moved the `set_team_member` function to the `LSX_WETU_Importer()` class.

### Fixed
- Fixed an error in the set_country function prohibiting certain tours from completing.
- Fixed the index key of the itinerary accommodation and destinations when importing a mobile safari.


## [[1.3.1]](https://github.com/lightspeeddevelopment/wetu-importer/releases/tag/1.3.1) - 2019-10-02

### Added
- Adding extra instructions on the importer.
- Adding mobile tooltip for documentation.
- Removing the conditional statements removing certain fields when the single template is disabled.
- Added in support for Mobile Safaris.


## [[1.3.0]](https://github.com/lightspeeddevelopment/wetu-importer/releases/tag/1.3.0) - 2019-09-06

### Added
- Added the sortable Reference Column for tours.
*- Added in the reference field to be imported to tours.
- Improved the styling of the Welcome page steps.
- Added in a function to capture the continent sub region.
- Added in datatables.net for pagination and sorting on the lists.
- Added in the ability to define custom titles.
- Added in the option to randomize the featured image that is grabbed from the attached destinations.
- Added in a WETU ID for images which stores the URL fragment from WETU.
- Removed the "All Itineraries" Option from the Tour Importer and set "Personal" as the default.
- Allowing the Destinations to import "Areas" from WETU as well.

### Fixed
- Fixed the sorting of the search results by relevance.
- Fixed the assigning of team and safari brands.
- Fixed the Connecting of destinations during the tour import.
- Fixed the stripping of HTML from the accommodation description.
- Fixed the accommodation description and excerpt not importing.


## [[1.2.1]](https://github.com/lightspeeddevelopment/wetu-importer/releases/tag/1.2.1) - 2019-07-09

### Added
- Removed the TO Maps plugin dependencies.

### Fixed
- Fixed the problem with the empty API key.
- Fixed the WETU tour search.
- Fixed the styling of the search on the accommodation and destination tabs.


## [[1.2.0]](https://github.com/lightspeeddevelopment/wetu-importer/releases/tag/1.2.0) - 2019-06-18

### Added
- Upgraded Wetu API to V8.
- Added in support for the group size and the tags fields.
- Coding Standards Updates
- Added UI enhancements for the Tour Importer Page
- Moved the settings tab to the importer page.

### Fixed
- Updated the 'file_get_contents()' call with 'wp_remote_get()'


## [[1.1.2]]()

### Changed
- Removing the need to index the accommodation and destination items from WETU.
- Changed the Accommodation and Destination search to use the WETU API Search.


## [[1.1.1]]()

### Added
- Added in a destination search which searches the cached "List" from the WETU content API.

### Changed
- Changed the continents taxonomy to only apply to the countries and not the regions.

### Fixed
- Tours no longer use the same destination for the featured and the banner image.


## [[1.1.0]](https://github.com/lightspeeddevelopment/wetu-importer/releases/tag/v1.1.0) - 2019-06-18

### Added
- Added compatibility with Tour Operator 1.1.

### Fixed
- Fixed small issues.


## [[1.0.0]](https://github.com/lightspeeddevelopment/wetu-importer/releases/tag/v1.0.1) - 2016-12-16

### Added
- First Version.
