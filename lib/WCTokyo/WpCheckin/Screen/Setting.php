<?php

namespace WCTokyo\WpCheckin\Screen;


use WCTokyo\WpCheckin\Pattern\SingletonPattern;

/**
 * Create setting screen.
 */
class Setting extends SingletonPattern {

	/**
	 * {@inheritDoc}
	 */
	protected function init() {
		add_action( 'admin_init', [ $this, 'register_option' ] );
	}

	/**
	 * Register option screen.
	 *
	 * @return void
	 */
	public function register_option() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}
		add_settings_section(
			'wp_checkin_settings',
			__( 'WordCamp チェックイン設定', 'wp-checkin' ),
			function() {
				printf( '<p>%s</p>', esc_html__( 'WordCampのチェックインシステムに必要な設定を行います。', 'wp-checkin' ) );
			},
			'general'
		);
		foreach ( [
			[ 'wordcamp_site_url', __( 'WordCampサイトのURL', 'wp-checkin' ), 'url' ],
			[ 'wordcamp_user_login', __( 'ユーザー名', 'wp-checkin' ), 'text' ],
			[ 'application_password', __( 'アプリケーションパスワード', 'wp-checkin' ), 'text' ],
		] as list( $option_name, $label, $type ) ) {
			add_settings_field( $option_name, $label, function() use ( $option_name, $type ) {
				$value = get_option( $option_name );
				printf( '<input type="%s" name="%s" value="%s" class="regular-text">', esc_attr( $type ), esc_attr( $option_name ), esc_attr( $value ) );
			}, 'general', 'wp_checkin_settings' );
			register_setting( 'general', $option_name );
		}
	}
}
