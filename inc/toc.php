<?php
/**
 * The contents list: the [auto_toc] shortcode, and an option that adds the list to long pages.
 *
 * The shortcode interface (the name, the "tags" and "title" attributes) follows Ikram Hawramani's
 * "WordPress Table-of-Contents Shortcode Done Properly" (MIT licence, copyright 2025 Ikram Hawramani,
 * https://github.com/Hawramani/WordPress-Table-of-Contents-Shortcode-Done-Properly). The code is the
 * theme's own, and it differs where the original cannot work in a theme:
 *
 *   - WordPress expands shortcodes at the_content priority 11. A filter at 99 that looks for the
 *     shortcode finds it already gone. Here the shortcode leaves a marker, and the_content 13 reads the
 *     finished headings and puts the list in the marker's place.
 *   - The original sets every heading's id from its text. Here an id the author gave is kept, and a made
 *     id never repeats any id on the page (the notes anchors and the footnote links depend on that).
 *   - The original rewrites the whole text through DOMDocument. Here only the opening tag of a heading
 *     that lacks an id changes, as inc/article.php does for the Journal.
 *   - The nested list is closed properly when the levels jump or fall.
 *
 * [auto_toc]                         the level-two and level-three headings
 * [auto_toc tags="h2,h3,h4"]         other levels (h1 to h6)
 * [auto_toc title="In this page"]    another title; title="" shows none
 *
 * Theme Options > Branding > Layout can add the list to every page with four or more level-two headings.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

/** The heading levels in a "tags" attribute, in order and without repeats: "h2, H3" gives array( 2, 3 ). */
function injil_help_toc_levels( $tags ) {
	$levels = array();
	foreach ( explode( ',', strtolower( (string) $tags ) ) as $tag ) {
		if ( preg_match( '/^\s*h([1-6])\s*$/', $tag, $m ) ) {
			$levels[ (int) $m[1] ] = (int) $m[1];
		}
	}
	sort( $levels );
	return $levels ? array_values( $levels ) : array( 2, 3 );
}

/**
 * The shortcode leaves a marker; injil_help_toc_replace() fills it once the headings are final.
 * The marker is an HTML comment, so it survives wpautop and every other filter at 11 and 12.
 */
add_shortcode(
	'auto_toc',
	static function ( $atts ) {
		$atts = shortcode_atts(
			array(
				'tags'  => 'h2,h3',
				'title' => __( 'Contents', 'injil-help' ),
			),
			$atts,
			'auto_toc'
		);
		return '<!--ih-toc:' . implode( ',', injil_help_toc_levels( $atts['tags'] ) ) . ':' . rawurlencode( (string) $atts['title'] ) . '-->';
	}
);

/** The text of a heading's inner HTML: footnote numbers dropped, tags stripped, on one line. */
function injil_help_toc_text( $html ) {
	$html = (string) preg_replace( '#<sup\b[^>]*>.*?</sup>#is', '', (string) $html );
	return trim( (string) preg_replace( '/\s+/u', ' ', html_entity_decode( wp_strip_all_tags( $html ), ENT_QUOTES, 'UTF-8' ) ) );
}

/**
 * The headings of some HTML, as a list of array( level, id, text ), with an id added to each heading of a
 * wanted level that lacks one. Ids already on the page, on any element, are reserved first.
 *
 * @param string $html   Rendered text.
 * @param int[]  $levels Heading levels to give ids.
 * @return array{0: string, 1: array} The text with the new ids, and the list.
 */
function injil_help_toc_headings( $html, $levels ) {
	$seen = array();
	if ( preg_match_all( '/\sid\s*=\s*["\']([^"\']+)["\']/i', (string) $html, $ids ) ) {
		$seen = array_fill_keys( $ids[1], true );
	}
	$items = array();
	$html  = (string) preg_replace_callback(
		'#<h([1-6])(\s[^>]*)?>(.*?)</h\1>#is',
		static function ( $m ) use ( &$seen, &$items, $levels ) {
			$level = (int) $m[1];
			$attrs = (string) ( $m[2] ?? '' );
			$text  = injil_help_toc_text( $m[3] );
			if ( ! in_array( $level, $levels, true ) || '' === $text ) {
				return $m[0];
			}
			if ( preg_match( '/\sid\s*=\s*["\']([^"\']+)["\']/i', $attrs, $own ) ) {
				$items[] = array( $level, $own[1], $text );
				return $m[0];
			}
			$base = sanitize_title( $text );
			if ( '' === $base ) {
				$base = 'section';
			}
			$slug = $base;
			for ( $n = 2; isset( $seen[ $slug ] ); $n++ ) {
				$slug = $base . '-' . $n;
			}
			$seen[ $slug ] = true;
			$items[]       = array( $level, $slug, $text );
			return '<h' . $level . ' id="' . esc_attr( $slug ) . '"' . $attrs . '>' . $m[3] . '</h' . $level . '>';
		},
		(string) $html
	);
	return array( $html, $items );
}

/**
 * A nested ordered list of headings. A level that jumps (h2 then h4) opens one list; a level that falls
 * closes lists back to the one it belongs in, and a level between two lists (h2, h4, h3) joins the deeper
 * one; a level above the shallowest listed is treated as the shallowest.
 *
 * @param array $items List of array( level, id, text ).
 * @return string
 */
function injil_help_toc_list( $items ) {
	if ( ! $items ) {
		return '';
	}
	$top   = min( array_column( $items, 0 ) );
	$out   = '';
	$stack = array();
	foreach ( $items as $item ) {
		$level = max( $top, (int) $item[0] );
		if ( ! $stack ) {
			$out    .= '<ol>';
			$stack[] = $level;
		} else {
			// Fall back to the open list this level belongs in: the first whose parent is not deeper.
			while ( count( $stack ) > 1 && $level <= $stack[ count( $stack ) - 2 ] ) {
				$out .= '</li></ol>';
				array_pop( $stack );
			}
			if ( $level > end( $stack ) ) {
				$out    .= '<ol>';
				$stack[] = $level;
			} else {
				// Same level, or between this list's parent and its first item (h2, h4, h3): a sibling.
				$out                        .= '</li>';
				$stack[ count( $stack ) - 1 ] = $level;
			}
		}
		$out .= '<li><a href="#' . esc_attr( $item[1] ) . '">' . esc_html( $item[2] ) . '</a>';
	}
	return $out . str_repeat( '</li></ol>', count( $stack ) );
}

/** Whether the text being filtered is the main text of the page being viewed. */
function injil_help_toc_is_main() {
	return is_singular() && ! is_admin() && ! is_feed() && is_main_query() && in_the_loop() && ! doing_filter( 'get_the_excerpt' );
}

/** Whether the contents list is added to this page without the shortcode. */
function injil_help_toc_auto_wanted() {
	return (bool) injil_help_get_option( 'toc_auto' ) && is_page() && ! is_front_page();
}

/** The marker the shortcode would leave, for the automatic list. */
function injil_help_toc_auto_marker() {
	return '<!--ih-toc:2,3:' . rawurlencode( __( 'Contents', 'injil-help' ) ) . '-->';
}

/**
 * Priority 13: after shortcodes (11) and the Journal's heading ids (12), before the contextual links (15)
 * and the share row (20). Without a marker the text is untouched.
 */
add_filter(
	'the_content',
	static function ( $content ) {
		$content = (string) $content;
		$main    = injil_help_toc_is_main();
		if ( $main && false === strpos( $content, '<!--ih-toc:' ) && injil_help_toc_auto_wanted() ) {
			$min = (int) apply_filters( 'injil_help_toc_min', 4 );
			if ( preg_match_all( '#<h2\b#i', $content ) >= $min ) {
				// After the breadcrumb row, which opens the content (inc/seo.php).
				$at      = preg_match( '#<nav class="ih-breadcrumb"[^>]*>.*?</nav>#s', $content, $b, PREG_OFFSET_CAPTURE ) ? $b[0][1] + strlen( $b[0][0] ) : 0;
				$content = substr( $content, 0, $at ) . injil_help_toc_auto_marker() . substr( $content, $at );
			}
		}
		if ( false === strpos( $content, '<!--ih-toc:' ) ) {
			return $content;
		}
		$pattern = '#(<p>\s*)?<!--ih-toc:([1-6,]+):([^>]*?)-->(\s*</p>)?#';
		if ( ! $main ) {
			// An excerpt, a feed, a widget or a REST read: no list, and no marker left behind.
			return (string) preg_replace( $pattern, '', $content );
		}
		preg_match_all( $pattern, $content, $markers, PREG_SET_ORDER );
		$levels = array();
		foreach ( $markers as $m ) {
			$levels = array_merge( $levels, array_map( 'intval', explode( ',', $m[2] ) ) );
		}
		list( $content, $items ) = injil_help_toc_headings( $content, array_unique( $levels ) );
		$n                       = 0;
		return (string) preg_replace_callback(
			$pattern,
			static function ( $m ) use ( $items, &$n ) {
				$want = array_map( 'intval', explode( ',', $m[2] ) );
				$list = injil_help_toc_list( array_values( array_filter( $items, static fn( $i ) => in_array( $i[0], $want, true ) ) ) );
				if ( '' === $list ) {
					return '';
				}
				++$n;
				$title = rawurldecode( $m[3] );
				$head  = '' !== $title ? '<p class="ih-k" id="ih-toc-title-' . $n . '">' . esc_html( $title ) . '</p>' : '';
				$label = '' !== $title ? 'aria-labelledby="ih-toc-title-' . $n . '"' : 'aria-label="' . esc_attr__( 'Contents', 'injil-help' ) . '"';
				return '<nav class="ih-toc" ' . $label . '>' . $head . $list . '</nav>';
			},
			$content
		);
	},
	13
);

/** The stylesheet, on the pages that carry a list: the shortcode in the text, or the automatic list. */
add_action(
	'wp_enqueue_scripts',
	static function () {
		if ( ! is_singular() ) {
			return;
		}
		$post = get_queried_object();
		if ( ( $post instanceof WP_Post && has_shortcode( $post->post_content, 'auto_toc' ) ) || injil_help_toc_auto_wanted() ) {
			wp_enqueue_style( 'injil-help-toc', get_stylesheet_directory_uri() . '/assets/css/toc.css', array( 'injil-help' ), INJIL_HELP_VERSION );
		}
	}
);

// The last line: functions.php checks for it, so a file an upload cut short is caught.
$GLOBALS['injil_help_loaded'][ basename( __FILE__, '.php' ) ] = true;
