<?php
/**
 * Archive template.
 *
 */


$page = max( 1, get_query_var( 'paged' ) );

$result = \WCTokyo\WpCheckin\Tickets::search( get_query_var( 's' ), $page );

?>

<form method="get" action="<?php echo home_url( '/checkin/' ); ?>" class="wp-checkin-form">
	<input class="wp-checkin-form-input" name="s" type="search" value="<?php the_search_query(); ?>" placeholder="<?php esc_attr_e( 'キーワードで絞り込み', 'wp-checkin' ); ?>" />

	<?php if ( ! empty( $result['tickets'] ) ) : ?>
		<p class="wp-checkin-form-summary">
			<?php
			// translators: %1$d is current page, %2$d is total page, %3$d is total items.
			echo esc_html( sprintf( __( '%1$d / %2$d（全%3$d件）', 'wp-checkin' ), $result['current'], $result['page'], $result['total'] ) );
			?>
		</p>
	<?php endif; ?>
</form>


<?php if ( empty( $result['tickets'] ) ) : ?>

	<?php wp_checkin_template( 'template-parts/no-found' ); ?>

<?php else : ?>

	<table class="wp-checkin-table">
		<thead>
		<tr>
			<th>&nbsp;</th>
			<th><?php esc_html_e( '氏名', 'wp-checkin' ); ?></th>
			<th class="wp-checkin-hidden"><?php esc_html_e( '種別', 'wp-checkin' ); ?></th>
			<th><?php esc_html_e( '購入日', 'wp-checkin' ); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ( $result['tickets'] as $ticket ) : ?>
			<tr class="wp-checkin-row">
				<td>
					<?php echo get_avatar( $ticket[4], 32 ); ?>
				</td>
				<td>
					<a href="<?php echo home_url( '/checkin/ticket/' . $ticket[0] ); ?>">
						<?php echo esc_html( wp_checkin_ticket_owner( $ticket ) ); ?>
						<small>
							<?php echo esc_html( $ticket[4] ); ?>
						</small>
					</a>
				</td>
				<td class="wp-checkin-hidden">
					<?php echo esc_html( $ticket[1] ); ?>
				</td>
				<td><?php echo esc_html( mysql2date( get_option( 'date_format' ), $ticket[5] ) ); ?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php if ( 1 < $result['page'] ) : ?>
		<nav class="wp-checkin-pagination">
			<ul>
				<?php for ( $i = 1; $i <= $result['page']; $i++ ) : ?>
					<li>
						<?php
						if ( $i === $result['current'] ) {
							printf( '<span class="wp-checkin-link-current">%d</span>', $i );
						} else {
							$url = home_url( '/checkin/page/' . $i . '/' );
							if ( get_query_var( 's' ) ) {
								$url = add_query_arg( [
									's' => get_query_var( 's' ),
								], $url );
							}
							printf( '<a href="%2$s" class="wp-checkin-link">%1$d</a>', $i, esc_url( $url ) );
						}
						?>
					</li>
				<?php endfor; ?>
			</ul>
		</nav>
	<?php endif; ?>
	<?php
endif;
