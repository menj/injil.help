<?php
/**
 * Rank Math SEO. When the plugin is active the theme hands it the head (title, meta
 * description, canonical, robots, Open Graph, the sitemap) and works inside it instead of
 * beside it:
 *
 *   - breadcrumbs: the theme declares rank-math-breadcrumbs support and, while Rank Math's
 *     breadcrumbs are on, prints Rank Math's own trail in the theme's breadcrumb row, so the
 *     visible trail and Rank Math's BreadcrumbList are the same list;
 *   - structured data: one graph, Rank Math's. The theme adds to it, through rank_math/json_ld,
 *     every node Rank Math does not make (the gospel as Book and Chapter, the commentaries and
 *     their translations, the glossary, tables, the dataset, the timeline's events, quotations,
 *     the FAQ, the two subjects, the site navigation, the manuscript images with their licence)
 *     and enriches Rank Math's Article with its citations and subjects. References are pointed
 *     at Rank Math's own WebPage, publisher and Article, so nothing dangles and nothing is
 *     printed twice;
 *   - the front-page description: when Rank Math has none, the theme's is used.
 *
 * Theme Options > SEO > Theme SEO output: "Automatic" works this way; "Always on" prints the
 * theme's own head and graph as well; "Always off" leaves everything to Rank Math.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

/** True when Rank Math SEO is loaded. */
function injil_help_rank_math_active() {
	return defined( 'RANK_MATH_VERSION' ) || class_exists( 'RankMath' );
}

/** True when the theme should work inside Rank Math: the plugin is active and the SEO option is Automatic. */
function injil_help_rank_math_mode() {
	return injil_help_rank_math_active() && 'auto' === injil_help_get_option( 'schema' );
}

/** True when Rank Math's breadcrumbs are on and its template function exists. */
function injil_help_rank_math_breadcrumbs_on() {
	if ( ! injil_help_rank_math_active() || ! function_exists( 'rank_math_get_breadcrumbs' ) ) {
		return false;
	}
	return class_exists( '\RankMath\Helper' ) && method_exists( '\RankMath\Helper', 'is_breadcrumbs_enabled' )
		? (bool) \RankMath\Helper::is_breadcrumbs_enabled()
		: true;
}

add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'rank-math-breadcrumbs' );
	}
);

/** Rank Math's trail in the theme's breadcrumb row, with the theme's class so it is styled the same. */
add_filter(
	'injil_help_breadcrumb_html',
	function ( $html ) {
		if ( ! injil_help_rank_math_mode() || ! injil_help_rank_math_breadcrumbs_on() ) {
			return $html;
		}
		$trail = rank_math_get_breadcrumbs(
			array(
				'wrap_before' => '<nav class="ih-breadcrumb rank-math-breadcrumb" aria-label="' . esc_attr__( 'Breadcrumb', 'injil-help' ) . '"><p>',
				'wrap_after'  => '</p></nav>',
			)
		);
		return $trail ? $trail : $html;
	}
);

/** The front-page description, when Rank Math's Titles & Meta leaves it empty. */
add_filter(
	'rank_math/frontend/description',
	function ( $description ) {
		if ( '' === trim( (string) $description ) && is_front_page() && injil_help_rank_math_mode() ) {
			return injil_help_trim_description( injil_help_seo_defaults()['home_description'] );
		}
		return $description;
	}
);

/** Replace every {"@id": old} reference in a node with {"@id": new}, or drop the property when new is empty. */
function injil_help_rank_math_repoint( $node, $map ) {
	if ( ! is_array( $node ) ) {
		return $node;
	}
	foreach ( $node as $key => $value ) {
		if ( is_array( $value ) && 1 === count( $value ) && isset( $value['@id'] ) && array_key_exists( $value['@id'], $map ) ) {
			if ( '' === $map[ $value['@id'] ] ) {
				unset( $node[ $key ] );
			} else {
				$node[ $key ] = array( '@id' => $map[ $value['@id'] ] );
			}
		} elseif ( is_array( $value ) ) {
			$node[ $key ] = injil_help_rank_math_repoint( $value, $map );
		}
	}
	return $node;
}

/**
 * The theme's nodes merged into Rank Math's graph. Rank Math keeps what it makes (publisher,
 * WebSite, WebPage, BreadcrumbList, primary image, author, its Article); the theme adds the rest.
 */
function injil_help_rank_math_json_ld( $data, $jsonld = null ) {
		if ( ! injil_help_rank_math_mode() || is_404() || ! function_exists( 'injil_help_schema_graph' ) ) {
			return $data;
		}
		$ids   = injil_help_schema_ids();
		$graph = injil_help_schema_graph();

		// What Rank Math already holds.
		$rm_ids     = array();
		$rm_types   = array();
		$rm_article = '';
		foreach ( (array) $data as $key => $entity ) {
			if ( ! is_array( $entity ) ) {
				continue;
			}
			if ( isset( $entity['@id'] ) ) {
				$rm_ids[ $entity['@id'] ] = true;
			}
			foreach ( (array) ( $entity['@type'] ?? array() ) as $type ) {
				$rm_types[ $type ] = true;
				if ( ! $rm_article && in_array( $type, array( 'Article', 'BlogPosting', 'NewsArticle', 'ScholarlyArticle' ), true ) ) {
					$rm_article = $key;
				}
			}
		}
		$publisher = isset( $data['publisher']['@id'] ) ? $data['publisher']['@id'] : '';
		$webpage   = isset( $data['WebPage']['@id'] ) ? $data['WebPage']['@id'] : '';

		// The theme's own copies of what Rank Math makes are left out.
		$own_page = '';
		$own_art  = '';
		$skip     = array( $ids['org'] => true, $ids['author'] => true, $ids['website'] => true );
		foreach ( $graph as $node ) {
			$types = (array) ( $node['@type'] ?? array() );
			$id    = $node['@id'] ?? '';
			if ( array_intersect( $types, array( 'WebPage', 'CollectionPage', 'ProfilePage', 'AboutPage', 'ContactPage', 'SearchResultsPage' ) ) ) {
				$own_page    = $id;
				$skip[ $id ] = true;
			} elseif ( array_intersect( $types, array( 'BreadcrumbList' ) ) ) {
				$skip[ $id ] = true;
			} elseif ( array_intersect( $types, array( 'Article', 'BlogPosting', 'ScholarlyArticle' ) ) ) {
				$own_art     = $id;
				$skip[ $id ] = true;
				// Its citations, subjects and place in a book go onto Rank Math's Article.
				if ( $rm_article ) {
					foreach ( array( 'citation', 'about', 'speakable' ) as $prop ) {
						if ( isset( $node[ $prop ] ) && ! isset( $data[ $rm_article ][ $prop ] ) ) {
							$data[ $rm_article ][ $prop ] = $node[ $prop ];
						}
					}
					// A chapter of the gospel or a commentary's translation: Rank Math's Article belongs to it as well as to its WebPage.
					$part = $node['isPartOf']['@id'] ?? '';
					if ( $part && $part !== $own_page ) {
						$have = isset( $data[ $rm_article ]['isPartOf'] ) ? $data[ $rm_article ]['isPartOf'] : array();
						$have = isset( $have['@id'] ) ? array( $have ) : (array) $have;
						$have[] = array( '@id' => $part );
						$data[ $rm_article ]['isPartOf'] = $have;
					}
				}
			}
		}

		$map = array(
			$ids['org']    => $publisher,
			$ids['author'] => $publisher,
		);
		if ( $own_page ) {
			$map[ $own_page ] = $webpage;
		}
		if ( $own_art ) {
			$map[ $own_art ] = $rm_article && isset( $data[ $rm_article ]['@id'] ) ? $data[ $rm_article ]['@id'] : '';
		}

		$n = 0;
		foreach ( $graph as $node ) {
			$id    = $node['@id'] ?? '';
			$types = (array) ( $node['@type'] ?? array() );
			if ( ( $id && ( isset( $skip[ $id ] ) || isset( $rm_ids[ $id ] ) ) ) || ( in_array( 'FAQPage', $types, true ) && isset( $rm_types['FAQPage'] ) ) ) {
				continue;
			}
			$data[ 'injil-help-' . ( ++$n ) ] = injil_help_rank_math_repoint( $node, $map );
		}
		if ( $rm_article ) {
			$data[ $rm_article ] = injil_help_rank_math_repoint( $data[ $rm_article ], $map );
		}
		return $data;
}
add_filter( 'rank_math/json_ld', 'injil_help_rank_math_json_ld', 50, 2 );
