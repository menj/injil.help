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
			'home_title'       => __( 'The Islamic Dilemma, Answered', 'injil-help' ),
			'home_description' => __( "The Islamic Dilemma says the Qur'an affirms the gospels it contradicts. A sourced answer: what the Injīl is, what the four gospels are, and the Qur'an as their measure.", 'injil-help' ),
			'pillar_title'     => __( 'The Islamic Dilemma, Answered: Qur\'an 5:47 and the Injīl', 'injil-help' ),
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

function injil_help_meta_description() {
	$d = injil_help_seo_defaults();
	if ( is_front_page() ) {
		return $d['home_description'];
	}
	if ( is_singular() ) {
		$post = get_queried_object();
		if ( has_excerpt( $post ) ) {
			return wp_strip_all_tags( get_the_excerpt( $post ) );
		}
		return wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 28, '…' );
	}
	if ( is_category() || is_tag() ) {
		return wp_strip_all_tags( term_description() );
	}
	return get_bloginfo( 'description' );
}

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
