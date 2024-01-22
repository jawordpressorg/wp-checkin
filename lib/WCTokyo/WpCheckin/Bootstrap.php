<?php

namespace WCTokyo\WpCheckin;


use WCTokyo\WpCheckin\Pattern\SingletonPattern;
use WCTokyo\WpCheckin\Screen\Setting;

/**
 * Bootstrap for plugin.
 */
class Bootstrap extends SingletonPattern {

	/**
	 * {@inheritDoc}
	 */
	protected function init() {
		Setting::get_instance();
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::add_command( 'checkin', Command::class );
		}
	}
}
