<?php
/**
 * Justified text: paragraphs and list items of the page text are set justified, with optional
 * hyphenation, a switch that keeps narrow screens ragged, and extra selectors to leave alone.
 *
 * This is the theme's own version of MENJ's plugin "Auto Justify Content" (GPLv2 or later). The plugin
 * builds one long selector list with !important on every property and prints it inline. Here the rules
 * are a static file, assets/css/justify.css, switched by body classes (ih-justify, ih-justify-hyphens,
 * ih-justify-desktop), and only the selectors an editor adds are printed inline.
 *
 * Theme Options > Reading > Justified text. Options: justify (default on), justify_scope (all or
 * posts), justify_hyphens (default on), justify_desktop (default off), justify_exclude.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

/** Whether the text of this request is set justified. */
function injil_help_justify_active() {
	if ( ! injil_help_get_option( 'justify' ) || is_admin() || is_feed() ) {
		return false;
	}
	if ( 'posts' === injil_help_get_option( 'justify_scope' ) ) {
		return is_singular( 'post' );
	}
	return is_singular();
}

/**
 * A list of CSS selectors from a text box: one line, only the characters a selector needs,
 * no braces, semicolons or slashes. Returns a comma-separated string, or an empty string.
 *
 * @param mixed $value Raw text.
 * @return string
 */
function injil_help_justify_clean_selectors( $value ) {
	$value = trim( (string) preg_replace( '/\s+/', ' ', (string) $value ) );
	$value = (string) preg_replace( '/[^a-zA-Z0-9.#\-_\s,:\[\]=()"\'>+~*]/', '', $value );
	return implode( ', ', injil_help_justify_split( $value ) );
}

/**
 * A selector list split at its top-level commas only, so a comma inside :is( ... ), :not( ... ) or [ ... ]
 * stays with its selector. Parts are trimmed; empty parts are dropped.
 *
 * @param string $list Selector list.
 * @return string[]
 */
function injil_help_justify_split( $list ) {
	$parts = array();
	$depth = 0;
	$cur   = '';
	foreach ( str_split( (string) $list ) as $ch ) {
		if ( '(' === $ch || '[' === $ch ) {
			++$depth;
		} elseif ( ( ')' === $ch || ']' === $ch ) && $depth > 0 ) {
			--$depth;
		}
		if ( ',' === $ch && 0 === $depth ) {
			$parts[] = $cur;
			$cur     = '';
			continue;
		}
		$cur .= $ch;
	}
	$parts[] = $cur;
	return array_values( array_filter( array_map( 'trim', $parts ), 'strlen' ) );
}

add_filter(
	'body_class',
	static function ( $classes ) {
		if ( injil_help_justify_active() ) {
			$classes[] = 'ih-justify';
			if ( injil_help_get_option( 'justify_hyphens' ) ) {
				$classes[] = 'ih-justify-hyphens';
			}
			if ( injil_help_get_option( 'justify_desktop' ) ) {
				$classes[] = 'ih-justify-desktop';
			}
		}
		return $classes;
	}
);

add_action(
	'wp_enqueue_scripts',
	static function () {
		if ( ! injil_help_justify_active() ) {
			return;
		}
		wp_enqueue_style( 'injil-help-justify', get_stylesheet_directory_uri() . '/assets/css/justify.css', array( 'injil-help' ), INJIL_HELP_VERSION );
		$sel = injil_help_justify_clean_selectors( injil_help_get_option( 'justify_exclude' ) );
		if ( '' !== $sel ) {
			$list = implode( ', ', array_map( static fn( $s ) => $s . ', ' . $s . ' p, ' . $s . ' li', injil_help_justify_split( $sel ) ) );
			wp_add_inline_style( 'injil-help-justify', $list . '{text-align:start !important;-webkit-hyphens:manual !important;hyphens:manual !important}' );
		}
	},
	20
);

// The last line: functions.php checks for it, so a file an upload cut short is caught.
$GLOBALS['injil_help_loaded'][ basename( __FILE__, '.php' ) ] = true;
