<?php
/*
Plugin Name: WP Checkin
Plugin URI: https://github.com/jawordpressorg/wp-checkin
Description: A plugin for WordCamp Checkin.
Author: Fumiki Takahashi
Author URI: https://takahashifumiki.com
Text Domain: wp-checkin
Domain Path: /languages/
License: GPL v3 or later.
Version: nightly
*/

add_action( 'plugins_loaded', 'wp_checkin_init' );

/**
 * Bootstrap
 *
 * @since 1.0.0
 * @access private
 */
function wp_checkin_init() {
	// i18n.
	load_plugin_textdomain( 'wp-checkin', false, basename( dirname( __FILE__ ) ) . '/languages' );
	// Load composer if exists.
	require_once __DIR__ . '/vendor/autoload.php';
	// Bootstrap plugins.

}
