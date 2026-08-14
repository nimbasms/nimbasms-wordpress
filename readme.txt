=== Nimba SMS ===
Contributors: nimbasms
Tags: sms, whatsapp, woocommerce, notifications, guinea
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connect WordPress to Nimba SMS, the business communication platform: WooCommerce notifications, alerts, SMS and WhatsApp messages.

== Description ==

**Nimba SMS** connects your WordPress site to [Nimba SMS](https://www.nimbasms.com), the professional communication platform for businesses, supporting SMS, WhatsApp and email (with more channels coming). This plugin covers the SMS and WhatsApp channels: send SMS with your company name as the sender, and WhatsApp messages through your Meta-approved templates, straight from WordPress. More channels will be added in future releases.

= Features =

* **WooCommerce**: for each order status (processing, completed, cancelled), enable SMS, WhatsApp, or both, with customizable message templates. If WhatsApp is the only enabled channel and sending fails, the SMS message is sent automatically as a fallback. The store administrator can also receive an SMS for every new order.
* **WhatsApp**: send messages through your Meta-approved WhatsApp Business templates (created from your Nimba SMS dashboard), with dynamic variables.
* **WordPress notifications**: SMS to the administrator on new user registration or new comment.
* **Contact Form 7 and WPForms**: SMS to the administrator on each form submission, and an optional confirmation SMS to the visitor when the form contains a phone field (auto-detected, or set the field name in the settings). Customizable messages.
* **Manual sending**: send an SMS or a WhatsApp template message to one or several numbers directly from the admin.
* **Send log**: history of sent messages with their delivery status, updated in real time through the Nimba SMS delivery webhook (URL provided in the settings, to paste into the "URL Webhook" field at https://www.nimbasms.com/app/api-keys).
* **Live balances**: your SMS and WhatsApp credits displayed in the settings.
* **For developers**: `nimbasms_send( $to, $message )` and `nimbasms_send_whatsapp( $to, $template, $variables )` functions plus hooks (`nimbasms_send_payload`, `nimbasms_after_send`, `nimbasms_wc_templates`, `nimbasms_webhook_received`...) to integrate messaging into your own extensions.

= Requirements =

A [Nimba SMS](https://www.nimbasms.com) account with an approved sender name. Your API credentials (SERVICE ID and SECRET TOKEN) are available on [the API keys page of your account](https://www.nimbasms.com/app/api-keys). API documentation: [developers.nimbasms.com](https://developers.nimbasms.com).

= External service =

This plugin communicates with the Nimba SMS API (https://api.nimbasms.com) to send messages and retrieve your account balance and sender names. Data transmitted: your API credentials, recipient phone numbers and message content. Conversely, if you configure the delivery webhook, Nimba SMS servers call a URL on your site (REST endpoint `nimbasms/v1/webhook`, protected by a secret token) to push message status changes. See the [Nimba SMS terms of use](https://www.nimbasms.com/conditions-generales-d-utilisation).

== Installation ==

1. Install the plugin from the WordPress plugin directory, or upload the `nimbasms` folder to `/wp-content/plugins/`.
2. Activate the plugin.
3. Go to **Nimba SMS** in the admin menu.
4. Enter your SERVICE ID and SECRET TOKEN, pick your sender name, and save.
5. Enable the notifications you need.

== Frequently Asked Questions ==

= Where do I find my API credentials? =

On the API keys page of your account: https://www.nimbasms.com/app/api-keys. Full documentation at developers.nimbasms.com.

= Does the plugin work without WooCommerce? =

Yes. The WooCommerce features only appear when WooCommerce is active.

= Does the plugin integrate with contact forms? =

Yes, with Contact Form 7 and WPForms (Lite or Pro). Enable the notifications in the Forms section of the settings; it appears when one of these plugins is active. The visitor phone field is auto-detected (WPForms phone field type, or any field whose name contains "phone" or "tel"), and you can set an exact field name in the settings.

= Does the plugin support WhatsApp? =

Yes. Enable the WhatsApp channel in the settings, then provide the name of a Meta-approved template (templates are created from your Nimba SMS dashboard). Email and other channels are coming in future releases.

= Can I send messages from my own code? =

Yes: `nimbasms_send( '624000000', 'My message' );` — filters and actions are available to customize sending.

= Which countries are covered? =

See the network coverage on www.nimbasms.com.

== Screenshots ==

1. Settings: API credentials, live SMS and WhatsApp balances, sender name and notifications.
2. WooCommerce: per-order-status panels with side-by-side SMS and WhatsApp columns.
3. Manual sending with channel selection.
4. Send log with real-time delivery statuses (via the delivery webhook).

== Changelog ==

= 1.1.0 =
* Contact Form 7 and WPForms integration: administrator SMS on each submission, optional visitor confirmation SMS with automatic phone field detection, customizable messages, and new developer filters (`nimbasms_forms_replacements`, `nimbasms_forms_visitor_phone`).

= 1.0.0 =
* Initial release: SMS and WhatsApp channels (Meta templates), WooCommerce integration with SMS fallback, WordPress notifications, manual sending, delivery-status webhook, send log, developer functions.

== Upgrade Notice ==

= 1.1.0 =
Adds Contact Form 7 and WPForms integration.
