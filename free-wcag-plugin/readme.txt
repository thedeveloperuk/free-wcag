=== Free WCAG ===
Contributors: developer
Tags: accessibility, wcag, ada, a11y, screen reader
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A comprehensive accessibility toolkit for WCAG 2.2 Level AA compliance. Features a user-facing toolbar, automated remediation, and content auditing.

== Description ==

**Free WCAG** transforms your WordPress site into an accessible experience for all users. Built for WCAG 2.2 Level AA compliance, this plugin provides both automatic fixes and user-controlled customization options.

= Key Features =

**🎛️ User-Facing Accessibility Toolbar**
Give your visitors control over their experience with a floating toolbar that offers:

* High contrast modes (Dark, Light, Yellow-on-Black)
* Text resizing (100% - 200%)
* Dyslexia-friendly and readable fonts
* Grayscale and color inversion
* Reading guide and reading mask
* Animation pause control
* Large cursor option

**🔧 Automatic Remediation**
The plugin automatically fixes common accessibility issues:

* Skip links injection for keyboard navigation
* Focus ring enforcement (WCAG 2.4.7)
* Link highlighting in content areas (WCAG 1.4.1)
* Target size enforcement (WCAG 2.5.8)
* Focus visibility when obscured by sticky headers (WCAG 2.4.11)
* Respects `prefers-reduced-motion` system setting

**🔍 Content Scanner**
Audit your content for accessibility issues:

* Missing image alt text detection
* Heading hierarchy validation
* Generic link text identification ("click here", "read more")
* Batch processing to prevent server timeouts
* Issue tracking with resolution status

**📊 Compliance Reporting**
Track your accessibility progress:

* Real-time compliance score
* Module status overview
* Export reports (JSON, CSV)
* Scan history tracking

= WCAG 2.2 Coverage =

This plugin addresses criteria from all four WCAG principles:

**Perceivable**
* 1.1.1 Non-text Content (scanner)
* 1.3.1 Info and Relationships (ARIA module)
* 1.4.1 Use of Color (link highlighting)
* 1.4.3 Contrast (high contrast modes)
* 1.4.4 Resize Text (text scaling)
* 1.4.12 Text Spacing (spacing adjustments)

**Operable**
* 2.1.1 Keyboard (full keyboard support)
* 2.1.2 No Keyboard Trap (focus management)
* 2.4.1 Bypass Blocks (skip links)
* 2.4.7 Focus Visible (focus rings)
* 2.4.11 Focus Not Obscured (NEW in 2.2)
* 2.5.8 Target Size Minimum (NEW in 2.2)

**Understandable**
* 3.2.6 Consistent Help (fixed toolbar position)

**Robust**
* 4.1.2 Name, Role, Value (ARIA patterns)
* 4.1.3 Status Messages (live regions)

= Privacy =

* User preferences are stored in browser localStorage only
* No data is sent to external servers
* No tracking or analytics
* GDPR compliant

= Requirements =

* WordPress 6.0 or higher
* PHP 8.0 or higher
* Modern browser (Chrome, Firefox, Safari, Edge)

== Installation ==

= Automatic Installation =

1. Go to **Plugins > Add New** in your WordPress admin
2. Search for "Free WCAG"
3. Click **Install Now** and then **Activate**

= Manual Installation =

1. Download the plugin ZIP file
2. Go to **Plugins > Add New > Upload Plugin**
3. Choose the ZIP file and click **Install Now**
4. Activate the plugin

= After Activation =

1. Go to **Accessibility** in your admin menu
2. Enable the modules you need
3. Configure the frontend toolbar settings
4. Run a content scan to identify existing issues

== Frequently Asked Questions ==

= Will this plugin make my site fully WCAG compliant? =

This plugin helps address many WCAG 2.2 Level AA criteria, but full compliance also depends on your theme, content, and other plugins. Use this as part of a comprehensive accessibility strategy.

= Does the toolbar slow down my site? =

No. The plugin uses conditional loading - assets are only loaded when features are enabled. CSS and JavaScript are minimal and optimized.

= Will users' preferences persist? =

Yes. User preferences are stored in the browser's localStorage, so they persist across sessions and page visits.

= Can I customize the toolbar appearance? =

Yes. You can choose the toolbar position (left, right, or bottom) and theme (auto, light, or dark) from the admin settings.

= Is the plugin itself accessible? =

Yes. The admin dashboard and frontend toolbar are fully keyboard navigable and work with screen readers.

= Does this work with page builders? =

Yes. The plugin works at the WordPress level and is compatible with popular page builders like Elementor, Beaver Builder, and Gutenberg.

= Can I use this on a multisite installation? =

Yes. Activate the plugin on individual sites within your network.

= What happens when I deactivate the plugin? =

All settings are preserved. When you reactivate, your configuration will be restored.

= What happens when I delete the plugin? =

All plugin data, including settings and scan results, are removed from the database.

= Is this plugin translation-ready? =

Yes. The plugin is fully internationalized and ready for translation.

== Screenshots ==

1. Admin Dashboard - Overview of modules and compliance score
2. Module Settings - Toggle individual accessibility features
3. Frontend Toolbar - User-facing accessibility options
4. Content Scanner - Identify and fix accessibility issues
5. Reports - Track your accessibility progress
6. High Contrast Mode - Example of dark high contrast theme

== Changelog ==

= 1.0.0 =
* Initial release
* Visual adjustments module (contrast, fonts, colors)
* Navigation module (skip links, focus management)
* Content module (animations, reading aids)
* ARIA module (landmarks, labels, live regions)
* Interaction module (target size, drag alternatives)
* Content scanner with batch processing
* Compliance reporting and export
* Full WCAG 2.2 Level AA coverage

== Upgrade Notice ==

= 1.0.0 =
Initial release of Free WCAG.

== Additional Information ==

= Contributing =

We welcome contributions! Please visit our GitHub repository to report issues or submit pull requests.

= Support =

For support questions, please use the WordPress.org support forums.

= Credits =

* [Atkinson Hyperlegible Font](https://brailleinstitute.org/freefont) by Braille Institute
* [OpenDyslexic Font](https://opendyslexic.org/) by Abbie Gonzalez
* Accessibility icon from WordPress Dashicons

