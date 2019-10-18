<?php

use Slim\Http\Request;
use Slim\Http\Response;
use WCTokyo\WpCheckin\TicketApi;

// Home.
$app->get('/', function (Request $request, Response $response, array $args) {
    // Render index view
    return $this->renderer->render($response, 'index.phtml', [
        'versions' => '1.0.0',
    ]);
});

// Search Endpoint
$app->get( '/search', [ TicketApi::get_instance(), 'handle_search' ] );

// Do ticket request.
$app->get('/ticket/{ticket_id}', function( Request $request, Response $response, array $args ) {
	// Render ticket view.
	return $this->renderer->render( $response, 'ticket.phtml', [
		'ticket_id' => $args['ticket_id'],
	] );
} );
// Ticket detail endpoint.
$app->get( '/ticket/{ticket_id}/detail', [ TicketApi::get_instance(), 'handle_get' ] );
$app->post( '/ticket/{ticket_id}/detail', [ TicketApi::get_instance(), 'handle_post' ] );
$app->delete( '/ticket/{ticket_id}/detail', [ TicketApi::get_instance(), 'handle_delete' ] );

// QR code
$app->get( '/qr/{ticket_id}', function(Request $request, Response $response, array $args) {
	// Render QR view.
	$url = 'https://chart.apis.google.com/chart?';
	$alt = sprintf( 'https://2019.tokyo.wp-checkin.com/ticket/%d', $args['ticket_id'] );
	$queries = [];
	foreach ( [
		'cht' => 'qr',
		'chs' => '300x300',
		'chl' => $alt,
	] as $key => $val ) {
		$queries[] = sprintf( '%s=%s', $key, rawurlencode( $val ) );
	}
	$url .= implode( '&amp;', $queries );
	return $this->renderer->render($response, 'qr.phtml', [
		'url' => $url,
		'alt' => $alt,
	]);
} );

// Handle github hook.
$app->post('/payload', function ( Request $request, Response $response, array $args ) {
    try {
        // This request is valid.
		$dir = dirname( __DIR__ );
        exec( sprintf('cd %s; ./bin/deploy.sh;', $dir ), $output );
        return $response->withJson([
            'messages'    => $output,
			'working_dir' => $dir,
        ], 200);
    } catch ( \Exception $e ) {
        return $response->withJson( [
            'message' => $e->getMessage(),
        ], $e->getCode() );
    }
});

// Monitor site
$app->get('/monitor', function (Request $request, Response $response, array $args) {
    $now = new DateTime('now', new DateTimeZone('Asia/Tokyo'));
    return $response->withJson([
        'status' => 'success',
        'timestamp' => $now->format(DateTime::ATOM),
    ], 200);
});
