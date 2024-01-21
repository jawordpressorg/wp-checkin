/**
 * Search form result.
 */
import React from 'react';
import { SearchForm } from './components/search-form';
// eslint-disable-next-line react/no-deprecated
import { render } from 'react-dom';
import { Ticket } from './components/ticket-page';

const form = document.getElementById( 'search-form' );
if ( form ) {
	// eslint-disable-next-line react/no-deprecated
	render( <SearchForm />, form );
}

const ticketWrapper = document.getElementById( 'ticket' );
if ( ticketWrapper ) {
	// eslint-disable-next-line react/no-deprecated
	render( <Ticket id={ ticketWrapper.dataset.ticketId } />, ticketWrapper );
}
