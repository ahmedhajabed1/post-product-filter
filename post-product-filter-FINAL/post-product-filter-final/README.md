# AJAX Post Filter - WordPress Plugin

**Version:** 1.0.0  
**Author:** Ahmed haj abed  
**License:** GPL v2 or later

## 📖 Description

A powerful WordPress plugin for filtering blog posts by categories with AJAX, featuring Elementor support, lazy loading, multiple pagination types, and complete customization options. Now with auto-apply filters and flexible display options.

## ✨ Features

- ✅ **Auto-Apply Filters** - Filters apply automatically on selection (no apply button needed)
- ✅ **AJAX Filtering** - No page reloads
- ✅ **Elementor Widget** - Drag and drop interface
- ✅ **Lazy Loading** - 50-70% faster page loads
- ✅ **3 Pagination Types** - Standard, Load More, Infinite Scroll
- ✅ **Flexible Display Options** - Toggle excerpt, read more, meta, and categories
- ✅ **Font Customization** - Customize title size and colors
- ✅ **SEO Optimized** - Proper meta tags
- ✅ **Fully Customizable** - Colors, text, styles
- ✅ **Responsive Design** - All devices supported
- ✅ **Clean Layout** - Modern 2-column grid design

## 🚀 Installation

### From WordPress Admin

1. Go to **Plugins → Add New**
2. Click **Upload Plugin**
3. Choose the ZIP file
4. Click **Install Now** and **Activate**

### Manual Installation

1. Unzip the plugin file
2. Upload to `/wp-content/plugins/ajax-post-filter/`
3. Activate through WordPress admin

## 📝 Usage

### Method 1: Elementor (Recommended)

1. Edit page with Elementor
2. Search for "Ajax Post Filter"
3. Drag widget to page
4. Configure settings visually
5. Publish!

### Method 2: Shortcode

```
[ajax_post_filter slug="default-preset"]
```

## ⚙️ Configuration

### Admin Panel

Navigate to **Post Filter** in WordPress admin to create and manage presets.

### Preset Settings

Each preset includes three tabs:

1. **General Settings** - Posts per page, pagination type, grid columns, category selection
2. **Display Options** - Toggle excerpt, read more button, meta info, categories, search box
3. **Styling** - Title font size/colors, button colors, custom CSS

### Key Features

- **Auto-Apply**: Filters apply automatically when checkboxes are checked
- **Auto-Reset**: Filters reset automatically when unchecked
- **Font Control**: Customize title size (10-60px) and colors
- **Show/Hide Elements**: Toggle excerpt, read more, meta, and category badges

## 🎨 Display Options

You can toggle the following for each preset:
- Post excerpt
- Read More button
- Post meta (date, author)
- Category badges
- Category search box
- Post count in categories

## 📱 Responsive

Works perfectly on:
- Desktop (1920px+)
- Laptop (1200px-1919px)
- Tablet (768px-1199px)
- Mobile (320px-767px)

## 🔧 Technical Details

**Requirements:**
- WordPress 5.0+
- PHP 7.4+
- jQuery (included with WordPress)

**Compatible with:**
- Elementor 3.0+
- Most WordPress themes
- Popular caching plugins

**Performance:**
- Lazy loading (native + fallback)
- Optimized AJAX queries
- Minimal file size (~150KB)
- Fast load times

## 📚 Changelog

### Version 1.0.0
- Auto-apply filters (no apply button)
- Auto-reset on uncheck (no reset button)
- Font size customization for titles
- Toggle excerpt display option
- Toggle read more button option
- Toggle post meta option
- Toggle category badges option
- Simplified admin panel (single page)
- Tabbed settings in preset editor
- Clean 2-column grid layout
- Improved mobile responsiveness

## 🐛 Troubleshooting

### Widget Not Showing
- Clear Elementor cache
- Regenerate CSS/JS
- Check WordPress/Elementor versions

### AJAX Not Working
- Check browser console for errors
- Verify nonce is valid
- Clear site cache

### Styling Issues
- Clear browser cache
- Check for theme conflicts
- Use custom CSS override

## 📞 Support

For support, feature requests, or bug reports:
- WordPress.org support forum
- Contact developer

## 🎓 Credits

- **Developer:** Ahmed haj abed
- **Icons:** Dashicons (WordPress)

## 📄 License

This plugin is licensed under GPL v2 or later.

```
Copyright (C) 2025 Ahmed haj abed

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
```

## 🙏 Thank You

Thank you for using AJAX Post Filter!

---

**Made with ❤️ by Ahmed haj abed**
