=== StockUnlocks - Mobile and Cell Phone Unlocking ===
Contributors: stockunlocks
Donate link: https://www.stockunlocks.com/donate
Tags: E-Commerce, Unlock, Mobile, Website, Mobile Unlock Website, Codes, Unlock Codes, Unlocking, Mobile Unlock, Mobile Unlocking, Phone Unlock, Phone Unlocking, Mobile Phone, Cell Phone, Unlock Cell Phone, SmartPhone, SmartPhone Unlock, iPhone, Unlock iPhone, iPhone Unlock, Dhru Fusion, Dhru Fusion API, Dhru API, Dhru, API, StockUnlocks, Darrell Henry
Requires at least: 4.0
Tested up to: 4.8
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create your own mobile unlocking store, without having to write a bunch of code.

== Description ==

Mobile and cell phone unlocking: Automate your mobile unlocking store with the StockUnlocks plugin combined with WooCommerce.

StockUnlocks is designed to transform your website into a remote, mobile unlocking machine.

The power and automation of the Dhru Fusion API makes it all possible. Connect to one or many Dhru Fusion mobile unlocking servers and forget about spreadsheets and manual email processing.

Now, focus your time and energy where they're needed the most.

Some of the outstanding features include:

*   Access to numerous mobile unlocking services from multiple Dhru Fusion unlocking providers.
*   Importing unlocking services directly into your own website.
*   Automatic price updating when your supplier's prices change.
*   Automated processing of unlocking requests.
*   Customizing automated email responses to your customers.
*   **NOTE**: The WooCommerce plugin is required in order to use this plugin. If you don't have it, you may download it here:
	[WooCommerce plugin for WordPress](https://wordpress.org/plugins/woocommerce/ "WooCommerce plugin for WordPress")

Sign up for website access at [www.stockunlocks.com](https://www.stockunlocks.com "StockUnlocks Home Page") to join the community and to take advantage of our forums and issue tracking.

== Installation ==

1. Upload the unzipped folder 'stockunlocks' and its contents to the '/wp-content/plugins/' directory
2. Unzip the plugin file
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Create an account at the [StockUnlocks Reseller Website](http://reseller.stockunlocks.com/singup.html "StockUnlocks Reseller Website") in order to fully test your installation
5. Use the 'Plugin Options' in the 'StockUnlocks' plugin menu to update all settings to reflect your website name and contact email address
6. Use the 'Providers' in the 'StockUnlocks' plugin menu to create a new Unlocking Service Provider. This can be for your current provider or the information you received after step 4 was completed
7. Use the 'Import Services' in the 'StockUnlocks' plugin menu to import unlocking services (Products) from your selected Provider
8. Use the 'Products' WooCommerce plugin menu to locate the recently imported Product(s). They will have the status 'Imported'
9. Edit the imported Product to your liking (especially 'Regular Price' found under 'Product Data > General').
10. **Tip**: If you're using the 'TEST - Available' or 'TEST - Unavailable', set your 'Regular Price' to **0.0** to speed up the testing from your website.
11. Change the Product status by clickng 'Publish'. NOTE: Products with status 'Imported' will not work with this plugin until changed to 'Publish'
12. Use the 'Plugin Options' in the 'StockUnlocks' plugin menu to enable and set the cron schedule
13. Navigate to yourwebsite-dotcom/shop and select one of the recently imported test services and place an order
14. Examine the automatic notifications for accuracy. Make needed changes via step 5 above
15. [Short Video Overview](http://youtu.be/agB00UKz0g4 "Short Video Overview")
16. [Plugin Home Page](https://www.stockunlocks.com/forums/forum/stockunlocks-wordpress-plugin/ "Plugin Home Page")
17. Happy unlocking!

== Frequently Asked Questions ==

= Why can't I import Services - not even just one? =

When a large number of services appear in your browser and no matter how many you select to import, you might see this message:

> `No services were imported or updated. Please select fewer services or modify memory settings in wp-config.php or php.ini`

When the plugin sees large amounts of data from your Dhru Fusion supplier, this error appears because of your memory configuration. 
If you can modify the memory settings in your **`wp-config.php`** and **`php.ini`** file, that should resolve it.

Here's what I have in my **`php.ini`**:

*   `max_execution_time = 300 ; Maximum execution time of each script, in seconds`
*   `max_input_time = 60 ; Maximum amount of time each script may spend parsing request data`
*   `memory_limit = 512M ; Maximum amount of memory a script may consume`

Here are the settings in the **`wp-config.php`**:

*   `define( 'WP_MEMORY_LIMIT', '256M' );`
*   `define( 'WP_MAX_MEMORY_LIMIT', '256M' );`

These are the 'default' settings in my installation. So far, it's worked for importing more than 200 services at one time. You may need to tweak these settings according to your needs.

= Hey!! I'm using the Advanced Custom Fields plugin - where did the menu go?? =

StockUnlocks relies on the Advanced Custom Fields plugin as well. ACF is already bundled with StockUnlocks, since there is a provision for doing so. 
Elliot Condon, the creator of ACF, allows distributing ACF in a plugin or theme as outlined here: [Distributing ACF](https://www.advancedcustomfields.com/resources/including-acf-in-a-plugin-theme/ "Distributing ACF"). 

*   The StockUnlocks plugin disables the ACF menu setting by doing this:

> `if( !defined('ACF_LITE') ) define('ACF_LITE',true);` 

*   This line may be commented out if you need to access the ACF menu. However, future StockUnlocks plugin updates might uncomment it; thus, hiding the ACF menu again.

== Screenshots ==

1. Providers page
2. Edit Provider page
3. Import Services page
4. Plugin Options page 1
5. Plugin Options page 2
6. Plugin Dashboard

== Changelog ==

= 1.1.0 =
*   Fix - Now updating displayed value for Product Service credit when remote value changes
*   Fix - Order details display formatting now works for WC 3.x and earlier versions
*   Dev - Added support for WC Sequential Order Numbers

= 1.0.9 =
*   Importing Services can now be done while running WP from a sub-directory
*   Automatic price updating - you asked for it, you got it ;-)

= 1.0.8.6 =
*   Fixed formatting and display issues related to themes built on bootstrap

= 1.0.8.4 =
*   Troubleshooting Option now properly retrieves the indicated number of services when enabled
*   Order Status options were changed to a more appropriate wording to include different kinds of orders
*   Activated sending the automated email to Admin when checking an order completely fails

= 1.0.8.1 =
*   Import Services adjusted to reduce potential memory errors

= 1.0.8 =
*   Modifications to allow full processing of a shopping cart with products from different providers
*   New Troubleshooting Option to limit the number of Services when importing
*   Updates 'Thank you for your order' email by changing labels: 'suwp_imei_values' to 'IMEI' and 'suwp_email_response' to 'Email'
*   Moved Product detail labels to appear above their respective fields/selection boxes

= 1.0.7 =
*   Combined Email Templates with the Plugin Options tab

= 1.0.5 =
*   Imported Products are now linked to the proper post_author id
*   Added a unique id for future technical support
*   Additional automated email notifications for admin users
*   Bug fixes

= 1.0.1 =
*   Including the Advanced Custom Fields plugin
*   Defaulting 'Serial length' to '15' when importing services for convenience

= 1.0 =
* Initial release of plugin

== Upgrade Notice ==

= 1.1.0 =
This updgrade fixes a problem where the Product service credit value was not being updated.
Udpated for WC 3.x and added backwards compatibility for 2.x
Now supporting WC Sequential Order Numbers.

= 1.0.9 =
This updgrade fixes the wp ajax URL problem when WP is installed in a sub-directory.
Access settings for automatic price updates via a single Product or Plugin Options.

= 1.0.8.6 =
This upgrade fixes a critical formatting and display issue for themes built on bootstrap 

= 1.0.8.4 =
This upgrade fixes the number of retrieved services to match the setting when the Troubleshooting Option is enabled. 
The Order Status choices were changed to be more informative. 
Automated message now being sent to Admin when checking on an order encounters a fatal error

= 1.0.8.1 =
This important upgrade reduces the memory used when importing services. This will cut down on the errors. 
All credit goes to a power user for pointing out a needed jQuery adjustment ;-)

= 1.0.8 =
This upgrade ensures Products from multiple Providers in one cart are all processed. 
New Troubleshooting Option limits the number of Services to be imported when tracking memory issues. 
Changed labels in email notifications: 'suwp_imei_values' to 'IMEI' and 'suwp_email_response' to 'Email'.

= 1.0.7 =
Combined Email Templates with the Plugin Options tab to prevent option settings being reset to default values when saving on either tab. 
All options now reside in one place.

= 1.0.5 =
When importing Products, now properly attributing the import to the post_author's id: $user_id = get_current_user_id(). 
Added an internal 'Support ID' for future technical support. 
Enabled automated email notifications for admin users when orders fail.

= 1.0.1 =
This upgrade now includes the Advanced Custom Fields plugin. When importing services, now defaulting its 'Serial length' value to '15'. 
This value can always be changed directly in the UI after import. The idea is to give you one less thing to look for after importing.

= 1.0 =
This is the initial release of the StockUnlocks plugin for WordPress

== Learn how to use this plugin! ==

Check out the [Tutorials](https://www.youtube.com/stockunlocks "Tutorials") on our channel to learn more about using the StockUnlocks plugin for WordPress. Head over to [www.youtube.com/stockunlocks](https://www.youtube.com/stockunlocks "StockUnlocks on YouTube") to learn more!