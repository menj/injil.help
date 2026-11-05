<?php
/**
 * Self-test for the log-in screen (inc/login.php). Run: php tests/login-test.php
 *
 * WordPress is stubbed. The test checks that the design switch and a log-in plugin both turn the
 * screen back to core's, that the body classes, link and text follow the switch, that the note
 * shows only on the log-in screen and never in the pop-up, and that every title tag keeps the
 * "<page> | <site>" rule under 60 characters. Exit code 0 on success.
 *
 * @package injil-help
 */

// A command-line script: a web request to it gets a 404 and nothing runs.
if ( 'cli' !== PHP_SAPI ) {
	http_response_code( 404 );
	exit;
}

define( 'ABSPATH', __DIR__ . '/' );
define( 'INJIL_HELP_VERSION', 'test' );

$GLOBALS['ih_opts']    = array( 'login_design' => 1, 'login_message' => '' );
$GLOBALS['ih_plugin']  = false;
$GLOBALS['ih_enqueue'] = array();

function add_action() {}
function add_filter() {}
function apply_filters( $tag, $value ) { return 'injil_help_login_plugin_active' === $tag ? ( $value || $GLOBALS['ih_plugin'] ) : $value; }
function injil_help_get_option( $key ) { return $GLOBALS['ih_opts'][ $key ] ?? null; }
function home_url( $p = '' ) { return 'https://injil.help' . $p; }
function get_bloginfo() { return 'The Rightly-Guided Injīl'; }
function wp_strip_all_tags( $s ) { return strip_tags( $s ); }
function esc_html( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES ); }
function sanitize_key( $s ) { return strtolower( preg_replace( '/[^a-z0-9_\-]/i', '', $s ) ); }
function wp_unslash( $s ) { return $s; }
function get_stylesheet_directory_uri() { return 'https://injil.help/t'; }
function wp_enqueue_style( $h ) { $GLOBALS['ih_enqueue'][] = $h; }
function wp_add_inline_style() {}
function wp_get_global_stylesheet() { return ''; }
function injil_help_text( $group, $key ) {
	$t = json_decode( file_get_contents( dirname( __DIR__ ) . '/inc/data/text.json' ), true );
	return (string) ( $t[ $group ][ $key ] ?? '' );
}
require dirname( __DIR__ ) . '/inc/seo.php';
require dirname( __DIR__ ) . '/inc/login.php';

$fail = 0;
function check( $ok, $what ) {
	global $fail;
	echo ( $ok ? 'ok   ' : 'FAIL ' ) . $what . "\n";
	$fail += $ok ? 0 : 1;
}

// On by default.
check( injil_help_login_design_on(), 'design on by default' );
check( 'https://injil.help/' === injil_help_login_header_url( 'https://wordpress.org/' ), 'logo links home' );
check( 'The Rightly-Guided Injīl' === injil_help_login_header_text( 'Powered by WordPress' ), 'logo text is the site name' );
check( in_array( 'ih-login', injil_help_login_body_class( array( 'login' ) ), true ), 'body class ih-login' );
injil_help_login_assets();
check( in_array( 'injil-help-login', $GLOBALS['ih_enqueue'], true ), 'stylesheet enqueued' );
check( false !== strpos( injil_help_login_message( '' ), 'ih-login-gloss' ), 'gloss printed' );

// The note: log-in screen only, never in the pop-up.
$GLOBALS['ih_opts']['login_message'] = 'Editors only <b>';
check( false !== strpos( injil_help_login_message( '' ), 'Editors only &lt;b&gt;' ), 'note printed and escaped' );
$_REQUEST['action'] = 'lostpassword';
check( false === strpos( injil_help_login_message( '' ), 'ih-login-note' ), 'note absent on lost password' );
$_REQUEST = array( 'interim-login' => '1' );
check( false === strpos( injil_help_login_message( '' ), 'ih-login-note' ), 'note absent in the pop-up' );
check( in_array( 'ih-login--interim', injil_help_login_body_class( array() ), true ), 'interim body class' );
$_REQUEST = array();

// Titles: every screen, "<page> | <site>", under 60 characters.
foreach ( array( 'login', 'lostpassword', 'retrievepassword', 'rp', 'resetpass', 'register', 'bogus', 'logout' ) as $action ) {
	$_REQUEST['action'] = $action;
	$t = injil_help_login_title( 'x' );
	check( mb_strlen( $t ) < 60 && 1 === substr_count( $t, ' | ' ) && ' | The Rightly-Guided Injīl' === substr( $t, -strlen( ' | The Rightly-Guided Injīl' ) ), "title for $action: $t" );
}
$_REQUEST = array();

// Off: core's screen.
$GLOBALS['ih_opts']['login_design'] = 0;
check( ! injil_help_login_design_on(), 'switch off' );
check( 'https://wordpress.org/' === injil_help_login_header_url( 'https://wordpress.org/' ), 'off: core link kept' );
check( array( 'login' ) === injil_help_login_body_class( array( 'login' ) ), 'off: no body class' );
check( 'm' === injil_help_login_message( 'm' ), 'off: message untouched' );
$GLOBALS['ih_enqueue'] = array();
injil_help_login_assets();
check( array() === $GLOBALS['ih_enqueue'], 'off: nothing enqueued' );

// A log-in plugin takes over.
$GLOBALS['ih_opts']['login_design'] = 1;
$GLOBALS['ih_plugin']               = true;
check( ! injil_help_login_design_on(), 'plugin active: theme steps aside' );
check( 'Powered by WordPress' === injil_help_login_header_text( 'Powered by WordPress' ), 'plugin active: core text kept' );

// The stylesheet is scoped and loaded only through login_enqueue_scripts.
$src = file_get_contents( dirname( __DIR__ ) . '/inc/login.php' );
check( false !== strpos( $src, "add_action( 'login_enqueue_scripts', 'injil_help_login_assets' )" ), 'assets on login_enqueue_scripts' );
check( ! preg_match( '/\bhorn/i', $src . file_get_contents( dirname( __DIR__ ) . '/assets/css/login.css' ) ), 'banned word absent' );

echo $fail ? "\n$fail failed\n" : "\nAll passed\n";
exit( $fail ? 1 : 0 );
