<?php
/**
 * The log-in screen in the site's own dress: its palette, typefaces, brandmark, star lattice and the
 * Qur'anic gloss of the header, in place of the WordPress defaults. The words are in
 * inc/data/text.json (group "login"), the styles in assets/css/login.css, which reads only the
 * palette and font tokens of theme.json.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'login_enqueue_scripts',
	function () {
		wp_enqueue_style( 'injil-help-login', get_stylesheet_directory_uri() . '/assets/css/login.css', array( 'login' ), INJIL_HELP_VERSION );
		// The log-in screen loads no global styles: give it the theme.json tokens, and any colours set under Theme Options.
		wp_add_inline_style( 'injil-help-login', wp_get_global_stylesheet( array( 'variables' ) ) . ( function_exists( 'injil_help_color_css' ) ? injil_help_color_css() : '' ) );
	}
);

// The typefaces declared in theme.json (KFGQPC Hafs for the gloss, Kagnue, Garamond, Sabon, Evangelion).
add_action( 'login_head', 'wp_print_font_faces' );

add_filter(
	'login_headerurl',
	function () {
		return home_url( '/' );
	}
);

add_filter(
	'login_headertext',
	function () {
		return wp_strip_all_tags( get_bloginfo( 'name' ) );
	}
);

/** The gloss above the form: Q 5:47 in KFGQPC Hafs, with its English and reference. */
add_filter(
	'login_message',
	function ( $message ) {
		$gloss = injil_help_text( 'login', 'gloss' );
		if ( '' === $gloss ) {
			return $message;
		}
		return '<p class="ih-login-gloss"><span class="ih-quran" dir="rtl" lang="ar">' . esc_html( $gloss ) . '</span><span class="ih-login-gloss-en">' . esc_html( injil_help_text( 'login', 'gloss_en' ) ) . '</span></p>' . $message;
	}
);

/** The title tag keeps the site's rule: "<page part> | <site name>", under 60 characters. */
add_filter(
	'login_title',
	function ( $title ) {
		if ( ! function_exists( 'injil_help_title_fit' ) ) {
			return $title;
		}
		$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : 'login'; // phpcs:ignore WordPress.Security.NonceVerification -- read only, to name the screen
		$key    = in_array( $action, array( 'lostpassword', 'retrievepassword', 'rp', 'resetpass', 'register' ), true ) ? $action : 'login';
		$part   = injil_help_text( 'login', 'title_' . $key );
		return injil_help_title_fit( '' !== $part ? $part : injil_help_text( 'login', 'title_login' ), injil_help_title_room() ) . ' | ' . injil_help_title_site();
	}
);
