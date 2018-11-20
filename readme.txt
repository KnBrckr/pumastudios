=== Shortcodes ===
Contributors: draca
Donate link: http://pumastudios.com/software/
Tags: shortcodes
Requires at least: 4.0
Tested up to: 4.9.1
Stable tag: 0.14

Various Short codes

== Description ==

Supports the following shortcodes:

= [page-children class=<class> parent=<id> children_of=<id> order_by=<order> exclude=<id list>]

class: defaults to 'page-children'
parent: return pages whose parent is this page, defaults to current page
children_of:  return pages, including grand-children, of this page
order_by: defaults to 'title', can be 'title', 'date', or 'order'
  'order' will order by page order setting on the pages.
exclude: exclude given comma separated list of page ids from list
page_id has been deprecated in favor of parent

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

= 0.14 =
* Remove page icon support. Not 100% reliable to add html tags to post title.

= 0.13.1 =
* Some other independent update has addressed editor filtering of Appointlet required settings.

= 0.13 =
* Wordpress editor filtering data-appointlet-organization attribute on img tags required by Appointlet calendar tool

= 0.12 =
* autofocus causes some browsers to auto-scroll to the focus field, results in not seeing beginning page content

= 0.11 =
* Add [page-children exclude] option to exclude given pages from list

= 0.10 =
* Add support to include a page icon where page titles are displayed
* FIX: [page-children] does not display filtered post title

= 0.9.2 =
* FIX: Leading non-word characters cause unexpected sort order
* FIX: Titles with numbers not sorted in a natural order

= 0.9.1 =
* Sorting of Post Titles should ignore leading articles "a", "an" and "the"

= 0.9 =
* Allow page-children to locate all grand-children of a page

= 0.8.1 =
* Fix Invalid argument supplied for foreach()

= 0.8 =
* Refactor Plugin to use loadable classes
* Add post featured image to RSS feeds
* Filter selected categories from RSS feed

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
