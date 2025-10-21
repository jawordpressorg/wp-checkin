<?php

namespace WCTokyo\WpCheckin;

/**
 * Ticket manager.
 */
class Tickets {


	/**
	 * Get ticket by ID.
	 *
	 * @param int $id
	 * @return array|null
	 */
	public static function get( $id ) {
		return self::filter( $id, 0 );
	}

	/**
	 * Get tickets by specified field.
	 *
	 * @param string $value Value to filter.
	 * @param int    $index Row index to filter.
	 *
	 * @return array|null
	 */
	public static function filter( $value, $index ) {
		foreach ( self::tickets( false ) as $ticket ) {
			// phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
			if ( isset( $ticket[ $index ] ) && $ticket[ $index ] == $value ) {
				return $ticket;
			}
		}
		return null;
	}

	/**
	 * Get all ticket data.
	 *
	 * @return array[]
	 */
	public static function tickets( $include_header = true ) {
		$tickets = (array) get_option( 'wordcamp_csv_file' );
		if ( ! $include_header && count( $tickets ) ) {
			array_shift( $tickets );
		}
		return $tickets;
	}

	/**
	 * Search ticket for the criteria
	 *
	 * @param string|array $query Search query or an array consists of column index and value.
	 * @param int $page
	 *
	 * @return array{tickets:array, page:int, current:int, total:int}
	 */
	public static function search( $query = '', $page = 1 ) {
		if ( is_array( $query ) ) {
			// This is index-column search.
			$tickets = array_values( array_filter( self::tickets( false ), function ( $ticket ) use ( $query ) {
				$not_found = false;
				foreach ( $query as $index => $value ) {
					// phpcs:ignore Universal.Operators.StrictComparisons.LooseNotEqual
					if ( ! isset( $ticket[ $index ] ) || $ticket[ $index ] != $value ) {
						$not_found = true;
						break;
					}
				}
				return ! $not_found;
			} ) );
		} elseif ( $query ) {
			$query   = array_values( array_filter( preg_split( '/[ 　+]/u', $query ) ) );
			$tickets = self::tickets( false );
			if ( ! empty( $query ) ) {
				// This is string search.
				$tickets = array_values( array_filter( $tickets, function ( $ticket ) use ( $query ) {
					// Flatten array.
					$str     = implode( '', $ticket );
					$matched = 0;
					foreach ( $query as $q ) {
						if ( str_contains( $str, $q ) ) {
							$matched++;
						}
					}
					return count( $query ) === $matched;
				} ) );
			}
		} else {
			$tickets = self::tickets( false );
		}
		$per_page = 100;
		$offset   = ( $page - 1 ) * $per_page;
		$index    = 0;
		$result   = [];
		foreach ( $tickets as $ticket ) {
			if ( $index >= $offset && $index < $offset + $per_page ) {
				$result[] = $ticket;
			}
			++$index;
		}
		$total = count( $tickets );
		return [
			'tickets' => $result,
			'page'    => ceil( $total / 100 ),
			'current' => $page,
			'total'   => $total,
		];
	}

	/**
	 * Get meta information of a ticket.
	 *
	 * @param array $ticket Ticket array.
	 * @return array
	 */
	public static function get_meta( $ticket ) {
		$tickets = self::tickets( true );
		if ( 1 > count( $tickets ) ) {
			return [];
		}
		$meta       = [];
		$prohibited = apply_filters( 'wp_checking_ignored_column_index', [
			0,
			1,
			2,
			3, // ID, 名前、メール
			8, // トランザクションID
		] );
		foreach ( $tickets[0] as $index => $label ) {
			// phpcs:ignore WordPress.PHP.StrictInArray.FoundNonStrictFalse
			if ( ! in_array( $index, $prohibited, false ) ) {
				$meta[ $label ] = $ticket[ $index ];
			}
		}
		return $meta;
	}

	/**
	 * Is this ticket checked in?
	 *
	 * @param int $ticket_id
	 *
	 * @return null|\WP_Post
	 */
	public static function is_checked_in( $ticket_id ) {
		$ticket = self::get( $ticket_id );
		if ( ! $ticket ) {
			return null;
		}
		return get_page_by_path( $ticket_id, OBJECT, 'checkin-log' );
	}
}
