=== Digital License Manager ===
Contributors: darkog
Tags: license key, license, key, software license, serial key, manager, woocommerce, wordpress
Requires at least: 4.7
Tested up to: 5.8
Stable tag: 1.1.1
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Easily manage and sell your digital licenses through your WordPress website

== Description ==

**Digital License Manager is licensing plugin for WordPress that can be used to keep track of and sell your licenses form your site. Supported and maintained by friendly team behind. 😎**

The plugin is compatible with WooCommerce although it can be used as standalone License Manager without WooCommerce.

### ✨ Core Features

* Manage your Licenses (status, license key, activations, etc)
* Manage License Activations. Keeps separate records of Activations identified by unique token instead of single activations count variable.
* Manage License Generators. Makes it possible to generate Licenses by specific rules (separator, chunks, length, allowed characters, prefix, suffix etc)
* Assign Generators to one or more WooCommerce products to generate Licenses based on the rules of the Generator when the product is purchased on certain Order status
* Automatically generate, sell and deliver keys through WooCommerce. Supports both Simple and Variable products.
* Brings separate page for Licenses in MyAccount when using with WooCommerce
* The purchased Licenses are included in the WooCommerce Emails (completed order, etc)
* License delivery Order statuses can be selected from the plugin Settings
* Create, update, activate, deactivate and validate licenses through the REST API
* Create and update generators through the REST API
* Import licenses form file
* Export licenses to PDF or CSV format. Allows column selection
* Works even without WooCommerce. If you want to use the plugin as standalone license manager or if you have another solution for selling the keys, you can use the REST API to create licenses.

### ✨ PRO Features

The PRO version focuses on making it possible to generate consistent cash flow through the integration with WooCommerce Subscriptions and provide updates through the Digital License Manager REST API to the users that purchased and activated their license. List of the additional features as follows:

* WooCommerce Subscriptions integration (License renews when subscription renews)
* Full software and software release management from the WordPress admin interface
* Easily create software and connect it to a product, add documentation, images, etc from the WordPress admin interface
* Easily create software releases for specific software, add changelog, etc from the WordPress admin interface
* REST API endpoint for update check that you can utilize to check if there is a new release of a given software ID
* REST API endpoint for update download that you can utilize to download the new release of a given software ID
* Activate or Deactivate licenses as a Customer through My Account for the customers
* Separate License page in My Account that shows list of Activations, Releases available for download and License information for the customers
* Ready to use plugin updater class. Useful if you want to integrate the Licensing plugin with your premium WordPress plugins and provide updates for users that purchased license
* Software download statistics (Coming soon)
* PDF certificates of the licenses (Coming soon)

<a href="http://bit.ly/dlmpurchase" target="_blank">Get Premium Version</a>

### 📃 REST API Documentation

The REST API is one of the crucial features that this plugin provides.

It allows developers to create/update/activate/deactivate/validate licenses through the API.

The REST API documentation can be found on the link below:

<a href="https://bit.ly/dlm-api" target="_blank">See Documentation</a>

### 📃 All Documentation

The complete documentation can be found on the link below:

<a href="https://bit.ly/dlm-docs" target="_blank">See Documentation</a>

### ➕ Feature Requests

Feature requests are welcome! Feel free to submit your ideas on the link below:

<a href="https://github.com/gdarko/digital-license-manager/issues/new">New Feature Request</a>

### ✔ Important

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

= How to create a license through the REST API? =

To create a license through the REST API, please follow this <a href="http://docs.codeverve.com/digital-license-manager/rest-api/licenses/create/" target="_blank">guide</a>.

= How to activate a license through the REST API? =

To activate a license through the REST API, please follow this <a href="http://docs.codeverve.com/digital-license-manager/rest-api/licenses/activate/" target="_blank">guide</a>.

= How to deactivate a license through the REST API? =

To deactivate a license through the REST API, please follow this <a href="http://docs.codeverve.com/digital-license-manager/rest-api/licenses/deactivate/" target="_blank">guide</a>.

= How to validate a license through the REST API? =

To validate a license through the REST API, please follow this <a href="http://docs.codeverve.com/digital-license-manager/rest-api/licenses/validate/" target="_blank">guide</a>.

== Screenshots ==

1. Licenses - Overview page
2. Licenses - Add/Edit form
3. Licenses - CSV Export form
4. Generators - Overview page
5. Generators - Add/Edit form
6. Generators - "Generate" page for generating license keys.
7. Activations - Overview page
8. Settings - General
9. Settings - WooCommerce (if WooCommerce is activated)
10. Settings - API Keys - Overview
11. Settings - API Keys - Add/Edit form
12. My Account - Licenses overview
13. My Account - Single order page - Shows the licenses
14. Order Email - Shows the licenses
15. Re-send licenses via Order page

== Changelog ==

= 1.1.1 =
*Release Date - 01 Oct 2021*

* Add PRO version references

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
