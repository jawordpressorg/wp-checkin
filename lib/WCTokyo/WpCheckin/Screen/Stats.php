<?php

namespace WCTokyo\WpCheckin\Screen;


use WCTokyo\WpCheckin\Pattern\SingletonPattern;
use WP_REST_Server;

class Stats extends SingletonPattern {

	protected function init() {
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		add_action( 'rest_api_init', [ $this, 'rest_api_init' ] );
	}

	/**
	 * メニューを登録
	 *
	 * @return void
	 */
	public function admin_menu() {
		$title = __( '統計情報', 'wc-tokyo' );
		add_submenu_page( 'edit.php?post_type=checkin-log', $title, $title, 'manage_options', 'wc-tokyo-checkin-log', [ $this, 'admin_menu_page' ] );
	}

	/**
	 * REST APIのエンドポイントを登録
	 *
	 * @return void
	 */
	public function rest_api_init() {
		register_rest_route( 'wp-checkin/v1', '/stats', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'api_render_stats' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'args'                => [],
			],
		] );
	}

	/**
	 * 管理画面のページを描画する
	 *
	 * @return void
	 */
	public function admin_menu_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'チェックイン統計', 'hametuha' ); ?></h1>

			<?php
			$stats = $this->get_stats();
			if ( empty( $stats ) ) {
				printf( '<p class="description">%s</p>', esc_html__( '該当するチケットがありません', 'wp-checkin' ) );
			} else {
				?>
				<table class="widefat">
					<thead>
					<tr>
						<th><?php esc_html_e( '名称', 'wp-checkin' ); ?></th>
						<th><?php esc_html_e( '販売数', 'wp-checkin' ); ?></th>
						<th><?php esc_html_e( '出席数', 'wp-checkin' ); ?></th>
						<th><?php esc_html_e( '出席率', 'wp-checkin' ); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ( $stats as $stat ) : ?>
						<tr>
							<th>
								<?php echo esc_html( $stat['label'] ); ?>
							</th>
							<td>
								<?php echo number_format( $stat['total'] ); ?>
							</td>
							<td>
								<?php echo number_format( $stat['attended'] ); ?>
							</td>
							<td>
								<?php
								echo $stat['total'] ? round( ( $stat['attended'] / $stat['total'] * 100 ), 2 ) . '%' : '0%';
								?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<p>
					<?php
					$stats_url = add_query_arg( [
						'_wpnonce' => wp_create_nonce( 'wp_rest' ),
					], rest_url( 'wp-checkin/v1/stats' ) );
					?>
					<a href="<?php echo esc_url( $stats_url ); ?>" class="button">
						<?php esc_html_e( 'CSVをダウンロード', 'wp-checkin' ); ?>
					</a>
				</p>
				<?php
			}
			?>
		</div>
		<?php
	}

	/**
	 * CSVを出力する
	 *
	 * @return void|\WP_Error
	 */
	public function api_render_stats() {
		$stats = $this->get_stats();
		if ( empty( $stats ) ) {
			return new \WP_Error( 'invalid_checkin', __( '統計情報が存在しません。', 'wp-checkin' ), [
				'status' => 404,
			] );
		}

		// CSVヘッダーを設定
		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="checkin-stats-' . gmdate( 'Y-m-d-His' ) . '.csv"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		// BOM付きUTF-8で出力（Excel対応）
		echo "\xEF\xBB\xBF";

		// 出力バッファを開く
		$output = fopen( 'php://output', 'w' );

		// ヘッダー行を出力
		fputcsv( $output, [
			__( '名称', 'wp-checkin' ),
			__( '販売数', 'wp-checkin' ),
			__( '出席数', 'wp-checkin' ),
			__( '出席率', 'wp-checkin' ),
		], ',', '"', '\\' );

		// データ行を出力
		foreach ( $stats as $stat ) {
			$attendance_rate = $stat['total'] ? round( ( $stat['attended'] / $stat['total'] * 100 ), 2 ) . '%' : '0%';
			fputcsv( $output, [
				$stat['label'],
				$stat['total'],
				$stat['attended'],
				$attendance_rate,
			], ',', '"', '\\' );
		}

		fclose( $output );
		exit;
	}

	/**
	 * チケットと出席情報を返す
	 *
	 * @return array{ticket_id: int, label:string, total:int, attended:int}[]
	 */
	public function get_stats() {
		$categories = \WCTokyo\WpCheckin\Tickets::get_categories();
		$tickets    = \WCTokyo\WpCheckin\Tickets::tickets( false );
		$stats      = [];

		foreach ( $categories as $index => $category ) {
			// このカテゴリのチケット総数をカウント
			$category_tickets = array_filter( $tickets, function ( $ticket ) use ( $category ) {
				return isset( $ticket[1] ) && $ticket[1] === $category;
			} );

			// このカテゴリでチェックイン済みのチケット数をカウント
			$attended = 0;
			foreach ( $category_tickets as $ticket ) {
				if ( \WCTokyo\WpCheckin\Tickets::is_checked_in( $ticket[0] ) ) {
					++$attended;
				}
			}

			$stats[] = [
				'ticket_id' => $index,
				'label'     => $category,
				'total'     => count( $category_tickets ),
				'attended'  => $attended,
			];
		}

		return $stats;
	}
}
