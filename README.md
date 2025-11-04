# Product Inquiry for WooCommerce

A professional WordPress plugin that allows customers to send inquiries about WooCommerce products directly from product pages. Perfect for stores that need pre-sale support, custom quotes, or product availability questions.

[![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-blue.svg)](https://wordpress.org/)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-5.0%2B-purple.svg)](https://woocommerce.com/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v2%2B-green.svg)](LICENSE)

## Features

### Core Functionality
- **Inquiry Button on Product Pages** - Automatically adds an "Inquiry" button to all WooCommerce products
- **AJAX-Powered Form Submission** - Smooth user experience with no page reloads
- **Email Notifications** - Instant notifications to store admin with inquiry details
- **Auto-Reply Emails** - Optional automated confirmation emails to customers
- **Admin Dashboard Management** - Full-featured admin interface to view, manage, and respond to inquiries

### Admin Features
- **Inquiry Management** - List view with filtering, sorting, and bulk actions
- **Status Tracking** - Mark inquiries as Unread, Processed, or Replied
- **Reply System** - Respond to customers directly from WordPress admin
- **CSV Export** - Export inquiries for analysis or record-keeping (up to 5,000 at once)
- **Reply History** - Complete log of all admin responses per inquiry

### Display Options
- **Popup Modal** - Clean overlay form (default)
- **Inline Form** - Display form directly on product page
- **Shortcode** - `[product_inquiry_form product_id="123"]` - Place forms anywhere
- **Gutenberg Block** - Visual block editor with live preview

### Customization
- **Settings Panel** - Configure emails, messages, and form behavior
- **Developer Hooks** - 15+ filters and actions for custom functionality
- **Translation Ready** - Full i18n support with `.pot` file included
- **PHPCS Compliant** - Follows WordPress Coding Standards

## Installation

### From ZIP File (Codecanyon Purchase)

1. Download the plugin ZIP file
2. Go to **WordPress Admin → Plugins → Add New → Upload Plugin**
3. Choose the ZIP file and click **Install Now**
4. Click **Activate Plugin**
5. Navigate to **WooCommerce → Settings → Inquiries** to configure

### From GitHub (Development)
```bash
cd wp-content/plugins
git clone https://github.com/Sahariar/Product-Inquiry-for-WooCommerce.git
cd product-inquiry-for-woocommerce
npm install
npm run build
```

Then activate via WordPress admin.

### Requirements

- WordPress 5.8 or higher
- WooCommerce 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## Quick Start

### 1. Basic Setup

After activation:
1. Go to **WooCommerce → Settings → Inquiries**
2. Set your admin email address
3. Customize the success message
4. Enable auto-reply if desired
5. Save settings

### 2. Display the Form

The plugin automatically adds an inquiry button to all product pages. No additional setup needed!

### 3. Manage Inquiries

View and respond to inquiries at **Product Inquiries** in the admin menu.

## Usage

### Product Page Button

By default, the inquiry button appears after the "Add to Cart" button. Configure position in settings:
- Before Add to Cart Button
- After Add to Cart Button (default)
- After Product Summary

### Using the Shortcode

Display an inquiry form for any product:
```php
[product_inquiry_form product_id="123"]
```

**Attributes:**
- `product_id` (required) - WooCommerce product ID
- `show_title` (optional) - Show product title (true/false, default: true)

**Examples:**
```php
// Basic usage
[product_inquiry_form product_id="123"]

// Without title
[product_inquiry_form product_id="123" show_title="false"]

// In PHP templates
<?php echo do_shortcode('[product_inquiry_form product_id="123"]'); ?>
```

### Using the Gutenberg Block

1. In the block editor, click the **(+)** button
2. Search for **"Product Inquiry Form"**
3. Insert the block
4. Enter the Product ID in the block settings (right sidebar)
5. Toggle "Show Product Title" as needed
6. Publish

The block provides a live preview in the editor and renders a functional form on the frontend.

## Settings

Navigate to **WooCommerce → Settings → Inquiries**

| Setting | Description | Default |
|---------|-------------|---------|
| Admin Email | Where inquiry notifications are sent | Site admin email |
| Success Message | Shown after successful submission | "Thank you for your inquiry..." |
| Form Display Mode | Popup modal or inline form | Popup |
| Enable Auto-Reply | Send confirmation email to customer | Enabled |
| Auto-Reply Subject | Email subject line | "We received your inquiry" |
| Auto-Reply Message | Email body with placeholders | Customizable template |
| Button Text | Text on inquiry button | "Product Inquiry" |
| Button Position | Where button appears on product page | After Add to Cart |

### Email Placeholders

Use these in auto-reply messages:
- `{customer_name}` - Customer's name
- `{product_name}` - Product title
- `{admin_email}` - Store admin email
- `{site_name}` - Site name
- `{site_url}` - Site URL

## Developer Documentation

### Available Hooks

#### Actions
```php
// After inquiry submitted
do_action( 'product_inquiry_for_woocommerce_inquiry_submitted', $inquiry_id, $inquiry_data );

// After admin reply sent
do_action( 'product_inquiry_for_woocommerce_inquiry_replied', $inquiry_id, $reply_message, $inquiry_data );
```

#### Filters

**Inquiry Submission:**
```php
// Modify inquiry data before saving
add_filter( 'product_inquiry_for_woocommerce_before_create_inquiry', 'custom_inquiry_data', 10, 2 );
function custom_inquiry_data( $inquiry_data, $post_data ) {
    // Add custom meta
    $inquiry_data['meta_input']['_custom_field'] = 'value';
    return $inquiry_data;
}
```

**Admin Email:**
```php
// Customize admin notification subject
add_filter( 'product_inquiry_for_woocommerce_admin_email_subject', 'custom_admin_subject', 10, 2 );

// Customize admin notification body
add_filter( 'product_inquiry_for_woocommerce_admin_email_body', 'custom_admin_body', 10, 3 );

// Customize admin notification headers
add_filter( 'product_inquiry_for_woocommerce_admin_email_headers', 'custom_admin_headers', 10, 2 );
```

**Auto-Reply Email:**
```php
// Customize auto-reply subject
add_filter( 'product_inquiry_for_woocommerce_auto_reply_subject', 'custom_autoreply_subject', 10, 2 );

// Customize auto-reply message
add_filter( 'product_inquiry_for_woocommerce_auto_reply_message', 'custom_autoreply_message', 10, 2 );

// Customize auto-reply headers
add_filter( 'product_inquiry_for_woocommerce_auto_reply_headers', 'custom_autoreply_headers', 10, 2 );
```

**Reply Email:**
```php
// Customize reply email subject
add_filter( 'product_inquiry_for_woocommerce_reply_email_subject', 'custom_reply_subject', 10, 3 );

// Customize reply email body
add_filter( 'product_inquiry_for_woocommerce_reply_email_body', 'custom_reply_body', 10, 3 );

// Customize reply email headers
add_filter( 'product_inquiry_for_woocommerce_reply_email_headers', 'custom_reply_headers', 10, 3 );
```

**CSV Export:**
```php
// Customize CSV headers
add_filter( 'product_inquiry_for_woocommerce_csv_export_headers', 'custom_csv_headers' );

// Customize CSV row data
add_filter( 'product_inquiry_for_woocommerce_csv_export_row', 'custom_csv_row', 10, 2 );
```

**Settings:**
```php
// Add custom settings fields
add_filter( 'product_inquiry_for_woocommerce_inquiry_settings', 'add_custom_settings' );
```

### Code Examples

#### Log Inquiries to External CRM
```php
add_action( 'product_inquiry_for_woocommerce_inquiry_submitted', 'log_to_crm', 10, 2 );
function log_to_crm( $inquiry_id, $inquiry_data ) {
    $aproduct_inquiry_for_woocommerce_url = 'https://crm.example.com/api/leads';
    
    wp_remote_post( $aproduct_inquiry_for_woocommerce_url, array(
        'body' => json_encode( array(
            'name'  => get_post_meta( $inquiry_id, 'product_inquiry_for_woocommerce_name', true ),
            'email' => get_post_meta( $inquiry_id, 'product_inquiry_for_woocommerce_email', true ),
        ) ),
        'headers' => array( 'Content-Type' => 'application/json' ),
    ) );
}
```

#### Add Custom Field to Export
```php
add_filter( 'product_inquiry_for_woocommerce_csv_export_headers', 'add_ip_column' );
function add_ip_column( $headers ) {
    $headers[] = 'IP Address';
    return $headers;
}

add_filter( 'product_inquiry_for_woocommerce_csv_export_row', 'add_ip_data', 10, 2 );
function add_ip_data( $row, $inquiry_id ) {
    $row[] = get_post_meta( $inquiry_id, 'product_inquiry_for_woocommerce_ip_address', true );
    return $row;
}
```

#### Send Slack Notification on New Inquiry
```php
add_action( 'product_inquiry_for_woocommerce_inquiry_submitted', 'notify_slack', 10, 2 );
function notify_slack( $inquiry_id, $inquiry_data ) {
    $webhook_url = 'https://hooks.slack.com/services/YOUR/WEBHOOK/URL';
    
    $message = sprintf(
        'New inquiry from %s about %s',
        $inquiry_data['meta_input']['product_inquiry_for_woocommerce_name'],
        get_the_title( $inquiry_data['meta_input']['product_inquiry_for_woocommerce_product_id'] )
    );
    
    wp_remote_post( $webhook_url, array(
        'body' => json_encode( array( 'text' => $message ) ),
    ) );
}
```

### Custom Post Type

Inquiries are stored as a custom post type: `product_inquiry`

**Post Meta Keys:**
- `_product_inquiry_for_woocommerce_name` - Customer name
- `_product_inquiry_for_woocommerce_email` - Customer email
- `_product_inquiry_for_woocommerce_phone` - Customer phone (optional)
- `_product_inquiry_for_woocommerce_message` - Inquiry message
- `_product_inquiry_for_woocommerce_product_id` - Associated product ID
- `_product_inquiry_for_woocommerce_date` - Submission date
- `_product_inquiry_for_woocommerce_status` - Status (new, processed, replied)
- `_product_inquiry_for_woocommerce_replies` - Array of admin replies

## Screenshots

### 1. Product Page Inquiry Button
![Product Page Button](assets/screenshot-1.png)
*Inquiry button appears on product pages*

### 2. Inquiry Form Modal
![Inquiry Form](assets/screenshot-2.png)
*Clean, user-friendly modal form*

### 3. Admin Inquiries List
![Admin List](assets/screenshot-3.png)
*Manage all inquiries from WordPress admin*

### 4. Single Inquiry View
![Single Inquiry](assets/screenshot-4.png)
*View details and reply to customers*

### 5. Settings Page
![Settings](assets/screenshot-5.png)
*Configure plugin behavior and email templates*

### 6. CSV Export
![CSV Export](assets/screenshot-6.png)
*Export inquiries for analysis*

### 7. Gutenberg Block
![Gutenberg Block](assets/screenshot-7.png)
*Visual block editor integration*

### 8. Reply Interface
![Reply Interface](assets/screenshot-8.png)
*Respond to customers directly from admin*

> **Note:** To add screenshots, place images in `assets/` folder and update paths above.

## Translations

The plugin is translation-ready with full i18n support.

### Available Languages

- English (default)
- Spanish - Coming soon
- French - Coming soon
- German - Coming soon

### Translate This Plugin

1. Install [Poedit](https://poedit.net/)
2. Open `languages/product-inquiry-for-woocommerce.pot`
3. Create translation for your language
4. Save as `product-inquiry-for-woocommerce-{locale}.po` (e.g., `product-inquiry-for-woocommerce-es_ES.po`)
5. Poedit will auto-generate the `.mo` file
6. Upload both files to `wp-content/languages/plugins/`

**Or** contribute translations via [translate.wordpress.org](https://translate.wordpress.org/)

## 🧪 Testing

### Run PHPCS
```bash
composer install
./vendor/bin/phpcs
```

### Run Tests (if applicable)
```bash
npm test
```

## Changelog

### Version 1.0.0 - 2025-01-15
- Initial release
- Product page inquiry button
- AJAX form submission
- Email notifications
- Admin management interface
- CSV export functionality
- Reply system
- Shortcode support
- Gutenberg block
- Settings page
- i18n support

## Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

Please ensure:
- Code follows WordPress Coding Standards
- All strings are translatable
- PHPCS passes without errors
- Changes are documented

## 📄 License

This plugin is licensed under the GPL v2 or later.
```
Product Inquiry for WooCommerce
Copyright (C) 2025 Your Name

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

## 👤 Author

**Sahariar Kabir**
- Website: [https://sahariarkabir.com/]
- GitHub: [@Sahariar](https://github.com/Sahariar)
- Email: sahariark@gmail.com

## Support

- **Documentation:** [https://example.com/docs](https://example.com/docs)
- **Support Forum:** [https://example.com/support](https://example.com/support)
- **Bug Reports:** [GitHub Issues](https://github.com/yourusername/product-inquiry-for-woocommerce/issues)

## Show Your Support

If this plugin helped your project, please consider:
- Giving it a ⭐ on GitHub
- Rating it on WordPress.org
- Sharing it with others

## Acknowledgments

- WordPress Plugin Boilerplate by [DevinVinson](https://github.com/DevinVinson/WordPress-Plugin-Boilerplate)
- WooCommerce team for excellent documentation
- All contributors and users

---

**Built with Love for the WordPress community**