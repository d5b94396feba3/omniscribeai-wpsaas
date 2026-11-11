=== IntelliAgent AI Lite ===
Contributors: shah646
Donate link: https://aica-intelliagent.com/
Tags: ai assistant, chatbot, product recommendations, customer support, woocommerce ai
Requires at least: 6.0
Tested up to: 7.0
Stable tag: 1.0.7
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

24/7 AI Sales & Customer Support Assistant for WordPress & WooCommerce. Automate FAQs, recommend products, and boost sales with multi-model AI.

== Description ==

IntelliAgent AI Lite brings intelligent chat assistance to your WordPress site. Powered by OpenAI's GPT models and Google Gemini, it provides instant, helpful responses to your visitors 24/7. Includes WooCommerce integration for product information and recommendations.

[View Demo](https://aica-intelliagent.com/) | [Documentation](https://docs.aica-intelliagent.com/) | [Support](https://aica-intelliagent.com/support)

= Features =

* 🤖 **Multiple AI Providers** - Choose between OpenAI (GPT) and Google Gemini
* 🌐 **Automatic Website Content Sync** - Automatically includes website content including pages and WooCommerce products
* 🛒 **WooCommerce Integration** - Product recommendations and product information display
* 🎨 **Customizable Widget** - Configure colors, avatar, and welcome messages
* 📝 **Chat History** - View and export all conversations
* ⭐ **Feedback System** - Collect visitor feedback on AI responses
* ❓ **FAQ Management** - Create and manage FAQs
* 🔒 **Privacy Focused** - Your data stays on your server
* 📱 **Mobile Responsive** - Works perfectly on all devices

= WooCommerce Integration =

* Product recommendations based on visitor queries
* Product information display (price, stock, description)
* Product URLs included in responses
* Knowledge base sync for products and pages

= Developer Features =

* WordPress Coding Standards compliant
* **WooCommerce HPOS Compatible** - Full support for High-Performance Order Storage
* Translation ready (i18n)
* AJAX-powered chat interface
* Clean, documented code

= Privacy & Security =

* Data sanitization and validation
* Nonce verification on all requests
* GDPR compliant
* All chat data stored locally in your WordPress database

= Privacy & External AI Services =

This plugin connects to third-party AI services to generate chat responses. By using this plugin, user-submitted chat queries and relevant website context are sent to the selected provider.

1. OpenAI API: Used to process and generate intelligent chat responses.
   - Data sent: User input text, chat history, and relevant website/product context.
   - Service: https://openai.com/
   - Privacy Policy: https://openai.com/policies/privacy-policy/
   - Terms of Service: https://openai.com/policies/terms-of-use/

2. Google Gemini API: Used to process and generate intelligent chat responses.
   - Data sent: User input text, chat history, and relevant website/product context.
   - Service: https://ai.google.dev/
   - Privacy Policy: https://policies.google.com/privacy
   - Terms of Service: https://ai.google.dev/terms

= Pro Version =

Upgrade to IntelliAgent AI Pro for:

* 🤖 **Multiple AI Providers** - Choose among Grok, OpenAI (GPT), and Google Gemini
* 🛒 **Advanced WooCommerce** - Add to cart functionality, checkout assistance, order creation
* 🔄 **Automated Cron Sync** - Keep knowledge base updated automatically
* 📄 **Document Training** - Upload PDFs, images, and documents for AI training
* 📧 **Email Notifications** - Get unlimited email alerts for new chats and transcripts
* 🌍 **40+ Languages** - Full multilingual support for global audiences
* 🎯 **AI Persona Generation** - Auto-generate AI personality from your content
* 📊 **AI Summary** - Detailed chat summaries and performance metrics
* ⚡ **Priority Support** - Direct access to our support team

[Get IntelliAgent AI Pro →](https://aica-intelliagent.com/pricing)

== Installation ==

1. Upload the `intelliagent-ai-lite` folder to the `/wp-content/plugins/` directory, or install via Plugins → Add New
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to IntelliAgent AI Lite → Settings to configure your API keys
4. Navigate to Website & Store Sync to sync your pages and products
5. The chat widget will automatically appear on your site

== Frequently Asked Questions ==

= Do I need an API key? =

Yes, you need either an OpenAI API key or Google Gemini API key. You can get them from:
* [OpenAI API Keys](https://platform.openai.com/api-keys)
* [Google AI Studio](https://aistudio.google.com/app/apikey)

= Does it work with WooCommerce? =

Yes! The Lite version includes WooCommerce integration for product recommendations and product information. Upgrade to Pro for add-to-cart functionality and checkout assistance.

= How do I sync my products? =

Go to IntelliAgent AI Lite → Store Sync. The plugin will automatically discover your pages and WooCommerce products. Click "Sync All Pending" to add them to the knowledge base.

= Is there a free version? =

Yes, this is the free version. It includes core chat functionality, WooCommerce product recommendations, FAQ management, and feedback collection.

= Does it work with any theme? =

Yes, the chat widget works with any WordPress theme. The appearance can be customized to match your site's branding.

= Where is chat data stored? =

All chat data is stored locally in your WordPress database. No data is sent to third parties except for the AI API requests.

== Screenshots ==

1. Chat widget on the frontend
2. Settings page
3. Dashboard view
4. Store sync interface
5. FAQ management
6. Feedback dashboard
7. Multi-Model AI Configuration

== Changelog ==

= 1.0.7 =
* Enhancement: Added updated logo and icon assets for the plugin directory.
* Enhancement: Verified full compatibility with WordPress 7.0 and WooCommerce 10.9.
* Tweak: Minor performance optimizations and code structure refinements.

= 1.0.6 =
* Fix: Minor fix on the media uploader for profile image (avatar) uploads.

= 1.0.5 =
* Fix: Completely removed `session_start()` and dependency on PHP sessions to ensure compatibility with server-side caching (Nginx/Varnish).
* Enhancement: Switched visitor identification to a secure Cookie UUID and WordPress Transients system.
* Enhancement: Verified and standardized all text domains to match the plugin slug.

= 1.0.4 =
* Enhancement: Added administrative opt-in for "Powered by" branding in settings to comply with repository guidelines.
* Fix: Standardized all class and function naming conventions with unique prefixes to prevent global namespace collisions.
* Fix: Hardened all admin submenu slugs and AJAX action strings using unique identifiers for better security.
* Fix: Optimized uninstaller logic to ensure complete cleanup of all plugin-defined options and database transients.

= 1.0.3 =
* Fix: Minor update to ensure database tables are correctly cleared during uninstallation.
* Fix: Refined uninstall logic to use dynamic slug retrieval, improving compliance with repository standards.
* Tweak: Added safety checks to verify the plugin path before executing data cleanup.

= 1.0.2 =
* Fix: Improved security by removing HEREDOC/NOWDOC syntax from core instruction files.
* Fix: Enhanced prefixing for all classes, functions, and database transients to prevent naming collisions.
* Fix: Updated all transients and options with unique prefixes to resolve directory review flags.
* Tweak: Optimized text domain consistency across all public and admin facing files.

= 1.0.1 =
* Feature: Declared full compatibility with WooCommerce High-Performance Order Storage (HPOS).
* Fix: Optimized SVG frontend icons for better performance.
* Fix: Resolved UI rendering issues within the chat menu and back button.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.7 =
Updated plugin directory logo assets, compatibility support for WordPress 7.0, and minor performance optimizations.

= 1.0.6 =
Maintenance update to resolve an issue with the media uploader when changing the profile avatar.

= 1.0.5 =
Important: This update removes PHP session dependencies to improve performance and compatibility with managed hosting environments and caching layers.

= 1.0.4 =
Important update: Adds a branding opt-in setting and improves prefixing consistency for all admin pages and AJAX actions to meet the latest WordPress repository requirements.

= 1.0.3 =
Maintenance update: Fixes a minor issue where database tables were not correctly dropped upon plugin deletion and improves compliance with repository coding standards.

= 1.0.2 =
Security and compatibility update: addresses directory review feedback regarding prefixing, naming collisions, and code structure.

= 1.0.1 =
Adds WooCommerce HPOS compatibility and minor UI bug fixes.

== Support ==

For support, feature requests, or bug reports:

* Visit the [WordPress.org support forum](https://wordpress.org/support/plugin/intelliagent-ai-lite)
* Check our [documentation](https://docs.aica-intelliagent.com/)
* Contact us via our [website](https://aica-intelliagent.com/support)

== Contributors & Developers ==

"IntelliAgent AI Lite" is open source software. The following people have contributed to this plugin.

**Contributors**
* shah646

== Credits ==

* Simple HTML DOM Parser - MIT License
* Font Awesome - SIL OFL 1.1 / MIT
* jQuery - MIT License
* OpenAI - https://openai.com
* Google Gemini - https://deepmind.google/technologies/gemini/

== Additional Links ==

* [Pro Version](https://aica-intelliagent.com/pricing)
* [Documentation](https://docs.aica-intelliagent.com)
* [Support](https://aica-intelliagent.com/support)