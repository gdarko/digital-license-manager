=== Digital License Manager ===
Contributors: darkog
Tags: license key, license, key, software license, serial key, manager, woocommerce, wordpress
Requires at least: 4.7
Tested up to: 5.8
Stable tag: 1.0.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Easily manage and sell your digital licenses through your WordPress website

== Description ==

Digital License Manager is licensing plugin for WordPress which can be used to keep track and sell your license keys form your WordPress site.

### Features

* Manage your Licenses (status, license key, activations, etc)
* Manage License Activations. Keeps separate records of Activations. They can be added through the REST API and are identified by unique token that you should keep in your third party software and you can use to check for updates, download latest version, etc.
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
* Works even without WooCommerce. If you want to use the plugin as standalone license manager

#### REST API

The REST API is one of the crucial features that this plugin provides. It allows developers to activate/deactivate/validate licenses through the API.

<a href="https://bit.ly/dlm-api">See Documentation</a>

#### Important

The plugin will create two files inside the `wp-content/uploads/dlm-files` folder. These files (`defuse.txt` and `secret.txt`) contain cryptographic secrets which are automatically generated if they don't exist. These cryptographic secrets are used to encrypt, decrypt and hash your licenses. Once they are generated please **back them up somewhere safe**. In case you lose these two files your encrypted license keys inside the database will remain forever lost!

== Installation ==

#### Manual installation

1. Upload the plugin files to the `/wp-content/plugins/digital-license-manager` directory, or install the plugin through the WordPress *Plugins* page directly.
1. Activate the plugin through the *Plugins* page in WordPress.
1. Use the *License Manager* → *Settings* page to configure the plugin.

#### Installation through WordPress

1. Open up your WordPress Dashboard and navigate to the *Plugins* page.
1. Click on *Add new*
1. In the search bar type "Digital License Manager"
1. Select this plugin and click on *Install now*

#### Important

The plugin will create two files inside the `wp-content/uploads/dlm-files` folder. These files (`defuse.txt` and `secret.txt`) contain cryptographic secrets which are automatically generated if they don't exist. These cryptographic secrets are used to encrypt, decrypt and hash your license keys. Once they are generated please **back them up somewhere safe**. In case you lose these two files your encrypted license keys inside the database will remain forever lost!

== Frequently Asked Questions ==

= Does it work without WooCommerce? =

No, it doesn't for now.

== Screenshots ==

== Changelog ==

= 1.0.0 =
*Release Date - 19 July 2021*

* Everything is new

== Upgrade Notice ==
