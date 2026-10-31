<?php
/**
 * SEO for the target keyword "the Islamic Dilemma".
 * Title, meta description, canonical and Open Graph. Structured data is in
 * inc/schema.php. Every part steps aside when Yoast, Rank Math or SEOPress is active.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

define( 'INJIL_HELP_KEYWORD', 'the Islamic Dilemma' );
define( 'INJIL_HELP_PILLAR_SLUG', 'islamic-dilemma' );

function injil_help_seo_defaults() {
	return apply_filters(
		'injil_help_seo_defaults',
		array(
			'home_title'       => __( 'The Islamic Dilemma, answered', 'injil-help' ),
			'home_description' => __( "The Islamic Dilemma says the Qur'an affirms the gospels it contradicts. A sourced answer: what the Injil (Injeel) is, and what the Qur'an says about Jesus.", 'injil-help' ),
			'pillar_title'     => __( 'The Islamic Dilemma explained', 'injil-help' ),
		)
	);
}

function injil_help_is_pillar() {
	return is_page( INJIL_HELP_PILLAR_SLUG );
}

/** Default social image: the bundled Birmingham Qur'an manuscript (public domain), or the URL set in Theme Options. */
function injil_help_default_og_image() {
	return apply_filters( 'injil_help_default_og_image', get_stylesheet_directory_uri() . '/assets/images/og-default.jpg' );
}

/*
 * Title tag. Every title on the site is "<page part> | <site name>" and stays under 60
 * characters (SSOT.md). The page part is the short title in inc/data/seo-titles.json when the
 * page has one, else the page title, fitted to what the site name leaves.
 */
const INJIL_HELP_TITLE_MAX = 59;

/** The short titles for the title tag, keyed by page path. */
function injil_help_seo_titles() {
	static $map = null;
	if ( null === $map ) {
		$map = array_filter( injil_help_data_file( 'seo-titles' ), 'is_string' );
	}
	return $map;
}

/**
 * A title cut to at most $max characters: whole if it fits, else the part before its colon or
 * its bracket when that keeps at least half the room, else whole words with the trailing
 * punctuation dropped.
 */
function injil_help_title_fit( $text, $max ) {
	$text = trim( preg_replace( '/\s+/u', ' ', (string) $text ) );
	if ( $max < 1 || mb_strlen( $text ) <= $max ) {
		return $max < 1 ? '' : $text;
	}
	foreach ( array( ': ', ' (' ) as $cut ) {
		$pos = mb_strpos( $text, $cut );
		if ( false !== $pos && $pos >= $max / 2 && $pos <= $max ) {
			return mb_substr( $text, 0, $pos );
		}
	}
	$cut = mb_substr( $text, 0, $max + 1 );
	$cut = false !== mb_strrpos( $cut, ' ' ) ? mb_substr( $cut, 0, mb_strrpos( $cut, ' ' ) ) : mb_substr( $text, 0, $max );
	return rtrim( $cut, " ,;:–-" );
}

/** The page part of the title for the current request. */
function injil_help_title_part() {
	$d = injil_help_seo_defaults();
	if ( is_front_page() ) {
		return $d['home_title'];
	}
	if ( injil_help_is_pillar() ) {
		return $d['pillar_title'];
	}
	if ( is_singular() ) {
		$post   = get_queried_object();
		$titles = injil_help_seo_titles();
		$path   = $post && 'page' === $post->post_type ? get_page_uri( $post ) : '';
		return $titles[ $path ] ?? wp_strip_all_tags( get_the_title( $post ) );
	}
	if ( is_search() ) {
		/* translators: %s: the search terms */
		return sprintf( __( 'Search: %s', 'injil-help' ), get_search_query( false ) );
	}
	if ( is_404() ) {
		return __( 'Page not found', 'injil-help' );
	}
	if ( is_archive() ) {
		return wp_strip_all_tags( get_the_archive_title() );
	}
	return '';
}

/** The whole title: "<page part> | <site name>", under 60 characters. */
function injil_help_document_title() {
	$site = injil_help_title_fit( wp_strip_all_tags( get_bloginfo( 'name' ) ), INJIL_HELP_TITLE_MAX );
	$part = injil_help_title_fit( injil_help_title_part(), INJIL_HELP_TITLE_MAX - mb_strlen( ' | ' . $site ) );
	return '' !== $part ? $part . ' | ' . $site : $site;
}

add_filter(
	'pre_get_document_title',
	function ( $title ) {
		return injil_help_schema_enabled() ? injil_help_document_title() : $title;
	}
);

// With an SEO plugin in charge of the head, its title follows the same rule.
foreach ( array( 'rank_math/frontend/title', 'wpseo_title', 'seopress_titles_title' ) as $injil_help_title_hook ) {
	add_filter(
		$injil_help_title_hook,
		function () {
			return injil_help_document_title();
		},
		99
	);
}
unset( $injil_help_title_hook );

/**
 * A meta description of at most $max characters: the whole text when it fits, else the
 * sentences that fit (at least half the length), else the words that fit with an ellipsis.
 * The Google SEO Starter Guide asks for a sentence or two; search results show about 160.
 */
function injil_help_trim_description( $text, $max = 160 ) {
	$text = trim( preg_replace( '/\s+/', ' ', (string) $text ) );
	if ( mb_strlen( $text ) <= $max ) {
		return $text;
	}
	$cut = mb_substr( $text, 0, $max );
	if ( preg_match( '/^(.{' . (int) ( $max / 2 ) . ',})[.!?](\s|$)/u', $cut, $m ) ) {
		return trim( $m[1] ) . '.';
	}
	$cut = preg_replace( '/\s+\S*$/u', '', mb_substr( $text, 0, $max - 1 ) );
	return rtrim( $cut, ' ,;:' ) . '…';
}

function injil_help_meta_description() {
	$d = injil_help_seo_defaults();
	if ( is_front_page() ) {
		return injil_help_trim_description( $d['home_description'] );
	}
	if ( is_singular() ) {
		$post = get_queried_object();
		if ( has_excerpt( $post ) ) {
			return injil_help_trim_description( wp_strip_all_tags( get_the_excerpt( $post ) ) );
		}
		return injil_help_trim_description( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ) );
	}
	if ( is_category() || is_tag() ) {
		return injil_help_trim_description( wp_strip_all_tags( term_description() ) );
	}
	return injil_help_trim_description( get_bloginfo( 'description' ) );
}

/** Search result pages are not indexed; the guide asks that crawling be kept off them. */
add_filter(
	'wp_robots',
	function ( $robots ) {
		if ( is_search() ) {
			$robots['noindex'] = true;
		}
		return $robots;
	}
);

/**
 * The visible breadcrumb row at the top of every page below the front page: Home, the parent
 * pages, the page itself. It mirrors the BreadcrumbList in the structured data, as the guide
 * asks ("a row of internal links at the top or bottom of the page").
 */
function injil_help_breadcrumb_html( $post ) {
	$items = array( array( __( 'Home', 'injil-help' ), home_url( '/' ) ) );
	foreach ( array_reverse( get_post_ancestors( $post ) ) as $ancestor ) {
		$items[] = array( wp_strip_all_tags( get_the_title( $ancestor ) ), get_permalink( $ancestor ) );
	}
	$html = '<nav class="ih-breadcrumb" aria-label="' . esc_attr__( 'Breadcrumb', 'injil-help' ) . '"><ol>';
	foreach ( $items as $item ) {
		$html .= '<li><a href="' . esc_url( $item[1] ) . '">' . esc_html( $item[0] ) . '</a></li>';
	}
	$html .= '<li aria-current="page">' . esc_html( wp_strip_all_tags( get_the_title( $post ) ) ) . '</li></ol></nav>';
	return apply_filters( 'injil_help_breadcrumb_html', $html, $post ); // inc/rank-math.php swaps in Rank Math's trail
}

add_filter(
	'the_content',
	function ( $content ) {
		// Block templates render the Post Content block outside a classic loop, so in_the_loop() is not checked (as in inc/share.php); the queried object is matched instead.
		if ( ! is_singular( array( 'page', 'post' ) ) || is_front_page() || ! is_main_query() || doing_filter( 'get_the_excerpt' ) ) {
			return $content;
		}
		$post = get_queried_object();
		if ( ! $post || get_the_ID() !== $post->ID || false !== strpos( $content, 'class="ih-breadcrumb"' ) ) {
			return $content;
		}
		return injil_help_breadcrumb_html( $post ) . $content;
	},
	5
);

/* Meta description, canonical, Open Graph, Twitter. */
add_action(
	'wp_head',
	function () {
		if ( ! injil_help_schema_enabled() ) {
			return;
		}
		$desc  = trim( injil_help_meta_description() );
		$url   = is_singular() ? get_permalink() : ( is_front_page() ? home_url( '/' ) : '' );
		$title = wp_get_document_title();
		$img   = is_singular() && has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'large' ) : injil_help_default_og_image();

		if ( $desc ) {
			printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );
		}
		if ( $url && ! is_singular() ) {
			printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $url ) );
		}
		$og = array(
			'og:type'        => is_singular( 'post' ) || injil_help_is_pillar() ? 'article' : 'website',
			'og:site_name'   => get_bloginfo( 'name' ),
			'og:title'       => $title,
			'og:description' => $desc,
			'og:url'         => $url,
			'og:locale'      => str_replace( '-', '_', get_bloginfo( 'language' ) ),
			'og:image'       => $img,
		);
		foreach ( array_filter( $og ) as $k => $v ) {
			printf( '<meta property="%s" content="%s">' . "\n", esc_attr( $k ), esc_attr( $v ) );
		}
		printf( '<meta name="twitter:card" content="%s">' . "\n", $img ? 'summary_large_image' : 'summary' );
	},
	1
);
