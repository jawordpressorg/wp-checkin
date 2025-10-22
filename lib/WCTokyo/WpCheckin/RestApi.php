<?php

namespace WCTokyo\WpCheckin;


use WCTokyo\WpCheckin\Pattern\SingletonPattern;

/**
 * REST API handler.
 */
class RestApi extends SingletonPattern {

	/**
	 * {@inheritDoc}
	 */
	protected function init() {
		add_action( 'init', [ $this, 'register_post' ] );
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Register post type for checkin log.
	 *
	 * @return void
	 */
	public function register_post() {
		register_post_type( 'checkin-log', [
			'public'            => false,
			'show_in_rest'      => false,
			'supports'          => [ 'title', 'editor', 'slug' ],
			'show_ui'           => true,
			'show_in_nav_menus' => false,
			'show_in_admin_bar' => false,
			'menu_icon'         => 'dashicons-tickets-alt',
			'labels'            => [
				'name'          => _x( 'チェックイン記録', '', 'wp-checkin' ),
				'singular_name' => _x( 'チェックイン記録', '', 'wp-checkin' ),
			],
		] );
	}

	/**
	 * Register REST API routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route( 'wp-checkin/v1', '/checkin/(?P<ticket_id>\d+)', [
			'methods'             => [ 'POST', 'GET', 'DELETE' ],
			'callback'            => [ $this, 'checkin' ],
			'permission_callback' => '__return_true',
			'args'                => [
				'ticket_id' => [
					'required'          => true,
					'type'              => 'integer',
					'validate_callback' => function ( $param ) {
						return is_numeric( $param );
					},
				],
				'auth_user' => [
					'required'          => true,
					'type'              => 'string',
					'validate_callback' => function ( $param ) {
						return get_option( 'wordcamp_auth_user' ) === $param;
					},
				],
				'auth_pass' => [
					'required'          => true,
					'type'              => 'string',
					'validate_callback' => function ( $param ) {
						return get_option( 'wordcamp_auth_pass' ) === $param;
					},
				],
			],
		] );
	}

	/**
	 * Callback for REST API
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function checkin( \WP_REST_Request $request ) {
		$ticket = Tickets::get( $request['ticket_id'] );
		if ( ! $ticket ) {
			return new \WP_Error( 'invalid_ticket', __( 'チケットが見つかりません。', 'wp-checkin' ), [
				'status' => 404,
			] );
		}
		$is_checked_in = Tickets::is_checked_in( $request['ticket_id'] );
		switch ( $request->get_method() ) {
			case 'POST':
				if ( $is_checked_in ) {
					return new \WP_Error( 'already_checked_in', __( 'チケットは既にチェックイン済みです。', 'wp-checkin' ), [
						'status' => 400,
					] );
				}
				$post_id = wp_insert_post( [
					// translators: %1$d is ticket ID, %2$s is ticket owner name.
					'post_title'  => sprintf( __( '#%1$d %2$s', 'wp-checkin' ), $ticket[0], wp_checkin_ticket_owner( $ticket ) ),
					'post_name'   => $ticket[0],
					'post_type'   => 'checkin-log',
					'post_date'   => current_time( 'mysql' ),
					'post_status' => 'publish',
				] );
				if ( is_wp_error( $post_id ) ) {
					return $post_id;
				}
				return new \WP_REST_Response( [
					'checked_in' => true,
					'items'      => Tickets::get_ticket_items( $ticket ),
				] );
			case 'GET':
				return new \WP_REST_Response( [
					'checked_in' => (bool) $is_checked_in,
					'items'      => $is_checked_in ? Tickets::get_ticket_items( $ticket ) : [],
				] );
			case 'DELETE':
				if ( ! $is_checked_in ) {
					return new \WP_Error( 'not_checked_in', __( 'チケットはチェックインされていません。', 'wp-checkin' ), [
						'status' => 400,
					] );
				}
				if ( wp_delete_post( $is_checked_in->ID, true ) ) {
					return new \WP_REST_Response( [
						'checked_in' => false,
					] );
				}
				return new \WP_Error( 'failed_to_delete', __( 'チェックイン記録の削除に失敗しました。', 'wp-checkin' ), [
					'status' => 500,
				] );
		}
	}
}
