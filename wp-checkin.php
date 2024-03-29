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
	\WCTokyo\WpCheckin\Bootstrap::get_instance();
}

/**
 * Get template part.
 *
 * @param string $name Relative path of template file on plugin root.
 * @param array $args Optional arguments.
 *
 * @return void
 */
function wp_checkin_template( $name, $args = [] ) {
	$path = __DIR__ . '/' . ltrim( $name, '/' );
	if ( ! preg_match( '/\.php$/u', $path ) ) {
		// If no extension, add .php.
		$path .= '.php';
	}
	if ( ! file_exists( $path ) ) {
		return;
	}
	load_template( $path, false, $args );
}

/**
 * Get URL of plugin.
 *
 * @param string $path Relative path from plugin root.
 *
 * @return string
 */
function wp_checkin_url( $path = '' ) {
	return plugins_url( $path, __FILE__ );
}

/**
 * Get the name of ticket owner.
 *
 * @param array $ticket
 * @return string
 */
function wp_checkin_ticket_owner( $ticket ) {
	// translators: %1$s is first name, %2$s is last name.
	return sprintf( _x( '%2$s %1$s', 'full-name', 'wp-checkin' ), $ticket[2], $ticket[3] );
}

/**
 * Get ticket detail with key=value array.
 *
 * @param array $ticket
 * @return array
 */
function wp_checkin_ticket_detail( $ticket ) {
	return \WCTokyo\WpCheckin\Tickets::get_meta( $ticket );
}
