<?php
/**
 * The Pexels API client: search, curated, one photo, and a collection, in PHP.
 *
 * The endpoints and the request follow Pexels' official JavaScript client (MIT licence, copyright
 * Pexels, https://github.com/pexels/pexels-javascript): GET https://api.pexels.com/v1/ with the key,
 * unprefixed, in the Authorization header. The code is the theme's own and uses wp_remote_get().
 *
 * The key is never in the theme. It comes from the constant INJIL_HELP_PEXELS_KEY in wp-config.php,
 * the filter injil_help_pexels_key, or Theme Options > Content > Pexels API key (stored in the
 * database). With no key, every function returns a WP_Error and nothing is requested.
 *
 * injil_help_pexels_entry() turns a photo into an images.json entry (key pexels-{id}, source
 * "pexels", url, page, credit "Photo: name, Pexels License."), which inc/images.php then copies into
 * the Media Library. The reply is cached for twelve hours, a 401 for ten minutes.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

/** The API key: the constant, then the filter, then the option. Letters and digits only. */
function injil_help_pexels_key() {
	$key = defined( 'INJIL_HELP_PEXELS_KEY' ) ? (string) INJIL_HELP_PEXELS_KEY : (string) injil_help_get_option( 'pexels_key' );
	$key = (string) apply_filters( 'injil_help_pexels_key', $key );
	return (string) preg_replace( '/[^A-Za-z0-9]/', '', $key );
}

/**
 * One request to the API.
 *
 * @param string $path   photos/search, curated, photos/{id}, collections/{id}.
 * @param array  $params Query arguments: query, per_page, page, orientation, size, color, locale.
 * @return array|WP_Error The decoded reply.
 */
function injil_help_pexels_request( $path, $params = array() ) {
	$key = injil_help_pexels_key();
	if ( '' === $key ) {
		return new WP_Error( 'injil_help_pexels_nokey', __( 'No Pexels API key is set.', 'injil-help' ) );
	}
	$path = trim( (string) $path, '/' );
	if ( ! preg_match( '#^(search|curated|photos/\d+|collections(/[A-Za-z0-9]+)?)$#', $path ) ) {
		return new WP_Error( 'injil_help_pexels_path', __( 'Not a Pexels endpoint.', 'injil-help' ) );
	}
	$allowed = array( 'query', 'per_page', 'page', 'orientation', 'size', 'color', 'locale' );
	$params  = array_intersect_key( (array) $params, array_flip( $allowed ) );
	ksort( $params );
	$url   = 'https://api.pexels.com/v1/' . $path . ( $params ? '?' . http_build_query( $params, '', '&', PHP_QUERY_RFC3986 ) : '' );
	$cache = 'ih_px_' . md5( $url . '|' . substr( md5( $key ), 0, 8 ) );
	$hit   = get_transient( $cache );
	if ( is_array( $hit ) ) {
		return $hit;
	}
	$res = wp_remote_get(
		$url,
		array(
			'timeout' => 15,
			'headers' => array(
				'Accept'        => 'application/json',
				'Authorization' => $key,
				'User-Agent'    => 'Pexels/PHP injil-help',
			),
		)
	);
	if ( is_wp_error( $res ) ) {
		return $res;
	}
	$code = (int) wp_remote_retrieve_response_code( $res );
	if ( 200 !== $code ) {
		if ( 401 === $code ) {
			set_transient( 'ih_px_bad_key', 1, 10 * MINUTE_IN_SECONDS );
		}
		return new WP_Error( 'injil_help_pexels_http', sprintf( /* translators: %d: HTTP status. */ __( 'Pexels answered %d.', 'injil-help' ), $code ), array( 'status' => $code ) );
	}
	delete_transient( 'ih_px_bad_key' );
	$data = json_decode( (string) wp_remote_retrieve_body( $res ), true );
	if ( ! is_array( $data ) ) {
		return new WP_Error( 'injil_help_pexels_json', __( 'Pexels sent a reply that is not JSON.', 'injil-help' ) );
	}
	set_transient( $cache, $data, 12 * HOUR_IN_SECONDS );
	return $data;
}

/** Photos matching a search. */
function injil_help_pexels_search( $query, $params = array() ) {
	return injil_help_pexels_request( 'search', array_merge( array( 'per_page' => 15 ), (array) $params, array( 'query' => (string) $query ) ) );
}

/** The curated photos. */
function injil_help_pexels_curated( $params = array() ) {
	return injil_help_pexels_request( 'curated', array_merge( array( 'per_page' => 15 ), (array) $params ) );
}

/** One photo by its id. */
function injil_help_pexels_photo( $id ) {
	return injil_help_pexels_request( 'photos/' . (int) $id );
}

/** The media of a collection. */
function injil_help_pexels_collection( $id, $params = array() ) {
	return injil_help_pexels_request( 'collections/' . rawurlencode( (string) $id ), (array) $params );
}

/**
 * An images.json entry for a photo from the API. The caption and the alt text are the editor's; the
 * API's own alt text is a fallback.
 *
 * @param array  $photo   One element of "photos".
 * @param string $alt     Alt text; empty takes the API's.
 * @param string $caption Caption.
 * @return array|WP_Error
 */
function injil_help_pexels_entry( $photo, $alt = '', $caption = '' ) {
	$id   = (int) ( $photo['id'] ?? 0 );
	$url  = (string) ( $photo['src']['large2x'] ?? $photo['src']['large'] ?? '' );
	$page = (string) ( $photo['url'] ?? '' );
	$by   = trim( wp_strip_all_tags( (string) ( $photo['photographer'] ?? '' ) ) );
	if ( $id < 1 || 0 !== strpos( $url, 'https://images.pexels.com/' ) || 0 !== strpos( $page, 'https://www.pexels.com/' ) || '' === $by ) {
		return new WP_Error( 'injil_help_pexels_photo', __( 'The photo is missing its id, address or photographer.', 'injil-help' ) );
	}
	return array(
		'file'    => 'pexels-' . $id,
		'source'  => 'pexels',
		'url'     => strtok( $url, '?' ),
		'page'    => $page,
		'alt'     => '' !== $alt ? $alt : trim( wp_strip_all_tags( (string) ( $photo['alt'] ?? '' ) ) ),
		'caption' => $caption,
		/* translators: %s: photographer. */
		'credit'  => sprintf( 'Photo: %s, Pexels License.', $by ),
	);
}

/** A notice on the Theme Options page when Pexels refused the key in the last ten minutes. */
add_action(
	'admin_notices',
	static function () {
		if ( current_user_can( 'manage_options' ) && get_transient( 'ih_px_bad_key' ) ) {
			echo '<div class="notice notice-warning"><p>' . esc_html__( 'Pexels refused the API key. Check it under Theme Options > Content > Pexels API key.', 'injil-help' ) . '</p></div>';
		}
	}
);

// The last line: functions.php checks for it, so a file an upload cut short is caught.
$GLOBALS['injil_help_loaded'][ basename( __FILE__, '.php' ) ] = true;
