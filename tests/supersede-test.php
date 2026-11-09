<?php
/**
 * Stub test of superseded items, renamed Journal posts, guards, the log and the dry-run rules
 * (inc/supersede.php).
 *
 * Run: php tests/supersede-test.php
 *
 * @package injil-help
 */

if ( 'cli' !== PHP_SAPI ) {
	exit;
}
define( 'ABSPATH', '/' );
$dir = sys_get_temp_dir() . '/ih-supersede-' . uniqid();
mkdir( $dir . '/inc/data', 0777, true );
$GLOBALS['dir']   = $dir;
$GLOBALS['opt']   = array();
$GLOBALS['posts'] = array();
$GLOBALS['meta']  = array();
$GLOBALS['edited'] = array();
const INJIL_HELP_JOURNAL_BASE = 'journal';
const INJIL_HELP_HASH_META    = '_injil_help_hash';
const INJIL_HELP_POST_META    = '_injil_help_post';
function add_action() {}
function add_filter() {}
function __( $s ) { return $s; }
function get_stylesheet_directory() { return $GLOBALS['dir']; }
function get_option( $k, $d = false ) { return array_key_exists( $k, $GLOBALS['opt'] ) ? $GLOBALS['opt'][ $k ] : $d; }
function update_option( $k, $v ) { $GLOBALS['opt'][ $k ] = $v; return true; }
function get_post_meta( $id, $k ) { return $GLOBALS['meta'][ $id ][ $k ] ?? ''; }
function injil_help_page_is_edited( $post ) { return ! empty( $GLOBALS['edited'][ $post->ID ] ); }
function injil_help_data_file( $name ) {
	$file = $GLOBALS['dir'] . '/inc/data/' . $name . '.json';
	return is_readable( $file ) ? (array) json_decode( (string) file_get_contents( $file ), true ) : array();
}
function injil_help_starter_pages() { return $GLOBALS['pages'] ?? array(); }
function injil_help_journal_entries() { return $GLOBALS['entries'] ?? array(); }
require dirname( __DIR__ ) . '/inc/supersede.php';

$checks = 0;
function check( $ok, $message ) {
	$GLOBALS['checks']++;
	if ( ! $ok ) {
		fwrite( STDERR, 'FAIL: ' . $message . "\n" );
		exit( 1 );
	}
}

// The map is cleaned.
check( array( 'a/b' => 'c', 'x' => 'y' ) === injil_help_clean_supersedes( array( ' /A/B/ ' => 'C', 'same' => 'same', '' => 'q', 'x' => 'Y/' ) ), 'The map is cleaned: lower case, trimmed, no empty or self replacing address.' );
check( array( 'x' => 'y' ) === injil_help_clean_supersedes( array( 'a' => 'b', 'b' => 'a', 'x' => 'y' ) ), 'A cycle supersedes nothing, a plain pair stays.' );
check( array() === injil_help_clean_supersedes( array( 'a' => 'b', 'b' => 'c', 'c' => 'a' ) ), 'A longer cycle supersedes nothing.' );
check( array( 'a' => 'b', 'b' => 'c' ) === injil_help_clean_supersedes( array( 'a' => 'b', 'b' => 'c' ) ), 'A chain is valid.' );
check( array() === injil_help_clean_supersedes( 'not a map' ), 'A map that is not an array is empty.' );

// The shipped map is empty.
$real = json_decode( (string) file_get_contents( dirname( __DIR__ ) . '/inc/data/supersedes.json' ), true );
check( array() === $real, 'inc/data/supersedes.json ships empty.' );

// The guard.
check( '' === injil_help_removal_guard( false, true, 90, 100 ) && '' === injil_help_removal_guard( false, true, 5, 0 ), 'Ten percent fewer items passes; no baseline passes.' );
check( '' !== injil_help_removal_guard( false, true, 89, 100 ) && '' !== injil_help_removal_guard( true, true, 100, 100 ) && '' !== injil_help_removal_guard( false, false, 100, 100 ), 'More than ten percent fewer, the kill switch, or unreadable data stops removals.' );
check( '' !== injil_help_removal_guard( false, true, 9, 11 ) && '' === injil_help_removal_guard( false, true, 10, 11 ), 'The threshold is not rounded down.' );

// Edited means: no hash, or the theme says so.
$GLOBALS['posts'][1] = (object) array( 'ID' => 1 );
check( false === injil_help_is_unedited( $GLOBALS['posts'][1] ), 'A post with no content hash counts as edited.' );
$GLOBALS['meta'][1][ INJIL_HELP_HASH_META ] = 'abc';
check( true === injil_help_is_unedited( $GLOBALS['posts'][1] ), 'A hashed post that the theme finds unedited is unedited.' );
$GLOBALS['edited'][1] = true;
check( false === injil_help_is_unedited( $GLOBALS['posts'][1] ), 'A hashed post that was edited is edited.' );

// Old addresses and chains.
injil_help_note_redirect( '/Journal/Old/', 5 );
check( array( 'journal/old' => 5 ) === $GLOBALS['opt'][ INJIL_HELP_REDIRECT_OPTION ], 'An old address is recorded in lower case without slashes.' );
injil_help_note_redirect( 'journal/newer', 7, 5 );
check( 7 === $GLOBALS['opt'][ INJIL_HELP_REDIRECT_OPTION ]['journal/old'] && 7 === $GLOBALS['opt'][ INJIL_HELP_REDIRECT_OPTION ]['journal/newer'], 'An earlier redirect follows the chain to the item that stays.' );
injil_help_note_redirect( 'self', 9, 9 );
check( ! isset( $GLOBALS['opt'][ INJIL_HELP_REDIRECT_OPTION ]['self'] ), 'An item never redirects to itself.' );

// The log keeps the last 100 lines.
for ( $i = 0; $i < 130; $i++ ) { injil_help_log( 'p' . $i, 'test' ); }
$log = $GLOBALS['opt'][ INJIL_HELP_LOG_OPTION ];
check( 100 === count( $log ) && 'p129' === end( $log )['item'] && 'p30' === $log[0]['item'], 'The log keeps the last 100 lines.' );

// What the dry run says.
$base = array( 'defined' => true, 'exists' => true, 'trashed' => false, 'edited' => false, 'renamable' => false, 'retired' => false, 'blocked' => false );
check( 'current' === injil_help_classify( $base ), 'An unedited item that exists is current.' );
check( 'kept' === injil_help_classify( array_merge( $base, array( 'edited' => true ) ) ), 'An edited item is kept.' );
check( 'kept' === injil_help_classify( array_merge( $base, array( 'trashed' => true ) ) ), 'An item you moved to the Trash stays there.' );
check( 'create' === injil_help_classify( array_merge( $base, array( 'exists' => false ) ) ), 'A missing item is created.' );
check( 'rename' === injil_help_classify( array_merge( $base, array( 'exists' => false, 'renamable' => true ) ) ), 'A missing item whose former post exists is renamed.' );
check( 'supersede' === injil_help_classify( array_merge( $base, array( 'retired' => true ) ) ), 'A retired item is superseded.' );
check( 'supersede-kept' === injil_help_classify( array_merge( $base, array( 'retired' => true, 'edited' => true ) ) ), 'An edited retired item stays.' );
check( 'held' === injil_help_classify( array_merge( $base, array( 'retired' => true, 'blocked' => true ) ) ), 'A guard holds the removal.' );
check( 'dropped' === injil_help_classify( array_merge( $base, array( 'defined' => false ) ) ), 'A dropped unedited item goes to the Trash.' );
check( 'dropped-kept' === injil_help_classify( array_merge( $base, array( 'defined' => false, 'edited' => true ) ) ), 'A dropped edited item stays.' );
check( 'held' === injil_help_classify( array_merge( $base, array( 'defined' => false, 'blocked' => true ) ) ), 'A guard holds a dropped item.' );
check( 'current' === injil_help_classify( array_merge( $base, array( 'defined' => false, 'trashed' => true ) ) ), 'A dropped item already in the Trash needs nothing.' );

define( 'INJIL_HELP_DISABLE_SUPERSEDE', true );
check( injil_help_supersede_disabled() && array() === injil_help_superseded_pairs( true ) && null === injil_help_journal_rename( array( 'slug' => 'x', 'title' => 'X', 'former' => array( 'y' ) ) ), 'The kill switch ends pairs and renames.' );

array_map( 'unlink', glob( $dir . '/inc/data/*' ) );
rmdir( $dir . '/inc/data' ); rmdir( $dir . '/inc' ); rmdir( $dir );
echo $checks . " superseded-item checks passed.\n";
