=== Shortcodes ===
Contributors: draca
Donate link: http://pumastudios.com/software/
Tags: shortcodes
Requires at least: 4.0
Tested up to: 4.7.4
Stable tag: 0.2.3

Various Short codes

== Description ==

Supports the following shortcodes:

= [page-children class=<class> page_id=<id> order_by=<order>]

class defaults to 'page-children'
page_id defaults to the current page
order_by defaults to 'title', can be 'title', 'date', or 'order'
  'order' will order by page order setting on the pages.

Also takes care of various fixups:

* Hide the woocommerce "Install Updater Plugin" message
* Cleanup admin-ajax.php when FORCE_SSL_ADMIN is set
* Include excerpt box on page edit screen - Useful for member-only content
* Auto-complete purchase of subscriptions for virtual products
* Allow WooCommerce download files to be in alternate /content directory

== Installation ==

Just like any other plugin

= Usage Hints =

= Reporting Problems =


== Frequently Asked Questions ==


== Screenshots ==


== Changelog ==

= 0.7 =
* Replace cost with "Free with Membership!" when part of a subscription
* Provide link to download page from downloads included in membership

= 0.6 =
* WooCommerce does not auto-complete virtual subscriptions

= 0.5 =
* WooCommerce files are not able to be located in /content vs. /wp-content

= 0.4 =
* Include excerpt box on page edit screen

= 0.3 =

* Create function to change "Backorder" text on front end - not enabled
* Remove Thrive Themes clone option from product list
* Use setup() method to hook WP vs. __construct()

= 0.2.3 =

* Use force_ssl_admin() like other parts of WordPress

= 0.2.2 =

* Incorrectly setting admin-ajax.php to always use http protocol

= 0.2.1 =

* Rename plugin
* Use set_url_scheme() to cleanup AJAX url
* Add order_by option to page-children shortcode

= 0.2 =

* Cleanup admin-ajax.php when FORCE_SSL_ADMIN is set

= 0.1 =

* Remove woocommerce update notifier
* Introduced page-children short code
