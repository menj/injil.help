<?php
/**
 * Self-test for the fixes from the live log of November 2026 (inc/security.php): XML-RPC off
 * unless Jetpack or INJIL_HELP_ALLOW_XMLRPC needs it, and the "The Injil: deny direct core files"
 * block in the site's .htaccess, with its subfolder prefix, its off constant and its removal.
 * Run: php tests/live-logs-test.php
 *
 * WordPress is stubbed; the .htaccess is a file in the temporary folder. The script runs itself
 * again with an argument to check the two cases that need a constant or a class defined from the
 * start. Exit code 0 on success.
 *
 * @package injil-help
 */

// A command-line script: a web request to it gets a 404 and nothing runs.
if ( 'cli' !== PHP_SAPI ) {
	http_response_code( 404 );
	exit;
}

$ih_mode = $argv[1] ?? '';
if ( 'jetpack' === $ih_mode ) {
	class Jetpack {} // phpcs:ignore
} elseif ( 'allow' === $ih_mode ) {
	define( 'INJIL_HELP_ALLOW_XMLRPC', true );
	define( 'INJIL_HELP_ALLOW_CORE_FILES', true );
}

$ih_tmp = sys_get_temp_dir() . '/ih-live-' . getmypid() . '-' . bin2hex( random_bytes( 4 ) );
mkdir( $ih_tmp . '/site/wp', 0700, true );
define( 'ABSPATH', $ih_tmp . '/site/' );
define( 'HOUR_IN_SECONDS', 3600 );
define( 'INJIL_HELP_OPTION', 'injil_help_options' );

$GLOBALS['ih_options'] = array( 'injil_help_options' => array( 'block_xmlrpc' => 1 ) );
$GLOBALS['ih_trans']   = array();
$GLOBALS['ih_filters'] = array();
$GLOBALS['ih_home']    = ABSPATH;
$GLOBALS['is_apache']  = true;

function add_action() {}
function add_filter( $tag ) {
	$GLOBALS['ih_filters'][] = $tag;
}
function remove_action() {}
function __( $s ) {
	return $s;
}
function injil_help_get_option( $k ) {
	return $GLOBALS['ih_options']['injil_help_options'][ $k ] ?? null;
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
function delete_transient( $k ) {
	unset( $GLOBALS['ih_trans'][ $k ] );
	return true;
}
function wp_json_encode( $v, $f = 0 ) {
	return json_encode( $v, $f );
}
function get_home_path() {
	return $GLOBALS['ih_home'];
}
function wp_is_writable( $path ) {
	return is_writable( $path );
}
/** As WordPress's extract_from_markers(): the lines between "# BEGIN $marker" and "# END $marker". */
function extract_from_markers( $file, $marker ) {
	$out = array();
	$in  = false;
	foreach ( is_file( $file ) ? file( $file, FILE_IGNORE_NEW_LINES ) : array() as $line ) {
		if ( "# END $marker" === $line ) {
			$in = false;
		}
		if ( $in ) {
			$out[] = $line;
		}
		if ( "# BEGIN $marker" === $line ) {
			$in = true;
		}
	}
	return $out;
}
/** As WordPress's insert_with_markers(): replace the marked section, or add it at the end. */
function insert_with_markers( $file, $marker, $lines ) {
	if ( ! empty( $GLOBALS['ih_readonly'] ) ) {
		return false;
	}
	$old   = is_file( $file ) ? file( $file, FILE_IGNORE_NEW_LINES ) : array();
	$block = array_merge( array( "# BEGIN $marker" ), $lines, array( "# END $marker" ) );
	$start = array_search( "# BEGIN $marker", $old, true );
	$end   = array_search( "# END $marker", $old, true );
	if ( false !== $start && false !== $end ) {
		array_splice( $old, $start, $end - $start + 1, $block );
	} else {
		$old = array_merge( $old, $block );
	}
	return false !== file_put_contents( $file, implode( "\n", $old ) . "\n" );
}

require dirname( __DIR__ ) . '/inc/security.php';

$fail = 0;
function ih_check( $ok, $what ) {
	global $fail;
	if ( ! $ok ) {
		++$fail;
		fwrite( STDERR, "FAIL: $what\n" );
	}
}
function ih_done( $tmp ) {
	global $fail;
	foreach ( array( '/site/.htaccess', '/site/wp/.htaccess' ) as $f ) {
		if ( is_file( $tmp . $f ) ) {
			unlink( $tmp . $f );
		}
	}
	rmdir( $tmp . '/site/wp' );
	rmdir( $tmp . '/site' );
	rmdir( $tmp );
	if ( $fail ) {
		fwrite( STDERR, "$fail check(s) failed\n" );
		exit( 1 );
	}
	echo "OK\n";
	exit( 0 );
}

$ht = ABSPATH . '.htaccess';
file_put_contents( $ht, "# BEGIN WordPress\nRewriteEngine On\n# END WordPress\n" );

// The two runs with Jetpack or the constants defined from the start.
if ( 'jetpack' === $ih_mode || 'allow' === $ih_mode ) {
	ih_check( injil_help_sec_xmlrpc_allowed() && ! injil_help_sec_xmlrpc_off(), "$ih_mode: XML-RPC stays on" );
	injil_help_sec_xmlrpc();
	ih_check( ! in_array( 'xmlrpc_enabled', $GLOBALS['ih_filters'], true ), "$ih_mode: no xmlrpc_enabled filter" );
	injil_help_sec_sync_htaccess( true );
	ih_check( array() === extract_from_markers( $ht, 'Block xmlrpc.php' ), "$ih_mode: no xmlrpc.php block" );
	$core = extract_from_markers( $ht, INJIL_HELP_SEC_CORE_MARKER );
	if ( 'allow' === $ih_mode ) {
		ih_check( array() === $core, 'INJIL_HELP_ALLOW_CORE_FILES: no core-file block' );
		ih_check( array() === injil_help_sec_core_wanted(), 'INJIL_HELP_ALLOW_CORE_FILES: nothing wanted' );
	} else {
		ih_check( in_array( 'RewriteRule ^readme\.html$ - [F,L]', $core, true ), 'jetpack: the core-file block is written' );
		ih_check( ! preg_grep( '/xmlrpc/', $core ), 'jetpack: the core-file block leaves xmlrpc.php open' );
	}
	ih_check( 'off' === get_option( 'injil_help_security_htaccess' ), "$ih_mode: the state reads off" );
	ih_done( $ih_tmp );
}

// 1. XML-RPC is off by default: the filters go on, with the pingback header and methods.
ih_check( ! injil_help_sec_xmlrpc_allowed() && injil_help_sec_xmlrpc_off(), 'XML-RPC is off with nothing that needs it' );
injil_help_sec_xmlrpc();
foreach ( array( 'xmlrpc_enabled', 'xmlrpc_methods', 'wp_headers', 'pings_open' ) as $tag ) {
	ih_check( in_array( $tag, $GLOBALS['ih_filters'], true ), "the $tag filter is added" );
}
$GLOBALS['ih_options']['injil_help_options']['block_xmlrpc'] = 0;
ih_check( ! injil_help_sec_xmlrpc_off(), 'the switch turns XML-RPC back on' );
$GLOBALS['ih_options']['injil_help_options']['block_xmlrpc'] = 1;

// 2. The core-file lines at the domain root.
$lines = injil_help_sec_core_lines( '' );
$want  = array(
	'<IfModule mod_rewrite.c>',
	'RewriteEngine On',
	'RewriteRule ^wp-includes/(?!js/tinymce/|ms-files\.php$).+\.php$ - [F,L]',
	'RewriteRule ^(wp-settings|wp-config-sample)\.php$ - [F,L]',
	'RewriteRule ^readme\.html$ - [F,L]',
	'RewriteRule ^wp-admin/(install|setup-config)\.php$ - [F,L]',
	'RewriteRule ^xmlrpc\.php$ - [F,L]',
	'</IfModule>',
);
ih_check( $want === $lines, 'the core-file lines: ' . json_encode( $lines ) );
ih_check( ! preg_grep( '/xmlrpc/', injil_help_sec_core_lines( '', false ) ), 'xmlrpc.php is left out when XML-RPC is on' );
foreach ( array( 'wp-includes/blocks/rss.php', 'wp-includes/ms-settings.php', 'wp-settings.php', 'wp-config-sample.php', 'readme.html', 'wp-admin/install.php', 'wp-admin/setup-config.php', 'xmlrpc.php' ) as $path ) {
	$hit = false;
	foreach ( $lines as $l ) {
		if ( preg_match( '#^RewriteRule \^(\S+)#', $l, $m ) && preg_match( '#^' . $m[1] . '#', $path ) ) {
			$hit = true;
		}
	}
	ih_check( $hit, "$path is denied" );
}
foreach ( array( 'wp-includes/js/tinymce/wp-tinymce.php', 'wp-includes/ms-files.php', 'wp-login.php', 'wp-admin/admin-ajax.php', 'wp-admin/upgrade.php', 'index.php', 'wp-includes/css/dashicons.css' ) as $path ) {
	$hit = false;
	foreach ( $lines as $l ) {
		if ( preg_match( '#^RewriteRule \^(\S+)#', $l, $m ) && preg_match( '#^' . $m[1] . '#', $path ) ) {
			$hit = true;
		}
	}
	ih_check( ! $hit, "$path stays open" );
}

// 3. The prefix: same folder, WordPress in a subfolder, WordPress outside, Windows paths, spaces.
ih_check( '' === injil_help_sec_core_prefix( '/var/www/site/', '/var/www/site/' ), 'same folder: no prefix' );
ih_check( 'wp/' === injil_help_sec_core_prefix( '/var/www/site', '/var/www/site/wp/' ), 'WordPress in wp/: prefix wp/' );
ih_check( null === injil_help_sec_core_prefix( '/var/www/site/', '/var/www/other/' ), 'WordPress outside: no block' );
ih_check( 'core/' === injil_help_sec_core_prefix( 'C:\\www\\site\\', 'C:\\www\\site\\core\\' ), 'Windows paths' );
ih_check( in_array( 'RewriteRule ^wp/readme\.html$ - [F,L]', injil_help_sec_core_lines( 'wp/' ), true ), 'the subfolder goes in front' );
ih_check( in_array( 'RewriteRule ^my\ wp/readme\.html$ - [F,L]', injil_help_sec_core_lines( 'my wp/' ), true ), 'a space in the folder is escaped' );

// 4. The sync writes both blocks after the WordPress section and keeps them once.
injil_help_sec_sync_htaccess( true );
$text = (string) file_get_contents( $ht );
ih_check( injil_help_sec_htaccess_lines() === extract_from_markers( $ht, 'Block xmlrpc.php' ), 'the xmlrpc.php block is written' );
ih_check( $want === extract_from_markers( $ht, INJIL_HELP_SEC_CORE_MARKER ), 'the core-file block is written' );
ih_check( strpos( $text, '# END WordPress' ) < strpos( $text, '# BEGIN ' . INJIL_HELP_SEC_CORE_MARKER ), 'the blocks come after the WordPress section' );
ih_check( 'written' === get_option( 'injil_help_security_htaccess' ), 'the state reads written' );
injil_help_sec_sync_htaccess( true );
ih_check( $text === file_get_contents( $ht ), 'a second sync changes nothing' );
ih_check( injil_help_sec_htaccess_state() === get_transient( 'injil_help_sec_htaccess_checked' ), 'the checked state is remembered' );

// 5. A changed state is synced at once, inside the hour: XML-RPC switched on.
$GLOBALS['ih_options']['injil_help_options']['block_xmlrpc'] = 0;
injil_help_sec_sync_htaccess();
ih_check( array() === extract_from_markers( $ht, 'Block xmlrpc.php' ), 'the xmlrpc.php block goes with the switch' );
ih_check( ! preg_grep( '/xmlrpc/', extract_from_markers( $ht, INJIL_HELP_SEC_CORE_MARKER ) ) && array() !== extract_from_markers( $ht, INJIL_HELP_SEC_CORE_MARKER ), 'the core-file block stays, without xmlrpc.php' );
ih_check( 'off' === get_option( 'injil_help_security_htaccess' ), 'the state reads off' );
$GLOBALS['ih_options']['injil_help_options']['block_xmlrpc'] = 1;

// 6. A failed write reads as unavailable and is tried again on the next admin page.
$GLOBALS['ih_readonly'] = true;
injil_help_sec_sync_htaccess( true );
ih_check( 'unavailable' === get_option( 'injil_help_security_htaccess' ), 'a failed write reads unavailable' );
ih_check( false === get_transient( 'injil_help_sec_htaccess_checked' ), 'a failed write is retried at once' );
$GLOBALS['ih_readonly'] = false;
injil_help_sec_sync_htaccess();
ih_check( 'written' === get_option( 'injil_help_security_htaccess' ), 'the retry writes the blocks' );

// 7. The lines to paste: both blocks with their markers, the subfolder carried.
$snip = injil_help_sec_htaccess_snippet( 'wp/' );
ih_check( 0 === strpos( $snip, "# BEGIN Block xmlrpc.php\n<Files xmlrpc.php>" ), 'the paste lines open with the xmlrpc.php block' );
ih_check( false !== strpos( $snip, "# BEGIN The Injil: deny direct core files\n<IfModule mod_rewrite.c>" ) && false !== strpos( $snip, 'RewriteRule ^wp/wp-admin/(install|setup-config)\.php$ - [F,L]' ), 'the paste lines carry the core-file block with the subfolder' );
ih_check( false === strpos( injil_help_sec_htaccess_snippet( null ), '^wp/' ) && '' !== injil_help_sec_htaccess_snippet( null ), 'worked out from ABSPATH, the lines have no prefix here' );

// 8. WordPress outside the .htaccess folder: no core-file block, and an old one is taken out.
$GLOBALS['ih_home'] = $ih_tmp . '/site/wp/';
file_put_contents( $ih_tmp . '/site/wp/.htaccess', "# BEGIN The Injil: deny direct core files\nold\n# END The Injil: deny direct core files\n" );
injil_help_sec_sync_htaccess( true );
ih_check( array() === extract_from_markers( $ih_tmp . '/site/wp/.htaccess', INJIL_HELP_SEC_CORE_MARKER ), 'WordPress outside the folder: the core-file block is emptied' );
$GLOBALS['ih_home'] = ABSPATH;

// 9. A theme switch takes both blocks out; a server that is not Apache gets no file.
injil_help_sec_remove_htaccess();
ih_check( array() === extract_from_markers( $ht, 'Block xmlrpc.php' ) && array() === extract_from_markers( $ht, INJIL_HELP_SEC_CORE_MARKER ), 'a theme switch empties both blocks' );
ih_check( false !== strpos( (string) file_get_contents( $ht ), "# BEGIN WordPress\nRewriteEngine On\n# END WordPress" ), 'the WordPress section is kept' );
$GLOBALS['is_apache'] = false;
ih_check( '' === injil_help_sec_htaccess_file(), 'no .htaccess on a server that is not Apache' );
injil_help_sec_sync_htaccess( true );
ih_check( 'unavailable' === get_option( 'injil_help_security_htaccess' ), 'not Apache: unavailable' );
$GLOBALS['is_apache'] = true;

// 10. The runs with Jetpack and with the constants, in their own processes.
foreach ( array( 'jetpack', 'allow' ) as $mode ) {
	$out  = array();
	$code = 0;
	exec( escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( __FILE__ ) . ' ' . $mode . ' 2>&1', $out, $code );
	ih_check( 0 === $code, "the $mode run passes: " . implode( ' / ', $out ) );
}

ih_done( $ih_tmp );
