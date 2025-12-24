# Custom Page Content Manager

Professional WordPress plugin for managing custom fields on pages with multilingual support and image upload capabilities.

**Version:** 2.0.0  
**Author:** Yossef Weal (يوسف وائل)  
**Author URI:** [https://portfolio-yossef-weal.netlify.app/](https://portfolio-yossef-weal.netlify.app/)  
**License:** GPL-2.0+

## ✨ Features

- 🎨 **Elementor Integration** - Dynamic Tags support for all field types
- ✏️ **Edit Fields** - Ability to rename and change field types
- 🖼️ **Image Upload** - WordPress media library integration for single and multiple images
- 🎨 Modern, professional admin interface with gradient design
- 🌍 Full internationalization (i18n) support
- 📱 Fully responsive design
- 🔄 RTL language support (Arabic, Hebrew, etc.)
- 🎯 Enhanced shortcodes with size and class attributes
- 🗑️ Safe field deletion with confirmation
- 📋 One-click shortcode copying
- 🔒 Security-first approach with nonces and capability checks
- ❤️ Developer credits footer on all admin pages

## 📦 Installation

1. Upload the `content-manager` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to **Page Content** in the admin menu

## 🚀 Usage

### Adding Custom Fields

1. Go to **Page Content** in the WordPress admin menu
2. Click **Manage Fields** for any page
3. Fill in the field name and select the field type
4. Click **Add Field**

### Field Types

| Type                | Description                        | Use Case                     |
| ------------------- | ---------------------------------- | ---------------------------- |
| **Text (Short)**    | Single-line text input             | Titles, names, URLs          |
| **Text (Long)**     | Multi-line textarea                | Descriptions, paragraphs     |
| **Number**          | Numeric input                      | Prices, quantities, years    |
| **Single Image**    | One image from media library       | Hero images, featured images |
| **Multiple Images** | Multiple images from media library | Galleries, sliders           |

### Using Shortcodes

#### Basic Usage

After creating a field, copy the generated shortcode:

```
[cpcm_field id="123" field="field-name"]
```

#### Image Fields with Attributes

For image fields, you can specify size and CSS class:

```
[cpcm_field id="123" field="hero-image" size="medium" class="my-custom-class"]
```

**Available Sizes:** `thumbnail`, `medium`, `large`, `full` (default)

#### Where to Use

- Page/Post content editor
- Elementor widgets (HTML/Shortcode widget)
- Theme template files using `do_shortcode()`
- Gutenberg blocks (Shortcode block)

### Example: Hero Section

**Admin Setup:**

1. Create field "hero_title" (Text)
2. Create field "hero_image" (Single Image)
3. Create field "hero_description" (Long Text)

**Theme Usage:**

```php
<section class="hero">
    <h1><?php echo do_shortcode('[cpcm_field id="5" field="hero_title"]'); ?></h1>
    <?php echo do_shortcode('[cpcm_field id="5" field="hero_image" size="large" class="hero-img"]'); ?>
    <p><?php echo do_shortcode('[cpcm_field id="5" field="hero_description"]'); ?></p>
</section>
```

### Example: Image Gallery

**Admin Setup:**

1. Create field "gallery" (Multiple Images)

**Theme Usage:**

```php
<div class="my-gallery">
    <?php echo do_shortcode('[cpcm_field id="5" field="gallery" size="medium"]'); ?>
</div>

<style>
.cpcm-image-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
}
.cpcm-image-gallery img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 8px;
}
</style>
```

## 🌍 Translation

The plugin is translation-ready and includes:

- **English** (default)
- **Arabic** (complete translation with RTL support)

### To Add a New Language

1. Use the `.pot` file in `/languages/` folder
2. Create a `.po` file for your language
3. Compile to `.mo` file
4. Place in `/languages/` folder

## 📁 File Structure

```
content-manager/
├── custom-page-content-manager.php  # Main plugin file
├── README.md                        # Documentation
├── includes/                        # Core classes
│   ├── class-cpcm-activator.php
│   ├── class-cpcm-deactivator.php
│   ├── class-cpcm-i18n.php
│   ├── class-cpcm-loader.php
│   └── class-cpcm-core.php
├── admin/                           # Admin functionality
│   ├── class-cpcm-admin.php
│   ├── css/cpcm-admin.css
│   ├── js/cpcm-admin.js
│   └── partials/
│       ├── cpcm-admin-list.php
│       └── cpcm-admin-edit.php
├── public/                          # Public functionality
│   └── class-cpcm-public.php
└── languages/                       # Translation files
    ├── custom-page-content-manager.pot
    └── custom-page-content-manager-ar.po
```

## 💻 Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher
- Modern web browser with JavaScript enabled

## 📝 Changelog

### Version 2.1.0 (Latest)

- ✅ **Elementor Integration** - Full support for Dynamic Tags
- ✅ **Edit Feature** - Ability to edit field name and type
- ✅ **Dynamic Tags** - Text, Number, Image, and Gallery tags
- ✅ **Image Upload Feature** - WordPress media library integration
- ✅ Single and multiple image support
- ✅ Enhanced shortcode with `size` and `class` attributes
- ✅ Complete OOP rewrite
- ✅ Modern, professional UI design
- ✅ Full internationalization support
- ✅ Fixed field deletion issues
- ✅ Added RTL support
- ✅ Developer credits footer
- ✅ Improved security
- ✅ Better code organization

### Version 1.0.0

- Initial release

## 🎯 Future Enhancements

Planned features for upcoming versions:

1. WYSIWYG editor for long text fields
2. Field groups and organization
3. Import/Export functionality
4. Custom Post Types support
5. Conditional logic
6. Field validation rules
7. Image drag & drop reordering
8. Built-in image cropping

## 👨‍💻 Developer

**Made with ❤️ by Yossef Weal (يوسف وائل)**

Portfolio: [https://portfolio-yossef-weal.netlify.app/](https://portfolio-yossef-weal.netlify.app/)

## 📞 Support

For questions, feature requests, or bug reports, please contact the developer through the portfolio website.

## 📄 License

This plugin is licensed under GPL-2.0+. You are free to use, modify, and distribute this plugin.

---

**Custom Page Content Manager** - Professional content management for WordPress pages.
