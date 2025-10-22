/*!
 * Attendance button
 *
 * @handle wp-checkin-attendance
 * @deps wp-element, wp-api-fetch, wp-i18n
 */

const { render, createRoot, useState, useEffect } = wp.element;
const { __ } = wp.i18n;

const container = document.getElementById( 'wp-checkin-attendance' );

const auth_pass = wpCheckin.pass;
const auth_user = wpCheckin.user;

const AttendingButton = ( props ) => {
	const ticketId = props[ 'ticket-id' ];
	const [ loading, setLoading ] = useState( true );
	const [ attending, setAttending ] = useState( false );
	const [ error, setError ] = useState( [] );
	const addError = ( msg ) => {
		error.push( msg );
		setError( error );
	};
	useEffect( () => {
		// Get status
		wp.apiFetch( { path: `/wp-checkin/v1/checkin/${ ticketId }/?auth_user=${ encodeURIComponent( auth_user ) }&auth_pass=${ encodeURIComponent( auth_pass ) }` } )
			.then( ( response ) => {
				setLoading( false );
				setAttending( response.checked_in );
			} )
			.catch( ( err ) => {
				setLoading( false );
				addError( err.message );
			} );
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [] );
	return (
		<>
			{ attending && (
				<p className="wp-checkin-owner-status">
					<span className="dashicons dashicons-yes"></span> { __( 'チェックイン済み', 'wp-checkin' ) }
				</p>
			) }
			<button disabled={ loading } className={ attending ? 'wp-checkin-btn-checked-in' : 'wp-checkin-btn-cancel' } onClick={ () => {
				setLoading( true );
				if ( ! attending ) {
					wp.apiFetch( {
						path: `/wp-checkin/v1/checkin/${ ticketId }/`,
						method: 'POST',
						data: {
							auth_user,
							auth_pass,

						},
					} )
						.then( ( response ) => {
							setLoading( false );
							setAttending( response.checked_in );
						} )
						.catch( ( err ) => {
							setLoading( false );
							addError( err.message );
						} );
				} else {
					wp.apiFetch( {
						path: `/wp-checkin/v1/checkin/${ ticketId }/?auth_user=${ encodeURIComponent( auth_user ) }&auth_pass=${ encodeURIComponent( auth_pass ) }`,
						method: 'DELETE',
					} )
						.then( ( response ) => {
							setLoading( false );
							setAttending( response.checked_in );
						} )
						.catch( ( err ) => {
							setLoading( false );
							addError( err.message );
						} );
				}
			} }>
				{ ! attending ? __( 'チェックイン', 'wp-checkin' ) : __( '取り消し', 'wp-checkin' ) }
			</button>
		</>
	);
};

if ( container ) {
	const ticketId = container.dataset.ticketId;
	if ( createRoot ) {
		// React <= 18
		createRoot( container ).render( <AttendingButton ticket-id={ ticketId } /> );
	} else {
		// React > 18
		render( <AttendingButton ticket-id={ ticketId } />, container );
	}
}
