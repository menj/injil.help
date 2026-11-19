<?php
/**
 * Self-test for justified text (inc/justify.php and assets/css/justify.css): when it applies, the body
 * classes, the cleaning of the selectors an editor types, and the stylesheet. WordPress is stubbed.
 * Run: php tests/justify-test.php
 *
 * @package injil-help
 */

if ( 'cli' !== PHP_SAPI ) {
	http_response_code( 404 );
	exit;
}

define( 'ABSPATH', __DIR__ . '/' );
$GLOBALS['t_opt']   = array( 'justify' => 1, 'justify_scope' => 'all', 'justify_hyphens' => 1, 'justify_desktop' => 0, 'justify_exclude' => '' );
$GLOBALS['t_view']  = array( 'singular' => true, 'post' => false );
$GLOBALS['t_hooks'] = array();

function injil_help_get_option( $k ) {
	return $GLOBALS['t_opt'][ $k ] ?? null;
}
function is_admin() {
	return false;
}
function is_feed() {
	return false;
}
function is_singular( $type = '' ) {
	return '' === $type ? $GLOBALS['t_view']['singular'] : ( 'post' === $type && $GLOBALS['t_view']['post'] );
}
function add_filter( $tag, $fn ) {
	$GLOBALS['t_hooks'][ $tag ][] = $fn;
}
function add_action( $tag, $fn ) {
	$GLOBALS['t_hooks'][ $tag ][] = $fn;
}

require dirname( __DIR__ ) . '/inc/justify.php';

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
function t_classes() {
	return $GLOBALS['t_hooks']['body_class'][0]( array( 'page' ) );
}

// Where it applies.
t_check( in_array( 'ih-justify', t_classes(), true ), 'on by default for a page' );
$GLOBALS['t_opt']['justify_scope'] = 'posts';
t_check( ! in_array( 'ih-justify', t_classes(), true ), 'scope "posts" leaves a page ragged' );
$GLOBALS['t_view']['post'] = true;
t_check( in_array( 'ih-justify', t_classes(), true ), 'scope "posts" justifies a post' );
$GLOBALS['t_opt']['justify_scope'] = 'all';
$GLOBALS['t_view']['singular']     = false;
t_check( ! in_array( 'ih-justify', t_classes(), true ), 'an archive is left alone' );
$GLOBALS['t_view']['singular'] = true;
$GLOBALS['t_opt']['justify']   = 0;
t_check( t_classes() === array( 'page' ), 'switched off adds no class' );
$GLOBALS['t_opt']['justify'] = 1;

// Switches.
t_check( in_array( 'ih-justify-hyphens', t_classes(), true ) && ! in_array( 'ih-justify-desktop', t_classes(), true ), 'hyphens on, desktop-only off' );
$GLOBALS['t_opt']['justify_hyphens'] = 0;
$GLOBALS['t_opt']['justify_desktop'] = 1;
t_check( ! in_array( 'ih-justify-hyphens', t_classes(), true ) && in_array( 'ih-justify-desktop', t_classes(), true ), 'hyphens off, desktop-only on' );

// Selectors.
t_check( '.no-justify, .quote-box' === injil_help_justify_clean_selectors( " .no-justify ,\n .quote-box " ), 'selectors are put on one line' );
$dirty = injil_help_justify_clean_selectors( '.a{color:red}/*x*/;' );
t_check( false === strpos( $dirty, '{' ) && false === strpos( $dirty, ';' ) && false === strpos( $dirty, '/' ), 'braces, semicolons and slashes are removed' );
t_check( '' === injil_help_justify_clean_selectors( '  ' ), 'empty stays empty' );
t_check( false === strpos( injil_help_justify_clean_selectors( '</style><script>' ), '<' ), 'angle brackets are removed' );

// The stylesheet.
$css = (string) file_get_contents( dirname( __DIR__ ) . '/assets/css/justify.css' );
t_check( substr_count( $css, '{' ) === substr_count( $css, '}' ), 'stylesheet braces balance' );
t_check( false === strpos( $css, '!important' ), 'stylesheet has no !important' );
t_check( false !== strpos( $css, 'text-align: justify' ) && false !== strpos( $css, 'hyphens: auto' ) && false !== strpos( $css, 'max-width: 767.98px' ), 'stylesheet justifies, hyphenates and has the narrow-screen rule' );
t_check( false !== strpos( $css, '.ih-footnotes li' ) && false !== strpos( $css, '.has-text-align-center' ), 'stylesheet spares the notes and centred text' );

echo $fail ? "\n$fail failed, $pass passed\n" : "All $pass checks passed\n";
exit( $fail ? 1 : 0 );
