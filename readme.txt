=== WooCommerce Order Attachment ===
Contributors: yourwordpressusername
Tags: woocommerce, order, email, attachment, invoice
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Attach a file to WooCommerce completed order emails directly from the order edit screen.

== Description ==

WooCommerce Order Attachment adds a simple file upload field to the WooCommerce order edit screen. Any file you attach will automatically be included as an email attachment when the completed order email is sent to the customer.

**Features:**

* Upload or select any file from the WordPress Media Library directly on the order edit screen.
* Automatically attaches the file to the WooCommerce "Completed Order" customer email.
* Fully compatible with WooCommerce High-Performance Order Storage (HPOS).
* No configuration needed — install, activate, and it works.
* Lightweight — no external libraries, no tracking, no bloat.
* Lightweight — no external libraries, no tracking, no bloat.
* Fully translatable.
* Secure — 2MB file size limit and strict capability checks.
* Restricted file types — PDF, DOC, DOCX, XLS, XLSX, ZIP, JPG, PNG.

**Use Cases:**

* Attach a PDF invoice to the completed order email.
* Include a warranty document, certificate, or license file with the order.
* Send custom delivery instructions or product guides with the completed order notification.

== Installation ==

1. Upload the `woocommerce-order-attachment` folder to the `/wp-content/plugins/` directory, or install the plugin directly from the WordPress plugin repository via **Plugins > Add New**.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Make sure WooCommerce is installed and active.
4. Open any order in **WooCommerce > Orders**.
5. Find the **Order Attachment** meta box in the sidebar.
6. Click **Upload / Select File** to choose a file from your Media Library.
7. Save the order.
8. When the order status is changed to **Completed**, the attached file will be included in the customer email automatically.

== Frequently Asked Questions ==

= Does this work with WooCommerce HPOS (High-Performance Order Storage)? =

Yes. The plugin is fully compatible with WooCommerce High-Performance Order Storage (custom order tables) as well as the classic post-based order storage.

= Which email does the file get attached to? =

The file is attached to the **Customer Completed Order** email — the email WooCommerce sends to the customer when an order status is changed to "Completed".

= Can I attach any file type? =
 
No. For security reasons, only the following file types are allowed: PDF, DOC, DOCX, XLS, XLSX, ZIP, JPG, PNG. If you need to allow other types, you can use the `woa_allowed_file_types` filter.

= Can I attach a different file to each order? =

Yes. Each order has its own independent attachment field. You can upload a unique file per order.

= What happens if I remove the file after the order is already completed? =

If you remove the file and re-send the completed order email, the file will no longer be attached. The plugin reads the attachment at the time the email is sent.

= Does this plugin send any data to external services? =

No. The plugin does not make any external requests, does not track usage, and does not send any data anywhere.

= Is this plugin compatible with multisite? =

The plugin has not been explicitly tested on WordPress Multisite. It may work, but Multisite support is not guaranteed at this time.

= Can I attach files to other WooCommerce emails, not just the completed order email? =

Not in the current version. Support for additional email types may be considered in a future release.

== Screenshots ==

1. The Order Attachment meta box on the order edit screen.
2. Selecting a file from the WordPress Media Library.
3. The completed order email received by the customer with the file attached.

== Changelog ==

= 1.1.0 =
* Enhancement: Moved inline JavaScript to external file.
* Security: Restricted allowed file types to PDF, Office documents, Images, and Zip.
* Security: Added post type validation for attachments.
* Enhancement: Added uninstall cleanup.

= 1.0.1 =
* Security: Added 2MB file size limit for attachments.
* Security: Enhanced user capability checks.
* Fix: Prevented infinite loop when saving orders.
* Tweak: Added explicit WooCommerce dependency declaration.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.1 =
Security and stability updates.

= 1.0.0 =
Initial release. No upgrade steps required.