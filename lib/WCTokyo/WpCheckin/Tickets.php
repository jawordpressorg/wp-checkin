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
		foreach ( self::tickets() as $ticket ) {
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
	public static function tickets() {
		return (array) get_option( 'wordcamp_csv_file' );
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
			$tickets = array_filter( self::tickets(), function( $ticket ) use ( $query ) {
				// Flatten array.
				$str = implode( '', $ticket );
				return str_contains( $str, $query );
			} );
		} else {
			$tickets = self::tickets();
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
}
