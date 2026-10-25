<?php
/**
 * Structured data: one JSON-LD graph per page, built from the types Google Search
 * documents and that this site has content for.
 *
 *   Organization (logo, sameAs)     every page
 *   Person (the author)             every page
 *   WebSite (+ sitelinks search)    every page (the SearchAction on the front page)
 *   WebPage / CollectionPage /      every page; ProfilePage on About the author,
 *     ContactPage / ProfilePage /    ContactPage on Contact, CollectionPage on
 *     SearchResultsPage              section indexes, archives and search
 *   BreadcrumbList                  every page below the front page
 *   Article / BlogPosting           every article page and post
 *   ImageObject (licence metadata)  every manuscript image, from its caption
 *   FAQPage                         every page with FAQ items
 *   ItemList                        section indexes (their entries)
 *   Book                            the gospel sections, as the work they belong to
 *
 * Output is skipped when Yoast, Rank Math or SEOPress is active, unless Theme
 * Options > SEO forces it on. Filter injil_help_schema_graph to change the graph.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

function injil_help_schema_enabled() {
	$plugin_active = defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || defined( 'SEOPRESS_VERSION' );
	return (bool) apply_filters( 'injil_help_output_schema', ! $plugin_active );
}

/* ---------- Site-wide nodes ---------- */

function injil_help_schema_ids() {
	$home = home_url( '/' );
	return array(
		'org'     => $home . '#organization',
		'author'  => $home . '#author',
		'website' => $home . '#website',
		'logo'    => $home . '#logo',
		'book'    => home_url( '/gospel-according-to-islam/' ) . '#book',
	);
}

/** Author details: Theme Options > SEO, falling back to the post author. */
function injil_help_schema_author( $post = null ) {
	$name = injil_help_get_option( 'author_name' );
	$url  = injil_help_get_option( 'author_url' );
	if ( ! $name && $post instanceof WP_Post ) {
		$name = get_the_author_meta( 'display_name', $post->post_author );
		$url  = $url ?: get_the_author_meta( 'user_url', $post->post_author );
	}
	$same = array_values( array_filter( array_map( 'trim', explode( "\n", (string) injil_help_get_option( 'same_as' ) ) ) ) );
	return array( $name ?: get_bloginfo( 'name' ), $url, $same );
}

function injil_help_schema_logo_url() {
	$custom = injil_help_get_option( 'org_logo' );
	if ( $custom ) {
		return $custom;
	}
	if ( has_site_icon() && ! injil_help_get_option( 'favicon_force' ) ) {
		return get_site_icon_url( 512 );
	}
	return get_stylesheet_directory_uri() . '/assets/brandmark.svg';
}

function injil_help_schema_site_nodes( $post = null ) {
	$ids  = injil_help_schema_ids();
	$home = home_url( '/' );
	list( $name, $url, $same ) = injil_help_schema_author( $post );

	$org = array(
		'@type' => 'Organization',
		'@id'   => $ids['org'],
		'name'  => get_bloginfo( 'name' ),
		'url'   => $home,
		'logo'  => array(
			'@type'      => 'ImageObject',
			'@id'        => $ids['logo'],
			'url'        => injil_help_schema_logo_url(),
			'contentUrl' => injil_help_schema_logo_url(),
			'caption'    => get_bloginfo( 'name' ),
		),
		'image' => array( '@id' => $ids['logo'] ),
		'founder' => array( '@id' => $ids['author'] ),
	);
	if ( $same ) {
		$org['sameAs'] = $same;
	}
	$person = array(
		'@type'    => 'Person',
		'@id'      => $ids['author'],
		'name'     => $name,
		'worksFor' => array( '@id' => $ids['org'] ),
	);
	if ( $url ) {
		$person['url'] = $url;
	}
	if ( $same ) {
		$person['sameAs'] = $same;
	}
	$d       = injil_help_seo_defaults();
	$website = array(
		'@type'         => 'WebSite',
		'@id'           => $ids['website'],
		'url'           => $home,
		'name'          => get_bloginfo( 'name' ),
		'alternateName' => wp_parse_url( $home, PHP_URL_HOST ),
		'description'   => $d['home_description'],
		'inLanguage'    => get_bloginfo( 'language' ),
		'publisher'     => array( '@id' => $ids['org'] ),
		'about'         => array(
			'@type' => 'Thing',
			'name'  => 'The Islamic Dilemma',
		),
	);
	if ( is_front_page() ) {
		$website['potentialAction'] = array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => $home . '?s={search_term_string}',
			),
			'query-input' => 'required name=search_term_string',
		);
	}
	return array( $org, $person, $website );
}

/* ---------- Content parsing ---------- */

/** Replace core/pattern references with the pattern's blocks, so the parsers see template content. */
function injil_help_expand_patterns( $blocks ) {
	$out = array();
	foreach ( $blocks as $block ) {
		if ( 'core/pattern' === $block['blockName'] && ! empty( $block['attrs']['slug'] ) ) {
			$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( $block['attrs']['slug'] );
			if ( $pattern ) {
				$out = array_merge( $out, injil_help_expand_patterns( parse_blocks( $pattern['content'] ) ) );
			}
			continue;
		}
		if ( ! empty( $block['innerBlocks'] ) ) {
			$block['innerBlocks'] = injil_help_expand_patterns( $block['innerBlocks'] );
		}
		$out[] = $block;
	}
	return $out;
}

/** FAQ items: core/details styled "FAQ item", and "Short answer" groups (heading + .ih-answer-text). */
function injil_help_collect_faq( $blocks, &$items ) {
	foreach ( $blocks as $block ) {
		$class = $block['attrs']['className'] ?? '';
		if ( 'core/details' === $block['blockName'] && false !== strpos( $class, 'is-style-faq' ) ) {
			preg_match( '#<summary[^>]*>(.*?)</summary>#s', $block['innerHTML'], $m );
			$question = trim( wp_strip_all_tags( $m[1] ?? '' ) );
			$answer   = '';
			foreach ( $block['innerBlocks'] as $inner ) {
				$answer .= render_block( $inner );
			}
			$answer = trim( wp_strip_all_tags( $answer ) );
			if ( $question && $answer ) {
				$items[] = array( html_entity_decode( $question, ENT_QUOTES ), html_entity_decode( $answer, ENT_QUOTES ) );
			}
		}
		if ( 'core/group' === $block['blockName'] && false !== strpos( $class, 'is-style-answer-item' ) ) {
			$question = '';
			$answer   = '';
			foreach ( $block['innerBlocks'] as $inner ) {
				$inner_class = $inner['attrs']['className'] ?? '';
				if ( 'core/heading' === $inner['blockName'] && '' === $question ) {
					$question = trim( wp_strip_all_tags( render_block( $inner ) ) );
				} elseif ( false !== strpos( $inner_class, 'ih-answer-text' ) ) {
					$answer .= ' ' . wp_strip_all_tags( render_block( $inner ) );
				}
			}
			$answer = trim( $answer );
			if ( $question && $answer ) {
				$items[] = array( html_entity_decode( $question, ENT_QUOTES ), html_entity_decode( $answer, ENT_QUOTES ) );
			}
			continue;
		}
		if ( ! empty( $block['innerBlocks'] ) ) {
			injil_help_collect_faq( $block['innerBlocks'], $items );
		}
	}
}

/**
 * Manuscript images with their licence, from each figure's caption: the image URL,
 * the Commons page it links to (acquireLicensePage), the credit line, the licence
 * named in the caption, and the photographer where one is named.
 */
function injil_help_collect_images( $html ) {
	$images = array();
	if ( ! preg_match_all( '#<figure[^>]*class="[^"]*wp-block-image[^"]*"[^>]*>(.*?)</figure>#s', $html, $figs ) ) {
		return $images;
	}
	foreach ( $figs[1] as $fig ) {
		if ( ! preg_match( '#<img[^>]+src="([^"]+)"[^>]*>#', $fig, $img ) ) {
			continue;
		}
		preg_match( '#alt="([^"]*)"#', $img[0], $alt );
		preg_match( '#<a[^>]+href="([^"]+)"#', $fig, $a );
		preg_match( '#<figcaption[^>]*>(.*?)</figcaption>#s', $fig, $cap );
		$caption = isset( $cap[1] ) ? html_entity_decode( trim( wp_strip_all_tags( $cap[1] ) ), ENT_QUOTES ) : '';
		$node    = array(
			'@type'      => 'ImageObject',
			'@id'        => $img[1],
			'contentUrl' => $img[1],
			'url'        => $img[1],
		);
		if ( ! empty( $alt[1] ) ) {
			$node['name']        = html_entity_decode( $alt[1], ENT_QUOTES );
			$node['description'] = html_entity_decode( $alt[1], ENT_QUOTES );
		}
		if ( $caption ) {
			$node['caption']    = $caption;
			$node['creditText'] = $caption;
		}
		if ( ! empty( $a[1] ) ) {
			$node['acquireLicensePage'] = $a[1];
		}
		if ( preg_match( '#CC BY(?:-SA)? ([0-9.]+)#i', $caption, $cc ) ) {
			$node['license'] = 'https://creativecommons.org/licenses/by' . ( false !== stripos( $cc[0], 'SA' ) ? '-sa' : '' ) . '/' . $cc[1] . '/';
		} elseif ( false !== stripos( $caption, 'public domain' ) ) {
			$node['license']         = 'https://creativecommons.org/publicdomain/mark/1.0/';
			$node['copyrightNotice'] = 'Public domain';
		}
		if ( preg_match( '#Photo: ([^.,]+)#', $caption, $ph ) ) {
			$node['creator']         = array(
				'@type' => 'Person',
				'name'  => trim( $ph[1] ),
			);
			$node['copyrightNotice'] = trim( $ph[1] );
		}
		$images[ $img[1] ] = $node;
	}
	return array_values( $images );
}

/** Section index entries (the cards), for an ItemList. */
function injil_help_collect_entries( $html ) {
	$list = array();
	if ( preg_match_all( '#<div class="wp-block-group ih-entry[^"]*">.*?<h3[^>]*><a href="([^"]+)">(.*?)</a></h3>#s', $html, $m, PREG_SET_ORDER ) ) {
		foreach ( $m as $i => $e ) {
			$list[] = array(
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'url'      => home_url( $e[1] ),
				'name'     => html_entity_decode( wp_strip_all_tags( $e[2] ), ENT_QUOTES ),
			);
		}
	}
	return $list;
}

/* ---------- Page classification ---------- */

function injil_help_schema_page_type( $post, $entries = array() ) {
	if ( is_front_page() ) {
		return 'WebPage';
	}
	if ( is_search() ) {
		return 'SearchResultsPage';
	}
	if ( is_archive() || is_home() ) {
		return 'CollectionPage';
	}
	if ( $post instanceof WP_Post && 'page' === $post->post_type ) {
		switch ( $post->post_name ) {
			case 'about-the-author':
				return 'ProfilePage';
			case 'contact':
				return 'ContactPage';
			case 'sitemap':
				return 'CollectionPage';
		}
		if ( count( $entries ) >= 3 ) {
			return 'CollectionPage';
		}
	}
	return 'WebPage';
}

/** Pages that are articles: singular content that is not an index, a utility page or the front page. */
function injil_help_schema_is_article( $post, $page_type = 'WebPage' ) {
	if ( ! $post instanceof WP_Post || is_front_page() || 'WebPage' !== $page_type ) {
		return false;
	}
	if ( 'post' === $post->post_type ) {
		return true;
	}
	if ( 'page' !== $post->post_type ) {
		return false;
	}
	$utility = array( 'contact', 'sitemap', 'privacy-policy', 'terms-of-use', 'credits', 'about-the-author', 'answers', 'corrections' );
	return ! in_array( $post->post_name, $utility, true );
}

/* ---------- The graph ---------- */

function injil_help_schema_graph() {
	$post  = get_queried_object();
	$post  = $post instanceof WP_Post ? $post : null;
	$ids   = injil_help_schema_ids();
	$graph = injil_help_schema_site_nodes( $post );
	$lang  = get_bloginfo( 'language' );

	// The page's content: the front page template, or the post.
	$content = $post ? $post->post_content : '';
	if ( is_front_page() ) {
		$template = get_block_template( get_stylesheet() . '//front-page' );
		$content  = $template ? $template->content : $content;
	}
	$blocks = $content ? injil_help_expand_patterns( parse_blocks( $content ) ) : array();
	$html   = '';
	foreach ( $blocks as $b ) {
		$html .= render_block( $b );
	}

	// URL and names.
	if ( is_front_page() ) {
		$url = home_url( '/' );
	} elseif ( is_singular() && $post ) {
		$url = get_permalink( $post );
	} elseif ( is_search() ) {
		$url = get_search_link();
	} elseif ( is_archive() ) {
		$url = get_term_link( get_queried_object() );
		$url = is_wp_error( $url ) ? home_url( add_query_arg( array() ) ) : $url;
	} else {
		$url = home_url( add_query_arg( array() ) );
	}
	$webpage_id    = $url . '#webpage';
	$breadcrumb_id = $url . '#breadcrumb';
	$title         = is_front_page() ? injil_help_seo_defaults()['home_title'] : ( $post ? wp_strip_all_tags( get_the_title( $post ) ) : wp_strip_all_tags( wp_get_document_title() ) );
	$description   = trim( injil_help_meta_description() );
	$images        = injil_help_collect_images( $html );
	$featured      = $post && has_post_thumbnail( $post ) ? get_the_post_thumbnail_url( $post, 'large' ) : '';
	$primary_image = $featured ?: ( $images ? $images[0]['contentUrl'] : injil_help_default_og_image() );

	// WebPage.
	$entries = $html ? injil_help_collect_entries( $html ) : array();
	$webpage = array(
		'@type'      => injil_help_schema_page_type( $post, $entries ),
		'@id'        => $webpage_id,
		'url'        => $url,
		'name'       => $title,
		'isPartOf'   => array( '@id' => $ids['website'] ),
		'inLanguage' => $lang,
		'primaryImageOfPage' => array( '@id' => $primary_image ),
	);
	if ( $description ) {
		$webpage['description'] = $description;
	}
	if ( $post && is_singular() ) {
		$webpage['datePublished'] = get_the_date( 'c', $post );
		$webpage['dateModified']  = get_the_modified_date( 'c', $post );
	}
	if ( ! is_front_page() ) {
		$webpage['breadcrumb'] = array( '@id' => $breadcrumb_id );
	}
	if ( is_front_page() || injil_help_is_pillar() ) {
		$webpage['about'] = array(
			'@type' => 'Thing',
			'name'  => 'The Islamic Dilemma',
		);
	}
	if ( 'ProfilePage' === $webpage['@type'] ) {
		$webpage['mainEntity'] = array( '@id' => $ids['author'] );
	}
	if ( 'CollectionPage' === $webpage['@type'] ) {
		if ( $entries ) {
			$webpage['mainEntity'] = array(
				'@type'           => 'ItemList',
				'name'            => $title,
				'numberOfItems'   => count( $entries ),
				'itemListElement' => $entries,
			);
		}
	}
	$graph[] = $webpage;

	// Breadcrumb.
	if ( ! is_front_page() ) {
		$crumbs = array(
			array(
				'@type'    => 'ListItem',
				'position' => 1,
				'name'     => get_bloginfo( 'name' ),
				'item'     => home_url( '/' ),
			),
		);
		$pos    = 2;
		if ( $post && is_singular() ) {
			foreach ( array_reverse( get_post_ancestors( $post ) ) as $ancestor ) {
				$crumbs[] = array(
					'@type'    => 'ListItem',
					'position' => $pos++,
					'name'     => wp_strip_all_tags( get_the_title( $ancestor ) ),
					'item'     => get_permalink( $ancestor ),
				);
			}
		}
		$crumbs[] = array(
			'@type'    => 'ListItem',
			'position' => $pos,
			'name'     => $title,
			'item'     => $url,
		);
		$graph[]  = array(
			'@type'           => 'BreadcrumbList',
			'@id'             => $breadcrumb_id,
			'itemListElement' => $crumbs,
		);
	}

	// Article.
	if ( $post && is_singular() && injil_help_schema_is_article( $post, $webpage['@type'] ) ) {
		$headline = $title;
		if ( strlen( $headline ) > 110 ) {
			$headline = rtrim( substr( $headline, 0, 109 ) ) . '…';
		}
		$image_list = array_values( array_unique( array_filter( array_merge( array( $featured ), wp_list_pluck( $images, 'contentUrl' ), array( injil_help_default_og_image() ) ) ) ) );
		$parent     = $post->post_parent ? get_post( $post->post_parent ) : null;
		$article    = array(
			'@type'            => 'post' === $post->post_type ? 'BlogPosting' : 'Article',
			'@id'              => $url . '#article',
			'headline'         => $headline,
			'name'             => $title,
			'url'              => $url,
			'mainEntityOfPage' => array( '@id' => $webpage_id ),
			'isPartOf'         => array( '@id' => $webpage_id ),
			'image'            => $image_list,
			'author'           => array( '@id' => $ids['author'] ),
			'publisher'        => array( '@id' => $ids['org'] ),
			'datePublished'    => get_the_date( 'c', $post ),
			'dateModified'     => get_the_modified_date( 'c', $post ),
			'inLanguage'       => $lang,
			'isAccessibleForFree' => true,
			'wordCount'        => str_word_count( wp_strip_all_tags( $html ) ),
		);
		if ( $description ) {
			$article['description'] = $description;
		}
		if ( $parent ) {
			$article['articleSection'] = wp_strip_all_tags( get_the_title( $parent ) );
		}
		if ( injil_help_is_pillar() ) {
			$article['about']    = array(
				array( '@type' => 'Thing', 'name' => 'The Islamic Dilemma' ),
				array( '@type' => 'Thing', 'name' => 'Injil' ),
			);
			$article['keywords'] = 'the Islamic Dilemma, Quran 5:47, Injil, tahrif, the four gospels';
		}
		// The 27 sections and the Introduction belong to a book.
		if ( $parent && 'gospel-according-to-islam' === $parent->post_name && ( 'introduction' === $post->post_name || ( function_exists( 'injil_help_gospel_section_number' ) && injil_help_gospel_section_number( $post->post_name ) ) ) ) {
			$article['isPartOf'] = array( '@id' => $ids['book'] );
			$graph[]             = array(
				'@type'         => 'Book',
				'@id'           => $ids['book'],
				'name'          => 'The Gospel According to Islam',
				'url'           => home_url( '/gospel-according-to-islam/' ),
				'author'        => array(
					'@type' => 'Person',
					'name'  => 'Ahmad Shafaat',
				),
				'publisher'     => array(
					'@type' => 'Organization',
					'name'  => 'Vantage Press',
				),
				'datePublished' => '1985',
				'inLanguage'    => 'en',
			);
		}
		$graph[] = $article;
	}

	// Images with licence metadata.
	foreach ( $images as $image ) {
		$image['representativeOfPage'] = $image['contentUrl'] === $primary_image;
		$graph[]                       = $image;
	}
	if ( $primary_image === injil_help_default_og_image() ) {
		$graph[] = array(
			'@type'                => 'ImageObject',
			'@id'                  => $primary_image,
			'contentUrl'           => $primary_image,
			'url'                  => $primary_image,
			'width'                => 1280,
			'height'               => 833,
			'caption'              => 'Birmingham Qur’an manuscript, Cadbury Research Library, University of Birmingham',
			'creditText'           => 'University of Birmingham, via Wikimedia Commons',
			'license'              => 'https://creativecommons.org/publicdomain/mark/1.0/',
			'copyrightNotice'      => 'Public domain',
			'acquireLicensePage'   => 'https://commons.wikimedia.org/wiki/File:Birmingham_Quran_manuscript.jpg',
			'representativeOfPage' => true,
		);
	}

	// FAQ.
	$faq = array();
	if ( $blocks ) {
		injil_help_collect_faq( $blocks, $faq );
	}
	if ( $faq ) {
		$main = array();
		foreach ( $faq as $qa ) {
			$main[] = array(
				'@type'          => 'Question',
				'name'           => $qa[0],
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $qa[1],
				),
			);
		}
		$graph[] = array(
			'@type'      => 'FAQPage',
			'@id'        => $url . '#faq',
			'url'        => $url,
			'isPartOf'   => array( '@id' => $webpage_id ),
			'inLanguage' => $lang,
			'mainEntity' => $main,
		);
	}

	return apply_filters( 'injil_help_schema_graph', $graph );
}

add_action(
	'wp_head',
	function () {
		if ( is_404() || ! injil_help_schema_enabled() ) {
			return;
		}
		$graph = injil_help_schema_graph();
		if ( ! $graph ) {
			return;
		}
		printf(
			"<script type=\"application/ld+json\">%s</script>\n",
			wp_json_encode(
				array(
					'@context' => 'https://schema.org',
					'@graph'   => $graph,
				),
				JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
			)
		);
	},
	5
);
