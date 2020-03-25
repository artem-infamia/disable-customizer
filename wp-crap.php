<?php
/*
Plugin Name: Disable Customizer
Plugin URI: https://github.com/artem-infamia/disable-customizer
Description: Completely removes the WordPress Customizer from loading in your install.
Version: 1.0.0
Author: Artem Zabolotniy (infamia)
Author URI: https://github.com/artem-infamia/
Text Domain: wp-crap
Domain Path: /languages
*/

if (!class_exists('Disable_Customizer')) {
    class Disable_Customizer
    {
        private static $instance;

        public static function instance()
        {
            if (!isset(self::$instance) && !(self::$instance instanceof Disable_Customizer)) {

                // Start your engines!
                self::$instance = new Disable_Customizer;

                // Load the structures to trigger initially
                add_action('plugins_loaded', array(self::$instance, 'load_languages'));
                add_action('init', array(self::$instance, 'init'), 10); // was priority 5
                add_action('admin_init', array(self::$instance, 'admin_init'), 10); // was priority 5

            }
            return self::$instance;
        }

        public function init()
        {

            // Remove customize capability
            add_filter('map_meta_cap', array(self::$instance, 'filter_to_remove_customize_capability'), 10, 4);
        }

        public function admin_init()
        {

            // Drop some customizer actions
            remove_action('plugins_loaded', '_wp_customize_include', 10);
            remove_action('admin_enqueue_scripts', '_wp_customize_loader_settings', 11);

            // Manually overrid Customizer behaviors
            add_action('load-customize.php', array(self::$instance, 'override_load_customizer_action'));
        }

        /**
         * Load our language files
         *
         * @access public
         * @return void
         */
        public function load_languages()
        {
            // Set textdomain string
            $textdomain = 'wp-crap';

            // The 'plugin_locale' filter is also used by default in load_plugin_textdomain()
            $locale = apply_filters('plugin_locale', get_locale(), $textdomain);

            // Set filter for WordPress languages directory
            $wp_languages_dir = apply_filters('crap_wp_languages_dir', WP_LANG_DIR . '/wp-crap/' . $textdomain . '-' . $locale . '.mo');

            // Translations: First, look in WordPress' "languages" folder
            load_textdomain($textdomain, $wp_languages_dir);

            // Translations: Next, look in plugin's "languages" folder (default)
            $plugin_dir = basename(dirname(__FILE__));
            $languages_dir = apply_filters('crap_languages_dir', $plugin_dir . '/languages');
            load_plugin_textdomain($textdomain, FALSE, $languages_dir);
        }

        /**
         * Remove customize capability
         *
         * This needs to be in public so the admin bar link for 'customize' is hidden.
         */
        public function filter_to_remove_customize_capability($caps = array(), $cap = '', $user_id = 0, $args = array())
        {
            if ($cap == 'customize') {
                return array('nope');
            }

            return $caps;
        }

        /**
         * Manually overriding specific Customizer behaviors
         */
        public function override_load_customizer_action()
        {
            // If accessed directly
            wp_die(__('The Customizer is currently disabled.', 'wp-crap'));
        }

    } // End Class
}

function Disable_Customizer() {
	return Disable_Customizer::instance();
}

// Exit if accessed directly
if ( __FILE__ != $_SERVER['SCRIPT_FILENAME'] ) {
    Disable_Customizer();
}