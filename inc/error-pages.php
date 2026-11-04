<?php
/**
 * The three wp-content drop-ins and the "Install error pages" button
 * (Appearance > Theme Options, below the tabs).
 *
 * WordPress shows wp-content/maintenance.php while it updates (503),
 * php-error.php on a fatal PHP error (500) and db-error.php when the database
 * cannot be reached (503). Each file is built from its own entry in
 * injil_help_error_page_kinds(), so each says what happened. The theme writes them
 * only when an administrator presses the button, never on its own, and never
 * overwrites a file without one of its markers.
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
 * The state of one drop-in in a directory.
 *
 * @param string $name File name.
 * @param string $dir  Directory, wp-content by default.
 * @return string missing | installed | outdated | foreign
 */
function injil_help_error_state( $name, $dir = '' ) {
	$path = rtrim( '' !== $dir ? $dir : WP_CONTENT_DIR, '/' ) . '/' . $name;
	if ( ! file_exists( $path ) ) {
		return 'missing';
	}
	if ( ! injil_help_error_is_ours( $path ) ) {
		return 'foreign';
	}
	return (string) file_get_contents( $path ) === injil_help_error_dropin_source( injil_help_error_dropins()[ $name ] ) ? 'installed' : 'outdated'; // phpcs:ignore WordPress.WP.AlternativeFunctions
}

/**
 * Write the drop-ins into a directory. A missing file, or one of the theme's
 * own, is written; anyone else's is left alone.
 *
 * @param string $dir Directory, wp-content by default.
 * @return array<string,string> File name => written | unchanged | kept | failed.
 */
function injil_help_error_install( $dir = '' ) {
	$dir    = rtrim( '' !== $dir ? $dir : WP_CONTENT_DIR, '/' );
	$result = array();
	foreach ( injil_help_error_dropins() as $name => $kind ) {
		$state = injil_help_error_state( $name, $dir );
		if ( 'foreign' === $state || 'installed' === $state ) {
			$result[ $name ] = 'foreign' === $state ? 'kept' : 'unchanged';
			continue;
		}
		$target = $dir . '/' . $name;
		// A temporary name, then a rename, so WordPress never reads half a file.
		$tmp = $target . '.' . uniqid( 'err-', true ) . '.tmp';
		if ( false !== @file_put_contents( $tmp, injil_help_error_dropin_source( $kind ) ) && @rename( $tmp, $target ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions, WordPress.PHP.NoSilencedErrors
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
	wp_safe_redirect( add_query_arg( 'injil_help_errors', '1', injil_help_error_pages_url() ) . '#injil_help_error-pages' );
	exit;
}
add_action( 'admin_post_injil_help_install_error_pages', 'injil_help_handle_install_error_pages' );

/**
 * The lines for .htaccess that point the web server at the static pages.
 *
 * @return string
 */
function injil_help_error_htaccess_snippet() {
	$base  = untrailingslashit( (string) wp_parse_url( get_stylesheet_directory_uri(), PHP_URL_PATH ) ) . '/errors/';
	$lines = array( '# The Injil: pages for the errors the web server answers before WordPress runs' );
	foreach ( array( 403, 500, 502, 503 ) as $code ) {
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
		'foreign'   => __( 'Another file is there; left alone', 'injil-help' ),
	);
	$when   = array(
		'maintenance.php' => __( 'WordPress is updating (503, Retry-After)', 'injil-help' ),
		'php-error.php'   => __( 'a fatal PHP error stops a page (500)', 'injil-help' ),
		'db-error.php'    => __( 'the database cannot be reached (503, Retry-After)', 'injil-help' ),
	);
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
	<p><?php esc_html_e( 'WordPress shows these files from wp-content when it cannot build a page. Each one is self-contained, so it works with the theme half uploaded or the database down. The button writes them. A file the theme did not write is never overwritten.', 'injil-help' ); ?></p>
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
	<p><?php esc_html_e( 'For errors the web server answers before WordPress starts, static pages are in the errors folder of the theme. Add these lines to .htaccess to use them:', 'injil-help' ); ?></p>
	<textarea readonly rows="5" class="large-text code" aria-label="<?php esc_attr_e( '.htaccess lines', 'injil-help' ); ?>"><?php echo esc_textarea( injil_help_error_htaccess_snippet() ); ?></textarea>
	</div>
	<?php
}
