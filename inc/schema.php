<?php
/**
 * Structured data: one JSON-LD graph per page, built from every schema.org type the
 * site's content supports (https://schema.org), whether or not a search engine shows it.
 *
 *   Organization (logo, sameAs, ContactPoint)   every page
 *   Person (the author)                          every page
 *   WebSite (+ SearchAction, about)              every page
 *   SiteNavigationElement (ItemList)             every page, from the header navigation
 *   WebPage / CollectionPage (+ ItemList) /      every page; ProfilePage + AboutPage on About the
 *     ProfilePage / AboutPage / ContactPage /    author, ContactPage on Contact, CollectionPage on
 *     SearchResultsPage                          section indexes, the sitemap, archives and search
 *   BreadcrumbList                               every page below the front page
 *   Article / BlogPosting (+ speakable,          every article page and post
 *     citation, about)
 *   Book + Chapter                               The Gospel According to Islam and its 27 sections
 *   Book (Arabic) + CreativeWork (translation)   each classical commentary page, with its author
 *   DefinedTermSet + DefinedTerm                 the glossary
 *   Table                                        every page with a comparison table
 *   Dataset                                      the sayings-order test
 *   Event                                        every dated entry of the timeline
 *   Quotation                                    every quotation block on a page
 *   FAQPage (Question, Answer)                   every page with FAQ items
 *   ImageObject (licence metadata)               every manuscript image, from its caption
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
		'nav'     => $home . '#navigation',
		'jesus'   => $home . '#isa-ibn-maryam',
		'quran'   => $home . '#quran',
		'book'    => home_url( '/gospel-according-to-islam/' ) . '#book',
	);
}

/** Author details: Theme Options > SEO, falling back to the site name. The post author's name is never used. */
function injil_help_schema_author( $post = null ) {
	$name = injil_help_get_option( 'author_name' );
	$url  = injil_help_get_option( 'author_url' );
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

/** The two subjects every page is about: ʿĪsā ibn Maryam and the Qur'an, with their Wikidata identifiers. */
function injil_help_schema_subjects() {
	$ids = injil_help_schema_ids();
	return array(
		array(
			'@type'         => 'Person',
			'@id'           => $ids['jesus'],
			'name'          => 'ʿĪsā ibn Maryam',
			'alternateName' => array( 'Jesus', 'Jesus of Nazareth', 'Jesus, son of Mary' ),
			'sameAs'        => array( 'https://www.wikidata.org/wiki/Q302' ),
		),
		array(
			'@type'      => 'Book',
			'@id'        => $ids['quran'],
			'name'       => 'The Qur’an',
			'alternateName' => 'al-Qurʾān',
			'inLanguage' => 'ar',
			'sameAs'     => array( 'https://www.wikidata.org/wiki/Q428' ),
		),
	);
}

function injil_help_schema_site_nodes( $post = null ) {
	$ids  = injil_help_schema_ids();
	$home = home_url( '/' );
	list( $name, $url, $same ) = injil_help_schema_author( $post );

	$org = array(
		'@type'        => 'Organization',
		'@id'          => $ids['org'],
		'name'         => get_bloginfo( 'name' ),
		'url'          => $home,
		'logo'         => array(
			'@type'      => 'ImageObject',
			'@id'        => $ids['logo'],
			'url'        => injil_help_schema_logo_url(),
			'contentUrl' => injil_help_schema_logo_url(),
			'caption'    => get_bloginfo( 'name' ),
		),
		'image'        => array( '@id' => $ids['logo'] ),
		'founder'      => array( '@id' => $ids['author'] ),
		'contactPoint' => array(
			'@type'       => 'ContactPoint',
			'contactType' => 'editorial',
			'url'         => home_url( '/contact/' ),
			'availableLanguage' => array( 'en', 'ms' ),
		),
	);
	if ( $same ) {
		$org['sameAs'] = $same;
	}
	$person = array(
		'@type'    => 'Person',
		'@id'      => $ids['author'],
		'name'     => $name,
		'worksFor' => array( '@id' => $ids['org'] ),
		'jobTitle' => 'Apologist',
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
		'author'        => array( '@id' => $ids['author'] ),
		'copyrightHolder' => array( '@id' => $ids['author'] ),
		'isAccessibleForFree' => true,
		'about'         => array(
			array( '@type' => 'Thing', 'name' => 'The Islamic Dilemma' ),
			array( '@id' => $ids['jesus'] ),
			array( '@id' => $ids['quran'] ),
		),
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => $home . '?s={search_term_string}',
			),
			'query-input' => 'required name=search_term_string',
		),
	);
	return array_merge( array( $org, $person, $website ), injil_help_schema_subjects(), array( injil_help_schema_navigation() ) );
}

/** The header navigation as an ItemList of SiteNavigationElement, read from parts/header.html. */
function injil_help_schema_navigation() {
	$ids   = injil_help_schema_ids();
	$items = array();
	$part  = get_stylesheet_directory() . '/parts/header.html';
	if ( is_readable( $part ) && preg_match_all( '~<!-- wp:navigation-link \{"label":"(.*?)","title":"(.*?)","url":"(.*?)"~u', (string) file_get_contents( $part ), $m, PREG_SET_ORDER ) ) {
		foreach ( $m as $i => $link ) {
			$label   = html_entity_decode( wp_strip_all_tags( str_replace( '\\"', '"', $link[1] ) ), ENT_QUOTES );
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'item'     => array(
					'@type'       => 'SiteNavigationElement',
					'name'        => trim( preg_replace( '/\s*(ΕΥΑΓΓΕΛΙΟΝ|ΠΑΥΛΟΣ|ΑΠΟΛΟΓΙΑ|[\x{0600}-\x{06FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}\s]+)$/u', '', $label ) ),
					'description' => html_entity_decode( str_replace( '\\"', '"', $link[2] ), ENT_QUOTES ),
					'url'         => home_url( $link[3] ),
				),
			);
		}
	}
	return array(
		'@type'           => 'ItemList',
		'@id'             => $ids['nav'],
		'name'            => 'Site navigation',
		'itemListElement' => $items,
	);
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
			if ( preg_match( '#<summary>(.*?)</summary>#s', $block['innerHTML'], $q ) ) {
				$answer = '';
				foreach ( $block['innerBlocks'] as $inner ) {
					$answer .= ' ' . wp_strip_all_tags( render_block( $inner ) );
				}
				$items[] = array( html_entity_decode( wp_strip_all_tags( $q[1] ), ENT_QUOTES ), trim( preg_replace( '/\s+/', ' ', html_entity_decode( $answer, ENT_QUOTES ) ) ) );
			}
			continue;
		}
		if ( 'core/group' === $block['blockName'] && false !== strpos( $class, 'is-style-answer-item' ) ) {
			$q = '';
			$a = '';
			foreach ( $block['innerBlocks'] as $inner ) {
				if ( 'core/heading' === $inner['blockName'] && ! $q ) {
					$q = html_entity_decode( wp_strip_all_tags( $inner['innerHTML'] ), ENT_QUOTES );
				} elseif ( false !== strpos( $inner['attrs']['className'] ?? '', 'ih-answer-text' ) ) {
					$a = html_entity_decode( wp_strip_all_tags( render_block( $inner ) ), ENT_QUOTES );
				}
			}
			if ( $q && $a ) {
				$items[] = array( $q, trim( preg_replace( '/\s+/', ' ', $a ) ) );
			}
			continue;
		}
		if ( ! empty( $block['innerBlocks'] ) ) {
			injil_help_collect_faq( $block['innerBlocks'], $items );
		}
	}
}

/** Quotation blocks (core/quote) on the page: the text and, where given, who is cited. */
function injil_help_collect_quotes( $blocks, &$quotes ) {
	foreach ( $blocks as $block ) {
		if ( 'core/quote' === $block['blockName'] ) {
			$text = '';
			foreach ( $block['innerBlocks'] as $inner ) {
				$text .= ' ' . wp_strip_all_tags( render_block( $inner ) );
			}
			$text = trim( preg_replace( '/\s+/', ' ', html_entity_decode( $text, ENT_QUOTES ) ) );
			if ( $text ) {
				$quote = array(
					'@type' => 'Quotation',
					'text'  => $text,
				);
				if ( preg_match( '#<cite>(.*?)</cite>#s', $block['innerHTML'], $c ) ) {
					$quote['creator'] = array(
						'@type' => 'Thing',
						'name'  => html_entity_decode( wp_strip_all_tags( $c[1] ), ENT_QUOTES ),
					);
				}
				$quotes[] = $quote;
			}
			continue;
		}
		if ( ! empty( $block['innerBlocks'] ) ) {
			injil_help_collect_quotes( $block['innerBlocks'], $quotes );
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
		} elseif ( false !== stripos( $caption, 'CC0' ) ) {
			$node['license']         = 'https://creativecommons.org/publicdomain/zero/1.0/';
			$node['copyrightNotice'] = 'CC0';
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

/** Section index entries (the cards) and the sitemap's page list, for an ItemList. */
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
	} elseif ( preg_match( '#<ul[^>]*class="[^"]*wp-block-page-list[^"]*"[^>]*>(.*)</ul>#s', $html, $ul ) && preg_match_all( '#<a[^>]+href="([^"]+)"[^>]*>(.*?)</a>#s', $ul[1], $m, PREG_SET_ORDER ) ) {
		foreach ( $m as $i => $e ) {
			$list[] = array(
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'url'      => $e[1],
				'name'     => html_entity_decode( wp_strip_all_tags( $e[2] ), ENT_QUOTES ),
			);
		}
	}
	return $list;
}

/** The works listed under a "Sources" heading, as CreativeWork citations: the italic title and the text before it as author. */
function injil_help_collect_citations( $html ) {
	$cites = array();
	if ( ! preg_match( '#<h2[^>]*>\s*Sources\s*</h2>(.*?)(?:<h2|$)#s', $html, $sec ) ) {
		return $cites;
	}
	if ( ! preg_match_all( '#<li>(.*?)</li>#s', $sec[1], $items ) ) {
		return $cites;
	}
	foreach ( $items[1] as $li ) {
		$plain = html_entity_decode( wp_strip_all_tags( $li ), ENT_QUOTES );
		$cite  = array( '@type' => 'CreativeWork' );
		// An article: “Title” and then the journal or volume in italics. Otherwise the first italic title is the work.
		$quote_first = false !== mb_strpos( $li, '“' ) && ( false === strpos( $li, '<em>' ) || mb_strpos( $li, '“' ) < mb_strpos( $li, '<em>' ) );
		if ( $quote_first && preg_match( '#^(.*?)“(.+?)”(.*)$#su', $plain, $art ) ) {
			$cite['@type'] = 'ScholarlyArticle';
			$cite['name']  = trim( $art[2] );
			$author        = trim( $art[1], " ,;:\u{2019}" );
			$after         = substr( $li, (int) mb_strpos( $li, '”' ) );
			if ( preg_match( '#<em>(.*?)</em>#s', $after, $j ) ) {
				$cite['isPartOf'] = array( '@type' => 'Periodical', 'name' => html_entity_decode( trim( wp_strip_all_tags( $j[1] ) ), ENT_QUOTES ) );
			}
		} elseif ( preg_match( '#^(.*?)<em>(.*?)</em>#s', $li, $m ) ) {
			$cite['name'] = html_entity_decode( trim( wp_strip_all_tags( $m[2] ) ), ENT_QUOTES );
			$author       = html_entity_decode( trim( wp_strip_all_tags( $m[1] ), " ,;:\u{2019}" ), ENT_QUOTES );
		} else {
			continue;
		}
		if ( ! $cite['name'] ) {
			continue;
		}
		if ( $author && mb_strlen( $author ) < 120 ) {
			$cite['author'] = array(
				'@type' => 'Person',
				'name'  => $author,
			);
		}
		if ( preg_match( '#\((?:[^()]*?,\s*)?(\d{4})\)#', wp_strip_all_tags( $li ), $y ) ) {
			$cite['datePublished'] = $y[1];
		}
		$cites[] = $cite;
	}
	return $cites;
}

/** Glossary entries: "<strong>Term</strong>: definition", several to a list item. */
function injil_help_collect_terms( $html ) {
	$terms = array();
	if ( preg_match_all( '#<strong>([^<]+)</strong>\s*:\s*(.*?)(?=<strong>|</li>|</p>)#s', $html, $m, PREG_SET_ORDER ) ) {
		foreach ( $m as $e ) {
			$name = html_entity_decode( trim( $e[1] ), ENT_QUOTES );
			$def  = trim( preg_replace( '/\s+/', ' ', html_entity_decode( wp_strip_all_tags( $e[2] ), ENT_QUOTES ) ) );
			if ( $name && $def ) {
				$terms[] = array(
					'@type'       => 'DefinedTerm',
					'name'        => $name,
					'description' => $def,
				);
			}
		}
	}
	return $terms;
}

/** Comparison tables (block style "discrepancy"): their caption or the heading before them. */
function injil_help_collect_tables( $html, $page_title ) {
	$tables = array();
	if ( preg_match_all( '#(?:<h[23][^>]*>(.*?)</h[23]>\s*(?:<!--[^>]*-->\s*)*)?<figure[^>]*class="[^"]*is-style-discrepancy[^"]*"[^>]*>(.*?)</figure>#s', $html, $m, PREG_SET_ORDER ) ) {
		foreach ( $m as $i => $t ) {
			$heads = array();
			if ( preg_match_all( '#<th[^>]*>(.*?)</th>#s', $t[2], $th ) ) {
				$heads = array_map( fn( $h ) => html_entity_decode( wp_strip_all_tags( $h ), ENT_QUOTES ), $th[1] );
			}
			$name = $t[1] ? html_entity_decode( wp_strip_all_tags( $t[1] ), ENT_QUOTES ) : $page_title;
			$tables[] = array(
				'@type'       => 'Table',
				'name'        => $name,
				'description' => $heads ? 'Columns: ' . implode( ', ', $heads ) : $page_title,
				'about'       => $page_title,
			);
		}
	}
	return $tables;
}

/** The timeline's dated entries as Events: "c. 6-4 BCE: text", "62 CE: text", "393 and 397 CE: text". */
function injil_help_collect_events( $html ) {
	$events = array();
	if ( ! preg_match_all( '#<li>(.*?)</li>#s', $html, $items ) ) {
		return $events;
	}
	foreach ( $items[1] as $li ) {
		$text = trim( preg_replace( '/\s+/', ' ', html_entity_decode( wp_strip_all_tags( $li ), ENT_QUOTES ) ) );
		if ( ! preg_match( '/^(c\. )?(\d{1,4})(?:[-–](\d{1,4}))?(?: and (\d{1,4}))? (BCE|CE):\s*(.+)$/u', $text, $m ) ) {
			continue;
		}
		$year = function ( $y ) use ( $m ) {
			$y = (int) $y;
			return ( 'BCE' === $m[5] ? '-' : '' ) . str_pad( (string) $y, 4, '0', STR_PAD_LEFT );
		};
		$event = array(
			'@type'       => 'Event',
			'name'        => mb_substr( $m[6], 0, 110 ),
			'description' => $m[6],
			'startDate'   => $year( $m[2] ),
			'eventStatus' => 'https://schema.org/EventScheduled',
		);
		if ( ! empty( $m[3] ) ) {
			$event['endDate'] = $year( $m[3] );
		} elseif ( ! empty( $m[4] ) ) {
			$event['endDate'] = $year( $m[4] );
		}
		if ( $m[1] ) {
			$event['description'] = 'Approximate date. ' . $m[6];
		}
		$events[] = $event;
	}
	return $events;
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
			case 'about':
				return 'AboutPage';
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
	$utility = array( 'contact', 'sitemap', 'privacy-policy', 'terms-of-use', 'credits', 'about', 'answers', 'corrections' );
	return ! in_array( $post->post_name, $utility, true );
}

/** The page's place in the site: the path of its slugs, parent first. */
function injil_help_schema_path( $post ) {
	if ( ! $post instanceof WP_Post ) {
		return array();
	}
	$slugs = array();
	foreach ( array_reverse( get_post_ancestors( $post ) ) as $ancestor ) {
		$slugs[] = get_post_field( 'post_name', $ancestor );
	}
	$slugs[] = $post->post_name;
	return $slugs;
}

/* ---------- The graph ---------- */

function injil_help_schema_graph() {
	$post  = get_queried_object();
	$post  = $post instanceof WP_Post ? $post : null;
	$ids   = injil_help_schema_ids();
	$graph = injil_help_schema_site_nodes( $post );
	$lang  = get_bloginfo( 'language' );
	$path  = injil_help_schema_path( $post );

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
	$entries   = $html ? injil_help_collect_entries( $html ) : array();
	$page_type = injil_help_schema_page_type( $post, $entries );
	$webpage   = array(
		'@type'      => $page_type,
		'@id'        => $webpage_id,
		'url'        => $url,
		'name'       => $title,
		'isPartOf'   => array( '@id' => $ids['website'] ),
		'inLanguage' => $lang,
		'primaryImageOfPage' => array( '@id' => $primary_image ),
		'isAccessibleForFree' => true,
		'publisher'  => array( '@id' => $ids['org'] ),
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
		$webpage['about'] = array( '@type' => 'Thing', 'name' => 'The Islamic Dilemma' );
	}
	if ( in_array( 'ProfilePage', (array) $page_type, true ) ) {
		$webpage['mainEntity'] = array( '@id' => $ids['author'] );
	}
	if ( 'CollectionPage' === $page_type && $entries ) {
		$webpage['mainEntity'] = array(
			'@type'           => 'ItemList',
			'name'            => $title,
			'numberOfItems'   => count( $entries ),
			'itemListElement' => $entries,
		);
	}
	$speakable = array(
		'@type'       => 'SpeakableSpecification',
		'cssSelector' => array( 'h1', '.entry-content > p' ),
	);
	$webpage['speakable'] = $speakable;
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

	// Article, with its citations, subjects and the works it belongs to.
	$article_id = $url . '#article';
	if ( $post && is_singular() && injil_help_schema_is_article( $post, $page_type ) ) {
		$headline = $title;
		if ( strlen( $headline ) > 110 ) {
			$headline = rtrim( substr( $headline, 0, 109 ) ) . '…';
		}
		$image_list = array_values( array_unique( array_filter( array_merge( array( $featured ), wp_list_pluck( $images, 'contentUrl' ), array( injil_help_default_og_image() ) ) ) ) );
		$parent     = $post->post_parent ? get_post( $post->post_parent ) : null;
		$article    = array(
			'@type'            => 'post' === $post->post_type ? 'BlogPosting' : array( 'Article', 'ScholarlyArticle' ),
			'@id'              => $article_id,
			'headline'         => $headline,
			'name'             => $title,
			'url'              => $url,
			'mainEntityOfPage' => array( '@id' => $webpage_id ),
			'isPartOf'         => array( '@id' => $webpage_id ),
			'image'            => $image_list,
			'author'           => array( '@id' => $ids['author'] ),
			'publisher'        => array( '@id' => $ids['org'] ),
			'copyrightHolder'  => array( '@id' => $ids['author'] ),
			'datePublished'    => get_the_date( 'c', $post ),
			'dateModified'     => get_the_modified_date( 'c', $post ),
			'inLanguage'       => $lang,
			'isAccessibleForFree' => true,
			'wordCount'        => str_word_count( wp_strip_all_tags( $html ) ),
			'about'            => array( array( '@id' => $ids['jesus'] ), array( '@id' => $ids['quran'] ) ),
			'speakable'        => $speakable,
		);
		if ( $description ) {
			$article['description'] = $description;
		}
		if ( $parent ) {
			$article['articleSection'] = wp_strip_all_tags( get_the_title( $parent ) );
		}
		if ( injil_help_is_pillar() ) {
			$article['about'][] = array( '@type' => 'Thing', 'name' => 'The Islamic Dilemma' );
			$article['about'][] = array( '@type' => 'Thing', 'name' => 'Injil' );
			$article['keywords'] = 'the Islamic Dilemma, Quran 5:47, Injil, tahrif, the four gospels';
		}
		$citations = injil_help_collect_citations( $html );
		if ( $citations ) {
			$article['citation'] = $citations;
		}

		// The gospel: Book and Chapter.
		$section = ( $parent && 'gospel-according-to-islam' === $parent->post_name && function_exists( 'injil_help_gospel_section_number' ) ) ? injil_help_gospel_section_number( $post->post_name ) : 0;
		if ( $section || ( $parent && 'gospel-according-to-islam' === $parent->post_name && 'introduction' === $post->post_name ) ) {
			$chapter_id              = $url . '#chapter';
			$article['isPartOf']     = array( '@id' => $chapter_id );
			$graph[]                 = array(
				'@type'         => 'Book',
				'@id'           => $ids['book'],
				'name'          => 'The Gospel According to Islam',
				'url'           => home_url( '/gospel-according-to-islam/' ),
				'author'        => array( '@type' => 'Person', 'name' => 'Ahmad Shafaat' ),
				'editor'        => array( '@id' => $ids['author'] ),
				'publisher'     => array( '@type' => 'Organization', 'name' => 'Vantage Press' ),
				'datePublished' => '1985',
				'inLanguage'    => 'en',
				'numberOfPages' => 27,
				'about'         => array( '@id' => $ids['jesus'] ),
			);
			$chapter = array(
				'@type'    => 'Chapter',
				'@id'      => $chapter_id,
				'name'     => $title,
				'url'      => $url,
				'isPartOf' => array( '@id' => $ids['book'] ),
				'author'   => array( '@type' => 'Person', 'name' => 'Ahmad Shafaat' ),
			);
			if ( $section ) {
				$chapter['position'] = $section;
			}
			$graph[] = $chapter;
		}

		// A classical commentary: the Arabic work and its English rendering.
		if ( isset( $path[1] ) && 'quran' === $path[0] && 'commentaries' === $path[1] && isset( $path[3] ) && function_exists( 'injil_help_tafsir_works' ) ) {
			$key   = preg_replace( '/-part-[a-z]+$/', '', $path[3] );
			$works = injil_help_tafsir_works();
			if ( isset( $works[ $key ] ) ) {
				$w       = $works[ $key ];
				$work_id = home_url( '/quran/commentaries/' ) . '#' . $key;
				$graph[] = array(
					'@type'      => 'Book',
					'@id'        => $work_id,
					'name'       => $w['title'],
					'alternateName' => $w['short'],
					'inLanguage' => 'ar',
					'author'     => array(
						'@type'       => 'Person',
						'name'        => $w['author'],
						'description' => 'Died ' . $w['died'],
					),
					'about'      => array( '@id' => $ids['quran'] ),
					'workTranslation' => array( '@id' => $work_id . '-en' ),
				);
				$graph[] = array(
					'@type'             => 'CreativeWork',
					'@id'               => $work_id . '-en',
					'name'              => $w['short'] . ' in English',
					'inLanguage'        => 'en',
					'translationOfWork' => array( '@id' => $work_id ),
					'translator'        => array( '@id' => $ids['org'] ),
					'hasPart'           => array( '@id' => $article_id ),
				);
				$article['isPartOf'] = array( '@id' => $work_id . '-en' );
			}
		}
		$graph[] = $article;
	}

	// The glossary as a DefinedTermSet.
	if ( $post && 'glossary' === $post->post_name ) {
		$terms = injil_help_collect_terms( $html );
		if ( $terms ) {
			$graph[] = array(
				'@type'          => 'DefinedTermSet',
				'@id'            => $url . '#terms',
				'name'           => $title,
				'url'            => $url,
				'hasDefinedTerm' => $terms,
			);
		}
	}

	// Comparison tables.
	foreach ( injil_help_collect_tables( $html, $title ) as $table ) {
		$table['isPartOf'] = array( '@id' => $webpage_id );
		$graph[]           = $table;
	}

	// The sayings-order test as a Dataset.
	if ( $post && 'correlation' === $post->post_name ) {
		$graph[] = array(
			'@type'       => 'Dataset',
			'@id'         => $url . '#dataset',
			'name'        => 'Order of the parallel sayings in the Gospel of Thomas against Matthew, Mark, Luke and Q',
			'description' => $description,
			'url'         => $url,
			'creator'     => array( '@id' => $ids['author'] ),
			'isAccessibleForFree' => true,
			'keywords'    => array( 'Gospel of Thomas', 'synoptic gospels', 'Q', 'correlation' ),
			'variableMeasured' => array( 'Spearman rank correlation', 'p-value' ),
		);
	}

	// The timeline as Events.
	if ( $post && 'timeline' === $post->post_name ) {
		foreach ( injil_help_collect_events( $html ) as $event ) {
			$event['isPartOf'] = array( '@id' => $webpage_id );
			$graph[]           = $event;
		}
	}

	// Quotations.
	$quotes = array();
	if ( $blocks ) {
		injil_help_collect_quotes( $blocks, $quotes );
	}
	foreach ( array_slice( $quotes, 0, 40 ) as $quote ) {
		$quote['isPartOf'] = array( '@id' => $webpage_id );
		$graph[]           = $quote;
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
