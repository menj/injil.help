<?php
/**
 * Structured data, second pass: the shared network model applied to the graph that
 * injil_help_schema_graph() builds. It adds, where the page shows the thing described:
 *
 *   Book + Chapter + Quotation   each Qur'an verse run shown in English (ih-vq), under its surah
 *   ItemList of Quotation        the sayings of Thomas, in order, on the two full-text pages
 *   ItemList of Event            the timeline, in order
 *   DefinedTerm @id              each glossary term, in its DefinedTermSet
 *   ImageObject credits          creator, credit, licence and format from inc/data/images.json
 *   SiteNavigationElement items  the header links as parts of the page's navigation element
 *   significantLink              the site links in the visible body, outside "Read next"
 *   Article details              abstract, dateCreated, copyrightYear, access modes and features
 *   Entities by @id              every subject from inc/data/schema-entities.json defined once
 *
 * Every node it adds has a stable @id (page URL or home URL plus a fragment). The calligraphic
 * mark (.ih-lanah) is kept out of every value, as SSOT.md requires.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

/** The calligraphic mark after a name, as markup and as text. Neither enters structured data. */
function injil_help_schema_strip_mark( $html ) {
	return (string) preg_replace( '#<(span|em|i|b|strong)\b[^>]*class="[^"]*\bih-lanah\b[^"]*"[^>]*>.*?</\1>#su', '', (string) $html );
}

/** Remove the mark's words from every string in the graph, in case a source carries them as plain text. */
function injil_help_schema_scrub( $value ) {
	if ( is_string( $value ) ) {
		$clean = str_replace( array( 'لعنة الله', 'لَعْنَةُ ٱللَّٰهِ', 'لعنة' ), '', $value );
		return $clean === $value ? $value : trim( preg_replace( '/\s{2,}/u', ' ', $clean ) );
	}
	if ( is_array( $value ) ) {
		foreach ( $value as $k => $v ) {
			$value[ $k ] = injil_help_schema_scrub( $v );
		}
	}
	return $value;
}

/** The @id of an entity in inc/data/schema-entities.json. */
function injil_help_schema_entity_id( $key ) {
	return home_url( '/' ) . '#entity-' . $key;
}

/** Every entity node, keyed by @id. */
function injil_help_schema_entity_nodes() {
	$data = injil_help_data_file( 'schema-entities' );
	$out  = array();
	foreach ( (array) ( $data['entities'] ?? array() ) as $key => $entity ) {
		$id         = injil_help_schema_entity_id( $key );
		$out[ $id ] = array( '@id' => $id ) + $entity;
	}
	return $out;
}

/** Every {"@id"} reference anywhere in a value. */
function injil_help_schema_refs( $value, &$refs ) {
	if ( ! is_array( $value ) ) {
		return;
	}
	if ( 1 === count( $value ) && isset( $value['@id'] ) && is_string( $value['@id'] ) ) {
		$refs[ $value['@id'] ] = true;
		return;
	}
	foreach ( $value as $v ) {
		injil_help_schema_refs( $v, $refs );
	}
}

/** A fragment-safe slug. */
function injil_help_schema_slug( $text ) {
	$text = remove_accents( html_entity_decode( (string) $text, ENT_QUOTES ) );
	$text = strtolower( preg_replace( '/[^A-Za-z0-9]+/', '-', $text ) );
	return trim( $text, '-' );
}

/** The @id of a surah, as a Chapter of the Qur'an. */
function injil_help_schema_surah_id( $n ) {
	return home_url( '/' ) . '#quran-surah-' . (int) $n;
}

/**
 * The Qur'an verses shown in English with their reference span (ih-vq): one Quotation for each run of
 * verses from one surah in one paragraph, with its text, reference and surah.
 */
function injil_help_schema_verse_quotes( $html, $url, $quran_id ) {
	$out = array();
	if ( ! preg_match_all( '#<p\b[^>]*>(.*?)</p>#s', $html, $paras ) ) {
		return $out;
	}
	foreach ( $paras[1] as $p ) {
		if ( false === strpos( $p, 'ih-vq' ) ) {
			continue;
		}
		$parts = preg_split( '#<span class="ih-v ih-vq"[^>]*>(\d+):(\d+)</span>#', $p, -1, PREG_SPLIT_DELIM_CAPTURE );
		$run   = null;
		for ( $i = 1; $i + 2 < count( $parts ) + 1; $i += 3 ) {
			$surah = (int) $parts[ $i ];
			$ayah  = (int) $parts[ $i + 1 ];
			$text  = injil_help_schema_text( preg_replace( '#<span class="ih-v[^"]*"[^>]*>.*?</span>|<sup.*?</sup>#s', '', (string) ( $parts[ $i + 2 ] ?? '' ) ) );
			if ( $run && $run['surah'] === $surah && $ayah === $run['to'] + 1 ) {
				$run['to']    = $ayah;
				$run['text'] .= ' ' . $text;
				continue;
			}
			if ( $run ) {
				$out[] = $run;
			}
			$run = array(
				'surah' => $surah,
				'from'  => $ayah,
				'to'    => $ayah,
				'text'  => $text,
			);
		}
		if ( $run ) {
			$out[] = $run;
		}
	}
	$nodes = array();
	$seen  = array();
	foreach ( $out as $r ) {
		$ref = $r['surah'] . ':' . $r['from'] . ( $r['to'] > $r['from'] ? '-' . $r['to'] : '' );
		$id  = $url . '#quran-' . str_replace( ':', '-', $ref );
		if ( '' === $r['text'] || isset( $seen[ $id ] ) ) {
			continue;
		}
		$seen[ $id ] = true;
		$nodes[]     = array(
			'@type'             => 'Quotation',
			'@id'               => $id,
			'name'              => 'Qur’an ' . $ref,
			'identifier'        => $ref,
			'text'              => $r['text'],
			'inLanguage'        => 'en',
			'isPartOf'          => array( '@id' => injil_help_schema_surah_id( $r['surah'] ) ),
			'translationOfWork' => array( '@id' => $quran_id ),
		);
	}
	return array_slice( $nodes, 0, 80 );
}

/** The sayings of Thomas on a full-text page, in order: "Saying N: Title" headings or "<strong>N.</strong>" paragraphs. */
function injil_help_schema_sayings( $html, $url, $work_id ) {
	$items = array();
	if ( preg_match_all( '#<h3[^>]*>\s*Saying (\d+)(?::\s*(.*?))?</h3>((?:\s*(?:<!--[^>]*-->\s*)*<p[^>]*>.*?</p>)+)#s', $html, $m, PREG_SET_ORDER ) ) {
		foreach ( $m as $s ) {
			preg_match_all( '#<p[^>]*>(.*?)</p>#s', $s[3], $ps );
			$items[] = array( (int) $s[1], injil_help_schema_text( $s[2] ), injil_help_schema_text( implode( ' ', $ps[1] ) ) );
		}
	} elseif ( preg_match_all( '#<p[^>]*>\s*<strong>(\d+)\.</strong>\s*(.*?)</p>((?:\s*(?:<!--[^>]*-->\s*)*<p(?:\s[^>]*)?>(?!\s*<strong>\d+\.</strong>).*?</p>)*)#s', $html, $m, PREG_SET_ORDER ) ) {
		foreach ( $m as $s ) {
			preg_match_all( '#<p[^>]*>(.*?)</p>#s', $s[3], $ps );
			$items[] = array( (int) $s[1], '', injil_help_schema_text( implode( ' ', array_merge( array( $s[2] ), $ps[1] ) ) ) );
		}
	}
	$list = array();
	foreach ( $items as $i => $s ) {
		$q = array(
			'@type'      => 'Quotation',
			'@id'        => $url . '#saying-' . $s[0] . ( isset( $list[ $s[0] ] ) ? '-' . $i : '' ),
			'name'       => 'Saying ' . $s[0] . ( $s[1] ? ': ' . $s[1] : '' ),
			'identifier' => (string) $s[0],
			'text'       => $s[2],
			'inLanguage' => 'en',
			'isPartOf'   => array( '@id' => $work_id ),
		);
		$list[] = array(
			'@type'    => 'ListItem',
			'position' => count( $list ) + 1,
			'item'     => $q,
		);
	}
	return $list;
}

/** Facts about an image from inc/data/images.json, found by its address. */
function injil_help_schema_image_entry( $src ) {
	if ( ! function_exists( 'injil_help_image_key_from_src' ) || ! function_exists( 'injil_help_images' ) ) {
		return array();
	}
	$key = injil_help_image_key_from_src( $src );
	if ( '' === $key ) {
		return array();
	}
	foreach ( injil_help_images() as $img ) {
		if ( ( $img['file'] ?? '' ) === $key ) {
			return $img;
		}
	}
	return array();
}

/** The media type an address names by its extension. */
function injil_help_schema_media_type( $src ) {
	$path = strtolower( (string) wp_parse_url( $src, PHP_URL_PATH ) );
	$map  = array(
		'jpg'  => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'png'  => 'image/png',
		'gif'  => 'image/gif',
		'webp' => 'image/webp',
		'avif' => 'image/avif',
		'svg'  => 'image/svg+xml',
		'tif'  => 'image/tiff',
		'tiff' => 'image/tiff',
	);
	$ext  = pathinfo( rawurldecode( $path ), PATHINFO_EXTENSION );
	return $map[ $ext ] ?? '';
}

/** One ImageObject with what images.json and the figure itself say about it. */
function injil_help_schema_enrich_image( $node, $html ) {
	$src   = (string) ( $node['contentUrl'] ?? '' );
	$entry = injil_help_schema_image_entry( $src );
	// The address first: a sideloaded copy may be AVIF or WebP although the source file is not.
	$type  = injil_help_schema_media_type( $src ) ?: injil_help_schema_media_type( $entry['file'] ?? '' ) ?: injil_help_schema_media_type( $entry['url'] ?? '' );
	if ( $type && ! isset( $node['encodingFormat'] ) ) {
		$node['encodingFormat'] = $type;
	}
	$quoted = preg_quote( $src, '#' );
	if ( preg_match( '#<img[^>]+src="' . $quoted . '"[^>]*>#', $html, $tag ) ) {
		if ( preg_match( '#\swidth="(\d+)"#', $tag[0], $w ) ) {
			$node['width'] = (int) $w[1];
		}
		if ( preg_match( '#\sheight="(\d+)"#', $tag[0], $h ) ) {
			$node['height'] = (int) $h[1];
		}
	}
	if ( $entry ) {
		$credit = trim( (string) ( $entry['credit'] ?? '' ) );
		if ( $credit && ! isset( $node['creditText'] ) ) {
			$node['creditText'] = $credit;
		}
		if ( 'pexels' === ( $entry['source'] ?? '' ) ) {
			$node['license']            = 'https://www.pexels.com/license/';
			$node['acquireLicensePage'] = $node['acquireLicensePage'] ?? (string) ( $entry['page'] ?? '' );
		} else {
			$node['acquireLicensePage'] = $node['acquireLicensePage'] ?? injil_help_commons_page( $entry['file'] );
			if ( ! isset( $node['license'] ) && false !== stripos( $credit, 'public domain' ) ) {
				$node['license']         = 'https://creativecommons.org/publicdomain/mark/1.0/';
				$node['copyrightNotice'] = 'Public domain';
			}
		}
		if ( preg_match( '#Photo: ([^.,]+)#', $credit, $ph ) || preg_match( '#Photograph by ([^.,]+)#', (string) ( $entry['caption'] ?? '' ), $ph ) ) {
			$node['creator']         = array(
				'@type' => 'Person',
				'name'  => trim( $ph[1] ),
			);
			$node['copyrightHolder'] = $node['creator'];
		}
	}
	if ( isset( $node['creator'] ) && ! isset( $node['copyrightHolder'] ) ) {
		$node['copyrightHolder'] = $node['creator'];
	}
	return $node;
}

/** Links to this site in the visible body, outside the "Read next" row, as absolute URLs. */
function injil_help_schema_body_links( $html, $url ) {
	$html  = preg_replace( '#<div class="wp-block-group ih-aside ih-next">.*?</div>#s', '', $html );
	$links = array();
	if ( preg_match_all( '~<a\b[^>]*href="(/[^"#?]*)"~', (string) $html, $m ) ) {
		foreach ( $m[1] as $path ) {
			$abs = home_url( $path );
			if ( $abs !== $url ) {
				$links[ $abs ] = true;
			}
		}
	}
	return array_slice( array_keys( $links ), 0, 30 );
}

/**
 * True when a text names the site's owner. The owner is named in structured data only as the author Person (SSOT.md),
 * so a paragraph that cites the owner's study is not taken as an abstract. The names are kept as
 * SHA-1 digests of lower-case words, so the theme's source does not carry them either.
 */
function injil_help_schema_names_owner( $text ) {
	$withheld = injil_help_schema_owner_digests();
	foreach ( preg_split( '/[^\p{L}]+/u', mb_strtolower( (string) $text ), -1, PREG_SPLIT_NO_EMPTY ) as $word ) {
		if ( isset( $withheld[ sha1( $word ) ] ) ) {
			return true;
		}
	}
	return false;
}

/**
 * The SHA-1 digests of the owner's names, lower case, as keys. The filter may add digests (the
 * test adds a stand-in name); the three built in always stay.
 */
function injil_help_schema_owner_digests() {
	$base  = array(
		'db57089a87a6d3ed7c940a520ae77b6d547c2216' => true,
		'ca184eff29b058d2768bae241379fe368f9d22fe' => true,
		'8e53f2fddde74470942091868d29da9477674eb2' => true,
	);
	$extra = apply_filters( 'injil_help_owner_digests', array() );
	foreach ( (array) $extra as $digest ) {
		if ( is_string( $digest ) && preg_match( '/^[0-9a-f]{40}$/', $digest ) ) {
			$base[ $digest ] = true;
		}
	}
	return $base;
}

/**
 * The owner's name taken out of every value of a graph. A node whose name is the owner's takes the
 * site's name instead and loses its personal properties; any other string loses the owner's words.
 */
function injil_help_schema_unname( $value ) {
	if ( is_string( $value ) ) {
		if ( ! injil_help_schema_names_owner( $value ) ) {
			return $value;
		}
		$withheld = injil_help_schema_owner_digests();
		$clean    = preg_replace_callback(
			'/\p{L}+/u',
			function ( $w ) use ( $withheld ) {
				return isset( $withheld[ sha1( mb_strtolower( $w[0] ) ) ] ) ? '' : $w[0];
			},
			$value
		);
		return trim( preg_replace( '/\s{2,}/u', ' ', $clean ) );
	}
	if ( ! is_array( $value ) ) {
		return $value;
	}
	// The one exception: the author Person node declares the owner as the schema author, with its
	// name, url and sameAs (owner's decision, 4 November 2026). Its other values are cleaned as usual.
	$ids = function_exists( 'injil_help_schema_ids' ) ? injil_help_schema_ids() : array();
	if ( isset( $value['@id'], $ids['author'] ) && $ids['author'] === $value['@id'] && 'Person' === ( $value['@type'] ?? '' ) ) {
		$keep = array_intersect_key( $value, array_flip( array( '@type', '@id', 'name', 'url', 'sameAs' ) ) );
		$rest = injil_help_schema_unname( array_diff_key( $value, $keep ) );
		return array_merge( $keep, $rest );
	}
	if ( isset( $value['name'] ) && is_string( $value['name'] ) && injil_help_schema_names_owner( $value['name'] ) ) {
		$value['name'] = get_bloginfo( 'name' );
		unset( $value['givenName'], $value['familyName'], $value['alternateName'], $value['sameAs'], $value['image'], $value['description'] );
		if ( isset( $value['@type'] ) && 'Person' === $value['@type'] ) {
			$value['@type'] = 'Organization';
		}
	}
	foreach ( $value as $k => $v ) {
		$value[ $k ] = injil_help_schema_unname( $v );
	}
	return $value;
}

/** The first paragraph of the body, as the visible lede. */
function injil_help_schema_lede( $html ) {
	if ( preg_match_all( '#<p(?:\s+class="([^"]*)")?[^>]*>(.*?)</p>#s', $html, $m, PREG_SET_ORDER ) ) {
		foreach ( $m as $p ) {
			if ( preg_match( '/\bih-(running-head|argument|chapter-num|eyebrow|meta|cite|breadcrumb|gloss)\b/', $p[1] ) ) {
				continue;
			}
			$text = injil_help_schema_text( preg_replace( '#<span class="ih-v[^"]*"[^>]*>.*?</span>|<sup\b.*?</sup>#s', ' ', $p[2] ) );
			if ( mb_strlen( $text ) >= 60 && ! injil_help_schema_names_owner( $text ) ) {
				return $text;
			}
		}
	}
	return '';
}

/** The gospels and works a comparison table's columns name, as entity references. */
function injil_help_schema_table_about( $description ) {
	$map  = array(
		'Mark'    => 'gospel-of-mark',
		'Matthew' => 'gospel-of-matthew',
		'Luke'    => 'gospel-of-luke',
		'John'    => 'gospel-of-john',
		'Thomas'  => 'gospel-of-thomas',
	);
	$refs = array();
	foreach ( $map as $col => $key ) {
		if ( preg_match( '/\b' . $col . '\b/', $description ) ) {
			$refs[] = array( '@id' => injil_help_schema_entity_id( $key ) );
		}
	}
	return $refs;
}

/**
 * The second pass over the page's graph. $ctx: url, webpage_id, article_id, html, post, path, ids.
 */
function injil_help_schema_extend( $graph, $ctx ) {
	$url        = $ctx['url'];
	$html       = $ctx['html'];
	$ids        = $ctx['ids'];
	$path       = $ctx['path'];
	$post       = $ctx['post'];
	$webpage_id = $ctx['webpage_id'];
	$article_id = $ctx['article_id'];
	$home       = home_url( '/' );

	$index = array();
	foreach ( $graph as $i => $node ) {
		if ( isset( $node['@id'] ) ) {
			$index[ $node['@id'] ] = $i;
		}
	}
	$has_article = isset( $index[ $article_id ] );
	$extra       = array();

	// The Qur'an as scripture.
	if ( isset( $index[ $ids['quran'] ] ) ) {
		$graph[ $index[ $ids['quran'] ] ]['genre'] = 'Scripture';
	}

	// Qur'an verses shown on the page, under their surahs.
	$verses = injil_help_schema_verse_quotes( $html, $url, $ids['quran'] );
	$surahs = array();
	foreach ( $verses as $q ) {
		$surahs[ (int) $q['identifier'] ] = true;
		if ( 'quran/passages' === $path ) {
			$q['isPartOf'] = array( $q['isPartOf'], array( '@id' => home_url( '/quran/passages/' ) . '#translation' ) );
		}
		$extra[] = $q;
	}
	foreach ( array_keys( $surahs ) as $n ) {
		$extra[] = array(
			'@type'      => 'Chapter',
			'@id'        => injil_help_schema_surah_id( $n ),
			'name'       => 'Sūrah ' . $n,
			'position'   => $n,
			'inLanguage' => 'ar',
			'isPartOf'   => array( '@id' => $ids['quran'] ),
		);
	}

	// The glossary's terms, each with its own @id, its set and its other spellings.
	foreach ( $graph as $i => $node ) {
		if ( 'DefinedTermSet' !== ( $node['@type'] ?? '' ) || empty( $node['hasDefinedTerm'] ) ) {
			continue;
		}
		$used = array();
		foreach ( $node['hasDefinedTerm'] as $t => $term ) {
			$slug = injil_help_schema_slug( $term['name'] ) ?: 'term';
			$id   = $url . '#term-' . $slug;
			$n    = 2;
			while ( isset( $used[ $id ] ) ) {
				$id = $url . '#term-' . $slug . '-' . $n++;
			}
			$used[ $id ]      = true;
			$term['@id']      = $id;
			$term['inDefinedTermSet'] = array( '@id' => $node['@id'] );
			$graph[ $i ]['hasDefinedTerm'][ $t ] = $term;
		}
		$graph[ $i ]['inLanguage'] = 'en';
		$graph[ $i ]['isPartOf']   = array( '@id' => $webpage_id );
	}

	// Images: credits, licence, format and size; quotations, events and tables get an @id.
	$q = 0;
	$t = 0;
	$events = array();
	foreach ( $graph as $i => $node ) {
		$type = $node['@type'] ?? '';
		if ( 'ImageObject' === $type ) {
			$graph[ $i ] = injil_help_schema_enrich_image( $node, $html );
		} elseif ( 'Quotation' === $type && ! isset( $node['@id'] ) ) {
			$graph[ $i ]['@id'] = $url . '#quotation-' . ( ++$q );
		} elseif ( 'Table' === $type && ! isset( $node['@id'] ) ) {
			$graph[ $i ]['@id'] = $url . '#table-' . ( ++$t );
			$about              = injil_help_schema_table_about( (string) ( $node['description'] ?? '' ) );
			if ( $about ) {
				$graph[ $i ]['about'] = $about;
			}
		} elseif ( 'Event' === $type && ! isset( $node['@id'] ) ) {
			$graph[ $i ]['@id'] = $url . '#event-' . ( count( $events ) + 1 );
			$events[]           = $graph[ $i ]['@id'];
		}
	}
	if ( $events ) {
		$extra[] = array(
			'@type'           => 'ItemList',
			'@id'             => $url . '#timeline',
			'name'            => wp_strip_all_tags( $post ? get_the_title( $post ) : '' ),
			'itemListOrder'   => 'https://schema.org/ItemListOrderAscending',
			'numberOfItems'   => count( $events ),
			'itemListElement' => array_map(
				function ( $id, $n ) {
					return array(
						'@type'    => 'ListItem',
						'position' => $n + 1,
						'item'     => array( '@id' => $id ),
					);
				},
				$events,
				array_keys( $events )
			),
		);
	}

	// The sayings of Thomas, in order.
	if ( in_array( $path, array( 'gospels/thomas/sayings', 'gospels/thomas/sayings-patterson-meyer' ), true ) ) {
		$sayings = injil_help_schema_sayings( $html, $url, $url . '#translation' );
		if ( $sayings ) {
			$extra[] = array(
				'@type'           => 'ItemList',
				'@id'             => $url . '#sayings',
				'name'            => 'The sayings of the Gospel of Thomas',
				'itemListOrder'   => 'https://schema.org/ItemListOrderAscending',
				'numberOfItems'   => count( $sayings ),
				'itemListElement' => $sayings,
			);
		}
	}

	// The page: its navigation's links and the site links in its body.
	if ( isset( $index[ $webpage_id ] ) ) {
		$wp  = &$graph[ $index[ $webpage_id ] ];
		$nav = isset( $index[ $ids['nav'] ] ) ? $graph[ $index[ $ids['nav'] ] ] : null;
		if ( $nav && ! empty( $wp['hasPart'] ) ) {
			foreach ( $wp['hasPart'] as $k => $part ) {
				if ( 'SiteNavigationElement' === ( $part['@type'] ?? '' ) ) {
					$wp['hasPart'][ $k ]['hasPart'] = array_map(
						function ( $n ) use ( $home ) {
							return array( '@id' => $home . '#nav-' . ( $n + 1 ) );
						},
						array_keys( $nav['itemListElement'] )
					);
				}
			}
		}
		$links = injil_help_schema_body_links( $html, $url );
		if ( $links ) {
			$wp['significantLink'] = $links;
		}
		unset( $wp );
	}

	// The article: its lede, dates, access modes and the Qur'an verses it quotes.
	if ( $has_article && $post ) {
		$a    = &$graph[ $index[ $article_id ] ];
		$lede = injil_help_schema_lede( $html );
		if ( $lede ) {
			$a['abstract'] = $lede;
		}
		$a['dateCreated']   = get_the_date( 'c', $post );
		$a['copyrightYear'] = (int) get_the_date( 'Y', $post );
		$images             = false !== strpos( $html, '<img' );
		$a['accessMode']    = $images ? array( 'textual', 'visual' ) : array( 'textual' );
		$a['accessModeSufficient'] = array(
			array(
				'@type'      => 'ItemList',
				'itemListElement' => array( 'textual' ),
				'description' => 'Text only',
			),
		);
		$features = array();
		if ( preg_match( '#<h2\b#', $html ) ) {
			$features[] = 'structuralNavigation';
		}
		if ( $images && ! preg_match( '#<img(?![^>]*\salt="[^"]+")[^>]*>#', $html ) ) {
			$features[] = 'alternativeText';
		}
		if ( $features ) {
			$a['accessibilityFeature'] = $features;
		}
		if ( $verses ) {
			$cites = isset( $a['citation'] ) ? (array) $a['citation'] : array();
			foreach ( $verses as $v ) {
				$cites[] = array( '@id' => $v['@id'] );
			}
			$a['citation'] = $cites;
		}
		unset( $a );
	}

	// The header links as SiteNavigationElements with their own @id.
	if ( isset( $index[ $ids['nav'] ] ) ) {
		foreach ( $graph[ $index[ $ids['nav'] ] ]['itemListElement'] as $k => $li ) {
			$graph[ $index[ $ids['nav'] ] ]['itemListElement'][ $k ]['item']['@id'] = $home . '#nav-' . ( $k + 1 );
		}
	}

	$graph = array_merge( $graph, $extra );

	// Entities: each one the page refers to is defined once.
	$refs = array();
	injil_help_schema_refs( $graph, $refs );
	$have = array();
	foreach ( $graph as $node ) {
		if ( isset( $node['@id'] ) ) {
			$have[ $node['@id'] ] = true;
		}
	}
	foreach ( injil_help_schema_entity_nodes() as $id => $node ) {
		if ( isset( $refs[ $id ] ) && ! isset( $have[ $id ] ) ) {
			$graph[] = $node;
		}
	}

	return injil_help_schema_unname( injil_help_schema_scrub( $graph ) );
}
