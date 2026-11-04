<?php
/**
 * Reading progress bar (3.21.0): the behaviour of the Reading Progress Bar plugin, built in.
 *
 * A thin bar fixed to the top or the bottom of the window fills as the reader scrolls a single
 * post or page of the chosen types (never the front page). Theme Options › Reading sets it on
 * or off, the post types, the position, the height and the two colours; empty colours read the
 * palette. The bar is decorative and carries aria-hidden: the browser's scroll bar already tells
 * every reader where they are, and a live progressbar would announce itself on every scroll.
 * While the plugin is active the theme prints no bar.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

/** Whether the bar belongs on this view. */
function injil_help_progress_wanted() {
	if ( function_exists( 'rp_show_it' ) || ! injil_help_get_option( 'progress' ) || ! is_singular() || is_front_page() ) {
		return false;
	}
	return in_array( get_post_type(), (array) injil_help_get_option( 'progress_types' ), true );
}

/** The bar's settings as CSS custom properties. */
function injil_help_progress_css() {
	$rules = '--ih-progress-h:' . max( 1, min( 12, (int) injil_help_get_option( 'progress_height' ) ) ) . 'px;';
	foreach ( array( 'progress_color' => '--ih-progress-fill', 'progress_track' => '--ih-progress-track' ) as $key => $var ) {
		$hex = sanitize_hex_color( (string) injil_help_get_option( $key ) );
		if ( $hex ) {
			$rules .= $var . ':' . $hex . ';';
		}
	}
	return '.ih-progress{' . $rules . '}';
}

add_action(
	'wp_enqueue_scripts',
	function () {
		if ( ! injil_help_progress_wanted() ) {
			return;
		}
		wp_enqueue_style( 'injil-help-progress', get_stylesheet_directory_uri() . '/assets/css/progress.css', array( 'injil-help' ), INJIL_HELP_VERSION );
		wp_add_inline_style( 'injil-help-progress', injil_help_progress_css() );
		wp_enqueue_script( 'injil-help-progress', get_stylesheet_directory_uri() . '/assets/js/progress.js', array(), INJIL_HELP_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	}
);

add_action(
	'wp_body_open',
	function () {
		if ( injil_help_progress_wanted() ) {
			$pos = 'bottom' === injil_help_get_option( 'progress_position' ) ? 'bottom' : 'top';
			printf( '<div class="ih-progress ih-progress--%s" aria-hidden="true" hidden><span class="ih-progress__fill"></span></div>' . "\n", esc_attr( $pos ) );
		}
	}
);
