<?php
/**
 * Self-test for the account-name guards and the development-file clean-up (inc/security.php), the
 * fall-back of former addresses and the adoption of WordPress's draft privacy page
 * (inc/starter-content.php), and the theme's .htaccess. Run: php tests/hardening-test.php
 *
 * WordPress is stubbed with an in-memory page store keyed by path. Exit code 0 on success.
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
define( 'DAY_IN_SECONDS', 86400 );
define( 'WEEK_IN_SECONDS', 604800 );
define( 'INJIL_HELP_OPTION', 'injil_help_options' );
define( 'OBJECT', 'OBJECT' );

$ih_theme              = dirname( __DIR__ );
$GLOBALS['ih_pages']   = array(); // path => WP_Post
$GLOBALS['ih_meta']    = array(); // ID => [ key => value ]
$GLOBALS['ih_options'] = array();
$GLOBALS['ih_trans']   = array();
$GLOBALS['ih_logged']  = false;
$GLOBALS['ih_404']     = true;
$GLOBALS['ih_env']     = 'production';
$GLOBALS['ih_home']    = 'https://injil.help';
$GLOBALS['ih_dir']     = $ih_theme;

class WP_Post {
	public $ID;
	public $post_name    = '';
	public $post_type    = 'page';
	public $post_parent  = 0;
	public $post_title   = '';
	public $post_content = '';
	public $post_excerpt = '';
	public $post_status  = 'publish';
}
class WP_Error {}
class IH_Redirect extends Exception {}

function add_action() {}
function add_filter() {}
function remove_action() {}
function register_activation_hook() {}
function do_action() {}
function apply_filters( $tag, $value ) {
	return $value;
}
function __( $s ) {
	return $s;
}
function get_stylesheet_directory() {
	return $GLOBALS['ih_dir'];
}
function home_url( $p = '' ) {
	return $GLOBALS['ih_home'] . $p;
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
function is_user_logged_in() {
	return $GLOBALS['ih_logged'];
}
function wp_get_environment_type() {
	return $GLOBALS['ih_env'];
}
function is_wp_error( $v ) {
	return $v instanceof WP_Error;
}
function wp_slash( $v ) {
	return $v;
}
function get_option( $k, $d = false ) {
	return $GLOBALS['ih_options'][ $k ] ?? $d;
}
function update_option( $k, $v ) {
	$GLOBALS['ih_options'][ $k ] = $v;
	return true;
}
function get_transient( $k ) {
	return $GLOBALS['ih_trans'][ $k ] ?? false;
}
function set_transient( $k, $v ) {
	$GLOBALS['ih_trans'][ $k ] = $v;
	return true;
}
function wp_safe_redirect( $to, $status ) {
	throw new IH_Redirect( $status . ' ' . $to );
}
function get_page_by_path( $path ) {
	return $GLOBALS['ih_pages'][ $path ] ?? null;
}
function get_post_status( $id ) {
	foreach ( $GLOBALS['ih_pages'] as $p ) {
		if ( $p->ID === (int) $id ) {
			return $p->post_status;
		}
	}
	return false;
}
function get_post_meta( $id, $key ) {
	return $GLOBALS['ih_meta'][ $id ][ $key ] ?? '';
}
function update_post_meta( $id, $key, $value ) {
	$GLOBALS['ih_meta'][ $id ][ $key ] = $value;
}
function wp_update_post( $args ) {
	foreach ( $GLOBALS['ih_pages'] as $p ) {
		if ( $p->ID === (int) $args['ID'] ) {
			foreach ( $args as $k => $v ) {
				if ( 'ID' !== $k ) {
					$p->$k = $v;
				}
			}
			return $p->ID;
		}
	}
	return 0;
}
function wp_insert_post( $args ) {
	$p = new WP_Post();
	foreach ( $args as $k => $v ) {
		$p->$k = $v;
	}
	$p->ID = 100 + count( $GLOBALS['ih_pages'] );
	$GLOBALS['ih_pages'][ $p->post_name ] = $p;
	return $p->ID;
}
function wp_kses_post( $s ) {
	return $s;
}
function esc_html( $s ) {
	return $s;
}
function esc_attr( $s ) {
	return $s;
}
function esc_url( $s ) {
	return $s;
}
function wp_strip_all_tags( $s ) {
	return strip_tags( (string) $s );
}
function wp_json_encode( $v, $f = 0 ) {
	return json_encode( $v, $f );
}
function sanitize_title( $s ) {
	return strtolower( trim( preg_replace( '/[^a-z0-9]+/i', '-', $s ), '-' ) );
}
function get_posts() {
	return array();
}
function ih_page( $path, $status = 'publish', $id = 0 ) {
	$p              = new WP_Post();
	$p->ID          = $id ? $id : 1 + count( $GLOBALS['ih_pages'] );
	$p->post_name   = basename( $path );
	$p->post_status = $status;
	$GLOBALS['ih_pages'][ $path ] = $p;
	return $p;
}

require $ih_theme . '/inc/starter-content.php';
require $ih_theme . '/inc/security.php';

$fail = 0;
function ih_check( $ok, $what ) {
	global $fail;
	if ( ! $ok ) {
		++$fail;
		fwrite( STDERR, "FAIL: $what\n" );
	}
}
function ih_visit( $path ) {
	$_SERVER['REQUEST_URI'] = $path;
	try {
		injil_help_redirect_former();
	} catch ( IH_Redirect $r ) {
		return $r->getMessage();
	}
	return '';
}

// 1. The REST users routes are gone for visitors and stay for a logged-in user.
$routes = array(
	'/wp/v2/posts'                  => 1,
	'/wp/v2/users'                  => 1,
	'/wp/v2/users/(?P<id>[\d]+)'    => 1,
	'/wp/v2/users/me'               => 1,
	'/wp/v2/users/(?P<user_id>(?:[\d]+|me))/application-passwords' => 1,
);
$left = injil_help_sec_hide_user_routes( $routes );
ih_check( ! isset( $left['/wp/v2/users'] ) && ! isset( $left['/wp/v2/users/(?P<id>[\d]+)'] ), 'the users routes are hidden from a visitor' );
ih_check( isset( $left['/wp/v2/posts'], $left['/wp/v2/users/me'] ), 'other routes stay' );
$GLOBALS['ih_logged'] = true;
ih_check( $routes === injil_help_sec_hide_user_routes( $routes ), 'a logged-in user keeps every route' );
$GLOBALS['ih_logged'] = false;

// 2. "?author=N" goes to About before WordPress can name the slug; a REST listing is left alone.
$wp             = new stdClass();
$wp->query_vars = array();
$_GET           = array( 'author' => '1' );
$got            = '';
try {
	injil_help_sec_author_query( $wp );
} catch ( IH_Redirect $r ) {
	$got = $r->getMessage();
}
ih_check( '301 https://injil.help/about/' === $got, "?author=1 goes to About ($got)" );
$wp->query_vars = array( 'rest_route' => '/wp/v2/posts' );
try {
	injil_help_sec_author_query( $wp );
	$got = '';
} catch ( IH_Redirect $r ) {
	$got = $r->getMessage();
}
ih_check( '' === $got, 'a REST request with ?author= is left alone' );
$_GET = array();
$oembed = injil_help_sec_oembed_author( array( 'author_name' => 'someone', 'author_url' => 'https://injil.help/author/someone/', 'provider_name' => 'Site' ) );
ih_check( array( 'provider_name' => 'Site' ) === $oembed, 'oEmbed loses the author name and link' );

// 3. A former address whose target is not published yet goes for now to the nearest published page.
$GLOBALS['ih_trans']['injil_help_former_map'] = array(
	'ver' => INJIL_HELP_VERSION,
	'map' => array(
		'quran/commentaries/al-muminun-50'        => 'quran/commentaries/refuge-on-high-ground',
		'quran/commentaries/al-muminun-50/tabari' => 'quran/commentaries/refuge-on-high-ground/tabari',
	),
);
ih_page( 'quran' );
ih_page( 'quran/commentaries' );
ih_page( 'quran/commentaries/refuge-on-high-ground' );
ih_page( 'quran/commentaries/refuge-on-high-ground/tabari', 'draft' );
ih_check( '302 https://injil.help/quran/commentaries/refuge-on-high-ground/' === ih_visit( '/quran/commentaries/al-muminun-50/tabari/' ), 'a draft target sends the visitor to its group page for now' );
ih_check( '302 https://injil.help/quran/commentaries/refuge-on-high-ground/' === ih_visit( '/quran/commentaries/al-muminun-50/tabari/4-2/' ), 'a page copy under a draft target goes to the group page' );
$GLOBALS['ih_pages']['quran/commentaries/refuge-on-high-ground/tabari']->post_status = 'publish';
ih_check( '301 https://injil.help/quran/commentaries/refuge-on-high-ground/tabari/' === ih_visit( '/quran/commentaries/al-muminun-50/tabari/' ), 'a published target gets the permanent redirect' );
unset( $GLOBALS['ih_pages']['quran/commentaries/refuge-on-high-ground/tabari'], $GLOBALS['ih_pages']['quran/commentaries/refuge-on-high-ground'] );
ih_check( '302 https://injil.help/quran/commentaries/' === ih_visit( '/quran/commentaries/al-muminun-50/tabari/' ), 'with the group missing too, the commentaries index is next' );
ih_check( '' === ih_visit( '/quran/nothing-here/' ), 'an address under no former path is left to WordPress' );
$GLOBALS['ih_404'] = false;
ih_check( '' === ih_visit( '/quran/commentaries/al-muminun-50/' ), 'a request that is no 404 is not sent on' );
$GLOBALS['ih_404'] = true;

// 4. The privacy page WordPress drafted on install is filled in and published.
$draft = ih_page( 'privacy-policy', 'draft', 3 );
$GLOBALS['ih_options']['wp_page_for_privacy_policy'] = 3;
$def    = array( 'path' => 'privacy-policy', 'title' => 'Privacy policy', 'excerpt' => 'How this site treats your data.', 'body' => array( 'This site sets no cookie.' ) );
$result = injil_help_populate_page( 1, $def );
ih_check( 'created' === $result, "the installer's draft is adopted ($result)" );
ih_check( 'publish' === $draft->post_status && 'Privacy policy' === $draft->post_title && '' !== $draft->post_content, 'the adopted page is published with the theme text' );
ih_check( 1 === count( array_filter( $GLOBALS['ih_pages'], fn( $p ) => 'privacy-policy' === $p->post_name ) ), 'no second privacy page is made' );
$draft->post_status = 'draft'; // the owner unpublishes it after the theme wrote it
ih_check( 'unchanged' === injil_help_populate_page( 1, $def ), 'a privacy page the theme wrote and the owner drafted stays a draft' );
ih_check( 'draft' === $draft->post_status, 'the owner keeps the draft' );

// 5. The development files leave a production theme folder, and only that.
$tmp = sys_get_temp_dir() . '/ih-hard-' . getmypid() . '-' . bin2hex( random_bytes( 4 ) );
mkdir( $tmp . '/theme/.git/objects', 0700, true );
mkdir( $tmp . '/theme/tests', 0700, true );
mkdir( $tmp . '/theme/bin', 0700, true );
mkdir( $tmp . '/outside', 0700, true );
file_put_contents( $tmp . '/theme/.git/objects/ab', 'x' );
file_put_contents( $tmp . '/theme/tests/a-test.php', '<?php' );
file_put_contents( $tmp . '/theme/README.md', '#' );
file_put_contents( $tmp . '/theme/readme.txt', 'kept' );
file_put_contents( $tmp . '/theme/bin/build.php', '<?php' );
file_put_contents( $tmp . '/outside/keep.txt', 'kept' );
symlink( $tmp . '/outside', $tmp . '/theme/.claude' );
$GLOBALS['ih_dir'] = $tmp . '/theme';
foreach ( array( 'local', 'development', 'staging' ) as $env ) {
	$GLOBALS['ih_env'] = $env;
	ih_check( array() === injil_help_dev_cleanup() && is_dir( $tmp . '/theme/.git' ), "a $env site keeps the development files" );
}
$GLOBALS['ih_env']  = 'production';
$GLOBALS['ih_home'] = 'http://injil.test';
ih_check( array() === injil_help_dev_cleanup() && is_dir( $tmp . '/theme/tests' ), 'a production site on a local host name keeps them' );
$GLOBALS['ih_home'] = 'https://injil.help';
ini_set( 'error_log', $tmp . '/error.log' );
$removed = injil_help_dev_cleanup();
sort( $removed );
ih_check( array( '.claude', '.git', 'README.md', 'tests' ) === $removed, 'a production site loses them: ' . json_encode( $removed ) );
ih_check( ! file_exists( $tmp . '/theme/.git' ) && ! file_exists( $tmp . '/theme/tests' ) && ! file_exists( $tmp . '/theme/README.md' ) && ! is_link( $tmp . '/theme/.claude' ), 'nothing of them is left' );
ih_check( is_file( $tmp . '/theme/readme.txt' ) && is_file( $tmp . '/theme/bin/build.php' ), 'readme.txt and bin/ stay' );
ih_check( is_file( $tmp . '/outside/keep.txt' ), 'a link is removed, never followed' );
ih_check( array() === injil_help_dev_cleanup(), 'a second run has nothing to do' );
$GLOBALS['ih_dir'] = $ih_theme;
unlink( $tmp . '/theme/readme.txt' );
unlink( $tmp . '/theme/bin/build.php' );
rmdir( $tmp . '/theme/bin' );
rmdir( $tmp . '/theme' );
unlink( $tmp . '/outside/keep.txt' );
rmdir( $tmp . '/outside' );
@unlink( $tmp . '/error.log' );
rmdir( $tmp );

// 6. The theme's .htaccess ships in the package and refuses what the clean-up removes, and bin/.
$ht = (string) file_get_contents( $ih_theme . '/.htaccess' );
ih_check( false !== strpos( $ht, '\.md' ) && false !== strpos( $ht, '\.git' ), '.htaccess refuses the Markdown notes and the Git files' );
ih_check( (bool) preg_match( '#RedirectMatch 403 \(\?i\)/\(\\\\\.git\[\^/\]\*\|\\\\\.claude\|tests\|bin\)#', $ht ), '.htaccess refuses .git, .claude, tests and bin' );
$attrs = (string) file_get_contents( $ih_theme . '/.gitattributes' );
ih_check( ! preg_match( '#^\S*htaccess\S*\s+export-ignore#m', $attrs ), '.htaccess is not export-ignored' );
foreach ( array_merge( glob( $ih_theme . '/tests/*.php' ), glob( $ih_theme . '/bin/*.php' ) ) as $file ) {
	ih_check( false !== strpos( (string) file_get_contents( $file ), "if ( 'cli' !== PHP_SAPI ) {" ), basename( $file ) . ' stops a web request' );
}

if ( $fail ) {
	fwrite( STDERR, "$fail check(s) failed\n" );
	exit( 1 );
}
echo "OK\n";
