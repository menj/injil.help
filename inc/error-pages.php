<?php
/**
 * The three wp-content drop-ins and the "Install error pages" button
 * (Appearance > Theme Options, below the tabs).
 *
 * WordPress shows wp-content/maintenance.php while it updates (503),
 * php-error.php on a fatal PHP error (500) and db-error.php when the database
 * cannot be reached (503). Each file is built from its own entry in
 * injil_help_error_page_kinds(), so each says what happened. The theme writes them
 * on activation, again on an administrator's next visit when a file is
 * missing or outdated, and when the button is pressed. It replaces a copy
 * another theme left behind (marked "Written by the ... theme; safe to
 * delete"), never touches a file without such a mark, and removes its own
 * files when another theme is activated.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/error-page-html.php';

/** The marker every drop-in the theme writes carries in its first line. */
const INJIL_HELP_ERR_MARKER = 'The Injil error page drop-in';

/**
 * Markers that identify a file as the theme's own, this version's and any
 * earlier one's.
 *
 * @return string[]
 */
function injil_help_error_markers() {
	return array( INJIL_HELP_ERR_MARKER, 'Written by the The Injil theme; safe to delete' );
}

/**
 * Drop-in file name => page kind.
 *
 * @return array<string,string>
 */
function injil_help_error_dropins() {
	return array(
		'maintenance.php' => 'maintenance',
		'php-error.php'   => 'php-error',
		'db-error.php'    => 'db-error',
	);
}

/** The folder in wp-content that holds the site's copies of the static pages. */
const INJIL_HELP_STATIC_ERRORS = 'injil-help-errors';

/**
 * The site's copies of the static pages for the web server, relative to
 * wp-content => status code. errors/ in the theme assumes a site at the
 * domain root; these carry the site's own home address, so they also work
 * when WordPress sits in a subfolder.
 *
 * @return array<string,string>
 */
function injil_help_error_static_files() {
	$out = array();
	foreach ( array( '403', '500', '502', '503', '504' ) as $code ) {
		$out[ INJIL_HELP_STATIC_ERRORS . '/' . $code . '.html' ] = $code;
	}
	return $out;
}

/**
 * Every file the theme writes into wp-content: the drop-ins, then the static
 * copies. File => page kind.
 *
 * @return array<string,string>
 */
function injil_help_error_files() {
	return array_merge( injil_help_error_dropins(), injil_help_error_static_files() );
}

/**
 * The text of any file the theme writes into wp-content.
 *
 * @param string $name A key of injil_help_error_files().
 * @return string
 */
function injil_help_error_file_source( $name ) {
	$static = injil_help_error_static_files();
	if ( ! isset( $static[ $name ] ) ) {
		return injil_help_error_dropin_source( injil_help_error_dropins()[ $name ] );
	}
	$site = wp_specialchars_decode( (string) get_bloginfo( 'name' ), ENT_QUOTES );
	$home = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	return '<!-- ' . INJIL_HELP_ERR_MARKER . ' (' . $static[ $name ] . ') ' . INJIL_HELP_VERSION . ". Written by the The Injil theme; safe to delete. -->\n"
		. injil_help_error_page_html( $static[ $name ], '' !== $site ? $site : 'The Rightly-Guided Injīl', '' !== $home ? $home : '/' ) . "\n";
}

/**
 * The source of one drop-in.
 *
 * @param string $kind Page kind.
 * @return string
 */
function injil_help_error_dropin_source( $kind ) {
	$site = wp_specialchars_decode( (string) get_bloginfo( 'name' ), ENT_QUOTES );
	$home = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	return injil_help_error_dropin_head( $kind, INJIL_HELP_VERSION )
		. injil_help_error_page_html( $kind, '' !== $site ? $site : 'The Rightly-Guided Injīl', '' !== $home ? $home : '/' ) . "\n";
}

/**
 * Whether the theme wrote a file.
 *
 * @param string $path File path.
 * @return bool
 */
function injil_help_error_is_ours( $path ) {
	$head = is_readable( $path ) ? (string) file_get_contents( $path, false, null, 0, 400 ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions
	foreach ( injil_help_error_markers() as $marker ) {
		if ( '' !== $head && false !== strpos( $head, $marker ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Whether a file is a drop-in another theme left behind: it carries a
 * "Written by the ... theme; safe to delete" mark and is not the theme's own.
 *
 * @param string $path File path.
 * @return bool
 */
function injil_help_error_is_other_theme( $path ) {
	if ( ! is_readable( $path ) || injil_help_error_is_ours( $path ) ) {
		return false;
	}
	$head = (string) file_get_contents( $path, false, null, 0, 400 ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	// The mark must sit on a comment line of the file's header.
	return 1 === preg_match( '~^//[^\n]*Written by the [^\n]{1,80} theme; safe to delete~m', $head );
}

/**
 * The state of one drop-in in a directory.
 *
 * @param string $name File name.
 * @param string $dir  Directory, wp-content by default.
 * @return string missing | installed | outdated | theme | foreign
 */
function injil_help_error_state( $name, $dir = '' ) {
	$path = rtrim( '' !== $dir ? $dir : WP_CONTENT_DIR, '/' ) . '/' . $name;
	if ( ! file_exists( $path ) ) {
		return 'missing';
	}
	if ( injil_help_error_is_other_theme( $path ) ) {
		return 'theme';
	}
	if ( ! injil_help_error_is_ours( $path ) ) {
		return 'foreign';
	}
	return (string) file_get_contents( $path ) === injil_help_error_file_source( $name ) ? 'installed' : 'outdated'; // phpcs:ignore WordPress.WP.AlternativeFunctions
}

/**
 * Write the drop-ins into a directory. A missing file, one of the theme's
 * own or a copy another theme left behind is written; a file without a
 * theme's mark is left alone.
 *
 * @param string $dir Directory, wp-content by default.
 * @return array<string,string> File name => written | unchanged | kept | failed.
 */
function injil_help_error_install( $dir = '' ) {
	$dir    = rtrim( '' !== $dir ? $dir : WP_CONTENT_DIR, '/' );
	$result = array();
	foreach ( array_keys( injil_help_error_files() ) as $name ) {
		$state = injil_help_error_state( $name, $dir );
		if ( 'foreign' === $state || 'installed' === $state ) {
			$result[ $name ] = 'foreign' === $state ? 'kept' : 'unchanged';
			continue;
		}
		$target = $dir . '/' . $name;
		if ( ! is_dir( dirname( $target ) ) ) {
			wp_mkdir_p( dirname( $target ) );
		}
		// A temporary name, then a rename, so WordPress never reads half a file.
		$tmp = $target . '.' . uniqid( 'err-', true ) . '.tmp';
		if ( false !== @file_put_contents( $tmp, injil_help_error_file_source( $name ) ) && @rename( $tmp, $target ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions, WordPress.PHP.NoSilencedErrors
			$result[ $name ] = 'written';
		} else {
			$result[ $name ] = 'failed';
			if ( file_exists( $tmp ) ) {
				@unlink( $tmp ); // phpcs:ignore WordPress.WP.AlternativeFunctions, WordPress.PHP.NoSilencedErrors
			}
		}
	}
	return $result;
}

/**
 * What the drop-ins embed: the theme version, the site name and the home
 * address. A change means the files are outdated.
 *
 * @return string
 */
function injil_help_error_fingerprint() {
	return md5( INJIL_HELP_VERSION . '|' . get_bloginfo( 'name' ) . '|' . home_url( '/' ) );
}

/**
 * Whether the drop-ins need writing: a file is missing or is another theme's
 * leftover, or the theme last wrote them for another version or site name.
 *
 * @return bool
 */
function injil_help_error_needs_install() {
	if ( get_option( 'injil_help_error_pages_fingerprint' ) !== injil_help_error_fingerprint() ) {
		return true;
	}
	foreach ( array_keys( injil_help_error_files() ) as $name ) {
		// Missing, another theme's leftover, or a copy of the theme's own that
		// differs from what it would write now (edited or half restored).
		if ( in_array( injil_help_error_state( $name ), array( 'missing', 'theme', 'outdated' ), true ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Write the drop-ins without the button: on activation, and on an
 * administrator's visit when they are missing or outdated. On a network only
 * the main site writes, since wp-content is shared. A clean run is
 * remembered; a failed one (wp-content not writable) is tried again on the
 * next visit.
 *
 * @return void
 */
function injil_help_error_auto_install() {
	if ( is_multisite() && ! is_main_site() ) {
		return;
	}
	if ( ! in_array( 'failed', injil_help_error_install(), true ) ) {
		update_option( 'injil_help_error_pages_fingerprint', injil_help_error_fingerprint(), false );
	}
}
add_action( 'after_switch_theme', 'injil_help_error_auto_install' );

/**
 * On an administrator's admin page load, write the drop-ins when they need it.
 *
 * @return void
 */
function injil_help_error_maybe_install() {
	if ( current_user_can( 'edit_theme_options' ) && injil_help_error_needs_install() ) {
		injil_help_error_auto_install();
	}
}
add_action( 'admin_init', 'injil_help_error_maybe_install' );

/**
 * Remove the theme's own drop-ins when another theme is activated. Only files
 * carrying one of the theme's markers are deleted. On a network the files
 * are left in place: wp-content is shared, and another site may still use
 * this theme.
 *
 * @return void
 */
function injil_help_error_remove() {
	if ( is_multisite() ) {
		return;
	}
	foreach ( array_keys( injil_help_error_files() ) as $name ) {
		$path = WP_CONTENT_DIR . '/' . $name;
		if ( injil_help_error_is_ours( $path ) ) {
			wp_delete_file( $path );
		}
	}
	if ( is_dir( WP_CONTENT_DIR . '/' . INJIL_HELP_STATIC_ERRORS ) ) {
		@rmdir( WP_CONTENT_DIR . '/' . INJIL_HELP_STATIC_ERRORS ); // phpcs:ignore WordPress.WP.AlternativeFunctions, WordPress.PHP.NoSilencedErrors -- only when empty.
	}
	delete_option( 'injil_help_error_pages_fingerprint' );
}
add_action( 'switch_theme', 'injil_help_error_remove' );

/**
 * The address the button returns to.
 *
 * @return string
 */
function injil_help_error_pages_url() {
	return admin_url( 'themes.php?page=injil-help-options' );
}

/**
 * "Install error pages": nonce and manage_options. On a network only the main
 * site writes, since wp-content is shared.
 */
function injil_help_handle_install_error_pages() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to do that.', 'injil-help' ), 403 );
	}
	check_admin_referer( 'injil_help_install_error_pages' );
	$result = ( is_multisite() && ! is_main_site() ) ? array() : injil_help_error_install();
	update_option( 'injil_help_error_pages_result', $result, false );
	if ( $result && ! in_array( 'failed', $result, true ) ) {
		update_option( 'injil_help_error_pages_fingerprint', injil_help_error_fingerprint(), false );
	}
	wp_safe_redirect( add_query_arg( 'injil_help_errors', '1', injil_help_error_pages_url() ) . '#injil_help_error-pages' );
	exit;
}
add_action( 'admin_post_injil_help_install_error_pages', 'injil_help_handle_install_error_pages' );

/**
 * The lines for .htaccess that point the web server at the site's copies of
 * the static pages in wp-content/injil-help-errors/.
 *
 * @return string
 */
function injil_help_error_htaccess_snippet() {
	$base  = untrailingslashit( (string) wp_parse_url( content_url( '/' ), PHP_URL_PATH ) ) . '/' . INJIL_HELP_STATIC_ERRORS . '/';
	$lines = array( '# The Injil: pages for the errors the web server answers before WordPress runs' );
	foreach ( array( 403, 500, 502, 503, 504 ) as $code ) {
		$lines[] = 'ErrorDocument ' . $code . ' ' . $base . $code . '.html';
	}
	return implode( "\n", $lines );
}

/**
 * The error pages section: status table, button and .htaccess lines. Its own
 * form, so it must sit outside any settings form.
 */
function injil_help_render_error_pages_section() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$labels = array(
		'missing'   => __( 'Not installed', 'injil-help' ),
		'installed' => __( 'Installed, current', 'injil-help' ),
		'outdated'  => __( 'Installed by the theme, outdated', 'injil-help' ),
		'theme'     => __( 'Left behind by another theme; replaced on the next install', 'injil-help' ),
		'foreign'   => __( 'Another file is there; left alone', 'injil-help' ),
	);
	$when   = array(
		'maintenance.php' => __( 'WordPress is updating (503, Retry-After)', 'injil-help' ),
		'php-error.php'   => __( 'a fatal PHP error stops a page (500)', 'injil-help' ),
		'db-error.php'    => __( 'the database cannot be reached (503, Retry-After)', 'injil-help' ),
	);
	foreach ( injil_help_error_static_files() as $name => $code ) {
		/* translators: %s: HTTP status code */
		$when[ $name ] = sprintf( __( 'the web server answers %s, through the .htaccess lines below', 'injil-help' ), $code );
	}
	$words  = array(
		'written'   => __( 'written', 'injil-help' ),
		'unchanged' => __( 'already current', 'injil-help' ),
		'kept'      => __( 'left alone, another file is there', 'injil-help' ),
		'failed'    => __( 'could not be written, wp-content is not writable', 'injil-help' ),
	);
	?>
	<div class="injil_help_error-pages" id="injil_help_error-pages">
	<h2><?php esc_html_e( 'Error pages', 'injil-help' ); ?></h2>
	<?php
	$last = get_option( 'injil_help_error_pages_result' );
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display of the last result only.
	if ( isset( $_GET['injil_help_errors'] ) && is_array( $last ) && $last ) {
		$parts = array();
		foreach ( $last as $file => $what ) {
			$parts[] = $file . ': ' . ( $words[ $what ] ?? $what );
		}
		printf( '<div class="notice %s inline"><p>%s</p></div>', in_array( 'failed', $last, true ) ? 'notice-error' : 'notice-success', esc_html( implode( '; ', $parts ) ) );
	}
	?>
	<p><?php esc_html_e( 'WordPress shows these files from wp-content when it cannot build a page. Each one is self-contained, so it works with the theme half uploaded or the database down. The theme writes them when it is activated and keeps them current; the button writes them at once. A copy another theme left behind is replaced, a file without a theme’s mark is never overwritten, and the theme removes its own files when another theme is activated.', 'injil-help' ); ?></p>
	<table class="widefat striped">
		<thead><tr><th scope="col"><?php esc_html_e( 'File', 'injil-help' ); ?></th><th scope="col"><?php esc_html_e( 'Shown when', 'injil-help' ); ?></th><th scope="col"><?php esc_html_e( 'Status', 'injil-help' ); ?></th></tr></thead>
		<tbody>
		<?php foreach ( $when as $name => $label ) : ?>
			<tr><td><code>wp-content/<?php echo esc_html( $name ); ?></code></td><td><?php echo esc_html( $label ); ?></td><td><?php echo esc_html( $labels[ injil_help_error_state( $name ) ] ); ?></td></tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php if ( is_multisite() && ! is_main_site() ) : ?>
		<p><?php esc_html_e( 'The whole network shares wp-content, so the error pages are installed from the main site.', 'injil-help' ); ?></p>
	<?php else : ?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:1em 0">
		<input type="hidden" name="action" value="injil_help_install_error_pages">
		<?php wp_nonce_field( 'injil_help_install_error_pages' ); ?>
		<?php submit_button( __( 'Install error pages', 'injil-help' ), 'secondary', 'submit', false ); ?>
	</form>
	<?php endif; ?>
	<p><?php esc_html_e( 'For errors the web server answers before WordPress starts, the theme writes the static pages above into wp-content/injil-help-errors, with this site’s home address. Add these lines to .htaccess to use them:', 'injil-help' ); ?></p>
	<textarea readonly rows="6" class="large-text code" aria-label="<?php esc_attr_e( '.htaccess lines', 'injil-help' ); ?>"><?php echo esc_textarea( injil_help_error_htaccess_snippet() ); ?></textarea>
	</div>
	<?php
}
