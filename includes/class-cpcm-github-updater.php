<?php
class CPCM_GitHub_Updater {
    const API_URL = 'https://api.github.com/repos/YOSSEF-WEAL/content-manager/releases/latest';
    const ZIP_URL = 'https://github.com/YOSSEF-WEAL/content-manager/archive/refs/tags/%s.zip';
    const REPO_URL = 'https://github.com/YOSSEF-WEAL/content-manager';
    const CACHE_KEY = 'cpcm_github_latest_release';
    const CACHE_TTL = 30 * MINUTE_IN_SECONDS;

    public static function init() {
        add_filter('pre_set_site_transient_update_plugins', array(__CLASS__, 'check_update'));
        add_filter('plugins_api', array(__CLASS__, 'plugins_api'), 10, 3);
        add_filter('upgrader_source_selection', array(__CLASS__, 'fix_source_directory'), 10, 4);
        add_action('upgrader_process_complete', array(__CLASS__, 'after_upgrade'), 10, 2);
    }

    public static function check_update($transient) {
        if (!is_object($transient) || empty($transient->checked)) {
            return $transient;
        }

        $current_version = defined('CPCM_VERSION') ? CPCM_VERSION : null;
        if (!$current_version) {
            return $transient;
        }

        $latest = self::get_latest_release();
        if (!$latest || empty($latest['tag_name'])) {
            return $transient;
        }

        $new_version = ltrim($latest['tag_name'], 'vV');
        if (version_compare($new_version, $current_version, '<=')) {
            return $transient;
        }

        $plugin_basename = defined('CPCM_PLUGIN_BASENAME') ? CPCM_PLUGIN_BASENAME : plugin_basename(__FILE__);

        $update = new stdClass();
        $update->slug = 'custom-page-content-manager';
        $update->plugin = $plugin_basename;
        $update->new_version = $new_version;
        $update->url = self::REPO_URL;
        $update->package = sprintf(self::ZIP_URL, $latest['tag_name']);

        $transient->response[$plugin_basename] = $update;
        return $transient;
    }

    public static function plugins_api($result, $action, $args) {
        if ($action !== 'plugin_information' || empty($args->slug) || $args->slug !== 'custom-page-content-manager') {
            return $result;
        }

        $latest = self::get_latest_release();
        $version = $latest && !empty($latest['tag_name']) ? ltrim($latest['tag_name'], 'vV') : (defined('CPCM_VERSION') ? CPCM_VERSION : '');
        $last_updated = $latest && !empty($latest['published_at']) ? $latest['published_at'] : '';

        $info = new stdClass();
        $info->name = 'Custom Page Content Manager';
        $info->slug = 'custom-page-content-manager';
        $info->version = $version;
        $info->author = '<a href="https://portfolio-yossef-weal.netlify.app/">Yossef Weal</a>';
        $info->homepage = self::REPO_URL;
        $info->download_link = $latest && !empty($latest['tag_name']) ? sprintf(self::ZIP_URL, $latest['tag_name']) : '';
        $info->last_updated = $last_updated;
        $info->sections = array(
            'description' => 'Professional plugin to manage custom fields for WordPress pages with multilingual support.',
        );

        return $info;
    }

    private static function get_latest_release() {
        $cached = get_transient(self::CACHE_KEY);
        if ($cached !== false) {
            return $cached;
        }

        $response = wp_remote_get(self::API_URL, array(
            'headers' => array(
                'Accept' => 'application/vnd.github+json',
                'User-Agent' => 'WordPress; CPCM',
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

        set_transient(self::CACHE_KEY, $data, self::CACHE_TTL);
        return $data;
    }

    public static function fix_source_directory($source, $remote_source, $upgrader, $hook_extra) {
        if (empty($hook_extra['type']) || $hook_extra['type'] !== 'plugin') {
            return $source;
        }

        $plugin_basename = defined('CPCM_PLUGIN_BASENAME') ? CPCM_PLUGIN_BASENAME : '';
        if (!empty($hook_extra['plugin']) && $plugin_basename && $hook_extra['plugin'] !== $plugin_basename) {
            return $source;
        }

        // The folder created by GitHub zip is typically repoName-tag
        $target_dir_name = basename(dirname(CPCM_PLUGIN_DIR)); // content-manager
        $new_source = trailingslashit(dirname($source)) . $target_dir_name . '/';

        if (@rename($source, $new_source)) {
            return $new_source;
        }

        return $source;
    }

    public static function after_upgrade($upgrader, $hook_extra) {
        if (!empty($hook_extra['type']) && $hook_extra['type'] === 'plugin') {
            delete_transient(self::CACHE_KEY);
        }
    }
}
