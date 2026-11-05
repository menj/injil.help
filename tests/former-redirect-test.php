<?php
/**
 * Self-test for the former addresses (inc/starter-content.php). Run: php tests/former-redirect-test.php
 *
 * The functions are read out of the theme files and run against stubs: an in-memory page store, the
 * real groups and works of inc/data/commentaries.json, and a former map built without the commentary
 * pages, as on 1 November 2026, when the PHP had landed and inc/data/tafsir/ had not. Every address
 * shape the logs showed must resolve: a 301 to the published target, or a 302 to the nearest
 * published page above it while the target is missing or a draft. Exit code 0 on success.
 *
 * @package injil-help
 */

// A command-line script: a web request to it gets a 404 and nothing runs.
if ( 'cli' !== PHP_SAPI ) {
	http_response_code( 404 );
	exit;
}

define( 'ABSPATH', __DIR__ . '/' );
define( 'INJIL_HELP_VERSION', '9.9.9' );
define( 'OBJECT', 'OBJECT' );
define( 'WEEK_IN_SECONDS', 604800 );
$GLOBALS['ih_root']  = dirname( __DIR__ );
$GLOBALS['ih_pages'] = array(); // path => status
$GLOBALS['ih_404']   = true;
$GLOBALS['ih_trans'] = array();

class WP_Post {
	public $post_status = 'publish';
}
class IH_Redirect extends Exception {
	public $to;
	public $code;
	public function __construct( $to, $code ) {
		$this->to   = $to;
		$this->code = $code;
		parent::__construct( "$code $to" );
	}
}
function get_stylesheet_directory() {
	return $GLOBALS['ih_root'];
}
function home_url( $p = '' ) {
	return 'https://injil.help' . $p;
}
function wp_parse_url( $u, $c = -1 ) {
	return parse_url( $u, $c );
}
function is_admin() {
	return false;
}
function is_preview() {
	return false;
}
function is_404() {
	return $GLOBALS['ih_404'];
}
function get_transient( $k ) {
	return $GLOBALS['ih_trans'][ $k ] ?? false;
}
function set_transient( $k, $v ) {
	$GLOBALS['ih_trans'][ $k ] = $v;
	return true;
}
function wp_safe_redirect( $to, $code ) {
	throw new IH_Redirect( $to, $code );
}
function get_page_by_path( $path ) {
	if ( ! isset( $GLOBALS['ih_pages'][ $path ] ) ) {
		return null;
	}
	$p              = new WP_Post();
	$p->post_status = $GLOBALS['ih_pages'][ $path ];
	return $p;
}
function injil_help_data_file( $name ) {
	$file = $GLOBALS['ih_root'] . '/inc/data/' . $name . '.json';
	return is_readable( $file ) ? (array) json_decode( (string) file_get_contents( $file ), true ) : array();
}
// The page list as a half-finished upload builds it: the other pages and their former paths, and no
// commentary page, since the commentary texts were not there yet.
function injil_help_starter_pages() {
	return array(
		array( 'path' => 'about', 'former' => array( 'about-the-author' ) ),
		array( 'path' => 'quran/commentaries' ),
	);
}

// Read the functions under test out of the theme, so the test runs the shipped code.
$src = (string) file_get_contents( $GLOBALS['ih_root'] . '/inc/starter-content.php' ) . (string) file_get_contents( $GLOBALS['ih_root'] . '/inc/tafsir.php' );
foreach ( array( 'injil_help_former_map', 'injil_help_data_fingerprint', 'injil_help_former_commentary_target', 'injil_help_redirect_former', 'injil_help_published_ancestor', 'injil_help_find_page', 'injil_help_tafsir_works', 'injil_help_tafsir_groups', 'injil_help_number_word', 'injil_help_tafsir_part_path' ) as $fn ) {
	if ( ! preg_match( '/^function ' . $fn . '\(.*?^}$/ms', $src, $m ) ) {
		echo "FAIL: $fn not found\n";
		exit( 1 );
	}
	eval( $m[0] ); // phpcs:ignore Squiz.PHP.Eval.Discouraged -- the theme's own source, read for the test.
}

$fail  = 0;
$count = 0;
function ih_check( $ok, $label ) {
	global $fail, $count;
	++$count;
	if ( ! $ok ) {
		++$fail;
		echo "FAIL: $label\n";
	}
}
function ih_visit( $url ) {
	$_SERVER['REQUEST_URI'] = $url;
	try {
		injil_help_redirect_former();
	} catch ( IH_Redirect $r ) {
		return $r->code . ' ' . $r->to;
	}
	return 'none';
}

$c = 'quran/commentaries';
foreach ( array( '', '/son-of-mary-as-example', '/son-of-mary-as-example/jalalayn', '/son-of-mary-as-example/tanwir', '/family-of-imran', '/family-of-imran/tabari', '/family-of-imran/tabari-part-two', '/birth-of-jesus', '/birth-of-jesus/tabari', '/birth-of-jesus/tabari-part-two', '/table-and-questioning', '/table-and-questioning/qurtubi' ) as $p ) {
	$GLOBALS['ih_pages'][ $c . $p ] = 'publish';
}
$GLOBALS['ih_pages']['about'] = 'publish';

$cases = array(
	'/quran/commentaries/az-zukhruf-57-65/jalalayn/'        => '301 https://injil.help/quran/commentaries/son-of-mary-as-example/jalalayn/',
	'/quran/commentaries/az-zukhruf-57-65/'                 => '301 https://injil.help/quran/commentaries/son-of-mary-as-example/',
	'/quran/commentaries/az-zukhruf-57-65/tanwir/'          => '301 https://injil.help/quran/commentaries/son-of-mary-as-example/tanwir/',
	'/quran/commentaries/al-imran-33-64/tabari/2/'          => '301 https://injil.help/quran/commentaries/family-of-imran/tabari-part-two/',
	'/quran/commentaries/maryam-16-37/tabari/2-2/'          => '301 https://injil.help/quran/commentaries/birth-of-jesus/tabari-part-two/',
	'/quran/commentaries/maryam-16-37/tabari/2-2/embed/'    => '301 https://injil.help/quran/commentaries/birth-of-jesus/tabari-part-two/',
	'/quran/commentaries/family-of-imran/tabari/2/'         => '301 https://injil.help/quran/commentaries/family-of-imran/tabari-part-two/',
	'/quran/commentaries/family-of-imran/tabari-2/'         => '301 https://injil.help/quran/commentaries/family-of-imran/tabari/',
	'/quran/commentaries/al-maidah-110-120/qurtubi/'        => '301 https://injil.help/quran/commentaries/table-and-questioning/qurtubi/',
	'/quran/commentaries/AZ-ZUKHRUF-57-65/Jalalayn/'        => '301 https://injil.help/quran/commentaries/son-of-mary-as-example/jalalayn/',
	'/about-the-author/'                                     => '301 https://injil.help/about/',
	'/quran/commentaries/az-zukhruf-57-65-2/'               => '301 https://injil.help/quran/commentaries/son-of-mary-as-example/',
	'/quran/commentaries/as-saff-6-2/tabari/'               => '302 https://injil.help/quran/commentaries/',
	'/quran/commentaries/son-of-mary-as-example-2/'         => '301 https://injil.help/quran/commentaries/son-of-mary-as-example/',
	'/quran/commentaries/family-of-imran/no-such-work/'     => 'none',
	// The target not written yet, or a draft: for now, the nearest published page above it.
	'/quran/commentaries/al-imran-33-64/saadi/'             => '302 https://injil.help/quran/commentaries/family-of-imran/',
	'/quran/commentaries/al-maidah-110-120/baghawi/'        => '302 https://injil.help/quran/commentaries/table-and-questioning/',
	'/quran/commentaries/an-nisa-155-159/tabari/'           => '302 https://injil.help/quran/commentaries/',
	'/quran/commentaries/al-imran-33-64/no-such-work/'      => '301 https://injil.help/quran/commentaries/family-of-imran/',
	// Present addresses and addresses under no former path are left to WordPress.
	'/quran/commentaries/family-of-imran/tabari/'           => 'none',
	'/quran/commentaries/no-such-group/tabari/'             => 'none',
	'/journal/some-post/'                                   => 'none',
);
$GLOBALS['ih_pages'][ $c . '/table-and-questioning/baghawi' ] = 'draft';
foreach ( $cases as $url => $want ) {
	$got = ih_visit( $url );
	ih_check( $want === $got, "$url: want $want, got $got" );
}

// A page that is not a 404 (a present page WordPress serves) is never sent on to an ancestor.
$GLOBALS['ih_404'] = false;
ih_check( 'none' === ih_visit( '/quran/commentaries/al-imran-33-64/saadi/' ), 'no 302 off a 404' );
$GLOBALS['ih_404'] = true;

// The cached map follows the data: a new fingerprint is a new map within the same version.
$cached = $GLOBALS['ih_trans']['injil_help_former_map']['ver'] ?? '';
ih_check( 0 === strpos( $cached, INJIL_HELP_VERSION . ':' ) && strlen( $cached ) > strlen( INJIL_HELP_VERSION ) + 1, 'the map cache key carries the data fingerprint' );
$GLOBALS['ih_trans']['injil_help_former_map'] = array( 'ver' => INJIL_HELP_VERSION, 'map' => array() );
ih_check( isset( injil_help_former_map()['about-the-author'] ), 'a map cached under the bare version is rebuilt' );

echo $fail ? "$fail of $count checks failed\n" : "All $count checks passed\n";
exit( $fail ? 1 : 0 );
