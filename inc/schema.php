<?php
/** The schema author: declared in the author Person node only (owner's decision, 4 November 2026). */
if ( ! defined( 'INJIL_HELP_SCHEMA_AUTHOR_NAME' ) ) {
	define( 'INJIL_HELP_SCHEMA_AUTHOR_NAME', 'Mohd Elfie Nieshaem Juferi' );
}
if ( ! defined( 'INJIL_HELP_SCHEMA_AUTHOR_URL' ) ) {
	define( 'INJIL_HELP_SCHEMA_AUTHOR_URL', 'https://menj.bio' );
}

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
 * inc/schema-extend.php then adds the Qur'an verse quotations, the Thomas sayings and timeline as
 * ordered lists, term and entity @ids, image credits, navigation links and article details.
 *
 * Output is skipped when Yoast, Rank Math or SEOPress is active, unless Theme
 * Options > SEO forces it on. Filter injil_help_schema_graph to change the graph.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

require_once get_stylesheet_directory() . '/inc/schema-content.php';
require_once get_stylesheet_directory() . '/inc/schema-extend.php';

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

/**
 * Author details. By the owner's decision of 4 November 2026 the schema author is the owner,
 * Mohd Elfie Nieshaem Juferi, with url https://menj.bio. The name is declared in the author Person
 * node only and never in visible text (SSOT.md); injil_help_schema_unname() keeps it out of every
 * other node. The sameAs links come from Theme Options > SEO, with https://menj.bio always among them.
 */
function injil_help_schema_author( $post = null ) {
	$same = array_values( array_filter( array_map( 'trim', explode( "\n", (string) injil_help_get_option( 'same_as' ) ) ) ) );
	if ( ! in_array( INJIL_HELP_SCHEMA_AUTHOR_URL, $same, true ) ) {
		$same[] = INJIL_HELP_SCHEMA_AUTHOR_URL;
	}
	return array( INJIL_HELP_SCHEMA_AUTHOR_NAME, INJIL_HELP_SCHEMA_AUTHOR_URL, $same );
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

	// masthead and verificationFactCheckingPolicy (injil_help_schema_policies()) belong to
	// NewsMediaOrganization; without that type validators report them as unknown on an Organization.
	$org = array(
		'@type'        => array( 'Organization', 'NewsMediaOrganization' ),
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
	$org              = array_merge( $org, injil_help_schema_policies() );
	$org['knowsAbout'] = array(
		array( '@id' => injil_help_schema_entity_id( 'islamic-dilemma' ) ),
		array( '@id' => $ids['jesus'] ),
		array( '@id' => $ids['quran'] ),
		array( '@id' => injil_help_schema_entity_id( 'injil' ) ),
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
		'publishingPrinciples' => home_url( '/method/' ),
		'about'         => array(
			array( '@id' => injil_help_schema_entity_id( 'islamic-dilemma' ) ),
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
			// The gloss under each label (Greek or Arabic) is decoration: drop the whole span before the tags go.
			$label   = preg_replace( '~<span class="ih-gloss[^"]*"[^>]*>.*?</span>~su', '', str_replace( '\\"', '"', $link[1] ) );
			$label   = html_entity_decode( wp_strip_all_tags( $label ), ENT_QUOTES );
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'item'     => array(
					'@type'       => 'SiteNavigationElement',
					'name'        => trim( $label ),
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

/**
 * Replace core/pattern references with the pattern's blocks, so the parsers see template content.
 *
 * A pattern can expand to no block (its slug is not registered, as while the theme is being
 * uploaded or when a saved template names a pattern that has since gone) or to several (with the
 * whitespace between them). Each expansion takes the place of the pattern's own placeholder in
 * innerContent, so the parent keeps one null for each inner block. Core's WP_Block::render() ends
 * the request with a fatal error when a null has no inner block to go with it.
 *
 * @param array $blocks Parsed blocks.
 * @param array $seen   Pattern slugs being expanded, so a pattern that names itself stops.
 * @return array
 */
function injil_help_expand_patterns( $blocks, $seen = array() ) {
	$out = array();
	foreach ( $blocks as $block ) {
		if ( ! is_array( $block ) ) {
			continue;
		}
		if ( 'core/pattern' === ( $block['blockName'] ?? null ) ) {
			$slug = (string) ( $block['attrs']['slug'] ?? '' );
			if ( '' !== $slug && ! isset( $seen[ $slug ] ) ) {
				$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( $slug );
				if ( $pattern && isset( $pattern['content'] ) ) {
					$out = array_merge( $out, injil_help_expand_patterns( parse_blocks( (string) $pattern['content'] ), $seen + array( $slug => true ) ) );
				}
			}
			continue;
		}
		$block = injil_help_schema_normalise_block( $block );
		if ( $block['innerBlocks'] ) {
			$inner   = array();
			$content = array();
			$i       = 0;
			foreach ( $block['innerContent'] as $piece ) {
				if ( is_string( $piece ) ) {
					$content[] = $piece;
					continue;
				}
				foreach ( injil_help_expand_patterns( array( $block['innerBlocks'][ $i++ ] ), $seen ) as $b ) {
					$inner[]   = $b;
					$content[] = null;
				}
			}
			$block['innerBlocks']  = $inner;
			$block['innerContent'] = $content;
		}
		$out[] = $block;
	}
	return $out;
}

/**
 * A parsed block whose innerContent holds exactly one null for each inner block, at every depth.
 *
 * A block built or changed by hand can carry more inner blocks than placeholders, or fewer. A
 * placeholder without its block is dropped; an inner block without a placeholder gets one at the
 * end, so its text is still read.
 *
 * @param array $block A parsed block.
 * @return array
 */
function injil_help_schema_normalise_block( $block ) {
	$block += array(
		'blockName'    => null,
		'attrs'        => array(),
		'innerBlocks'  => array(),
		'innerHTML'    => '',
		'innerContent' => array(),
	);
	$block['attrs']        = is_array( $block['attrs'] ) ? $block['attrs'] : array();
	$block['innerHTML']    = is_string( $block['innerHTML'] ) ? $block['innerHTML'] : '';
	$block['innerBlocks']  = array_values( array_filter( (array) $block['innerBlocks'], 'is_array' ) );
	$block['innerBlocks']  = array_map( 'injil_help_schema_normalise_block', $block['innerBlocks'] );
	$count                 = count( $block['innerBlocks'] );
	$content               = array();
	$nulls                 = 0;
	if ( ! $block['innerContent'] && '' !== $block['innerHTML'] ) {
		$content[] = $block['innerHTML']; // a block built by hand with its markup in innerHTML only
	}
	foreach ( (array) $block['innerContent'] as $piece ) {
		if ( is_string( $piece ) ) {
			$content[] = $piece;
		} elseif ( null === $piece && $nulls < $count ) {
			$content[] = null;
			++$nulls;
		}
	}
	for ( ; $nulls < $count; $nulls++ ) {
		$content[] = null;
	}
	$block['innerContent'] = $content;
	return $block;
}

/**
 * A block's markup written out from its own pieces, with no block rendered: the fallback when
 * rendering fails.
 *
 * @param array $block A parsed block.
 * @return string
 */
function injil_help_schema_block_markup( $block ) {
	$block = injil_help_schema_normalise_block( $block );
	$html  = '';
	$i     = 0;
	foreach ( $block['innerContent'] as $piece ) {
		$html .= is_string( $piece ) ? $piece : injil_help_schema_block_markup( $block['innerBlocks'][ $i++ ] );
	}
	return $html;
}

/**
 * Render a block for the structured data only. The block is normalised first, and a render that
 * throws falls back to the block's own markup, so a malformed block costs the graph some text
 * and never the page.
 *
 * @param array $block A parsed block.
 * @return string
 */
function injil_help_schema_render( $block ) {
	if ( ! is_array( $block ) ) {
		return '';
	}
	$block = injil_help_schema_normalise_block( $block );
	try {
		return (string) render_block( $block );
	} catch ( \Throwable $e ) {
		error_log( 'The Injil: a block could not be rendered for the structured data (' . $e->getMessage() . '); its markup was read instead.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
		return injil_help_schema_block_markup( $block );
	}
}

/** FAQ items: core/details styled "FAQ item", and "Short answer" groups (heading + .ih-answer-text). */
function injil_help_collect_faq( $blocks, &$items ) {
	foreach ( $blocks as $block ) {
		$class = $block['attrs']['className'] ?? '';
		if ( 'core/details' === $block['blockName'] && false !== strpos( $class, 'is-style-faq' ) ) {
			if ( preg_match( '#<summary>(.*?)</summary>#s', $block['innerHTML'], $q ) ) {
				$answer = '';
				foreach ( $block['innerBlocks'] as $inner ) {
					$answer .= ' ' . wp_strip_all_tags( injil_help_schema_render( $inner ) );
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
					$a = html_entity_decode( wp_strip_all_tags( injil_help_schema_render( $inner ) ), ENT_QUOTES );
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
				$text .= ' ' . wp_strip_all_tags( injil_help_schema_render( $inner ) );
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
		if ( false !== stripos( $caption, 'Pexels License' ) ) {
			$node['license'] = 'https://www.pexels.com/license/';
		}
		if ( false !== stripos( $caption, 'Pixabay Content License' ) ) {
			$node['license'] = 'https://pixabay.com/service/license-summary/';
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

/**
 * The images.json entry for a Media Library attachment, found by the Commons file (or Pexels key)
 * recorded when it was sideloaded. A page's first image and its "more" images are both searched.
 */
function injil_help_schema_attachment_entry( $att ) {
	$key = $att && defined( 'INJIL_HELP_COMMONS_META' ) ? (string) get_post_meta( (int) $att, INJIL_HELP_COMMONS_META, true ) : '';
	if ( '' === $key || ! function_exists( 'injil_help_images' ) ) {
		return array();
	}
	foreach ( injil_help_images() as $img ) {
		foreach ( array_merge( array( $img ), (array) ( $img['more'] ?? array() ) ) as $one ) {
			if ( ( $one['file'] ?? '' ) === $key ) {
				return $one;
			}
		}
	}
	return array();
}

/**
 * The file an image shows, as one comparable key: a Commons file name (from its address or as
 * given), a Pexels key, or an uploaded address's file name, each lower case, with spaces as
 * underscores and without the extension or WordPress's -WIDTHxHEIGHT and -scaled suffixes. So the
 * Commons source, its JPEG upload and an AVIF or resized copy of it give the same key.
 */
function injil_help_schema_file_key( $src ) {
	$src = (string) $src;
	if ( '' === $src ) {
		return '';
	}
	$key = false !== strpos( $src, '://' ) && function_exists( 'injil_help_image_key_from_src' ) ? injil_help_image_key_from_src( $src ) : '';
	if ( '' === $key ) {
		$path = false !== strpos( $src, '://' ) ? (string) wp_parse_url( $src, PHP_URL_PATH ) : $src;
		$key  = rawurldecode( basename( $path ) );
	}
	$key = strtolower( str_replace( ' ', '_', $key ) );
	$key = preg_replace( '/\.[a-z0-9]+$/', '', $key );
	return (string) preg_replace( '/(?:-\d+x\d+|-scaled|-e\d{10,})+$/', '', $key );
}

/**
 * An ImageObject for an uploaded image address (a featured image, or an image Rank Math refers to),
 * with the same caption, credit and licence a figure of that image carries. The address is its @id
 * and contentUrl, so a reference to it resolves whatever format the upload was stored in.
 */
function injil_help_schema_upload_image( $url, $att = 0 ) {
	if ( ! $att && function_exists( 'attachment_url_to_postid' ) ) {
		$att = (int) attachment_url_to_postid( $url );
	}
	$entry = injil_help_schema_attachment_entry( $att );
	$node  = array();
	if ( $entry && function_exists( 'injil_help_image_block' ) ) {
		$found = injil_help_collect_images( injil_help_image_block( $entry ) );
		$node  = $found ? $found[0] : array();
	}
	if ( ! $node ) {
		$node = array( '@type' => 'ImageObject' );
		$alt  = $att ? trim( (string) get_post_meta( (int) $att, '_wp_attachment_image_alt', true ) ) : '';
		if ( $alt ) {
			$node['name']        = $alt;
			$node['description'] = $alt;
		}
		$caption = $att && function_exists( 'wp_get_attachment_caption' ) ? trim( wp_strip_all_tags( (string) wp_get_attachment_caption( $att ) ) ) : '';
		$node['caption']    = $caption ?: get_bloginfo( 'name' );
		$node['creditText'] = $node['caption'];
	}
	$node['@id']        = $url;
	$node['contentUrl'] = $url;
	$node['url']        = $url;
	return $node;
}

/**
 * Make every {"@id"} reference to an image address resolve: an address no node defines gets an
 * ImageObject of its own. Other references are left alone. A keyed graph (Rank Math's) takes a key prefix.
 */
function injil_help_schema_resolve_images( $graph, $key_prefix = '' ) {
	$refs = array();
	injil_help_schema_refs( $graph, $refs );
	$have = array();
	$walk = function ( $v ) use ( &$walk, &$have ) {
		if ( ! is_array( $v ) ) {
			return;
		}
		if ( isset( $v['@id'] ) && is_string( $v['@id'] ) && count( $v ) > 1 ) {
			$have[ $v['@id'] ] = true;
		}
		foreach ( $v as $c ) {
			$walk( $c );
		}
	};
	$walk( $graph );
	foreach ( array_keys( $refs ) as $ref ) {
		if ( ! isset( $have[ $ref ] ) && preg_match( '#^https?://[^\s]+\.(?:avif|webp|jpe?g|png|gif|svg|tiff?)(?:\?.*)?$#i', $ref ) ) {
			$node = injil_help_schema_upload_image( $ref );
			if ( function_exists( 'injil_help_schema_enrich_image' ) ) {
				$node = injil_help_schema_enrich_image( $node, '' );
			}
			if ( '' === $key_prefix ) {
				$graph[] = $node;
			} else {
				$graph[ $key_prefix . count( $have ) ] = $node;
			}
			$have[ $ref ] = true;
		}
	}
	return $graph;
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
			$cite['headline'] = mb_substr( $cite['name'], 0, 110 );
			$author        = trim( $art[1], " ,;:\u{2019}" );
			$after         = substr( $li, (int) strpos( $li, '”' ) ); // a byte offset, as substr() expects
			if ( preg_match( '#<em>(.*?)</em>#s', $after, $j ) ) {
				$cite['isPartOf'] = array( '@type' => 'Periodical', 'name' => html_entity_decode( trim( wp_strip_all_tags( $j[1] ) ), ENT_QUOTES ) );
			}
		} elseif ( preg_match( '#^(.*?)<em>(.*?)</em>#s', $li, $m ) ) {
			$cite['name'] = html_entity_decode( trim( wp_strip_all_tags( $m[2] ) ), ENT_QUOTES );
			$author       = html_entity_decode( trim( wp_strip_all_tags( $m[1] ), " ,;:\u{2019}" ), ENT_QUOTES );
		} else {
			// A source with no italic title is cited by its whole line, as the reader sees it (3.25.7).
			$cite['name'] = trim( preg_replace( '/\s+/u', ' ', $plain ) );
			$author       = '';
			if ( preg_match( '#href="(https?://[^"]+)"#', $li, $u ) ) {
				$cite['url'] = html_entity_decode( $u[1], ENT_QUOTES | ENT_HTML5, 'UTF-8' ); // "&amp;" in the markup is "&" in the address
			}
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
	if ( preg_match_all( '#<strong>([^<]+)</strong>\s*(\((?:also spelt|also written|also)\s+[^)]*\))?\s*:\s*(.*?)(?=<strong>|</li>|</p>)#s', $html, $m, PREG_SET_ORDER ) ) {
		foreach ( $m as $e ) {
			$name = html_entity_decode( trim( $e[1] ), ENT_QUOTES );
			$def  = trim( preg_replace( '/\s+/', ' ', html_entity_decode( wp_strip_all_tags( $e[3] ), ENT_QUOTES ) ) );
			if ( $name && $def ) {
				$term = array(
					'@type'       => 'DefinedTerm',
					'name'        => $name,
					'description' => $def,
				);
				// "(also spelt Injil, Injeel)": the other spellings the entry gives.
				if ( ! empty( $e[2] ) ) {
					$alt = preg_replace( '/^\((?:also spelt|also written|also)\s+|\)$/u', '', html_entity_decode( wp_strip_all_tags( $e[2] ), ENT_QUOTES ) );
					$term['alternateName'] = array_values( array_filter( array_map( 'trim', preg_split( '/,|\bor\b/u', $alt ) ) ) );
				}
				$terms[] = $term;
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
			case 'journal':
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

/**
 * The CSS selectors of a page's SpeakableSpecification, each one only where the page's markup has it.
 * The h1 is on every template. The front page has no .entry-content, and on a page built from groups
 * (the answers page) no paragraph sits directly inside it, so ".entry-content > p" is given only when
 * a paragraph block is at the top level of the post's content. The hero's lede and the section intros
 * are named by their classes where the page carries them.
 *
 * @param array  $blocks   The page's top-level blocks, patterns expanded.
 * @param string $html     The rendered content.
 * @param bool   $is_front Whether this is the front page, rendered from its template.
 * @return string[]
 */
function injil_help_schema_speakable_selectors( $blocks, $html, $is_front ) {
	$selectors = array( 'h1' );
	// The class must be a whole token of the attribute: "ih-intro-extra" is another class.
	$has_class = function ( $class ) use ( $html ) {
		return (bool) preg_match( '#<p\s[^>]*\bclass="(?:[^"]*\s)?' . preg_quote( $class, '#' ) . '(?:\s[^"]*)?"#', $html );
	};
	if ( $has_class( 'ih-cover-lede' ) ) {
		$selectors[] = '.ih-cover-lede';
	}
	if ( ! $is_front && $has_class( 'ih-intro' ) ) {
		$selectors[] = '.ih-intro';
	}
	if ( ! $is_front ) {
		foreach ( (array) $blocks as $block ) {
			if ( isset( $block['blockName'] ) && 'core/paragraph' === $block['blockName'] ) {
				$selectors[] = '.entry-content > p';
				break;
			}
		}
	}
	return $selectors;
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
		$html .= injil_help_schema_render( $b );
	}
	$html = injil_help_schema_strip_mark( $html );

	// URL and names.
	if ( is_front_page() ) {
		$url = home_url( '/' );
	} elseif ( is_singular() && $post ) {
		$url = get_permalink( $post );
	} elseif ( is_search() ) {
		$url = get_search_link();
	} elseif ( is_archive() ) {
		// A post type, date or author archive has no term; get_term_link() would warn on it.
		$url = injil_help_term_link( get_queried_object() );
		$url = '' !== $url ? $url : home_url( add_query_arg( array() ) );
	} else {
		$url = home_url( add_query_arg( array() ) );
	}
	$webpage_id    = $url . '#webpage';
	$breadcrumb_id = $url . '#breadcrumb';
	$title         = is_front_page() ? injil_help_seo_defaults()['home_title'] : ( $post ? wp_strip_all_tags( get_the_title( $post ) ) : wp_strip_all_tags( wp_get_document_title() ) );
	$description   = trim( injil_help_meta_description() );
	$images        = injil_help_collect_images( $html );
	$featured      = $post && has_post_thumbnail( $post ) ? (string) get_the_post_thumbnail_url( $post, 'large' ) : '';
	// The featured image is the primary image, so it needs a node of its own. A figure of the same
	// file (still at its Commons address, or at another upload of it) becomes that node; otherwise
	// one is built from the attachment's images.json entry.
	if ( $featured && ! in_array( $featured, wp_list_pluck( $images, 'contentUrl' ), true ) ) {
		$thumb = injil_help_schema_upload_image( $featured, (int) get_post_thumbnail_id( $post ) );
		$att   = (int) get_post_thumbnail_id( $post );
		$want  = array_filter(
			array(
				injil_help_schema_file_key( $featured ),
				$att && defined( 'INJIL_HELP_COMMONS_META' ) ? injil_help_schema_file_key( (string) get_post_meta( $att, INJIL_HELP_COMMONS_META, true ) ) : '',
			)
		);
		$match = null;
		foreach ( $images as $i => $image ) {
			$key = injil_help_schema_file_key( (string) ( $image['contentUrl'] ?? '' ) );
			if ( '' !== $key && in_array( $key, $want, true ) ) {
				$match = $i;
				break;
			}
		}
		if ( null !== $match ) {
			$images[ $match ]['@id']        = $featured;
			$images[ $match ]['contentUrl'] = $featured;
			$images[ $match ]['url']        = $featured;
		} else {
			$images[] = $thumb;
		}
	}
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
		$webpage['about'] = array( '@id' => injil_help_schema_entity_id( 'islamic-dilemma' ) );
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
		'cssSelector' => injil_help_schema_speakable_selectors( $blocks, $html, is_front_page() ),
	);
	// "In short" on a Journal post (inc/article.php), when it shows.
	if ( $post && is_singular( 'post' ) && function_exists( 'injil_help_article_points_show' ) && injil_help_article_points_show( $post ) ) {
		$speakable['cssSelector'][] = '.ih-points';
	}
	$webpage['author']    = array( '@id' => $ids['author'] );
	$webpage['speakable'] = $speakable;
	$webpage              = array_merge( $webpage, injil_help_schema_page_elements( $url, $html ) );
	$related              = injil_help_schema_related_links( $html );
	if ( $related ) {
		$webpage['relatedLink'] = $related;
	}
	$shares = injil_help_schema_share_actions( $post );
	if ( $shares ) {
		$webpage['potentialAction'] = $shares;
	}
	$path_str = implode( '/', $path );
	$subjects = injil_help_schema_entities( $path_str );
	if ( ! isset( $webpage['about'] ) && $subjects['about'] ) {
		$webpage['about'] = $subjects['about'];
	}
	// Corrections to a page that is not an article (a section index) go on the page itself; an article carries its own.
	if ( $post && ! injil_help_schema_is_article( $post, $page_type ) ) {
		$corrections = injil_help_schema_corrections( $path_str );
		if ( $corrections ) {
			$webpage['correction'] = $corrections;
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
			foreach ( injil_help_breadcrumb_parents( $post ) as $item ) {
				$crumbs[] = array(
					'@type'    => 'ListItem',
					'position' => $pos++,
					'name'     => $item[0],
					'item'     => $item[1],
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
			$article['about'][] = array( '@id' => injil_help_schema_entity_id( 'islamic-dilemma' ) );
			$article['about'][] = array( '@id' => injil_help_schema_entity_id( 'injil' ) );
			$article['keywords'] = 'the Islamic Dilemma, Quran 5:47, Injil, tahrif, the four gospels';
		}
		$citations = injil_help_collect_citations( $html );
		if ( $citations ) {
			$article['citation'] = $citations;
		}
		$about            = array_merge( $article['about'], $subjects['about'] );
		$article['about'] = array_values( array_intersect_key( $about, array_unique( array_map( 'wp_json_encode', $about ) ) ) );
		if ( $subjects['mentions'] ) {
			$article['mentions'] = $subjects['mentions'];
		}
		$corrections = injil_help_schema_corrections( $path_str );
		if ( $corrections ) {
			$article['correction'] = $corrections;
		}
		$translation = injil_help_schema_translations( $path_str, $ids['quran'] );
		if ( $translation ) {
			$article['isPartOf'] = array( '@id' => $translation['part_of'] );
			$graph               = array_merge( $graph, $translation['nodes'] );
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

	// Claims: the labelled paragraphs and the status badges, as they appear on this page.
	$appears_in = ( $post && is_singular() && injil_help_schema_is_article( $post, $page_type ) ) ? $article_id : $webpage_id;
	foreach ( injil_help_schema_claims( $html, $appears_in, $ids['org'] ) as $claim ) {
		$graph[] = $claim;
	}

	// Objection and reply: the objection, the best Christian reply and the response.
	foreach ( injil_help_schema_objections( $html ) as $question ) {
		$question['isPartOf'] = array( '@id' => $webpage_id );
		$graph[]              = $question;
	}

	// The claims of the named articles answered on the responses page.
	if ( 'resources/responses' === $path_str ) {
		foreach ( injil_help_schema_response_claims( $html, $ids['org'] ) as $claim ) {
			$claim['appearance'] = array( '@id' => $appears_in );
			$graph[]             = $claim;
		}
	}

	// The corrections log.
	if ( 'corrections' === $path_str ) {
		foreach ( injil_help_schema_corrections() as $correction ) {
			$correction['isPartOf'] = array( '@id' => $webpage_id );
			$graph[]                = $correction;
		}
	}

	// The Qur'anic glosses and the manuscripts table.
	foreach ( array_merge( injil_help_schema_glosses( $html, $ids['quran'] ), injil_help_schema_manuscripts( $html ) ) as $node ) {
		$node['isPartOf'] = $node['isPartOf'] ?? array( '@id' => $webpage_id );
		$graph[]          = $node;
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

	$graph = injil_help_schema_extend(
		$graph,
		array(
			'url'        => $url,
			'webpage_id' => $webpage_id,
			'article_id' => $article_id,
			'html'       => $html,
			'post'       => $post,
			'path'       => $path_str,
			'ids'        => $ids,
		)
	);

	return injil_help_schema_resolve_images( apply_filters( 'injil_help_schema_graph', $graph ) );
}

/**
 * The graph, or an empty one when building it throws. The graph is extra to the page: whatever goes
 * wrong while it is built, the page is still sent. The wp_head printer and the Rank Math merge
 * (inc/rank-math.php) both read it through here.
 *
 * @return array
 */
function injil_help_schema_graph_safe() {
	try {
		return (array) injil_help_schema_graph();
	} catch ( \Throwable $e ) {
		error_log( 'The Injil: the structured data was left out of this page (' . $e->getMessage() . ').' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
		return array();
	}
}

add_action(
	'wp_head',
	function () {
		if ( ! injil_help_schema_enabled() ) {
			return;
		}
		$graph = injil_help_schema_graph_safe();
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

// The last line: functions.php checks for it, so a file an upload cut short is caught.
$GLOBALS['injil_help_loaded'][ basename( __FILE__, '.php' ) ] = true;
