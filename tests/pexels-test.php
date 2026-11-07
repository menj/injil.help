<?php
/**
 * Self-test for the Pexels client (inc/pexels.php): the key sources, the refusal to call without a
 * key, the request's address and header, the endpoint whitelist, the cache, a 401, and the
 * images.json entry. WordPress is stubbed; no request leaves the machine. Run: php tests/pexels-test.php
 *
 * @package injil-help
 */

if ( 'cli' !== PHP_SAPI ) {
	http_response_code( 404 );
	exit;
}

define( 'ABSPATH', __DIR__ . '/' );
define( 'MINUTE_IN_SECONDS', 60 );
define( 'HOUR_IN_SECONDS', 3600 );

$GLOBALS['t_opt']   = array( 'pexels_key' => '' );
$GLOBALS['t_cache'] = array();
$GLOBALS['t_calls'] = array();
$GLOBALS['t_reply'] = array( 'code' => 200, 'body' => '{"photos":[{"id":1}]}' );

class WP_Error {
	public $code;
	public $data;
	public function __construct( $code = '', $message = '', $data = null ) {
		$this->code = $code;
		$this->data = $data;
	}
}
function is_wp_error( $x ) {
	return $x instanceof WP_Error;
}
function __( $s ) {
	return $s;
}
function esc_html__( $s ) {
	return $s;
}
function apply_filters( $tag, $v ) {
	return $v;
}
function add_action() {}
function injil_help_get_option( $k ) {
	return $GLOBALS['t_opt'][ $k ] ?? null;
}
function get_transient( $k ) {
	return $GLOBALS['t_cache'][ $k ] ?? false;
}
function set_transient( $k, $v ) {
	$GLOBALS['t_cache'][ $k ] = $v;
	return true;
}
function delete_transient( $k ) {
	unset( $GLOBALS['t_cache'][ $k ] );
}
function wp_strip_all_tags( $s ) {
	return trim( strip_tags( (string) $s ) );
}
function wp_remote_get( $url, $args ) {
	$GLOBALS['t_calls'][] = array( $url, $args );
	return $GLOBALS['t_reply'];
}
function wp_remote_retrieve_response_code( $r ) {
	return $r['code'];
}
function wp_remote_retrieve_body( $r ) {
	return $r['body'];
}

require dirname( __DIR__ ) . '/inc/pexels.php';

$pass = 0;
$fail = 0;
function t_check( $ok, $what ) {
	global $pass, $fail;
	if ( $ok ) {
		++$pass;
	} else {
		++$fail;
		echo "FAIL: $what\n";
	}
}

// No key: an error, and no request.
$r = injil_help_pexels_search( 'manuscript' );
t_check( is_wp_error( $r ) && 'injil_help_pexels_nokey' === $r->code && ! $GLOBALS['t_calls'], 'no key: error, no request' );

// The option is the key, cleaned to letters and digits.
$GLOBALS['t_opt']['pexels_key'] = " abc-123 \n";
t_check( 'abc123' === injil_help_pexels_key(), 'key keeps letters and digits only' );

// A search: address, header, cache.
$r = injil_help_pexels_search( 'Greek papyrus', array( 'per_page' => 3, 'evil' => 'x' ) );
t_check( ! is_wp_error( $r ) && 1 === $r['photos'][0]['id'], 'a search returns the decoded reply' );
list( $url, $args ) = $GLOBALS['t_calls'][0];
t_check( 'https://api.pexels.com/v1/search?per_page=3&query=Greek%20papyrus' === $url, 'search address, sorted, unknown argument dropped: ' . $url );
t_check( 'abc123' === $args['headers']['Authorization'], 'the key goes unprefixed in Authorization' );
injil_help_pexels_search( 'Greek papyrus', array( 'per_page' => 3 ) );
t_check( 1 === count( $GLOBALS['t_calls'] ), 'the second identical search comes from the cache' );

// Endpoints.
injil_help_pexels_photo( 2014422 );
t_check( 'https://api.pexels.com/v1/photos/2014422' === end( $GLOBALS['t_calls'] )[0], 'photo endpoint' );
$n = count( $GLOBALS['t_calls'] );
$r = injil_help_pexels_request( 'users/me' );
t_check( is_wp_error( $r ) && 'injil_help_pexels_path' === $r->code && count( $GLOBALS['t_calls'] ) === $n, 'an endpoint off the list is refused' );
$r = injil_help_pexels_request( 'photos/../x' );
t_check( is_wp_error( $r ) && count( $GLOBALS['t_calls'] ) === $n, 'path traversal is refused' );

// A 401 is an error, flagged, and not cached.
$GLOBALS['t_reply'] = array( 'code' => 401, 'body' => '{"status":401}' );
$r = injil_help_pexels_curated();
t_check( is_wp_error( $r ) && 401 === $r->data['status'] && get_transient( 'ih_px_bad_key' ), 'a 401 is an error and sets the flag' );
$GLOBALS['t_reply'] = array( 'code' => 200, 'body' => '{"photos":[]}' );
$r = injil_help_pexels_curated();
t_check( ! is_wp_error( $r ) && false === get_transient( 'ih_px_bad_key' ), 'after a good reply the flag is gone' );
$GLOBALS['t_reply'] = array( 'code' => 200, 'body' => 'not json' );
t_check( is_wp_error( injil_help_pexels_collection( 'abc' ) ), 'a reply that is not JSON is an error' );

// The images.json entry.
$photo = array(
	'id'           => 123,
	'url'          => 'https://www.pexels.com/photo/old-book-123/',
	'photographer' => 'Jane Doe',
	'alt'          => 'An old book',
	'src'          => array( 'large2x' => 'https://images.pexels.com/photos/123/pexels-photo-123.jpeg?auto=compress&cs=tinysrgb&w=940' ),
);
$e = injil_help_pexels_entry( $photo, '', 'A caption.' );
t_check( 'pexels-123' === $e['file'] && 'pexels' === $e['source'] && 'https://images.pexels.com/photos/123/pexels-photo-123.jpeg' === $e['url'], 'entry key, source and bare address' );
t_check( 'Photo: Jane Doe, Pexels License.' === $e['credit'] && 'An old book' === $e['alt'] && 'A caption.' === $e['caption'], 'entry credit, alt fallback, caption' );
$photo['src']['large2x'] = 'https://evil.example/x.jpg';
t_check( is_wp_error( injil_help_pexels_entry( $photo ) ), 'an address off images.pexels.com is refused' );

echo $fail ? "\n$fail failed, $pass passed\n" : "All $pass checks passed\n";
exit( $fail ? 1 : 0 );
