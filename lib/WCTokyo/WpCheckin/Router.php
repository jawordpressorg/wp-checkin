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
		if ( in_array( $is_checkin, [ 'archive', 'single' ], true ) ) {
			wp_enqueue_style( 'wp-checkin' );
			// Load template and exit.
			$args = [];
			switch ( $is_checkin ) {
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
		add_rewrite_rule( '^checkin/page/(\d+)/?$', 'index.php?checkin=archive&paged=$matches[1]', 'top' );
		add_rewrite_rule( '^checkin/ticket/(\d+)/?$', 'index.php?checkin=single&p=$matches[1]', 'top' );
	}
}
