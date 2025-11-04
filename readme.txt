=== Product Inquiry for WooCommerce ===
Contributors: sahariarkabir
Donate link: https://sahariarkabir.com/
Tags: woocommerce, product inquiry, contact form, email, ajax
Requires at least: 5.8
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Professional product inquiry system for WooCommerce with AJAX forms, admin management, email notifications, and CSV export.

== Description ==

**Product Inquiry for WooCommerce** allows customers to send inquiries directly from product pages. Perfect for stores needing pre-sale support, custom quotes, or availability questions.

= Key Features =

* **AJAX Inquiry Forms** - Smooth submission with no page reloads
* **Admin Dashboard** - Manage all inquiries from WordPress admin
* **Email Notifications** - Instant admin alerts + optional customer auto-replies
* **Reply System** - Respond to customers directly from admin panel
* **CSV Export** - Export inquiries for analysis (up to 5,000 at once)
* **Flexible Display** - Popup modal or inline form options
* **Shortcode Support** - Place forms anywhere with `[product_inquiry_form]`
* **Gutenberg Block** - Visual block editor with live preview
* **Developer Friendly** - 15+ hooks and filters for customization
* **Translation Ready** - Full i18n support with .pot file included
* **PHPCS Compliant** - Follows WordPress Coding Standards

= Perfect For =

* B2B stores requiring custom quotes
* Products with variable pricing
* Out-of-stock inquiries
* Wholesale or bulk order requests
* Pre-sale technical questions
* Custom product configurations

= Premium Support =

Need help? We provide:
* Priority email support
* Installation assistance
* Custom development
* Feature requests

[View Documentation](https://sahariarkabir.com/docs/product-inquiry) | [Support Forum](https://sahariarkabir.com/support)

== Installation ==

= Automatic Installation =

1. Log into your WordPress admin
2. Go to **Plugins > Add New**
3. Search for "Product Inquiry for WooCommerce"
4. Click **Install Now** then **Activate**
5. Navigate to **WooCommerce > Settings > Inquiries** to configure

= Manual Installation =

1. Download the plugin ZIP file
2. Go to **Plugins > Add New > Upload Plugin**
3. Choose the ZIP file and click **Install Now**
4. Click **Activate Plugin**
5. Configure at **WooCommerce > Settings > Inquiries**

= After Activation =

1. Go to **WooCommerce > Settings > Inquiries**
2. Set your admin email address
3. Customize success message and auto-reply settings
4. Choose popup or inline form display
5. Save settings

The inquiry button will automatically appear on all product pages.

== Frequently Asked Questions ==

= Does this work with all WooCommerce themes? =

Yes. The plugin uses standard WooCommerce hooks and is compatible with all themes that follow WooCommerce standards.

= Can I customize the form fields? =

The core fields (name, email, message) are required for proper functionality. Custom fields can be added using developer hooks.

= Where are inquiries stored? =

Inquiries are stored as a custom post type in your WordPress database. They're fully searchable and can be exported to CSV.

= Can customers see their inquiry history? =

Currently, inquiries are managed by admins only. Customer portal features may be added in future versions.

= Does it send email notifications? =

Yes. Admin receives instant notifications for new inquiries. Customers can receive optional auto-reply confirmations.

= Can I reply to inquiries from WordPress? =

Yes. The admin panel includes a reply interface that sends emails directly to customers.

= Is it GDPR compliant? =

The plugin stores only the data customers submit. You're responsible for adding privacy policy links and obtaining consent as needed.

= Can I export inquiries? =

Yes. Use the CSV export feature to download up to 5,000 inquiries at once with all details and metadata.

= Does it work with WPML/Polylang? =

Yes. The plugin is fully translation-ready and compatible with multilingual plugins.

= Can I use it without WooCommerce? =

No. This plugin requires WooCommerce to be installed and active.

== Screenshots ==

1. Product page inquiry button integrated with WooCommerce
2. Clean AJAX-powered inquiry form modal
3. Admin inquiries list with status tracking and filtering
4. Single inquiry view with customer details and reply interface
5. Settings page for email templates and form configuration
6. CSV export functionality with date range selection
7. Gutenberg block for placing forms anywhere
8. Reply interface showing customer communication history

== Changelog ==

= 1.0.0 - 2025-01-15 =
* Initial release
* Product page inquiry button
* AJAX form submission with validation
* Email notifications (admin + auto-reply)
* Admin management dashboard
* Inquiry status tracking (new, processed, replied)
* Reply system with email delivery
* CSV export (up to 5,000 inquiries)
* Shortcode support: [product_inquiry_form]
* Gutenberg block with live preview
* Settings page for customization
* Translation-ready with .pot file
* PHPCS compliant code
* 15+ developer hooks and filters

== Upgrade Notice ==

= 1.0.0 =
Initial release. Install to add professional inquiry system to your WooCommerce store.

== Developer Documentation ==

= Shortcode Usage =

Basic usage:
`[product_inquiry_form product_id="123"]`

Without product title:
`[product_inquiry_form product_id="123" show_title="false"]`

In PHP templates:
`<?php echo do_shortcode('[product_inquiry_form product_id="123"]'); ?>`

= Available Hooks =

**Actions:**
* `product_inquiry_for_woocommerceinquiry_submitted` - Fires after inquiry is saved
* `product_inquiry_for_woocommerceinquiry_replied` - Fires after admin sends reply

**Filters:**
* `product_inquiry_for_woocommercebefore_create_inquiry` - Modify inquiry data before saving
* `product_inquiry_for_woocommerceadmin_email_subject` - Customize admin email subject
* `product_inquiry_for_woocommerceadmin_email_body` - Customize admin email body
* `product_inquiry_for_woocommerceauto_reply_subject` - Customize auto-reply subject
* `product_inquiry_for_woocommerceauto_reply_message` - Customize auto-reply message
* `product_inquiry_for_woocommercereply_email_subject` - Customize reply email subject
* `product_inquiry_for_woocommercereply_email_body` - Customize reply email body
* `product_inquiry_for_woocommercecsv_export_headers` - Modify CSV column headers
* `product_inquiry_for_woocommercecsv_export_row` - Modify CSV row data

= Example: Log to External CRM =

`add_action( 'product_inquiry_for_woocommerceinquiry_submitted', 'log_to_crm', 10, 2 );
function log_to_crm( $inquiry_id, $inquiry_data ) {
    // Send to your CRM API
    wp_remote_post( 'https://crm.example.com/api/leads', array(
        'body' => json_encode( array(
            'name' => get_post_meta( $inquiry_id, '_product_inquiry_for_woocommerce_name', true ),
            'email' => get_post_meta( $inquiry_id, '_product_inquiry_for_woocommerce_email', true ),
        ) ),
    ) );
}`

= Example: Custom CSV Column =

`add_filter( 'product_inquiry_for_woocommercecsv_export_headers', 'add_ip_column' );
function add_ip_column( $headers ) {
    $headers[] = 'IP Address';
    return $headers;
}

add_filter( 'product_inquiry_for_woocommercecsv_export_row', 'add_ip_data', 10, 2 );
function add_ip_data( $row, $inquiry_id ) {
    $row[] = get_post_meta( $inquiry_id, '_product_inquiry_for_woocommerce_ip_address', true );
    return $row;
}`

View full documentation at: https://sahariarkabir.com/docs/product-inquiry

== Support ==

* Documentation: https://sahariarkabir.com/docs/product-inquiry
* Support Forum: https://wordpress.org/support/plugin/product-inquiry-for-woocommerce/
* Bug Reports: https://github.com/Sahariar/product-inquiry-for-woocommerce/issues

== Privacy Policy ==

This plugin stores inquiry submissions in your WordPress database. Data includes:
* Customer name
* Email address
* Phone number (optional)
* Inquiry message
* Product ID
* Submission date
* IP address (for security)

This data is retained until manually deleted by site administrators. The plugin does not share data with external services unless you integrate third-party tools via hooks.

For GDPR compliance, ensure you:
* Add a privacy policy link to your inquiry forms
* Obtain proper consent before collecting data
* Honor data deletion requests promptly
