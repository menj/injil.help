<?php
/**
 * SEO for the target keyword "the Islamic Dilemma".
 * Title, meta description, canonical, Open Graph, and WebSite / BreadcrumbList
 * schema. Every part steps aside when Yoast, Rank Math or SEOPress is active.
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
		$img   = is_singular() && has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'large' ) : apply_filters( 'injil_help_default_og_image', 'https://commons.wikimedia.org/wiki/Special:FilePath/Birmingham_Quran_manuscript.jpg?width=1200' );

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

/* WebSite and BreadcrumbList schema; Article "about" for the pillar page. */
add_filter(
	'injil_help_schema_graph',
	function ( $graph ) {
		if ( is_front_page() ) {
			$graph[] = array(
				'@type'           => 'WebSite',
				'@id'             => home_url( '/#website' ),
				'url'             => home_url( '/' ),
				'name'            => get_bloginfo( 'name' ),
				'alternateName'   => 'injil.help',
				'description'     => injil_help_seo_defaults()['home_description'],
				'inLanguage'      => get_bloginfo( 'language' ),
				'about'           => array(
					'@type' => 'Thing',
					'name'  => 'The Islamic Dilemma',
				),
				'potentialAction' => array(
					'@type'       => 'SearchAction',
					'target'      => home_url( '/?s={search_term_string}' ),
					'query-input' => 'required name=search_term_string',
				),
			);
		}
		if ( is_singular() && ! is_front_page() ) {
			$items = array(
				array(
					'@type'    => 'ListItem',
					'position' => 1,
					'name'     => get_bloginfo( 'name' ),
					'item'     => home_url( '/' ),
				),
			);
			$post  = get_queried_object();
			$pos   = 2;
			foreach ( array_reverse( get_post_ancestors( $post ) ) as $ancestor ) {
				$items[] = array(
					'@type'    => 'ListItem',
					'position' => $pos++,
					'name'     => get_the_title( $ancestor ),
					'item'     => get_permalink( $ancestor ),
				);
			}
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $pos,
				'name'     => wp_strip_all_tags( get_the_title( $post ) ),
				'item'     => get_permalink( $post ),
			);
			$graph[] = array(
				'@type'           => 'BreadcrumbList',
				'itemListElement' => $items,
			);
		}
		if ( injil_help_is_pillar() ) {
			foreach ( $graph as &$node ) {
				if ( 'Article' === ( $node['@type'] ?? '' ) ) {
					$node['about']    = array(
						array(
							'@type' => 'Thing',
							'name'  => 'The Islamic Dilemma',
						),
						array(
							'@type' => 'Thing',
							'name'  => 'Injil',
						),
					);
					$node['keywords'] = 'the Islamic Dilemma, Quran 5:47, Injil, tahrif, the four gospels';
				}
			}
		}
		return $graph;
	}
);
