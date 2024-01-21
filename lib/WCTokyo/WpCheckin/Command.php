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
		$me = $this->rest_request( '/wp/v2/users/me', 'GET', [
			'context' => 'edit',
		] );
		if ( is_wp_error( $me ) ) {
			\WP_CLI::error( $me->get_error_message() );
		}
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
}
