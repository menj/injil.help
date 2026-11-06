<?php
/**
 * Browser icons: the head tags, the web app manifest and /favicon.ico.
 *
 * Three cases, set on the Design tab. A custom favicon URL prints that one link and nothing
 * else. "Always use the brandmark" prints the theme's set and hides WordPress's Site Icon tags
 * (the site_icon_meta_tags filter in inc/options.php). Otherwise the theme's set prints only
 * while no Site Icon is set, and a Site Icon gets WordPress's own tags. Every icon file in
 * assets/images/ is drawn from assets/brandmark.svg.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

/**
 * Which icons this request uses: 'custom', 'brandmark' or 'site-icon'.
 *
 * @return string
 */
function injil_help_favicon_mode() {
	if ( injil_help_get_option( 'favicon_url' ) ) {
		return 'custom';
	}
	return injil_help_get_option( 'favicon_force' ) || ! has_site_icon() ? 'brandmark' : 'site-icon';
}

/** The icon tags, on the site, the login screen and the admin. */
function injil_help_favicon_tags() {
	$mode = injil_help_favicon_mode();
	if ( 'custom' === $mode ) {
		printf( '<link rel="icon" href="%s">' . "\n", esc_url( injil_help_get_option( 'favicon_url' ) ) );
		return;
	}
	if ( 'brandmark' !== $mode ) {
		return;
	}
	$dir = get_stylesheet_directory_uri() . '/assets/images/';
	$ver = INJIL_HELP_VERSION;
	printf( '<link rel="icon" type="image/svg+xml" href="%s">' . "\n", esc_url( $dir . 'favicon.svg?ver=' . $ver ) );
	printf( '<link rel="icon" type="image/png" sizes="32x32" href="%s">' . "\n", esc_url( $dir . 'favicon-32.png?ver=' . $ver ) );
	printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( $dir . 'apple-touch-icon.png?ver=' . $ver ) );
	printf( '<link rel="manifest" href="%s">' . "\n", esc_url( $dir . 'site.webmanifest?ver=' . $ver ) );
	echo '<meta name="theme-color" content="#1f5c4a">' . "\n";
}
add_action( 'wp_head', 'injil_help_favicon_tags' );
add_action( 'login_head', 'injil_help_favicon_tags' );
add_action( 'admin_head', 'injil_help_favicon_tags' );

/**
 * Browsers and bots ask for /favicon.ico on their own. With the brandmark in use, answer with
 * assets/images/favicon.ico: a day's cache and an ETag, with a 304 while it is unchanged. A
 * custom favicon URL gets a redirect to it. With a Site Icon, WordPress redirects to that.
 */
function injil_help_serve_favicon() {
	$mode = injil_help_favicon_mode();
	if ( 'custom' === $mode ) {
		wp_redirect( esc_url_raw( injil_help_get_option( 'favicon_url' ) ) ); // phpcs:ignore WordPress.Security.SafeRedirect -- the owner's own setting.
		exit;
	}
	$file = get_stylesheet_directory() . '/assets/images/favicon.ico';
	if ( 'brandmark' !== $mode || ! is_readable( $file ) ) {
		return;
	}
	$etag = '"' . md5_file( $file ) . '"';
	header( 'Content-Type: image/x-icon' );
	header( 'Cache-Control: public, max-age=86400' );
	header( 'ETag: ' . $etag );
	// If-None-Match may list several validators, weak ones (W/"...") or *.
	$sent = array_map(
		static function ( $v ) {
			return preg_replace( '#^W/#', '', trim( $v ) );
		},
		explode( ',', wp_unslash( (string) ( $_SERVER['HTTP_IF_NONE_MATCH'] ?? '' ) ) ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	);
	if ( in_array( '*', $sent, true ) || in_array( $etag, $sent, true ) ) {
		status_header( 304 );
		exit;
	}
	status_header( 200 );
	header( 'Content-Length: ' . (string) filesize( $file ) );
	// A HEAD request gets the headers alone.
	if ( 'HEAD' !== ( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		readfile( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	}
	exit;
}
add_action( 'do_faviconico', 'injil_help_serve_favicon', 5 );

/**
 * WordPress ends a HEAD request before do_favicon, with an empty HTML answer. template_redirect
 * runs first, so a HEAD request gets the icon's headers too.
 */
function injil_help_favicon_head_request() {
	if ( is_favicon() ) {
		injil_help_serve_favicon();
	}
}
add_action( 'template_redirect', 'injil_help_favicon_head_request', 0 );

// The last line: functions.php checks for it, so a file an upload cut short is caught.
$GLOBALS['injil_help_loaded'][ basename( __FILE__, '.php' ) ] = true;
