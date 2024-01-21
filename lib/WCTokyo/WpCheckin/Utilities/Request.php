<?php

namespace WCTokyo\WpCheckin\Utilities;


/**
 * Request REST API to WordCamp site.
 */
trait Request {

	/**
	 * Send Request to REST API.
	 *
	 * @param string $path   Request path.
	 * @param string $method Request method[ 'GET', 'POST', 'PUT', 'DELETE', 'HEAD' ].
	 * @param array  $data   Query param for GET, body for POST.
	 *
	 * @return array|\WP_Error
	 */
	protected function rest_request( $path, $method = 'GET', $data = [] ) {
		$method = strtoupper( $method );
		if ( ! in_array( $method, [ 'GET', 'POST', 'PUT', 'DELETE', 'HEAD' ], true ) ) {
			return new \WP_Error( 'invalid_method', __( 'メソッドが無効です。', 'wp-checkin' ), [
				'status' => 400,
			] );
		}
		$url = get_option( 'wordcamp_site_url' );
		$user = get_option( 'wordcamp_user_login' );
		$pass = get_option( 'application_password' );
		if ( ! $url || ! $user || ! $pass ) {
			return new \WP_Error( 'invalid_credentials', __( '認証情報が設定されていません。', 'wp-checkin' ), [
				'status' => 400,
			] );
		}
		$endpoint = trailingslashit( $url ) . 'wp-json/' . ltrim( $path, '/' );
		$args = [
			'method'  => $method,
			'timeout' => (int) apply_filters( 'wp_checkin_timeout', 30, $path, $method, $data ),
			'headers' => [
				'Authorization' => 'Basic ' . base64_encode( $user . ':' . $pass ),
				'Content-Type'  => 'application/json',
			],
		];
		switch ( $method ) {
			case 'POST':
				$args['body'] = wp_json_encode( $data );
				break;
			default:
				$endpoint = add_query_arg( $data, $endpoint );
				break;
		}
		$response = wp_remote_request( $endpoint, $args );
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		switch ( $method ) {
			case 'HEAD':
				return wp_remote_retrieve_headers( $response );
			default:
				return json_decode( $response['body'], true );
		}

	}
}
