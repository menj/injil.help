<?php
/**
 * Self-test for the contents list (inc/toc.php): the shortcode marker, ids that are kept and never
 * repeated, nesting that stays balanced when levels jump or fall, the automatic list on long pages,
 * and a marker that never reaches an excerpt. Run: php tests/toc-test.php
 *
 * WordPress is stubbed with the few functions the file calls. Exit code 0 on success.
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

$GLOBALS['ih_filters']   = array();
$GLOBALS['ih_shortcode'] = array();
$GLOBALS['ih_opt']       = array( 'toc_auto' => 0 );
$GLOBALS['ih_view']      = array( 'main' => true, 'page' => true );

function __( $s ) {
	return $s;
}
function esc_html( $s ) {
	return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' );
}
function esc_attr( $s ) {
	return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' );
}
function esc_attr__( $s ) {
	return esc_attr( $s );
}
function wp_strip_all_tags( $s ) {
	return trim( strip_tags( (string) $s ) );
}
function sanitize_title( $s ) {
	return trim( (string) preg_replace( '/[^a-z0-9]+/', '-', strtolower( remove_accents( wp_strip_all_tags( $s ) ) ) ), '-' );
}
function remove_accents( $s ) {
	return strtr( (string) $s, array( 'ā' => 'a', 'ī' => 'i', 'ū' => 'u', 'ʿ' => '', 'ʾ' => '' ) );
}
function shortcode_atts( $pairs, $atts ) {
	return array_merge( $pairs, array_intersect_key( (array) $atts, $pairs ) );
}
function add_shortcode( $tag, $fn ) {
	$GLOBALS['ih_shortcode'][ $tag ] = $fn;
}
function add_filter( $tag, $fn, $priority = 10 ) {
	$GLOBALS['ih_filters'][ $tag ][ $priority ][] = $fn;
}
function add_action( $tag, $fn, $priority = 10 ) {
	add_filter( $tag, $fn, $priority );
}
function apply_filters( $tag, $value ) {
	return $value;
}
function injil_help_get_option( $key ) {
	return $GLOBALS['ih_opt'][ $key ] ?? null;
}
function is_singular() {
	return $GLOBALS['ih_view']['main'];
}
function is_admin() {
	return false;
}
function is_feed() {
	return false;
}
function is_main_query() {
	return $GLOBALS['ih_view']['main'];
}
function in_the_loop() {
	return $GLOBALS['ih_view']['main'];
}
function doing_filter() {
	return false;
}
function is_page() {
	return $GLOBALS['ih_view']['page'];
}
function is_front_page() {
	return false;
}

require dirname( __DIR__ ) . '/inc/toc.php';

$pass = 0;
$fail = 0;
function ih_check( $ok, $what ) {
	global $pass, $fail;
	if ( $ok ) {
		++$pass;
	} else {
		++$fail;
		echo "FAIL: $what\n";
	}
}
/** Run the_content priority 13 the way WordPress does, on text the shortcode has already expanded. */
function ih_filter( $content ) {
	foreach ( $GLOBALS['ih_filters']['the_content'][13] as $fn ) {
		$content = $fn( $content );
	}
	return $content;
}
function ih_shortcode( $atts = array() ) {
	return $GLOBALS['ih_shortcode']['auto_toc']( $atts );
}
function ih_balanced( $html ) {
	foreach ( array( 'ol', 'li', 'nav' ) as $tag ) {
		if ( substr_count( $html, "<$tag" ) !== substr_count( $html, "</$tag>" ) ) {
			return false;
		}
	}
	$dom = new DOMDocument();
	libxml_use_internal_errors( true );
	$dom->loadHTML( '<?xml encoding="utf-8" ?><body>' . $html . '</body>' );
	$bad = array_filter( libxml_get_errors(), static fn( $e ) => false !== strpos( $e->message, 'Opening and ending tag mismatch' ) );
	libxml_clear_errors();
	return ! $bad;
}

// The shortcode is registered, and the level list is read as written.
ih_check( isset( $GLOBALS['ih_shortcode']['auto_toc'] ), 'the shortcode is registered' );
ih_check( injil_help_toc_levels( 'h2, H3' ) === array( 2, 3 ), 'levels "h2, H3"' );
ih_check( injil_help_toc_levels( 'h4,h1,h4' ) === array( 1, 4 ), 'levels sort and do not repeat' );
ih_check( injil_help_toc_levels( 'p,div,h9' ) === array( 2, 3 ), 'a list with no heading level falls back to h2,h3' );
ih_check( ih_shortcode( array( 'tags' => 'h2' ) ) === '<!--ih-toc:2:Contents-->', 'the marker for tags="h2"' );
ih_check( ih_shortcode( array( 'title' => 'In this page' ) ) === '<!--ih-toc:2,3:In%20this%20page-->', 'the marker carries the title, encoded' );

// A list in the middle of a page, with the author's own ids, a repeated heading and a footnote number.
$page = '<p>Intro.</p><p><!--ih-toc:2,3:Contents--></p>'
	. '<h2 id="start">Start</h2><p>a</p>'
	. '<h3>Detail</h3><p>b</p><h3>Detail</h3><p>c</p>'
	. '<h2>Second part<sup class="ih-fnref"><a href="#fn-1">1</a></sup></h2><p>d</p>'
	. '<h2 id="notes">Notes</h2><ol><li id="fn-1">x</li></ol>';
$out  = ih_filter( $page );
ih_check( 1 === substr_count( $out, '<nav class="ih-toc"' ), 'one list is placed' );
ih_check( false === strpos( $out, '<!--ih-toc' ), 'no marker is left' );
ih_check( false === strpos( $out, '<p><nav' ) && false === strpos( $out, '</nav></p>' ), 'the paragraph around a lone marker is removed' );
ih_check( false !== strpos( $out, '<h2 id="start">' ), 'an id the author gave is kept' );
ih_check( false !== strpos( $out, '<h3 id="detail">' ) && false !== strpos( $out, '<h3 id="detail-2">' ), 'a repeated heading gets a second id' );
ih_check( false !== strpos( $out, '<h2 id="second-part">' ), 'a heading without an id gets one from its text' );
ih_check( false !== strpos( $out, '<h2 id="notes">' ), 'the notes id is untouched' );
ih_check( false !== strpos( $out, '<a href="#second-part">Second part</a>' ), 'a footnote number is left out of the entry' );
ih_check( false !== strpos( $out, '<a href="#notes">Notes</a>' ), 'the notes heading is listed' );
ih_check( ih_balanced( $out ), 'the list is balanced' );

// Ids already on other elements are never reused.
$out = ih_filter( '<!--ih-toc:2:Contents--><div id="intro"></div><h2>Intro</h2>' );
ih_check( false !== strpos( $out, '<h2 id="intro-2">' ), 'an id on another element is reserved' );

// Nesting: a jump, a fall below the start and a fall of two levels all stay balanced.
$out = ih_filter( '<!--ih-toc:1,2,3,4:Contents--><h2>A</h2><h4>B</h4><h3>C</h3><h2>D</h2><h1>E</h1><h4>F</h4><h2>G</h2>' );
ih_check( ih_balanced( $out ), 'jumps and falls stay balanced' );
ih_check( false !== strpos( $out, '<li><a href="#a">A</a><ol><li><a href="#b">B</a></li><li>' ) || false !== strpos( $out, '<li><a href="#a">A</a><ol><li><a href="#b">B</a>' ), 'a deeper heading nests under the one before it' );
ih_check( substr_count( $out, '<li>' ) === 7, 'every heading of a wanted level is listed' );

// A level between two lists (h2, h4, h3) joins the deeper one; it does not open a second list beside it.
$out = ih_filter( '<!--ih-toc:2,3,4:Contents--><h2>A</h2><h4>B</h4><h3>C</h3><h2>D</h2>' );
ih_check( false !== strpos( $out, '<li><a href="#a">A</a><ol><li><a href="#b">B</a></li><li><a href="#c">C</a></li></ol></li><li><a href="#d">D</a></li>' ), 'h2, h4, h3: one nested list' );

// The levels of a marker decide what it lists; other headings get no id.
$out = ih_filter( '<!--ih-toc:2:Contents--><h2>One</h2><h3>Two</h3>' );
ih_check( false !== strpos( $out, 'href="#one"' ) && false === strpos( $out, 'href="#two"' ), 'a marker lists only its levels' );
ih_check( false !== strpos( $out, '<h3>Two</h3>' ), 'a heading of an unwanted level gets no id' );

// An empty title shows no title; an empty list shows nothing.
$out = ih_filter( '<!--ih-toc:2:-->' . '<h2>Only</h2>' );
ih_check( false === strpos( $out, 'class="ih-k"' ) && false !== strpos( $out, 'aria-label="Contents"' ), 'no title: a label and no title line' );
ih_check( '' === ih_filter( '<!--ih-toc:2:Contents-->' ), 'no headings: nothing is shown' );
ih_check( false === strpos( ih_filter( '<!--ih-toc:2:Contents--><h2></h2><h2><img src="x"></h2>' ), '<nav' ), 'a heading with no text is skipped' );

// Two markers each get their own list.
$out = ih_filter( '<!--ih-toc:2:One--><h2>A</h2><h3>B</h3><!--ih-toc:3:Two-->' );
ih_check( 2 === substr_count( $out, '<nav class="ih-toc"' ) && false !== strpos( $out, 'id="ih-toc-title-2"' ), 'two markers, two lists, two title ids' );

// Where the text is not the page (an excerpt, a feed, a widget), no marker survives and no id is added.
$GLOBALS['ih_view']['main'] = false;
$out                        = ih_filter( '<p><!--ih-toc:2:Contents--></p><h2>A</h2>' );
ih_check( '<h2>A</h2>' === $out, 'outside the main text the marker goes and headings are untouched' );
$GLOBALS['ih_view']['main'] = true;

// The automatic list.
$long = '<nav class="ih-breadcrumb"><a href="/">Home</a></nav><p>Intro.</p><h2>A</h2><h2>B</h2><h2>C</h2><h2>D</h2>';
$out  = ih_filter( $long );
ih_check( false === strpos( $out, '<nav class="ih-toc"' ), 'the automatic list is off by default' );
$GLOBALS['ih_opt']['toc_auto'] = 1;
$out                           = ih_filter( $long );
ih_check( false !== strpos( $out, '</nav><nav class="ih-toc"' ), 'the automatic list follows the breadcrumb row' );
ih_check( false === strpos( ih_filter( '<p>Intro.</p><h2>A</h2><h2>B</h2><h2>C</h2>' ), '<nav class="ih-toc"' ), 'three sections are too few for the automatic list' );
ih_check( 1 === substr_count( ih_filter( '<p>x</p><!--ih-toc:2:Mine--><h2>A</h2><h2>B</h2><h2>C</h2><h2>D</h2>' ), '<nav class="ih-toc"' ) && false !== strpos( ih_filter( '<!--ih-toc:2:Mine--><h2>A</h2><h2>B</h2><h2>C</h2><h2>D</h2>' ), 'Mine' ), 'a list placed by hand is not doubled' );
$GLOBALS['ih_view']['page'] = false;
ih_check( false === strpos( ih_filter( $long ), '<nav class="ih-toc"' ), 'the automatic list is for pages only' );

// Text with no marker and no automatic list passes through byte for byte.
$GLOBALS['ih_opt']['toc_auto'] = 0;
$plain                         = "<h2>A</h2>\n<p>Arabic <span lang=\"ar\" dir=\"rtl\">إنجيل</span> &amp; <br/> text.</p>";
ih_check( $plain === ih_filter( $plain ), 'text without a marker is returned unchanged' );

echo $fail ? "\n$fail failed, $pass passed\n" : "All $pass checks passed\n";
exit( $fail ? 1 : 0 );
