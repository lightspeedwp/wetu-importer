=== LSX Content Importer for Wetu ===
Contributors: feedmymedia, lightspeedwp, eleshar, krugazul, 
Tags: lsx, tour operator, tours, destination, accommodation, wetu, importer, travel, tourism
Requires at least: 6.7
Tested up to: 6.7
Requires PHP: 8.0
Stable tag: 1.5
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Plugin URI: https://lsx.design/products/wetu-importer/
Donate link: https://lsdev.biz/lsx/donate/
Author URI: https://lightspeedwp.agency/

This plugin allows website owners to import destination, accommodation and tour content into the LSX Tour Operator plugin via the Wetu.com content API. 

== Description ==
LSX Importer for Wetu integrates with the Wetu Tour Operator system to import destinations, accommodations, and tour content directly into the LSX Tour Operators plugin format. This enables a seamless workflow for travel and tourism websites, ensuring rich itineraries and consistent data structures within your WordPress site.

**Key Features:**
- Imports Wetu destinations, accommodations, and tours.
- Aligns imported data to LSX Tour Operators plugin format.
- Fully compatible with the Gutenberg (block) editor.
- Supports background processing for large-scale imports.
- Requires LSX Tour Operators plugin for optimal functionality.

**Minimum Requirements:**
- WordPress 6.7 or higher
- PHP 8.0 or higher
- LSX Tour Operators plugin active

== Installation ==
1. Install and activate the [LSX Tour Operators](https://wordpress.org/plugins/lsx-tour-operators/) plugin.
2. Download and install LSX Importer for Wetu from WordPress.org or upload it to `/wp-content/plugins/`.
3. Activate the plugin through the 'Plugins' menu in WordPress.
4. Configure your Wetu API credentials in the plugin’s settings page.
5. Start importing Wetu data into your LSX Tour Operators environment.

== Frequently Asked Questions ==
= Do I need the LSX Tour Operators plugin? =
Yes. The LSX Importer for Wetu relies on LSX Tour Operators to properly handle and display imported tour data.

= What happens if I don’t meet the minimum requirements? =
You must have WordPress 6.7 or newer and PHP 8.0 or newer. Without these, the plugin may not function as intended. Consider upgrading your environment before using this plugin.

= How does it handle large imports? =
The plugin uses background processing to manage large imports efficiently, preventing timeouts and performance bottlenecks.

== Screenshots ==
1. **Wetu Import Settings:** Easily configure and manage your Wetu API credentials.
2. **Imported Destinations:** View imported locations seamlessly integrated into LSX Tour Operators.
3. **Itinerary Blocks:** Display your itineraries in beautiful, Gutenberg-compatible layouts.

== Changelog ==
= 1.5 =
* Updated minimum WordPress requirement to 6.7 and PHP requirement to 8.0.
* Added 'Requires Plugins:' header to ensure LSX Tour Operators dependency is clear.
* Improved Gutenberg compatibility for displaying imported itineraries.
* Enhanced Wetu API field mapping and data integrity checks.
* Optimized background processing for large imports.
* Updated plugin headers, URIs, and documentation for clarity and brand consistency.
