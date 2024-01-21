<?php

namespace WCTokyo\WpCheckin;

use cli\Table;
use WCTokyo\WpCheckin\Utilities\Request;

/**
 * CLI utilities for wp-checkin
 *
 */
class Command extends \WP_CLI_Command {

	use Request;

	/**
	 * Check if credential is valid.
	 *
	 * @subcommand is-valid
	 * @return void
	 */
	public function is_valid() {
		$me    = $this->rest_request( '/wp/v2/users/me', 'GET', [
			'context' => 'edit',
		] );
		$table = new Table();
		$table->setHeaders( [ 'Property', 'Value' ] );
		foreach ( $me as $key => $value ) {
			if ( 0 === strpos( $key, '_' ) ) {
				continue;
			}
			if ( empty( $value ) ) {
				$value = 'EMPTY';
			} elseif ( is_array( $value ) ) {
				$value = 'ARRAY';
			}
			$table->addRow( [ $key, $value ] );
		}
		$table->display();
		\WP_CLI::success( __( 'ユーザー情報を取得しました。', 'wp-checkin' ) );
	}

	/**
	 * Display API.
	 *
	 * @return void
	 */
	public function namespaces() {
		$response = $this->rest_request( '/' );
		foreach ( $response['namespaces'] as $namespace ) {
			\WP_CLI::line( sprintf( '%s/wp-json/%s', trailingslashit( get_option( 'wordcamp_site_url' ) ), $namespace ) );
		}
		\WP_CLI::line( '' );
		// translators: %d is number of namespaces.
		\WP_CLI::success( sprintf( __( '%dの名前空間があります。', 'wp-checkin' ), count( $response['namespaces'] ) ) );
	}

	/**
	 * {@see Request::rest_request()}
	 */
	private function request( $path, $method = 'GET', $data = [] ) {
		$response = $this->rest_request( $path, $method, $data );
		if ( is_wp_error( $response ) ) {
			\WP_CLI::error( $response->get_error_message() );
		}
		return $response;
	}
}
