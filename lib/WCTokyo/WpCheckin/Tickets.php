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
			if ( isset( $ticket[ $index ] ) && $ticket[ $index] == $value ) {
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
	 * @param $query
	 * @param $page
	 *
	 * @return array{tickets:array, page:int, current:int, total:int}
	 */
	public static function search( $query = '', $page = 1 ) {
		if ( $query ) {
			$tickets = array_filter( self::tickets( false ), function( $ticket ) use ( $query ) {
				// Flatten array.
				$str = implode( '', $ticket );
				return str_contains( $str, $query );
			} );
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
			$index++;
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
		$meta = [];
		$prohibited = [
			0, 1, 2, 3, // ID, 名前、メール
			8, // トランザクションID

		];
		foreach ( $tickets[0] as $index => $label ) {
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
