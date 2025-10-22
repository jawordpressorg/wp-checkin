<?php
/**
 * Single template.
 *
 * @var array $args
 */

$ticket = \WCTokyo\WpCheckin\Tickets::get( $args['id'] );
?>
<p class="wp-checkin-return">
	<a href="<?php echo esc_url( home_url( '/checkin' ) ); ?>">
		<span class="dashicons dashicons-tickets"></span>
		<?php esc_html_e( 'チケット検索', 'wp-checkin' ); ?>
	</a>
	<a href="#" onclick="window.history.back();">
		<span class="dashicons dashicons-redo"></span>
		<?php esc_html_e( '一つ戻る', 'wp-checkin' ); ?>
	</a>
</p>

<?php
if ( ! $ticket ) {
	wp_checkin_template( 'template-parts/no-found' );
	return;
}
?>

<hr />

<div class="wp-checkin-owner">
	<p><?php echo get_avatar( $ticket[4] ); ?></p>
	<h2><?php echo esc_html( wp_checkin_ticket_owner( $ticket ) ); ?></h2>
	<small><?php echo esc_html( $ticket[1] ); ?></small>

	<div id="wp-checkin-attendance" data-ticket-id="<?php echo esc_attr( $ticket[0] ); ?>"></div>
</div>

<table class="wp-checkin-ticket-detail">
	<caption>
		<?php esc_html_e( 'チケット詳細', 'wp-checkin' ); ?>
	</caption>
	<tbody>
		<?php foreach ( wp_checkin_ticket_detail( $ticket ) as $label => $value ) : ?>
		<tr>
			<th><?php echo esc_html( $label ); ?></th>
			<td><?php echo esc_html( $value ?: '---' ); ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
