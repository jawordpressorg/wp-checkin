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
		Router::get_instance();
		RestApi::get_instance();
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::add_command( 'checkin', Command::class );
		}
		// Register assets.
		add_action( 'init', [ $this, 'register_assets' ] );
	}

	/**
	 * Register plugin assets.
	 *
	 * @return void
	 */
	public function register_assets() {
		$path = dirname( __FILE__, 4 ) . '/wp-dependencies.json';
		if ( ! file_exists( $path ) ) {
			return;
		}
		$dependencies = json_decode( file_get_contents( $path ), true );
		if ( ! $dependencies ) {
			return;
		}
		foreach ( $dependencies as $dependency ) {
			if ( empty( $dependency['path'] ) ) {
				continue;
			}
			$handle = $dependency['handle'];
			$hash   = $dependency['hash'];
			$url    = wp_checkin_url( $dependency['path'] );
			$deps   = $dependency['deps'];
			switch ( $dependency['ext'] ) {
				case 'js':
					$footer = [
						'in_footer' => $dependency['footer'],
					];
					if ( in_array( $dependency['strategy'], [ 'defer', 'async' ], true ) ) {
						$footer['strategy'] = $dependency['strategy'];
					}
					wp_register_script( $handle, $url, $deps, $hash, $footer );
					break;
				case 'css':
					wp_register_style( $handle, $url, $deps, $hash, $dependency['media'] );
					break;
			}
		}
	}
}
