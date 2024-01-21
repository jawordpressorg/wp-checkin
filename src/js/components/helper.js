/**
 * Helper libraries.
 */

export function fetchApi( path, init = {} ) {
	const url =
		document.location.protocol + '//' + document.location.host + path;
	const options = {
		credentials: 'include',
	};
	for ( const prop in init ) {
		if ( ! init.hasOwnProperty( prop ) ) {
			continue;
		}
		options[ prop ] = init[ prop ];
	}

	return fetch( url, options );
}
