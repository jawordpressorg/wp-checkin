<?php

namespace WCTokyo\WpCheckin;


use WCTokyo\WpCheckin\Pattern\SingletonPattern;

/**
 * URL router for plugin.
 *
 * This will overrides default routing system and display other template.
 */
class Router extends SingletonPattern {

	/**
	 * Register hooks to generate rewrite rules.
	 *
	 * @return void
	 */
	protected function init() {
		add_action( 'init', [ $this, 'add_rewrite_rules' ] );
		add_filter( 'query_vars', [ $this, 'add_query_vars' ] );
		add_action( 'pre_get_posts', [ $this, 'pre_get_posts' ] );
		add_action( 'admin_bar_menu', [ $this, 'admin_bar_menu' ], 300 );
	}

	/**
	 * Add query var 'checkin' for rewrite rules.
	 *
	 * @param string[] $vars Default query vars
	 * @return string[]
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'checkin';
		return $vars;
	}

	/**
	 * Hijack query
	 *
	 * @param \WP_Query $wp_query
	 * @return void
	 */
	public function pre_get_posts( $wp_query ) {
		$is_checkin = $wp_query->get( 'checkin' );
		if ( ! get_query_var( 'checkin' ) || ! $wp_query->is_main_query() ) {
			return;
		}
		if ( in_array( $is_checkin, [ 'archive', 'single', 'qr' ], true ) ) {
			$do_auth_header = true;
			wp_enqueue_style( 'wp-checkin' );
			// Load template and exit.
			$args = [];
			switch ( $is_checkin ) {
				case 'qr':
					$this->render_qr();
					break;
				case 'archive':
					$args = [
						'title' => __( '登録済みのチケット', 'wp-checkin' ),
					];
					break;
				case 'single':
					wp_enqueue_script( 'wp-checkin-attendance' );
					wp_localize_script( 'wp-checkin-attendance', 'wpCheckin', [
						'user' => get_option( 'wordcamp_auth_user' ),
						'pass' => get_option( 'wordcamp_auth_pass' ),
					] );
					$id   = get_query_var( 'p' );
					$args = [
						// translators: %d is ticket ID.
						'title' => sprintf( __( 'チケット: %d', 'wp-checkin' ), $id ),
						'id'    => get_query_var( 'p' ),
					];
					break;
			}
			// Create fake post.
			$GLOBALS['post'] = new \WP_Post( (object) [
				'ID'            => 0,
				'post_title'    => $args['title'],
				'post_status'   => 'publish',
				'comment_count' => 0,
			] );
			remove_action( 'wp_head', 'feed_links_extra', 3 );
			// Do authorization header.
			if ( $do_auth_header ) {
				$this->do_authorization_header();
			}
			// Render hijacked template.
			do_action( 'template_redirect' );
			wp_checkin_template( 'template-parts/header', $args );
			wp_checkin_template( 'template-parts/' . $is_checkin, $args );
			wp_checkin_template( 'template-parts/footer', $args );
			exit;
		}
	}

	/**
	 * Register rewrite rules.
	 *
	 * @return void
	 */
	public function add_rewrite_rules() {
		// Front archive.
		add_rewrite_rule( '^checkin/?$', 'index.php?checkin=archive', 'top' );
		add_rewrite_rule( '^checkin/qr.png/?$', 'index.php?checkin=qr', 'top' );
		add_rewrite_rule( '^checkin/page/(\d+)/?$', 'index.php?checkin=archive&paged=$matches[1]', 'top' );
		add_rewrite_rule( '^checkin/ticket/(\d+)/?$', 'index.php?checkin=single&p=$matches[1]', 'top' );
	}

	/**
	 * Do authorization header.
	 *
	 * @return void
	 */
	public function do_authorization_header() {
		$user = get_option( 'wordcamp_auth_user' );
		$pass = get_option( 'wordcamp_auth_pass' );
		if ( ! isset( $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'] ) || $user !== $_SERVER['PHP_AUTH_USER'] || $pass !== $_SERVER['PHP_AUTH_PW'] ) {
			header( 'WWW-Authenticate: Basic realm="Enter username and password."' );
			header( 'Content-Type: text/plain; charset=utf-8' );
			wp_die( __( 'このページを閲覧するためにはユーザー名とパスワードが必要です。', 'wp-checkin' ), get_status_header_desc( 401 ), [
				'status'   => 401,
				'response' => 401,
			] );
		}
	}

	/**
	 * Custom admin bar.
	 *
	 * @param \WP_Admin_Bar $admin_bar Admin bar instance.
	 * @return void
	 */
	public function admin_bar_menu( \WP_Admin_Bar &$admin_bar ) {
		$admin_bar->add_node( [
			'parent' => 'site-name',
			'id'     => 'wp-checkin',
			'title'  => __( 'チケット一覧ページ', 'wp=-checkin' ),
			'href'   => home_url( 'checkin' ),
			'meta'   => [
				'tabindex' => 0,
			],
		] );
	}

	/**
	 * Render QR code.
	 *
	 * @return void
	 */
	public function render_qr() {
		$url    = home_url( 'checkin' );
		$params = [
			'g' => 2,
			'f' => 3,
			'e' => 4,
		];
		$query  = [];
		foreach ( $params as $name => $index ) {
			$query[ $index ] = filter_input( INPUT_GET, $name );
		}
		$tickets = Tickets::search( $query );
		if ( 1 === $tickets['total'] ) {
			$url = home_url( 'checkin/ticket/' . $tickets['tickets'][0][0] );
		} elseif ( ! empty( $query[4] ) ) {
			// Not found. Try to search with email.
			$url = home_url( 'checkin/?s=' . rawurlencode( $query[4] ) );
		}
		// Generate URL with Google Chart API.
		$api_url = add_query_arg( [
			'cht' => 'qr',
			'chs' => '300x300',
			'chl' => $url,
		], 'https://chart.apis.google.com/chart' );
		$content = file_get_contents( $api_url );
		header( 'Content-Type: image/png' );
		echo $content;
		exit;
	}
}
