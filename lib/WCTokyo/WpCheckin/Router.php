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
		add_action( 'template_redirect', [ $this, 'hijack_template' ] );
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

	/**
	 * Hijack template.
	 *
	 * @return void
	 */
	public function hijack_template() {
		$is_checkin = get_query_var( 'checkin' );
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
					$id   = get_query_var( 'p' );
					$args = [
						'title' => sprintf( __( 'チケット: %d', 'wp-checkin' ), $id ),
						'id'    => get_query_var( 'p' ),
					];
					break;
			}
			wp_checkin_template( 'template-parts/header', $args );
			wp_checkin_template( 'template-parts/' . $is_checkin, $args );
			wp_checkin_template( 'template-parts/footer', $args );
			exit;
		}
	}
}
