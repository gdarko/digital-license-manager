=== Digital License Manager ===
Contributors: darkog, codeverve
Tags: license key, license manager, software license, serial key, woocommerce
Requires at least: 4.7
Requires PHP: 5.6
Tested up to: 6.4
Stable tag: 1.5.6
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Sell and manage your digital licenses through your WordPress or WooCommerce website

== Description ==

**Digital License Manager is licensing plugin for WordPress that can be used sell your license keys form your site 😎**

The plugin is compatible with WooCommerce, although it can be used as standalone License Manager without WooCommerce.

### ✨ Core Features

* Supperts Licenses (key, status, activations)
* Supports License Activations (in separate database table table)
* Supports Generators (Allows to configure & generate license keys in specific format)
* WooCommerce HPOS (<a href="https://woocommerce.com/document/high-performance-order-storage/">High-Performance Order Storage</a>) Support
* Supports generating a license on product purchase with assigned Generator
* Supports delivering a license on product purchase from stock (stock licenses with ACTIVE status)
* Supports both Simple and Variable products for License key delivery
* Manual License Activations in WooCommerce MyAccount page
* Displays all the Licenses in WooCommerce MyAccount page
* Displays each License on a separate subpage in WooCommerce My Account page
* Supports downloading License Certificate in PDF Format from the licnese page in WooCommerce My Account
* Option to include purchased License in the WooCommerce order confirmation email
* Option to select on which WooCommerce order statuses to deliver Licenses
* Rest API endpoints for Licenses (create, update, delete, list, show, activate, deactivate, validate)
* Rest API endpoints for Generators (create, update, delete, list, show, generate licenses)
* Rest API protected with API Key and configurable permissions per API Key
* Option to Import licenses form CSV file
* Option to Export licenses to PDF or CSV file. Allows column selection
* Migration tool for migrating from other plugins like License Manager for WooCommerce
* Support for "WooCommerce PDF Invoices and Packing Slips" by WP Overnight to display license key in invoice
* Gutenberg Block that lists all the licenses owned by the current user
* Gutenberg Block that provides a form where user can check validity of a license key
* Shortcode "[dlm_licenses_table]". Lists the licenses owned by the current user
* Shortcode "[dlm_licenses_check]"  Displays a form where user can check validity of a license key
* PHP functions and hooks for developers, easily extendible.

The plugin works fine even without WooCommerce. If you want to use the plugin as standalone license manager or if you have another solution for selling the keys, you can use the REST API to create licenses.

### ✨ Additional Features

Our PRO version focuses on support for WooCommerce Subscriptions, detailed software showcase in the product page tabs, software update delivery through special REST API endpoints and can be configured as WordPress update server to deliver updates to your premium plugins/themes.

List of the additional features as follows:

* WooCommerce Subscriptions <a href="https://docs.codeverve.com/digital-license-manager/handbook/woocommerce/subscriptions/" target="_blank" rel="noopener">support</a>. Extend or generates new license upon subscription renewal.
* Subscriptions for WooCommerce support, a free alternative to WooCommerce Subscriptions. Extend or generates new license upon subscription renewal.
* <a href="https://docs.codeverve.com/digital-license-manager/handbook/software/managing-software/" target="_blank" rel="noopener">Software management</a>. If you sell software, you will be able to add your software, setup a gallery, support information, FAQs that appear on the product page.
* <a href="https://docs.codeverve.com/digital-license-manager/handbook/software/manage-releases/" target="_blank" rel="noopener">Software release management</a>. Downloadable software can be distributed as a releases. For each version you create a release in the Software editor.
* Software <a href="https://docs.codeverve.com/digital-license-manager/rest-api/software/single/" target="_blank">details REST Endpoint</a> to get information about specific software. This is useful if you want to perform update check from your premium software.
* Software <a href="https://docs.codeverve.com/digital-license-manager/rest-api/software/download/" target="_blank">download REST Endpoint</a> to download the latest release for specific software. This is useful if you want to provide updates for your software.
* Separate Battle-tested and well-documented WordPress package to create License Activation form and deliver plugin/theme updates through your Digital License Manager Software API.
* Ready to use <a href="https://docs.codeverve.com/digital-license-manager/wordpress-theme-plugin-updates/" target="_blank" rel="noopener">theme/plugin updater</a> library with integration guide. Useful if you want to integrate license activations to your plugins/themes.
* Additional plugin <a href="https://docs.codeverve.com/digital-license-manager/handbook/configuration/#WooCommerce" target="_blank" rel="noopener">Options</a> to enable or disable features like the "Licenses" access in My Account.
* WPML support. The plugin will share the stock to the translations of the original product. If you configure licenses from stock on the original product then you pick the same setting on the translation products, it should work correctly.
* Polylang support. (Coming soon)
* Software download statistics (Coming soon)

<a href="https://codeverve.com/product/digital-license-manager-pro/" target="_blank">Get PRO Version</a>

### 📃 REST API Documentation

The REST API is one of the crucial features that this plugin provides.

It allows developers to create/update/activate/deactivate/validate licenses through the API.

The REST API documentation can be found on the link below:

<a href="https://docs.codeverve.com/digital-license-manager/rest-api/" target="_blank">See Documentation</a>

### 📃 All Documentation

The complete documentation can be found on the link below:

<a href="https://docs.codeverve.com/digital-license-manager/handbook/woocommerce/products/" target="_blank">See Documentation</a>

### 📃 Client Libraries

The following client libraries and integrations are available:

* <a href="https://github.com/ideologix/dlm-php" target="_blank">PHP Library</a>
* <a href="https://github.com/ideologix/DLM-NET" target="_blank">C# Library</a>
* <a href="https://github.com/ideologix/dlm-python" target="_blank">Python Library</a>
* <a href="https://github.com/ideologix/dlm-plugin-pro" target="_blank">Premium WordPress Updater (Requires PRO)</a>
* <a href="https://github.com/ideologix/dlm-plugin-pro" target="_blank">Premium Plugin Example (Requires PRO)</a>
* <a href="https://github.com/ideologix/dlm-theme-pro" target="_blank">Premium Theme Example (Requires PRO)</a>

### ➕ Issues / Feature Requests

The development can be tracked on our <a href="https://github.com/gdarko/digital-license-manager">Github Repository</a>.

Any contributions are welcome! Feel free to submit pull requests or report issues.

* <a href="https://github.com/gdarko/digital-license-manager">Digital License Manager on Github</a>


### 👏 Acknowledgements

This plugin was early fork of License Manager for WooCommerce by Drazen Bebic and WPExperts. However, nowadays, the code has been completely rewritten in order to modernize it and provide better support, stability and new features.

Other acknowledgments: Micromodal, Tom-Select, Flatpickr, defuse/PHP-Encryption, spipu/html2pdf, tecnickcom/tcpdf, 10quality/wp-query-builder, ignitekit/wp-notices.

Props to <a href="https://profiles.wordpress.org/pondermatic/">@pondermatic</a> for contributing to our plugin.


### ✔ Important Note

The plugin will create  `wp-content/uploads/dlm-files` directory which stores the cryptographic secrets that are required to decrypt the licenses. If you lose this file, you will lose your license keys as well. **Make sure you backup those files!**

== Installation ==

### Manual installation

1. Upload the plugin files to the `/wp-content/plugins/digital-license-manager` directory, or install the plugin through the WordPress *Plugins* page directly.
1. Activate the plugin through the *Plugins* page in WordPress.
1. Use the *License Manager* → *Settings* page to configure the plugin.

### Installation through WordPress

1. Open up your WordPress Dashboard and navigate to the *Plugins* page.
1. Click on *Add new*
1. In the search bar type "Digital License Manager"
1. Select this plugin and click on *Install now*

### Important

The plugin will create  `wp-content/uploads/dlm-files` directory which stores the cryptographic secrets that are required to decrypt the licenses. If you lose this file, you will lose your license keys as well. **Make sure you backup those files!**

== Frequently Asked Questions ==

= Does it work without WooCommerce? =

Yes it does.

= How License Activations work? =

The License activations are stored as a separate database entries and not using a counter. This allows easier management of License Activation per device/installation/etc.

= I am using "License Manager for WooCommerce", how to migrate to Digital License Manager?

Looking to migrate from License Manager for WooCommerce? <a href="https://docs.codeverve.com/digital-license-manager/migration/migrate-from-license-manager-for-woocommerce/">Read more</a>!

= How to create a license through the REST API? =

To create a license through the REST API, please follow this <a href="http://docs.codeverve.com/digital-license-manager/rest-api/licenses/create/" target="_blank">guide</a>.

= How to activate a license through the REST API? =

To activate a license through the REST API, please follow this <a href="http://docs.codeverve.com/digital-license-manager/rest-api/licenses/activate/" target="_blank">guide</a>.

= How to deactivate a license through the REST API? =

To deactivate a license through the REST API, please follow this <a href="http://docs.codeverve.com/digital-license-manager/rest-api/licenses/deactivate/" target="_blank">guide</a>.

= How to validate a license through the REST API? =

To validate a license through the REST API, please follow this <a href="http://docs.codeverve.com/digital-license-manager/rest-api/licenses/validate/" target="_blank">guide</a>.

= Can I assign license keys to past WooCommerce orders that were placed before i installed Digital Licnese Manager?

Yes, you can do this by going to Settings > Tools > "Generate Licneses For Past Orders". You must select a Generator that will be used to generate keys for the orders.

== Screenshots ==

1. Licenses - Overview page
2. Licenses - Add/Edit form
3. Licenses - CSV Export form
4. Generators - Overview page
5. Generators - Add/Edit form
6. Generators - "Generate" page for generating license keys
7. Activations - Overview page
8. Settings - General
9. Settings - WooCommerce (if WooCommerce is activated)
10. Settings - API Keys - Overview
11. Settings - API Keys - Add/Edit form
12. Settings - Tools page. Includes tool for Migration from License Manager for WooCommerce, etc
13. My Account - Licenses overview
14. My Account - Single license page, includes: basic details, certificate download and activations log table
15. My Account - Single license page > Manual Activation Form (if enabled)
16. My Account - Single license page > PDF Certificate download
17. My Account - Single order page that includes licenses
18. Order Email - Shows the licenses
19. Re-send licenses via Order page
20. Gutenberg blocks for user licenses table and license check form
21. Shortcodes for user licenses table and license check form
22. User licenses table displayed by using block or shortcode
23. License check form displayed by using block or shortcode

== Changelog ==

= 1.5.6 =
*Release date - 01 Dec 2023*

* Fix the License Activations table display in My Account
* Fix issues related to API calls
* Improvements to single License/Generator UI
* Improvements to the uninstallation cleanup procedure
* Removed unused wp_ajax endpoint

= 1.5.5 =
*Release date - 27 Nov 2023

* Fix issue related to notice display

= 1.5.4 =
*Release date - 27 Nov 2023*

* Improvements to the <a href="https://docs.codeverve.com/digital-license-manager/migration/migrate-from-license-manager-for-woocommerce/">migration tool</a> from License Manager for WooCommerce
* Add <a href="https://docs.codeverve.com/digital-license-manager/migration/migrate-from-license-manager-for-woocommerce/">Compatibility layer</a> for the REST API for License Manager for WooCommerce
* Add public <a href="https://github.com/gdarko/digital-license-manager/blob/master/helpers.php">helper functions</a> that can be used to interact with the plugin's database
* Add migration notices
* Fixes various smaller issues here and there
* Improves the unit tests collection

= 1.5.3 =
*Release Date - 22 Oct 2023*

* Fix activating license as customer "permission denied" from My Account page
* Update block dependencies
* Test with WooCommerce 8.2

= 1.5.2 =
*Release Date - 20 Sep 2023*

* Fix High-Performance-Order-Storage compatibility issues
* Fix issue when generating bulk licenses from the Generators > Generate page
* Add filter to allow adjustments to the WooCommerce product edit tab classes
* Fix confusing logic on the dlm_validate_product/order_id filters

= 1.5.1 =
*Release Date - 16 Sep 2023*

* Fix issue that caused no license key to be sent via email/order thank you page
* Add Gutenberg Block: "DLM: Licenses Table" -> The block lists the licenses owned by the current user
* Add Gutenberg Block: "DLM: Licenses Check" -> The block provides a form where user can check validity of a license key
* Add Shortcode: "[dlm_licenses_table]" -> The shortcode lists the licenses owned by the current user
* Add Shortcode: "[dlm_licenses_check]" -> The shortcode provides a form where user can check validity of a license key
* Add support for "WooCommerce PDF Invoices and Packing Slips" by WP Overnight. License key will be displayed in the invoice.


= 1.5.0 =
*Release Date - 01 Aug 2023*

* Add copyright information to the files, a nice welcome screen.
* Add more unit tests
* Improvements to the data api
* Improvements to the order_complete meta saving
* Improvements to the license stock database query
* Fix PHP warnings, improve PHP 8.2 compatibility
* Fix generator crud bugs
* Test with WordPress 6.3
* Test with WooCommerce 8.0

= 1.4.4 =
*Release Date - 25 Jul 2023*


= 1.4.3 =
*Release Date - 03 May 2023*

* Do not enforce expired license validation when deactivting token through the REST API
* Add option in Settings > WooCommerce to enable masking the license keys on the public facing pages like Order Received" page
* Show My Account licenses status as Expired when license is expired
* Fix issue when clearing Generator valeus in edit page
* Move "Max Activations" at the bottom before expiresIn in Generator edit
* Add support on the dropdown search endpoint for generators
* Add "Past Orders License Generator" tool in Settings > Tools that allows users to generate licenses for past orders by selectting Generator.
* Add UI improvements to the styling of the Tools page
* Add unit tests for orders/products

= 1.4.2 =
*Release Date - 22 Mar 2023*

* Fix for PHP wranings caused by the new activations table revamp reported <a href="https://github.com/gdarko/digital-license-manager/issues/20">here</a>
* Various improvements to the source code for flexibility
* Minor improvements for the License Service

= 1.4.1 =
*Release Date - 13 Feb 2023*

* Hotfix for Product Edit page issue in the License Manager tab, conditionals were not working

= 1.4.0 =
*Release Date - 13 Feb 2023*

* Add support for <a target="_blank" href="https://woocommerce.com/document/high-performance-order-storage/">WooCommerce High Performance Order Storage</a>
* Add support to filter /licenses and /generators GET REST API endpoints with query parameters
* Add Manual License Activations to allow customers enter manual activations, feature enabled from Settings
* Refactored My Account's single license activations table actions, allow developers to add custom actions here
* Add Delete button on the Activations that were added manually in My Account's single license activations table
* Removed jQuery relience, refactored all the javascript to get rid of jQuery completely
* Replaced Select2 (jQuery) with Tom-Select, a native JS library for select dropdowns with ajax support
* Replaced jQuery UI's datepicker with flatpicker, a native JS library for date/time picker input
* Refactored and simplified license reveal functionality in WooCommerce single order page in admin
* Add a filter dlm_woocommerce_order_item_actions for managing the order item actions in the WooCommerce single order page in admin
* Fixed issue introduced in v1.3.9 that thrown error when updating a license from admin
* Tested compatibility with WordPress 6.2
* Tested compatibility with WooCommerce 7.3
* A lot of other small code quality improvements

= 1.3.9 =
*Release Date - 08 Jan 2023*

* Add Generators Abstraction layer/hook and Abstract class. <a href="https://docs.codeverve.com/digital-license-manager/handbook/generators/extending-abstraction/">Read more</a>
* Add "Max Activations Behavior" option to "Product Edit" > "License Manager" to make the purchased quantity of the product to be used as max activations limit, by default the old behavior will be preserved and the limit will depend on the Generator/Stock
* Add `$orderItem` parameter to the `dlm_skip_licenses_generation_for_order_product` filter
* Add services layer, move business logic code form Utils\Data to Core\Services, mark the old ones as deprecated and keep it backwards compatible
* Fix PHP warnings on certain actions
* Update i18n .pot template

= 1.3.8 =
*Release Date - 16 Dec 2022*

* Fix possible fatal error when using kadence woocommerce email customizer
* Test compatibility with WooCommerce 7.2

= 1.3.7 =
*Release Date - 12 Dec 2022*

* Fix logic error that made the order note when no licenses in stock found incorrect
* Add action hook `dlm_stock_delivery_assigned_licenses($assignedLicenses, $neededAmount, $availableStock, $order, $product)` that is triggered once licenses from stock assignment finished. Useful to notify third party something based on the delivered licenses or if no licenses were delivered due to empty stock
* Add duplicate check to Licenses import based on plugin settings for allowing duplicates
* Add duplicate check warnings into admin notices when importing licenses
* Fix issue in Licenses import that allowed empty lines to be imported as empty licenses from clipboard or file
* Refactor file import process, moved temporary import file path from plugin folder to uploads
* Refactor order licenses resend email/action, the resend was not working on some environments
* Make use of 'paginate' property for wc_get_products in dropdown search fields
* Code quality and formatting improvements

= 1.3.6 =
*Release Date - 24 Nov 2022*

* Add code and status to error when license key is not found via rest api.
* Refactor Migrator class for better flexibility and reusability, fixes issues related to migrations.

= 1.3.5 =
*Release Date - 14 Nov 2022*

* Add support for assinging product variations to Licenses when selling from stock
* Rewrite stock syncrhonization functionality
* Test with the latest WooCommerce version
* Test with the latest WordPress version

= 1.3.4 =
*Release Date - 04 Oct 2022*

* Fix conflict with WooCommerce subscriptions in the product editor
* Add valid_for that tells how many days the license will be valid after purchased from *stock*
* Improve license create/edit screen field labels wording
* Fix invalid markup on the generator create/edit pages in admin
* Fix generator title badges in the generators list table in admin
* Fix license import functionality, do not set expiry date immediately and make use of the valid_for property
* Fix potential issue with stock decreasing and set validFor=null in save license procedure in admin
* Fix issue warning thrown when deleting licenses in the admin side, props @pondermatic
* Replaced WordPress meta function calls with WooCommerce product/order meta functions calls to comply with upcoming WooCommerce custom tables database implementation

= 1.3.3 =
*Release Date - 20 Aug 2022*

* Added option to specify license expiration date/time format in Settings. Props <a href="https://github.com/gdarko/digital-license-manager/pull/6">@pondermatic</a>
* Fixed issues related to license expiration date/time output consistency. Props <a href="https://github.com/gdarko/digital-license-manager/pull/6">@pondermatic</a>
* Fixed issues with PHP warnings. Props <a href="https://github.com/gdarko/digital-license-manager/pull/6">@pondermatic</a>
* Fixed issues in "License Manager for WooCommerce" data migration tool, related to unhandled PHP exceptions.
* Fixed several typos in "License Manager for WooCommerce" data migration tool.

= 1.3.2 =
*Release Date - 26 Jul 2022*

* Fix typo in the migration file

= 1.3.1 =
*Release Date - 26 Jun 2022*

* Fix PHP warning on Admin > Activations page

= 1.3.0 =
*Release Date - 25 May 2022*

* Added Support for WordPress 6.0 and WooCommerce 6.5
* Added License Certifications feature in PDF format (integrates in WooCommerce and Licenses table admin screens)
* Added RestAPI optional 'token' parameter to the /activate endpoint to reactivate existing license token and not create new one if needed
* Added Single License page in My Account
* Added License Activation log in My Account single License page
* Added Migration tool for migrating from "License Manager for Woocommerce"
* Added permalinks flush mechanism to flush permalinks after plugin activation. Fixes issues with 404 pages in WooCommerce "My Account"
* Added "Help" page in "Settings"
* Removed redundant valid_for column in the licenses table in favor of expires_at
* Fixed issue with the pagination and filters on the License Activations page
* Fixed a problem that caused admins not able to clear activation limit on a license (set unlimited activations)
* Refreshed admin edit/create screens style
* Refreshed admin settings screens style
* Simplified the Licenses list table in admin screen
* Simplified the WooCommerce templates directory tree
* Improved the css/js resource queuing
* Improved PHP 8.1 compatibility
* Refactor the Abstracts/Interfaces naming to support PSR standard
* Upgraded jQuery UI css version to v1.13.1

= 1.2.2 =
*Release Date - 23 Mar 2022*

* Fix various notices found
* Fix license/validate endpoint. It was returning error response when activation token is deactivated. It should return the activation object with the populated deactivated_at prperty.
* Re-write the license generation in WooCommerce
* Improve code, add various hooks for better extendibility.

= 1.2.1 =
*Release Date - 03 Mar 2022*

* Fix Multisite database/fileystem initial setup. Run the database/filesystem setup on each blog once the plugin is activated network wide.

= 1.2.0 =
*Release Date - 15 Dec 2021*

* Complete rewrite of product edit data DLM fields
* Added error display when validation fails upon product save
* Added welcome notice
* Fix admin dropdowns product search
* Fix admin dropdowns order search
* Fix license update process that removed expiration date
* Fix purchased licenses display in the order page
* Fix Generator REST API problems related to the delete endpoint
* Fix API key last access date/time display
* Fix a problem that skipped the creation of the product_downloads table
* Fix activations limit calculations
* Imrpoved REST API validation and handling
* Improved the license expiration display in license table
* Updated the product fields. Instead of use stock and use generator this is now a single dropdown.
* Code style improvements

= 1.1.1 =
*Release Date - 10 Oct 2021*

* Add PRO version references
* Minor bug fixes

= 1.1.0 =
*Release Date - 24 Sep 2021*

* Add delete rest endpoint for Licenses and Generators
* Fix license activation validate endpoint
* Fix dlm_rest_api_pre_response filter
* Fix Generators update endpoint
* Improve "Re-send licenses" button in order screen
* Improve Customer delivery email
* Code style improvements

= 1.0.0 =
*Release Date - 19 Jul 2021*

* Everything is new

== Upgrade Notice ==