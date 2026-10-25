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
define( 'INJIL_HELP_PILLAR_SLUG', 'the-islamic-dilemma' );

function injil_help_seo_defaults() {
	return apply_filters(
		'injil_help_seo_defaults',
		array(
			'home_title'       => __( 'The Islamic Dilemma, answered', 'injil-help' ),
			'home_description' => __( "The Islamic Dilemma says the Qur'an affirms the gospels it contradicts. A sourced answer: what the Injīl is, what the four gospels are, and the Qur'an as their measure.", 'injil-help' ),
			'pillar_title'     => __( 'The Islamic Dilemma, answered: Qur’an 5:47 and the Injīl', 'injil-help' ),
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

/* Title tag. */
add_filter(
	'document_title_parts',
	function ( $parts ) {
		if ( ! injil_help_schema_enabled() ) {
			return $parts;
		}
		$d = injil_help_seo_defaults();
		if ( is_front_page() ) {
			$parts['title'] = $d['home_title'];
			$parts['site']  = get_bloginfo( 'name' );
			unset( $parts['tagline'] );
		} elseif ( injil_help_is_pillar() ) {
			$parts['title'] = $d['pillar_title'];
		}
		return $parts;
	}
);

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
