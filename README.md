# Free WCAG

WordPress accessibility toolkit aimed at WCAG 2.2 Level AA. It combines a user-facing toolbar, automatic remediation, and content auditing to help site owners improve accessibility without heavy setup.

## Features
- User toolbar: contrast modes (dark/light/yellow-on-black), text resizing, dyslexia/readable fonts, grayscale/invert, reading guide/mask, pause animations, large cursor.
- Automatic fixes: skip links, focus rings, link highlighting, target size, focus not obscured, honors `prefers-reduced-motion`.
- Content scanner: finds missing alts, heading hierarchy issues, generic link text; batch processing with issue tracking.
- Reporting: compliance score, module status overview, scan history, export to CSV/JSON.
- Privacy: user preferences stored in localStorage only; no external requests or tracking.

### WCAG 2.2 Coverage (highlights)
- Perceivable: 1.1.1, 1.3.1, 1.4.1, 1.4.3, 1.4.4, 1.4.12
- Operable: 2.1.1, 2.1.2, 2.4.1, 2.4.7, 2.4.11, 2.5.8
- Understandable: 3.2.6
- Robust: 4.1.2, 4.1.3

## Requirements
- WordPress 6.0+
- PHP 8.0+
- Modern browser (Chrome, Firefox, Safari, Edge)

## Installation
1) From WordPress: Plugins → Add New → search “Free WCAG” (or upload the ZIP) → Install → Activate.  
2) Manual: copy this folder to `wp-content/plugins/free-wcag`, then activate in **Plugins**.

## After Activation
1) Go to **Accessibility** in the WordPress admin.  
2) Enable the modules you need and configure the frontend toolbar.  
3) Run a content scan to identify existing issues.  
4) Review the compliance score and export reports if needed.

## FAQ
- Will this make my site fully compliant? It covers many WCAG 2.2 Level AA items, but full compliance depends on your theme, content, and other plugins.
- Does it slow the site? Assets load conditionally and are lightweight.
- Do user preferences persist? Yes, stored in browser localStorage.
- Can I customize the toolbar? Yes—position (left/right/bottom) and theme (auto/light/dark).
- Is the plugin itself accessible? Admin UI and toolbar are keyboard- and screen reader-friendly.
- Works with page builders? Yes; built to be page-builder agnostic.
- Multisite support? Yes; activate per site.
- What happens on deactivate/delete? Settings persist on deactivate; all plugin data is removed on delete.
- Translation-ready? Yes; fully internationalized.

## Contributing
Issues and pull requests are welcome. Please open an issue first for major changes.

## Credits
- Atkinson Hyperlegible Font by Braille Institute
- OpenDyslexic Font by Abbie Gonzalez
- Accessibility icon from WordPress Dashicons

## Support
- WordPress.org support forums (preferred) 

## License
GPL-2.0-or-later. See `LICENSE` for details.

