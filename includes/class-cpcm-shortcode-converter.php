<?php
/**
 * Shortcode Converter - Converts CPCM shortcodes in HTML attributes
 *
 * This class intercepts the final HTML output and converts CPCM shortcodes 
 * found inside HTML attributes (href, src, etc.) to their actual database values.
 * 
 * Problem: Elementor URL-encodes shortcodes in link fields and prepends http://
 * Solution: Use output buffering to catch final HTML, decode and process shortcodes
 *
 * @package    CPCM
 * @subpackage CPCM/includes
 * @since      2.3.0
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Class CPCM_Shortcode_Converter
 * 
 * Handles automatic conversion of shortcodes in HTML attributes.
 * Works by intercepting the final page output via output buffering,
 * finding shortcodes in href/src attributes, and replacing them with actual values.
 *
 * @since 2.3.0
 */
class CPCM_Shortcode_Converter {

    /**
     * Flag to prevent infinite loops during content processing.
     * Set to true when processing starts, reset when done.
     *
     * @since  2.3.0
     * @access private
     * @var    bool
     */
    private static $is_processing = false;

    /**
     * Cache for processed content to avoid reprocessing same content.
     * Key: MD5 hash of content, Value: processed content
     *
     * @since  2.3.0
     * @access private
     * @var    array
     */
    private static $content_cache = array();

    /**
     * Initialize the class and register hooks
     *
     * @since 2.3.0
     */
    public function __construct() {
        $this->register_hooks();
    }

    /**
     * Register output buffer hook.
     * Only runs on frontend, skips admin and Elementor editor.
     *
     * @since  2.3.0
     * @access private
     */
    private function register_hooks() {
        // Skip processing in admin area or Elementor editor
        if ($this->is_admin_or_editor()) {
            return;
        }

        // Start output buffering early to catch all content
        add_action('template_redirect', array($this, 'start_output_buffer'), 1);
    }

    /**
     * Check if current request is admin area or Elementor editor.
     * We don't want to process shortcodes in the editor - only on frontend.
     *
     * @since  2.3.0
     * @access private
     * @return bool True if in admin or editor mode
     */
    private function is_admin_or_editor() {
        if (is_admin()) {
            return true;
        }

        // Elementor adds these GET params when in preview/edit mode
        if (isset($_GET['elementor-preview']) || isset($_GET['preview'])) {
            return true;
        }

        return false;
    }

    /**
     * Start output buffering to capture the entire page HTML.
     * The callback will process the buffer before sending to browser.
     *
     * @since 2.3.0
     */
    public function start_output_buffer() {
        ob_start(array($this, 'process_output_buffer'));
    }

    /**
     * Process the captured output buffer.
     * This is called automatically when the buffer is flushed.
     *
     * @since  2.3.0
     * @param  string $buffer The complete HTML output
     * @return string Processed HTML with shortcodes converted
     */
    public function process_output_buffer($buffer) {
        return $this->convert_shortcodes_in_content($buffer);
    }

    /**
     * Main processing function - converts shortcodes in content.
     * Includes safety checks for empty content, infinite loops, and caching.
     *
     * @since  2.3.0
     * @param  string $content The HTML content to process
     * @return string Processed content with shortcodes converted to values
     */
    public function convert_shortcodes_in_content($content) {
        // Safety check: skip empty or non-string content
        if (empty($content) || !is_string($content)) {
            return $content;
        }

        // Safety check: prevent infinite loops
        if (self::$is_processing) {
            return $content;
        }

        // Quick check: skip if no CPCM shortcodes present
        // This checks for 'cpcm_field' which appears in both encoded and decoded versions
        if (strpos($content, 'cpcm_field') === false) {
            return $content;
        }

        // Performance: return cached result if available
        $content_hash = md5($content);
        if (isset(self::$content_cache[$content_hash])) {
            return self::$content_cache[$content_hash];
        }

        // Set processing flag to prevent recursion
        self::$is_processing = true;

        // Do the actual shortcode replacement
        $processed_content = $this->process_shortcodes_in_attributes($content);

        // Reset processing flag
        self::$is_processing = false;

        // Cache result (limit to 100 entries to prevent memory issues)
        if (count(self::$content_cache) < 100) {
            self::$content_cache[$content_hash] = $processed_content;
        }

        return $processed_content;
    }

    /**
     * Find and replace shortcodes in HTML attributes.
     * Targets href and src attributes that contain 'cpcm_field'.
     * 
     * Example input:  href="http://[cpcm_field%20id=45%20field=url-test]"
     * Example output: href="https://example.com/actual-url"
     *
     * @since  2.3.0
     * @access private
     * @param  string $content The HTML content
     * @return string Content with shortcodes replaced
     */
    private function process_shortcodes_in_attributes($content) {
        // Pattern matches: href="...cpcm_field..." or src="...cpcm_field..."
        // The attribute value can contain URL-encoded characters
        $pattern = '/(href|src)=["\']([^"\']*cpcm_field[^"\']*)["\']/';
        
        $processed = preg_replace_callback(
            $pattern,
            array($this, 'replace_attribute_value'),
            $content
        );

        // If regex failed, return original to avoid breaking the page
        if ($processed === null) {
            return $content;
        }

        return $processed;
    }

    /**
     * Callback to process a single attribute match.
     * Handles URL decoding, http:// prefix removal, and shortcode execution.
     * 
     * Elementor transforms: [cpcm_field id="45" field="test"]
     * Into: http://[cpcm_field%20id=45%20field=test]
     * 
     * This function reverses that and gets the actual value.
     *
     * @since  2.3.0
     * @access private
     * @param  array $matches Regex matches [0]=full match, [1]=attr name, [2]=attr value
     * @return string The corrected attribute (e.g., href="https://actual-url.com")
     */
    private function replace_attribute_value($matches) {
        $attribute_name = $matches[1];  // 'href' or 'src'
        $attribute_value = $matches[2]; // The encoded shortcode value
        
        // Step 1: URL decode (converts %20 back to spaces, etc.)
        $decoded = urldecode($attribute_value);
        
        // Step 2: Remove http:// or https:// prefix that Elementor adds before shortcode
        if (preg_match('/^https?:\/\/\[cpcm_field/', $decoded)) {
            $decoded = preg_replace('/^https?:\/\//', '', $decoded);
        }
        
        // Step 3: Try WordPress do_shortcode() first
        if (preg_match('/\[cpcm_field\s+([^\]]+)\]/', $decoded, $shortcode_match)) {
            $shortcode = '[cpcm_field ' . $shortcode_match[1] . ']';
            $result = do_shortcode($shortcode);
            
            // If shortcode returned a different value (was processed), use it
            if (!empty($result) && $result !== $shortcode) {
                return $attribute_name . '="' . esc_attr($result) . '"';
            }
        }
        
        // Step 4: Fallback - manually parse and fetch value from database
        // This handles cases where Elementor stripped the quotes from attributes
        // Pattern matches: id=45 field=url-test (with or without quotes)
        if (preg_match('/\[cpcm_field\s+id=["\']?(\d+)["\']?\s+field=["\']?([^"\'\]]+)["\']?\s*\]/', $decoded, $manual_match)) {
            $page_id = intval($manual_match[1]);
            $field_name = trim($manual_match[2]);
            
            $value = self::get_field_value($page_id, $field_name);
            
            if (!empty($value)) {
                return $attribute_name . '="' . esc_attr($value) . '"';
            }
        }
        
        // If nothing worked, return unchanged to avoid breaking the link
        return $matches[0];
    }

    /**
     * Get field value directly from WordPress post meta.
     * This is a fallback when do_shortcode() doesn't work.
     * 
     * Field values are stored as: post_meta key = 'cpcm_' + field_name
     * Field definitions are stored in: post_meta key = '_cpcm_fields'
     *
     * @since  2.3.0
     * @access public
     * @param  int    $page_id The WordPress page/post ID
     * @param  string $field   The field name (without 'cpcm_' prefix)
     * @return string The field value, or empty string if not found
     */
    public static function get_field_value($page_id, $field) {
        if (empty($page_id) || empty($field)) {
            return '';
        }

        // Clean field name
        $field = trim($field);

        // Get the field value from post meta
        $value = get_post_meta($page_id, 'cpcm_' . $field, true);

        if (empty($value)) {
            return '';
        }

        // Check if this is an image field - need to return URL instead of ID
        $fields = get_post_meta($page_id, '_cpcm_fields', true);
        $field_key = sanitize_title($field);

        if (is_array($fields) && isset($fields[$field_key])) {
            $field_type = $fields[$field_key]['type'];

            // For single image fields, convert attachment ID to URL
            if ($field_type === 'single_image' && is_numeric($value)) {
                $image_url = wp_get_attachment_url($value);
                return $image_url ? $image_url : '';
            }
        }

        return $value;
    }

    /**
     * Clear the content cache.
     * Useful when field values are updated and cache needs refresh.
     *
     * @since  2.3.0
     * @access public
     * @static
     */
    public static function clear_cache() {
        self::$content_cache = array();
    }
}
