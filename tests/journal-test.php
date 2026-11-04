<?php
/**
 * Self-test for the Journal (inc/journal.php, inc/data/posts.json). Run: php tests/journal-test.php
 *
 * WordPress is stubbed with an in-memory post store. The test checks the entries (a slug of words
 * only, a title tag under 60 characters, 700 to 1,100 words of prose, a date, notes and sources,
 * internal links to pages that exist, no banned word and no owner's name), then seeds them at a fixed
 * "now": a past date must be published and a future one scheduled, each with its exact post_date.
 * A second run must change nothing; a run after the date of a scheduled post must leave its date
 * alone; an edited post and a binned post must be kept. Each post's block markup must balance. The WordPress sample post is still binned
 * on activation. Exit code 0 on success.
 *
 * @package injil-help
 */

define( 'ABSPATH', __DIR__ . '/' );
$GLOBALS['ih_theme']   = dirname( __DIR__ );
$GLOBALS['ih_store']   = array(); // ID => WP_Post
$GLOBALS['ih_meta']    = array(); // ID => [ key => value ]
$GLOBALS['ih_terms']   = array(); // name => id
$GLOBALS['ih_writes']  = 0;
$GLOBALS['ih_options'] = array();

class WP_Post {
	public $ID;
	public $post_name = '';
	public $post_type = 'post';
	public $post_parent = 0;
	public $post_title = '';
	public $post_content = '';
	public $post_excerpt = '';
	public $post_status = 'publish';
	public $post_date = '';
	public $post_date_gmt = '';
	public $post_modified_gmt = '';
	public $post_category = array();
}
class WP_Error {}

// ---------- WordPress stubs ----------
function add_action() {}
function add_filter() {}
function remove_action() {}
function register_activation_hook() {}
function apply_filters( $tag, $value ) {
	return $value;
}
function do_action() {}
function get_stylesheet_directory() {
	return $GLOBALS['ih_theme'];
}
function get_stylesheet_directory_uri() {
	return 'https://injil.help/wp-content/themes/injil-help';
}
function home_url( $path = '' ) {
	return 'https://injil.help' . ( '' === $path ? '' : '/' . ltrim( $path, '/' ) );
}
function user_trailingslashit( $s ) {
	return rtrim( $s, '/' ) . '/';
}
function __( $s ) {
	return $s;
}
function _n( $a, $b, $n ) {
	return 1 === $n ? $a : $b;
}
function esc_html( $s ) {
	return htmlspecialchars( (string) $s, ENT_QUOTES );
}
function esc_attr( $s ) {
	return htmlspecialchars( (string) $s, ENT_QUOTES );
}
function esc_url( $s ) {
	return (string) $s;
}
function wp_kses_post( $s ) {
	return $s;
}
function wp_strip_all_tags( $s ) {
	return trim( strip_tags( (string) $s ) );
}
function number_format_i18n( $n ) {
	return number_format( (float) $n );
}
function sanitize_title( $s ) {
	return strtolower( trim( preg_replace( '/[^A-Za-z0-9]+/', '-', (string) $s ), '-' ) );
}
function remove_accents( $s ) {
	return $s;
}
function wp_parse_args( $a, $d ) {
	return array_merge( $d, (array) $a );
}
function wp_json_encode( $v, $f = 0 ) {
	return json_encode( $v, $f );
}
function get_theme_mod( $k, $d = false ) {
	return $d;
}
function wp_slash( $v ) {
	return $v;
}
function is_wp_error( $v ) {
	return $v instanceof WP_Error;
}
function get_option( $k, $d = false ) {
	return $GLOBALS['ih_options'][ $k ] ?? $d;
}
function update_option( $k, $v ) {
	$GLOBALS['ih_options'][ $k ] = $v;
	return true;
}
function wp_doing_ajax() {
	return false;
}
function current_user_can() {
	return true;
}
/** The site runs on UTC+8 (Kuala Lumpur), so the conversion is tested. */
function get_gmt_from_date( $date ) {
	return gmdate( 'Y-m-d H:i:s', strtotime( $date . ' UTC' ) - 8 * HOUR_IN_SECONDS );
}
define( 'HOUR_IN_SECONDS', 3600 );
define( 'OBJECT', 'OBJECT' );
function get_post( $id ) {
	return $GLOBALS['ih_store'][ (int) $id ] ?? null;
}
function get_post_meta( $id, $key, $single = false ) {
	return $GLOBALS['ih_meta'][ $id ][ $key ] ?? '';
}
function update_post_meta( $id, $key, $value ) {
	$GLOBALS['ih_meta'][ $id ][ $key ] = $value;
	return true;
}
function get_posts( $args ) {
	$out = array();
	foreach ( $GLOBALS['ih_store'] as $p ) {
		if ( $p->post_type !== $args['post_type'] || ! in_array( $p->post_status, (array) $args['post_status'], true ) ) {
			continue;
		}
		if ( isset( $args['meta_key'] ) && get_post_meta( $p->ID, $args['meta_key'] ) !== $args['meta_value'] ) {
			continue;
		}
		$out[] = 'ids' === ( $args['fields'] ?? '' ) ? $p->ID : $p;
	}
	$n = $args['numberposts'] ?? ( $args['posts_per_page'] ?? 5 );
	return -1 === $n ? $out : array_slice( $out, 0, $n );
}
function get_post_field( $field, $id ) {
	$p = get_post( $id );
	return $p ? ( $p->$field ?? '' ) : '';
}
function get_page_by_path( $path, $output = null, $type = 'page' ) {
	foreach ( $GLOBALS['ih_store'] as $p ) {
		if ( $p->post_type === $type && $p->post_name === $path ) {
			return $p;
		}
	}
	return null;
}
function wp_insert_post( $data ) {
	$p = new WP_Post();
	$p->ID = count( $GLOBALS['ih_store'] ) + 1;
	foreach ( $data as $k => $v ) {
		$p->$k = $v;
	}
	$p->post_modified_gmt = $p->post_date_gmt;
	$GLOBALS['ih_store'][ $p->ID ] = $p;
	$GLOBALS['ih_writes']++;
	return $p->ID;
}
function wp_update_post( $data ) {
	$p = get_post( $data['ID'] );
	foreach ( $data as $k => $v ) {
		$p->$k = $v;
	}
	$GLOBALS['ih_writes']++;
	return $p->ID;
}
function wp_trash_comment() {}
function wp_trash_post( $id ) {
	get_post( $id )->post_status = 'trash';
}
function get_comments() {
	return array();
}
function term_exists( $name ) {
	return isset( $GLOBALS['ih_terms'][ $name ] ) ? array( 'term_id' => $GLOBALS['ih_terms'][ $name ] ) : null;
}
function wp_insert_term( $name ) {
	$GLOBALS['ih_terms'][ $name ] = count( $GLOBALS['ih_terms'] ) + 100;
	return array( 'term_id' => $GLOBALS['ih_terms'][ $name ] );
}

require $GLOBALS['ih_theme'] . '/inc/options.php';
require $GLOBALS['ih_theme'] . '/inc/starter-content.php';
require $GLOBALS['ih_theme'] . '/inc/journal.php';

$fail = 0;
function ih_fail( $what, $msg ) {
	global $fail;
	$fail++;
	echo "FAIL {$what}: {$msg}\n";
}

// ---------- The entries ----------
$entries = injil_help_journal_entries();
$titles  = array_filter( injil_help_data_file( 'seo-titles' ), 'is_string' );
$paths   = array();
foreach ( injil_help_starter_pages_list() as $def ) {
	$paths[ $def['path'] ] = true;
}
$site_room = 59 - mb_strlen( ' | The Rightly-Guided Injīl' );
$slugs     = array();
$tags      = array();
if ( count( $entries ) < 8 ) {
	ih_fail( 'posts.json', count( $entries ) . ' entries, expected at least 8' );
}
foreach ( $entries as $e ) {
	$slug = $e['slug'];
	if ( ! preg_match( '/^[a-z]+(?:-[a-z]+)*$/', $slug ) ) {
		ih_fail( $slug, 'the slug must be words only, no digits' );
	}
	if ( preg_match( '/(^|-)(the|a|an)(-|$)/', $slug ) ) {
		ih_fail( $slug, 'the slug carries an article' );
	}
	if ( isset( $slugs[ $slug ] ) || isset( $paths[ $slug ] ) ) {
		ih_fail( $slug, 'the slug is used twice' );
	}
	$slugs[ $slug ] = true;
	$tag            = $titles[ 'journal/' . $slug ] ?? $e['title'];
	if ( mb_strlen( $tag ) > $site_room ) {
		ih_fail( $slug, "the title tag part is " . mb_strlen( $tag ) . " characters, the room is {$site_room}" );
	}
	if ( isset( $tags[ $tag ] ) ) {
		ih_fail( $slug, 'the title tag is not unique' );
	}
	$tags[ $tag ] = true;
	if ( ! preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $e['date'] ) || false === strtotime( $e['date'] ) ) {
		ih_fail( $slug, 'the date is not Y-m-d H:i:s' );
	}
	$text  = implode( "\n", $e['body'] );
	$prose = preg_replace( '/\{\{fn:.*?\}\}/s', '', $text );
	$prose = preg_replace( '/## Sources.*$/s', '', $prose );
	$prose = preg_replace( '/^@\w+$/m', '', strip_tags( $prose ) );
	$words = count( preg_split( '/\s+/u', trim( $prose ), -1, PREG_SPLIT_NO_EMPTY ) );
	if ( $words < 700 || $words > 1100 ) {
		ih_fail( $slug, "{$words} words, expected 700 to 1,100" );
	}
	if ( false === strpos( $text, '{{fn:' ) || false === strpos( $text, '## Sources' ) ) {
		ih_fail( $slug, 'no notes or no Sources list' );
	}
	preg_match_all( '#href="/([^"]*)"#', $text, $m );
	if ( count( $m[1] ) < 2 ) {
		ih_fail( $slug, 'fewer than 2 internal links' );
	}
	foreach ( $m[1] as $link ) {
		$link = trim( $link, '/' );
		if ( 0 === strpos( $link, 'journal/' ) ) {
			if ( ! in_array( substr( $link, 8 ), array_column( $entries, 'slug' ), true ) ) {
				ih_fail( $slug, "link to a Journal post that does not exist: {$link}" );
			}
		} elseif ( ! isset( $paths[ $link ] ) && ! isset( $titles[ $link ] ) ) {
			ih_fail( $slug, "link to a page that does not exist: {$link}" );
		}
	}
	foreach ( (array) $e['related'] as $rel ) {
		if ( ! isset( $paths[ $rel ] ) ) {
			ih_fail( $slug, "Read next names a page that does not exist: {$rel}" );
		}
	}
	$all = $text . ' ' . $e['title'] . ' ' . $e['excerpt'];
	if ( preg_match( '/\bhorn/i', $all ) ) {
		ih_fail( $slug, 'the banned word' );
	}
	if ( preg_match( '/Elfie|Juferi|\bMENJ\b/i', $all ) ) {
		ih_fail( $slug, "the owner's name" );
	}
	if ( false !== strpos( $all, '—' ) ) {
		ih_fail( $slug, 'an em dash' );
	}
	if ( preg_match( "/\b(?:do|does|did|is|are|was|were|has|have|had|could|would|should|wo|ca)n[’']t\b|\b(?:it|that|there|here|what|let)[’']s\b/i", preg_replace( '/“[^”]*”/u', '', $all ) ) ) {
		ih_fail( $slug, 'a contraction outside a quotation' );
	}
}

// ---------- Status from the date ----------
$now = strtotime( '2026-11-03 04:00:00 UTC' ); // 12:00 on 3 November 2026, site time
if ( 'publish' !== injil_help_journal_status( '2026-10-08 09:00:00', $now ) ) {
	ih_fail( 'status', 'a past date must publish' );
}
if ( 'future' !== injil_help_journal_status( '2026-12-22 09:00:00', $now ) ) {
	ih_fail( 'status', 'a future date must be scheduled' );
}
if ( 'publish' !== injil_help_journal_status( '2026-11-03 11:59:00', $now ) || 'future' !== injil_help_journal_status( '2026-11-03 12:01:00', $now ) ) {
	ih_fail( 'status', 'the date is compared in site time' );
}

// ---------- The sample post is still binned on activation ----------
$sample = wp_insert_post( array( 'post_type' => 'post', 'post_name' => 'hello-world', 'post_title' => 'Hello world!', 'post_status' => 'publish', 'post_date_gmt' => '2026-01-01 00:00:00' ) );
injil_help_remove_sample_content();
if ( 'trash' !== get_post( $sample )->post_status ) {
	ih_fail( 'sample', 'the WordPress sample post was not binned' );
}

// ---------- Seeding ----------
$tally = injil_help_journal_seed( $now );
if ( count( $entries ) !== $tally['created'] || $tally['failed'] ) {
	ih_fail( 'seed', 'first run: ' . json_encode( $tally ) );
}
foreach ( $entries as $e ) {
	$p = injil_help_journal_find( $e['slug'] );
	if ( ! $p ) {
		ih_fail( $e['slug'], 'not written' );
		continue;
	}
	if ( $p->post_date !== $e['date'] || $p->post_date_gmt !== get_gmt_from_date( $e['date'] ) ) {
		ih_fail( $e['slug'], 'post_date is not the fixed date' );
	}
	if ( $p->post_status !== injil_help_journal_status( $e['date'], $now ) ) {
		ih_fail( $e['slug'], "status {$p->post_status} does not match the date" );
	}
	if ( ! $p->post_category ) {
		ih_fail( $e['slug'], 'no category' );
	}
	preg_match_all( '#<!--\s+(/)?wp:([a-z0-9/-]+)[^>]*?(/)?-->#', $p->post_content, $bm, PREG_SET_ORDER );
	$depth = array();
	foreach ( $bm as $b ) {
		if ( ! empty( $b[3] ) ) {
			continue;
		}
		$depth[ $b[2] ] = ( $depth[ $b[2] ] ?? 0 ) + ( empty( $b[1] ) ? 1 : -1 );
	}
	if ( array_filter( $depth ) ) {
		ih_fail( $e['slug'], 'the block markup does not balance: ' . json_encode( array_filter( $depth ) ) );
	}
	if ( false === strpos( $p->post_content, 'ih-aside ih-next' ) ) {
		ih_fail( $e['slug'], 'no Read next row' );
	}
	if ( injil_help_journal_permalink( $p ) !== 'https://injil.help/journal/' . $e['slug'] . '/' ) {
		ih_fail( $e['slug'], 'the address is not /journal/<slug>/' );
	}
}
$published = count( array_filter( $GLOBALS['ih_store'], function ( $p ) { return 'post' === $p->post_type && 'publish' === $p->post_status; } ) );
$scheduled = count( array_filter( $GLOBALS['ih_store'], function ( $p ) { return 'future' === $p->post_status; } ) );

// A second run changes nothing.
$writes = $GLOBALS['ih_writes'];
$count  = count( $GLOBALS['ih_store'] );
$again  = injil_help_journal_seed( $now );
if ( $again['current'] !== count( $entries ) || $GLOBALS['ih_writes'] !== $writes || count( $GLOBALS['ih_store'] ) !== $count ) {
	ih_fail( 'seed', 'second run was not idempotent: ' . json_encode( $again ) );
}

// WordPress publishes a scheduled post on its day; a later run leaves it published with its date.
$last = injil_help_journal_find( end( $entries )['slug'] );
$last->post_status = 'publish';
injil_help_journal_seed( strtotime( '2027-01-10 00:00:00 UTC' ) );
if ( 'publish' !== $last->post_status || $last->post_date !== end( $entries )['date'] ) {
	ih_fail( 'seed', 'a later run changed a published post' );
}

// An edited post and a binned post are kept.
$first = injil_help_journal_find( $entries[0]['slug'] );
$first->post_content .= "\n<p>Edited.</p>";
$second = injil_help_journal_find( $entries[1]['slug'] );
$second->post_status = 'trash';
$kept = injil_help_journal_seed( $now );
if ( 2 !== $kept['kept'] || false === strpos( $first->post_content, 'Edited.' ) || 'trash' !== $second->post_status || count( $GLOBALS['ih_store'] ) !== $count ) {
	ih_fail( 'seed', 'edited or binned posts were not kept: ' . json_encode( $kept ) );
}

// A post with no content hash was never stamped by the theme: a version-change run keeps it,
// whether its text differs from the shipped text or matches it.
$third  = injil_help_journal_find( $entries[2]['slug'] );
$fourth = injil_help_journal_find( $entries[3]['slug'] );
$third->post_content .= "\n<p>Customised before the hash.</p>";
foreach ( array( $third, $fourth ) as $unstamped ) {
	unset( $GLOBALS['ih_meta'][ $unstamped->ID ][ INJIL_HELP_HASH_META ], $GLOBALS['ih_meta'][ $unstamped->ID ][ INJIL_HELP_HEAD_META ] );
}
$fourth_before = $fourth->post_content;
$fourth->post_title = 'Retitled by the owner';
$unhashed = injil_help_journal_seed( $now );
if ( 4 !== $unhashed['kept'] || false === strpos( $third->post_content, 'Customised before the hash.' ) || 'Retitled by the owner' !== $fourth->post_title || $fourth->post_content !== $fourth_before ) {
	ih_fail( 'seed', 'a post with no stored hash was overwritten: ' . json_encode( $unhashed ) );
}
if ( ! injil_help_page_is_edited( $third ) || ! injil_help_page_is_edited( $fourth ) ) {
	ih_fail( 'seed', 'injil_help_page_is_edited() treats a post with no stored hash as unedited' );
}
// A stamped post that still matches its hash is the theme's own text and may be rewritten.
$fifth = injil_help_journal_find( $entries[4]['slug'] );
if ( injil_help_page_is_edited( $fifth ) ) {
	ih_fail( 'seed', 'a post that matches its stored hash counts as edited' );
}

// The page seeder takes the same path: a starter page with no stored hash is kept on a version-change
// run, and "Update all pages from the theme" ($force) still writes it.
$page_def = array( 'path' => 'zz-unstamped', 'title' => 'Shipped title', 'excerpt' => '', 'body' => array( '<!-- wp:paragraph -->', '<p>Shipped text.</p>', '<!-- /wp:paragraph -->' ) );
$page_id  = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => 'zz-unstamped', 'post_title' => 'Shipped title', 'post_excerpt' => '', 'post_content' => '<p>The owner wrote this.</p>' ) );
$page_res = injil_help_populate_page( 0, $page_def, true, false );
if ( 'kept' !== $page_res || '<p>The owner wrote this.</p>' !== get_post( $page_id )->post_content ) {
	ih_fail( 'pages', 'a starter page with no stored hash was overwritten (' . $page_res . ')' );
}
if ( 'updated' !== injil_help_populate_page( 0, $page_def, true, true ) || false === strpos( get_post( $page_id )->post_content, 'Shipped text.' ) ) {
	ih_fail( 'pages', 'a forced update did not write the unstamped page' );
}

echo 'Entries: ' . count( $entries ) . "; published {$published}, scheduled {$scheduled} at 3 November 2026\n";
echo $fail ? "FAILED: {$fail}\n" : "OK\n";
exit( $fail ? 1 : 0 );
