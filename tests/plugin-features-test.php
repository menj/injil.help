<?php
/**
 * Self-test for the unlisted posts (inc/unlist.php) and the search addresses
 * (inc/search-permalinks.php). Run: php tests/plugin-features-test.php
 *
 * WordPress is stubbed. The test checks that the stored list is cleaned, that the WHERE clause
 * leaves the unlisted IDs out of a listing and keeps them in a singular query and an admin
 * screen, that an admin AJAX call from another host still hides them, that the previous and
 * next links skip them, that the "Read next" row loses its link to an unlisted page, and that
 * the search redirect builds /search/term/ and /search/term/page/2/ and refuses an empty term.
 * Exit code 0 on success.
 *
 * @package injil-help
 */

define( 'ABSPATH', __DIR__ . '/' );

$GLOBALS['ih_options'] = array();
$GLOBALS['ih_admin']   = false;
$GLOBALS['ih_ajax']    = false;
$GLOBALS['ih_referer'] = '';
$GLOBALS['ih_hooks']   = array();
$GLOBALS['ih_meta']    = array(); // post ID => '1'
class IH_Test_DB {
	public $posts    = 'wp_posts';
	public $postmeta = 'wp_postmeta';
	public function prepare( $sql, ...$args ) { return $sql; }
	public function get_col( $sql ) { return array_map( 'strval', array_keys( $GLOBALS['ih_meta'] ) ); }
}
$GLOBALS['wpdb'] = new IH_Test_DB();
function update_post_meta( $id, $key, $value ) { $GLOBALS['ih_meta'][ (int) $id ] = $value; return true; }
function delete_post_meta( $id, $key ) { unset( $GLOBALS['ih_meta'][ (int) $id ] ); return true; }
function sanitize_text_field( $s ) { return trim( strip_tags( (string) $s ) ); }

function add_action( $hook, $cb, $p = 10, $a = 1 ) { $GLOBALS['ih_hooks'][ $hook ][] = $cb; }
function add_filter( $hook, $cb, $p = 10, $a = 1 ) { $GLOBALS['ih_hooks'][ $hook ][] = $cb; }
function add_shortcode( $tag, $cb ) {}
function apply_filters( $hook, $value ) { return $value; }
function get_option( $key, $default = false ) { return array_key_exists( $key, $GLOBALS['ih_options'] ) ? $GLOBALS['ih_options'][ $key ] : $default; }
function update_option( $key, $value, $autoload = null ) { $GLOBALS['ih_options'][ $key ] = $value; return true; }
function is_admin() { return $GLOBALS['ih_admin']; }
function wp_doing_ajax() { return $GLOBALS['ih_ajax']; }
function current_user_can( $cap ) { return true; }
function wp_get_referer() { return $GLOBALS['ih_referer']; }
function admin_url( $path = '' ) { return 'https://injil.help/wp-admin/' . $path; }
function wp_parse_url( $url, $component = -1 ) { return parse_url( $url, $component ); }
function trailingslashit( $s ) { return rtrim( $s, '/' ) . '/'; }
function get_permalink( $id ) { return 'https://injil.help/page-' . (int) $id . '/'; }
function get_page_by_path( $path ) { return in_array( $path, array( 'gospels', 'paul' ), true ) ? (object) array( 'ID' => 1 ) : null; }
function sanitize_key( $k ) { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $k ) ); }

require dirname( __DIR__ ) . '/inc/unlist.php';
require dirname( __DIR__ ) . '/inc/search-permalinks.php';

$fail = 0;
function check( $ok, $label ) {
	global $fail;
	echo ( $ok ? 'ok   ' : 'FAIL ' ) . $label . "\n";
	$fail += $ok ? 0 : 1;
}

// The plugin's list is copied in once; a malformed list is emptied.
$GLOBALS['ih_options']['unlist_posts'] = array( '12', 7, 7, 0, -3, 'x' );
check( array( 7, 12 ) === injil_help_unlisted_ids(), 'the plugin list is copied and cleaned' );
check( array( 7, 12 ) === $GLOBALS['ih_options']['injil_help_unlisted'], 'the copy is stored under the theme option' );
check( array() === injil_help_unlist_normalise( '' ), 'an empty-string list counts as empty' );

injil_help_set_unlisted( 30, true );
injil_help_set_unlisted( 7, false );
injil_help_set_unlisted( 30, true );
check( array( 12, 30 ) === injil_help_unlisted_ids(), 'set_unlisted adds once and removes' );
check( array( 12, 30 ) === $GLOBALS['ih_options']['unlist_posts'], 'the plugin option mirrors the theme list' );
check( array( 12 => '1', 30 => '1' ) === $GLOBALS['ih_meta'], 'each post keeps its state in post meta' );

// Two saves that each read the list before the other wrote: the meta keeps both.
$GLOBALS['ih_options']['injil_help_unlisted'] = array( 12 ); // a stale list, as a racing request would leave it
injil_help_set_unlisted( 44, true );
check( array( 12, 30, 44 ) === injil_help_unlisted_ids(), 'a save rebuilds the list from the meta, so no edit is lost' );
injil_help_set_unlisted( 44, false );

// The plugin is switched on again, changes its list, and is switched off: the theme takes the change.
$GLOBALS['ih_options']['unlist_posts'] = array( 30, 55 );
check( array( 30, 55 ) === injil_help_unlisted_ids(), 'a later change by the plugin is taken over' );
check( array( 30 => '1', 55 => '1' ) === $GLOBALS['ih_meta'], 'the meta follows the plugin change' );
check( array( 30, 55 ) === injil_help_unlisted_live_ids(), 'the live list is the theme list while the plugin is off' );
injil_help_set_unlisted( 12, true );
injil_help_set_unlisted( 55, false );
check( injil_help_is_unlisted( 30 ) && ! injil_help_is_unlisted( 7 ), 'is_unlisted reads the list' );

$list   = (object) array( 'is_singular' => false );
$single = (object) array( 'is_singular' => true );
$where  = injil_help_unlist_where( ' AND 1=1', $list );
check( ' AND 1=1 AND wp_posts.ID NOT IN ( 12, 30 ) ' === $where, 'a listing (home, archive, feed, search, REST, sitemap) leaves them out' );
check( ' AND 1=1' === injil_help_unlist_where( ' AND 1=1', $single ), 'a singular query keeps them, so the address still opens' );

$GLOBALS['ih_admin'] = true;
check( ' AND 1=1' === injil_help_unlist_where( ' AND 1=1', $list ), 'an admin screen keeps them' );
$GLOBALS['ih_ajax']    = true;
$GLOBALS['ih_referer'] = 'https://elsewhere.example/wp-admin/edit.php';
check( false !== strpos( injil_help_unlist_where( '', $list ), 'NOT IN' ), 'admin AJAX from another host hides them' );
$GLOBALS['ih_referer'] = 'https://injil.help/wp-admin-foo/';
check( false !== strpos( injil_help_unlist_where( '', $list ), 'NOT IN' ), 'a look-alike admin path hides them' );
$GLOBALS['ih_referer'] = 'https://INJIL.help/wp-admin/edit.php';
check( '' === injil_help_unlist_where( '', $list ), 'admin AJAX from this admin keeps them' );
$GLOBALS['ih_admin'] = false;
$GLOBALS['ih_ajax']  = false;

check( " AND p.post_type = 'post' AND p.ID NOT IN ( 12, 30 ) " === injil_help_unlist_adjacent( " AND p.post_type = 'post'" ), 'previous and next links skip them' );

$row = '<div class="ih-next"><p><a href="/page-12/">Twelve</a> · <a href="/gospels/">Gospels</a> · <a href="/page-30/">Thirty</a></p></div>';
check( '<div class="ih-next"><p><a href="/gospels/">Gospels</a></p></div>' === injil_help_unlist_strip_links( $row, array( '/page-12/', '/page-30/' ) ), 'the Read next row loses its links to unlisted pages' );
check( false !== strpos( injil_help_unlist_related_row( $row, array( 'attrs' => array( 'className' => 'ih-aside ih-next' ) ) ), '/gospels/' ), 'the row filter keeps the listed link' );
check( $row === injil_help_unlist_related_row( $row, array( 'attrs' => array( 'className' => 'ih-plate' ) ) ), 'other groups are left alone' );

// Search addresses.
$home = 'https://injil.help/';
check( 'https://injil.help/search/servant%20of%20god/' === injil_help_search_pretty_url( $home, 'search', 'servant of god' ), '/?s=term goes to /search/term/' );
check( 'https://injil.help/search/injil/page/2/' === injil_help_search_pretty_url( $home, 'search', 'injil', 2 ), 'pagination keeps /page/2/' );
check( 'https://injil.help/find/a/b/' === injil_help_search_pretty_url( $home, 'find', 'a/b' ), 'a custom base, and a slash kept as core keeps it' );
check( '' === injil_help_search_pretty_url( $home, 'search', '   ' ), 'an empty search is not redirected' );
check( 'https://injil.help/search/x?post_type=page' === injil_help_search_pretty_url( $home, 'search', 'x', 1, array( 'post_type' => 'page' ), false ), 'other arguments are carried over' );
check( array( 'post_type' => array( 'post', 'page' ), 'lang' => 'en' ) === injil_help_search_clean_args( array( 'post_type' => array( 'post', '<b>page</b>' ), 'lang' => 'en' ) ), 'array arguments such as post_type[] are kept' );
check( 'https://injil.help/search/x/?post_type%5B0%5D=post&post_type%5B1%5D=page' === injil_help_search_pretty_url( $home, 'search', 'x', 1, array( 'post_type' => array( 'post', 'page' ) ) ), 'the redirect carries them' );
check( 'search' === injil_help_search_base(), 'the default base is search' );
$GLOBALS['ih_options']['injil_help_search_base'] = 'Find Me!';
check( 'findme' === injil_help_search_base(), 'the base is sanitised' );
$GLOBALS['ih_options']['injil_help_search_base'] = 'my_search';
check( 'mysearch' === injil_help_search_base(), 'underscores are removed, as the help text says' );
foreach ( array( 'feed', 'Page', 'wp-json', 'tag', 'author' ) as $reserved ) {
	$GLOBALS['ih_options']['injil_help_search_base'] = $reserved;
	check( 'search' === injil_help_search_base(), "the reserved base {$reserved} falls back to search" );
}
check( '' === injil_help_search_clean_base( 'gospels' ), 'a base that is the address of a page is refused' );
check( 'find' === injil_help_search_clean_base( 'find' ), 'a free base is kept' );
check( isset( $GLOBALS['ih_hooks']['template_redirect'] ), 'the redirect is hooked' );

echo $fail ? "\n{$fail} failed\n" : "\nall passed\n";
exit( $fail ? 1 : 0 );
