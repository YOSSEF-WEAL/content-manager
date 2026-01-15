<?php
/**
 * GitHub Updater for Custom Page Content Manager
 * 
 * Checks for plugin updates from GitHub releases and handles the update process.
 * Always checks for fresh updates on admin pages (no caching in admin).
 *
 * @package    CPCM
 * @subpackage CPCM/includes
 * @since      2.0.0
 */

class CPCM_GitHub_Updater {
    const API_URL = 'https://api.github.com/repos/YOSSEF-WEAL/content-manager/releases/latest';
    const ZIP_URL = 'https://github.com/YOSSEF-WEAL/content-manager/archive/refs/tags/%s.zip';
    const REPO_URL = 'https://github.com/YOSSEF-WEAL/content-manager';
    const CACHE_KEY = 'cpcm_github_latest_release';
    
    // Cache for 5 minutes only (to reduce API calls but still be responsive)
    const CACHE_TTL = 5 * MINUTE_IN_SECONDS;

    public static function init() {
        // Hook into WordPress update system
        add_filter('pre_set_site_transient_update_plugins', array(__CLASS__, 'check_update'));
        add_filter('plugins_api', array(__CLASS__, 'plugins_api'), 10, 3);
        add_filter('upgrader_source_selection', array(__CLASS__, 'fix_source_directory'), 10, 4);
        add_action('upgrader_process_complete', array(__CLASS__, 'after_upgrade'), 10, 2);
        
        // Always clear cache on admin pages to ensure fresh update checks
        add_action('admin_init', array(__CLASS__, 'clear_cache_on_admin'));
    }
    
    /**
     * Clear the update cache on admin pages.
     * This ensures fresh update checks when users are in admin area.
     * Note: Cache is now handled directly in get_latest_release() for better control.
     */
    public static function clear_cache_on_admin() {
        // Always clear cache on admin pages to ensure fresh checks
        // This is a backup - main logic is in get_latest_release()
        global $pagenow;
        if (is_admin() && in_array($pagenow, array('plugins.php', 'update-core.php', 'index.php', 'admin.php'))) {
            delete_transient(self::CACHE_KEY);
        }
    }

    /**
     * Check for plugin updates from GitHub.
     * Compares current version with latest GitHub release.
     */
    public static function check_update($transient) {
        if (!is_object($transient)) {
            $transient = new stdClass();
        }

        $current_version = defined('CPCM_VERSION') ? CPCM_VERSION : null;
        if (!$current_version) {
            return $transient;
        }

        $plugin_basename = defined('CPCM_PLUGIN_BASENAME') ? CPCM_PLUGIN_BASENAME : plugin_basename(__FILE__);

        // Initialize response array if not exists
        if (!isset($transient->response)) {
            $transient->response = array();
        }

        // Remove any old update entries for this plugin to prevent stale updates
        if (isset($transient->response[$plugin_basename])) {
            unset($transient->response[$plugin_basename]);
        }

        $latest = self::get_latest_release();
        if (!$latest || empty($latest['tag_name'])) {
            return $transient;
        }

        // Remove 'v' prefix from tag name for version comparison
        $new_version = ltrim($latest['tag_name'], 'vV');
        
        // Clean and normalize versions for comparison
        // Remove leading/trailing whitespace and ensure proper format
        $new_version = trim($new_version);
        $current_version_clean = trim($current_version);
        
        // Only show update if new version is greater than current
        // version_compare handles version strings properly (e.g., "2.3.0" vs "2.3.1")
        if (version_compare($new_version, $current_version_clean, '<=')) {
            return $transient;
        }

        // Build update object
        $update = new stdClass();
        $update->slug = 'custom-page-content-manager';
        $update->plugin = $plugin_basename;
        $update->new_version = $new_version;
        $update->url = self::REPO_URL;
        $update->package = sprintf(self::ZIP_URL, $latest['tag_name']);
        $update->tested = get_bloginfo('version');
        $update->requires_php = '7.4';

        $transient->response[$plugin_basename] = $update;
        return $transient;
    }

    /**
     * Provide plugin information for the WordPress plugin details popup.
     */
    public static function plugins_api($result, $action, $args) {
        if ($action !== 'plugin_information' || empty($args->slug) || $args->slug !== 'custom-page-content-manager') {
            return $result;
        }

        $latest = self::get_latest_release();
        $version = $latest && !empty($latest['tag_name']) ? ltrim($latest['tag_name'], 'vV') : (defined('CPCM_VERSION') ? CPCM_VERSION : '');
        $last_updated = $latest && !empty($latest['published_at']) ? $latest['published_at'] : '';
        $changelog = $latest && !empty($latest['body']) ? $latest['body'] : '';

        $info = new stdClass();
        $info->name = 'Custom Page Content Manager';
        $info->slug = 'custom-page-content-manager';
        $info->version = $version;
        $info->author = '<a href="https://portfolio-yossef-weal.netlify.app/">Yossef Weal</a>';
        $info->homepage = self::REPO_URL;
        $info->download_link = $latest && !empty($latest['tag_name']) ? sprintf(self::ZIP_URL, $latest['tag_name']) : '';
        $info->last_updated = $last_updated;
        $info->tested = get_bloginfo('version');
        $info->requires_php = '7.4';
        $info->sections = array(
            'description' => 'Professional plugin to manage custom fields for WordPress pages with multilingual support.',
            'changelog' => nl2br(esc_html($changelog)),
        );

        return $info;
    }

    /**
     * Get the latest release from GitHub API.
     * Always fetches fresh data in admin area (no cache).
     * Uses short-term caching only on frontend to reduce API calls.
     */
    private static function get_latest_release() {
        // Always fetch fresh data in admin area (especially on plugins page)
        $is_admin_page = is_admin();
        $is_plugins_page = false;
        
        if ($is_admin_page) {
            global $pagenow;
            // Check if we're on plugins page or update page
            $is_plugins_page = in_array($pagenow, array('plugins.php', 'update-core.php', 'index.php'));
        }
        
        // Only use cache on frontend (not in admin)
        if (!$is_admin_page) {
            $cached = get_transient(self::CACHE_KEY);
            if ($cached !== false) {
                return $cached;
            }
        } else {
            // In admin, always delete cache to ensure fresh check
            delete_transient(self::CACHE_KEY);
        }

        // Make API request to GitHub
        $response = wp_remote_get(self::API_URL, array(
            'headers' => array(
                'Accept' => 'application/vnd.github+json',
                'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; CPCM/' . CPCM_VERSION,
            ),
            'timeout' => 15,
        ));

        if (is_wp_error($response)) {
            return false;
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code !== 200) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if (!is_array($data)) {
            return false;
        }

        // Only cache on frontend, not in admin
        if (!$is_admin_page) {
            set_transient(self::CACHE_KEY, $data, self::CACHE_TTL);
        }
        
        return $data;
    }

    /**
     * Fix the source directory after download.
     * GitHub zips have a folder name like "repo-name-tag", we rename it.
     */
    public static function fix_source_directory($source, $remote_source, $upgrader, $hook_extra) {
        if (empty($hook_extra['type']) || $hook_extra['type'] !== 'plugin') {
            return $source;
        }

        $plugin_basename = defined('CPCM_PLUGIN_BASENAME') ? CPCM_PLUGIN_BASENAME : '';
        if (!empty($hook_extra['plugin']) && $plugin_basename && $hook_extra['plugin'] !== $plugin_basename) {
            return $source;
        }

        // Rename to match expected plugin folder name
        $target_dir_name = basename(dirname(CPCM_PLUGIN_DIR)); // content-manager
        $new_source = trailingslashit(dirname($source)) . $target_dir_name . '/';

        if (@rename($source, $new_source)) {
            return $new_source;
        }

        return $source;
    }

    /**
     * Clear cache after plugin upgrade completes.
     */
    public static function after_upgrade($upgrader, $hook_extra) {
        if (!empty($hook_extra['type']) && $hook_extra['type'] === 'plugin') {
            $plugin_basename = defined('CPCM_PLUGIN_BASENAME') ? CPCM_PLUGIN_BASENAME : '';
            
            // Check if our plugin was updated
            if (!empty($hook_extra['plugin']) && $hook_extra['plugin'] === $plugin_basename) {
                delete_transient(self::CACHE_KEY);
                
                // Clear WordPress update cache to remove stale update notifications
                $update_transient = get_site_transient('update_plugins');
                if (is_object($update_transient) && isset($update_transient->response[$plugin_basename])) {
                    unset($update_transient->response[$plugin_basename]);
                    set_site_transient('update_plugins', $update_transient);
                }
            }
        }
    }
}
