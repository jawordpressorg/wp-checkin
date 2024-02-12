<?php

namespace WCTokyo\WpCheckin\Screen;


use WCTokyo\WpCheckin\Pattern\SingletonPattern;

/**
 * Create setting screen.
 */
class Setting extends SingletonPattern {

	/**
	 * @var string Ajax action name.
	 */
	protected $ajax_action = 'wp_checkin_csv_upload';

	/**
	 * {@inheritDoc}
	 */
	protected function init() {
		add_action( 'admin_init', [ $this, 'register_option' ] );
		add_action( 'admin_menu', [ $this, 'add_menu' ] );
		add_action( 'wp_ajax_' . $this->ajax_action, [ $this, 'upload_csv' ] );
	}

	/**
	 * Register menu page.
	 *
	 * @return void
	 */
	public function add_menu() {
		add_options_page( __( 'WordCamp チェックイン', 'wp-checkin' ), __( 'チェックイン', 'wp-checkin' ), 'manage_options', 'wp-checkin', function() {
			?>
			<div class="wrap">
				<h1><?php esc_html_e( 'WordCamp チェックイン設定', 'wp-checkin' ); ?></h1>
				<form method="post" action="<?php echo admin_url( 'options.php' ); ?>">
					<?php
					settings_fields( 'wp-checkin' );
					do_settings_sections( 'wp-checkin' );
					submit_button();
					?>
				</form>
				<h2><?php esc_html_e( 'チケット情報', 'wp-checkin' ); ?></h2>
				<?php $this->csv_form(); ?>
			</div>
			<?php
		} );
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
			'wp-checkin'
		);
		// Register setting for options.
		foreach ( [
			[ 'wordcamp_site_url', __( 'WordCampサイトのURL', 'wp-checkin' ), 'url' ],
			[ 'wordcamp_auth_user', __( '共有基本認証ユーザー名', 'wp-checkin' ), 'text' ],
			[ 'wordcamp_auth_pass', __( '共有基本認証パスワード', 'wp-checkin' ), 'password' ],
		] as list( $option_name, $label, $type ) ) {
			add_settings_field( $option_name, $label, function() use ( $option_name, $type ) {
				$value = get_option( $option_name );
				printf( '<input type="%s" name="%s" value="%s" class="regular-text">', esc_attr( $type ), esc_attr( $option_name ), esc_attr( $value ) );
			}, 'wp-checkin', 'wp_checkin_settings' );
			register_setting( 'wp-checkin', $option_name );
		}
	}

	/**
	 * CSV form.
	 *
	 * @return void
	 */
	protected function csv_form() {
		$action  = add_query_arg( [
			'action' => $this->ajax_action,
		], admin_url( 'admin-ajax.php' ) );
		$csv     = (array) get_option( 'wordcamp_csv_file' );
		$updated = get_option( 'wordcamp_csv_updated' );
		$errors  = [
			__( '権限がありません。', 'wp-checkin' ),
			__( '不正なリクエストです。', 'wp-checkin' ),
			__( 'ファイルがアップロードされていません。', 'wp-checkin' ),
			__( 'CSVファイルをアップロードしてください。', 'wp-checkin' ),
		];
		$error   = filter_input( INPUT_GET, 'error' );
		if ( isset( $errors[ $error ] ) ) {
			printf(
				'<div class="error"><p>%s</p></div>',
				esc_html( $errors[ $error ] )
			);
		}
		if ( 'true' === filter_input( INPUT_GET, 'success' ) ) {
			printf(
				'<div class="updated"><p>%s</p></div>',
				esc_html__( 'CSVファイルをアップロードしました。', 'wp-checkin' )
			);
		}
		?>
			<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( $action ); ?>">
				<?php
				wp_nonce_field( $this->ajax_action, '_wpnonce' );
				printf( '<p class="description">%s: %d</p>', esc_html__( '登録されているCSVの行数', 'wp-checkin' ), count( $csv ) );
				if ( empty( $updated ) ) {
					printf( '<p class="wp-ui-text-notification">%s</p>', esc_html__( 'チケットデータがインポートされていません。', 'wp-checkin' ) );
				} else {
					printf( '<p class="wp-ui-text-primary">%s: %s</p>', esc_html__( 'CSV更新日: ', 'wp-checkin' ), mysql2date( get_option( 'date_format' ), $updated ) );
				}
				?>
				<input type="file" name="wordcamp_csv" id="wordcamp_csv" />
				<?php submit_button(); ?>
			</form>
		<?php
	}

	/**
	 * Upload CSV.
	 *
	 * @return void
	 */
	public function upload_csv() {
		try {
			if ( ! current_user_can( 'manage_options' ) ) {
				throw new \Exception( 0 );
			}
			if ( ! check_ajax_referer( $this->ajax_action, '_wpnonce', false ) ) {
				throw new \Exception( 1 );
			}
			if ( empty( $_FILES['wordcamp_csv'] ) ) {
				throw new \Exception( 2 );
			}
			$file = $_FILES['wordcamp_csv'];
			if ( 'csv' !== pathinfo( $file['name'], PATHINFO_EXTENSION ) ) {
				throw new \Exception( 3 );
			}
			// Update option.
			if ( ! file_exists( $file['tmp_name'] ) ) {
				throw new \Exception( 4 );
			}
			// Convert CSV to array.
			$tickets = [];
			$csv     = new \SplFileObject( $file['tmp_name'], 'r' );
			$csv->setFlags( \SplFileObject::READ_CSV );
			foreach ( $csv as $row ) {
				if ( ! empty( $row ) ) {
					$tickets[] = $row;
				}
			}
			update_option( 'wordcamp_csv_file', $tickets );
			update_option( 'wordcamp_csv_updated', current_time( 'mysql' ) );
			wp_safe_redirect( add_query_arg( [
				'page'    => 'wp-checkin',
				'success' => 'true',
			], admin_url( 'options-general.php' ) ) );
			exit;
		} catch ( \Exception $e ) {
			wp_safe_redirect( add_query_arg( [
				'page'  => 'wp-checkin',
				'error' => $e->getMessage(),
			], admin_url( 'options-general.php' ) ) );
			exit;
		}
	}
}
