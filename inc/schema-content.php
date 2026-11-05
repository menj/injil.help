<?php
/**
 * Structured data for the content and page elements that inc/schema.php does not model by itself:
 * the site's editorial policies, the page's own elements (header, navigation, footer, main content,
 * notes, share row, "Read next"), the claim labels and status badges, the objection-and-reply blocks,
 * the claims on the responses page, the corrections log, the Qur'anic glosses, the manuscripts table,
 * the translations reproduced on the site, and the subjects of each part. Every function returns
 * schema.org nodes or properties; injil_help_schema_graph() places them.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

/** Plain text of a fragment of block HTML. */
function injil_help_schema_text( $html ) {
	return trim( preg_replace( '/\s+/u', ' ', html_entity_decode( wp_strip_all_tags( (string) $html ), ENT_QUOTES ) ) );
}

/** The site's editorial policies, as properties of the Organization, which is also typed NewsMediaOrganization for masthead and verificationFactCheckingPolicy. */
function injil_help_schema_policies() {
	return array(
		'publishingPrinciples'           => home_url( '/method/' ),
		'verificationFactCheckingPolicy' => home_url( '/method/' ),
		'correctionsPolicy'              => home_url( '/corrections/' ),
		'actionableFeedbackPolicy'       => home_url( '/contact/' ),
		'masthead'                       => home_url( '/about/' ),
	);
}

/**
 * The page's own elements: header, navigation and footer on every page; the main content; and,
 * when the page has them, its notes and its share row. Each is a WebPageElement with its selector.
 */
function injil_help_schema_page_elements( $url, $html ) {
	$parts = array(
		array(
			'@type'       => 'WPHeader',
			'@id'         => $url . '#header',
			'cssSelector' => '.ih-header',
		),
		array(
			'@type'       => 'SiteNavigationElement',
			'@id'         => $url . '#navigation',
			'name'        => 'Site navigation',
			'cssSelector' => '.ih-nav',
		),
		array(
			'@type'       => 'WPFooter',
			'@id'         => $url . '#footer',
			'cssSelector' => '.ih-footer',
		),
	);
	if ( false !== strpos( $html, 'id="notes"' ) ) {
		$parts[] = array(
			'@type'       => 'WebPageElement',
			'@id'         => $url . '#notes',
			'name'        => 'Notes',
			'cssSelector' => '#notes ~ ol',
		);
	}
	if ( function_exists( 'injil_help_share_wanted' ) && injil_help_share_wanted() ) {
		$parts[] = array(
			'@type'       => 'WebPageElement',
			'@id'         => $url . '#share',
			'name'        => 'Share',
			'cssSelector' => '.ih-share',
		);
	}
	return array(
		'hasPart'           => $parts,
		'mainContentOfPage' => array(
			'@type'       => 'WebPageElement',
			'@id'         => $url . '#main',
			'cssSelector' => 'main',
		),
	);
}

/** The share row's networks as ShareActions, each with the URL it opens. */
function injil_help_schema_share_actions( $post ) {
	if ( ! $post || ! function_exists( 'injil_help_share_wanted' ) || ! injil_help_share_wanted() || ! function_exists( 'injil_help_share_markup' ) ) {
		return array();
	}
	$actions = array();
	if ( preg_match_all( '#<li class="ih-share-item ih-share-([a-z0-9-]+)"><a class="ih-share-link" href="([^"]+)"#', injil_help_share_markup( $post ), $m, PREG_SET_ORDER ) ) {
		foreach ( $m as $hit ) {
			$actions[] = array(
				'@type'  => 'ShareAction',
				'name'   => ucfirst( $hit[1] ),
				'target' => array(
					'@type'       => 'EntryPoint',
					'urlTemplate' => html_entity_decode( $hit[2], ENT_QUOTES ),
				),
			);
		}
	}
	return $actions;
}

/** The "Read next" row as absolute URLs, for WebPage relatedLink. */
function injil_help_schema_related_links( $html ) {
	$links = array();
	if ( preg_match_all( '#<div class="wp-block-group ih-aside ih-next">(.*?)</div>#s', $html, $rows ) ) {
		foreach ( $rows[1] as $row ) {
			if ( preg_match_all( '~href="(/[^"#?]*)"~', $row, $m ) ) {
				foreach ( $m[1] as $path ) {
					$links[] = home_url( $path );
				}
			}
		}
	}
	return array_values( array_unique( $links ) );
}

/**
 * The claim labels (Scholarly consensus, Minority view, Islamic position) with the paragraph each
 * one heads, and the gospel status badges (Agrees, Conflicts and the rest) with their item: each
 * a Claim that appears on this page.
 */
function injil_help_schema_claims( $html, $appearance, $org_id, $limit = 60 ) {
	$claims = array();
	if ( preg_match_all( '#<p class="is-style-label-(consensus|minority|islamic)">(.*?)</p>\s*(?:<!--[^>]*-->\s*)*<p[^>]*>(.*?)</p>#s', $html, $m, PREG_SET_ORDER ) ) {
		foreach ( $m as $hit ) {
			$text = injil_help_schema_text( $hit[3] );
			if ( '' === $text ) {
				continue;
			}
			$claims[] = array(
				'@type'      => 'Claim',
				'name'       => injil_help_schema_text( $hit[2] ),
				'text'       => $text,
				'keywords'   => injil_help_schema_text( $hit[2] ),
				'appearance' => array( '@id' => $appearance ),
				'author'     => array( '@id' => $org_id ),
			);
		}
	}
	if ( preg_match_all( '#<li class="ih-s ih-s-([a-z]+)"><span class="ih-badge">(.*?)</span>(.*?)</li>#s', $html, $m, PREG_SET_ORDER ) ) {
		foreach ( $m as $hit ) {
			$text = injil_help_schema_text( $hit[3] );
			if ( '' === $text ) {
				continue;
			}
			$claims[] = array(
				'@type'      => 'Claim',
				'name'       => injil_help_schema_text( $hit[2] ),
				'text'       => $text,
				'keywords'   => injil_help_schema_text( $hit[2] ),
				'appearance' => array( '@id' => $appearance ),
				'author'     => array( '@id' => $org_id ),
			);
		}
	}
	return array_slice( $claims, 0, $limit );
}

/**
 * Each objection-and-reply block as a Question: the objection, the best Christian reply as the
 * suggested answer, and the site's response as the accepted answer.
 */
function injil_help_schema_objections( $html ) {
	$out = array();
	if ( ! preg_match_all( '#<div class="wp-block-group[^"]*is-style-objection-reply">(.*?)(?=<div class="wp-block-group[^"]*is-style-objection-reply">|<h2|$)#s', $html, $blocks ) ) {
		return $out;
	}
	foreach ( $blocks[1] as $block ) {
		$parts = preg_split( '#<h4 class="wp-block-heading">(.*?)</h4>#', $block, -1, PREG_SPLIT_DELIM_CAPTURE );
		$by    = array();
		for ( $i = 1; $i + 1 < count( $parts ); $i += 2 ) {
			$by[ strtolower( injil_help_schema_text( $parts[ $i ] ) ) ] = injil_help_schema_text( $parts[ $i + 1 ] );
		}
		if ( empty( $by['objection'] ) || empty( $by['response'] ) ) {
			continue;
		}
		$question = array(
			'@type'          => 'Question',
			'name'           => $by['objection'],
			'text'           => $by['objection'],
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => $by['response'],
			),
		);
		if ( ! empty( $by['best christian reply'] ) ) {
			$question['suggestedAnswer'] = array(
				'@type' => 'Answer',
				'text'  => $by['best christian reply'],
			);
		}
		$out[] = $question;
	}
	return $out;
}

/** The claims stated on the responses page ("Claim." paragraphs), each with the article it appeared in. */
function injil_help_schema_response_claims( $html, $org_id ) {
	$out = array();
	foreach ( preg_split( '#<h2[^>]*>#', $html ) as $section ) {
		$heading = preg_match( '#^(.*?)</h2>#s', $section, $h ) ? injil_help_schema_text( $h[1] ) : '';
		if ( ! preg_match( '/^\d+\.\s*(.+)$/u', $heading, $name ) ) {
			continue;
		}
		if ( preg_match_all( '#<p><strong>Claim\.</strong>(.*?)</p>#s', $section, $m ) ) {
			foreach ( $m[1] as $claim ) {
				$out[] = array(
					'@type'             => 'Claim',
					'text'              => injil_help_schema_text( $claim ),
					'firstAppearance'   => array(
						'@type' => 'CreativeWork',
						'name'  => $name[1],
					),
					'claimInterpreter'  => array( '@id' => $org_id ),
				);
			}
		}
	}
	return $out;
}

/** The corrections log, read from its pattern: [ date, path, page name, was, now ] per row. */
function injil_help_schema_correction_rows() {
	static $rows = null;
	if ( null !== $rows ) {
		return $rows;
	}
	$rows    = array();
	$pattern = function_exists( 'injil_help_pattern_file' ) ? injil_help_pattern_file( 'corrections-log' ) : null;
	if ( ! $pattern || ! preg_match_all( '#<tr><td>([0-9-]{10})</td><td>(.*?)</td><td>(.*?)</td><td>(.*?)</td></tr>#s', $pattern['content'], $m, PREG_SET_ORDER ) ) {
		return $rows;
	}
	foreach ( $m as $hit ) {
		$path   = preg_match( '~href="/([^"#?]*?)/?"~', $hit[2], $p ) ? $p[1] : '';
		$rows[] = array( $hit[1], $path, injil_help_schema_text( $hit[2] ), injil_help_schema_text( $hit[3] ), injil_help_schema_text( $hit[4] ) );
	}
	return $rows;
}

/** CorrectionComment nodes: all of them for the log itself, or those for one page path. */
function injil_help_schema_corrections( $path = null ) {
	$out = array();
	foreach ( injil_help_schema_correction_rows() as $row ) {
		if ( null !== $path && $row[1] !== $path ) {
			continue;
		}
		$node = array(
			'@type'       => 'CorrectionComment',
			'dateCreated' => $row[0],
			'text'        => sprintf( 'Was: %s Now: %s', $row[3], $row[4] ),
			'url'         => home_url( '/corrections/' ),
		);
		if ( $row[1] ) {
			$node['about'] = array(
				'@type' => 'WebPage',
				'url'   => home_url( '/' . $row[1] . '/' ),
				'name'  => $row[2],
			);
		}
		$out[] = $node;
	}
	return $out;
}

/** The Qur'anic glosses (Arabic in KFGQPC Hafs with a citation line) as Quotations from the Qur'an. */
function injil_help_schema_glosses( $html, $quran_id ) {
	$out = array();
	if ( preg_match_all( '#<p class="ih-gloss ih-ar ih-quran"[^>]*>(.*?)</p>\s*(?:<!--[^>]*-->\s*)*<p class="ih-cite">(.*?)</p>#s', $html, $m, PREG_SET_ORDER ) ) {
		foreach ( $m as $hit ) {
			$cite  = injil_help_schema_text( $hit[2] );
			$quote = array(
				'@type'      => 'Quotation',
				'text'       => injil_help_schema_text( $hit[1] ),
				'inLanguage' => 'ar',
				'isPartOf'   => array( '@id' => $quran_id ),
			);
			if ( preg_match( '/^(.*?)\s*·\s*Q\s*(\d+:\d+(?:-\d+)?)$/u', $cite, $c ) ) {
				$quote['description'] = $c[1];
				$quote['citation']    = 'Qur’an ' . $c[2];
			} elseif ( '' !== $cite ) {
				$quote['description'] = $cite;
			}
			$out[] = $quote;
		}
	}
	return $out;
}

/** The manuscripts table as Manuscript nodes, each an example of the work it carries. */
function injil_help_schema_manuscripts( $html ) {
	$out = array();
	if ( ! preg_match( '#<table><thead><tr><th>Work</th><th>Manuscript</th>.*?<tbody>(.*?)</tbody>#s', $html, $t ) ) {
		return $out;
	}
	if ( preg_match_all( '#<tr><td>(.*?)</td><td>(.*?)</td><td>(.*?)</td><td>(.*?)</td><td>(.*?)</td></tr>#s', $t[1], $rows, PREG_SET_ORDER ) ) {
		foreach ( $rows as $r ) {
			$form  = injil_help_schema_text( $r[3] );
			$out[] = array(
				'@type'         => 'Manuscript',
				'name'          => injil_help_schema_text( $r[2] ),
				'exampleOfWork' => array(
					'@type' => 'CreativeWork',
					'name'  => injil_help_schema_text( $r[1] ),
				),
				'inLanguage'    => trim( strtok( $form, ',' ) ),
				'material'      => trim( (string) substr( $form, strlen( strtok( $form, ',' ) ) + 1 ) ),
				'temporalCoverage' => injil_help_schema_text( $r[4] ),
				'description'   => injil_help_schema_text( $r[5] ),
			);
		}
	}
	return $out;
}

/**
 * The translations reproduced in full: the Gospel of Thomas in Mattison's and in Patterson and
 * Meyer's renderings, and the Qur'an passages in The Clear Quran. Returns the nodes and the id the
 * page's article belongs to, or an empty array.
 */
function injil_help_schema_translations( $path, $quran_id ) {
	$thomas = array(
		'@type'      => 'Book',
		'@id'        => home_url( '/gospels/thomas/' ) . '#work',
		'name'       => 'The Gospel of Thomas',
		'inLanguage' => 'cop',
	);
	switch ( $path ) {
		case 'gospels/thomas/sayings':
			$id = home_url( '/gospels/thomas/sayings/' ) . '#translation';
			return array(
				'part_of' => $id,
				'nodes'   => array(
					$thomas + array( 'workTranslation' => array( '@id' => $id ) ),
					array(
						'@type'             => 'CreativeWork',
						'@id'               => $id,
						'name'              => 'The Gospel of Thomas, translated by Mark M. Mattison',
						'inLanguage'        => 'en',
						'translationOfWork' => array( '@id' => $thomas['@id'] ),
						'translator'        => array( '@type' => 'Person', 'name' => 'Mark M. Mattison' ),
						'license'           => 'https://creativecommons.org/publicdomain/mark/1.0/',
						'copyrightNotice'   => 'Public domain',
					),
				),
			);
		case 'gospels/thomas/sayings-patterson-meyer':
			$id = home_url( '/gospels/thomas/sayings-patterson-meyer/' ) . '#translation';
			return array(
				'part_of' => $id,
				'nodes'   => array(
					$thomas + array( 'workTranslation' => array( '@id' => $id ) ),
					array(
						'@type'             => 'CreativeWork',
						'@id'               => $id,
						'name'              => 'The Gospel of Thomas, translated by Stephen J. Patterson and Marvin Meyer',
						'inLanguage'        => 'en',
						'translationOfWork' => array( '@id' => $thomas['@id'] ),
						'translator'        => array(
							array( '@type' => 'Person', 'name' => 'Stephen J. Patterson' ),
							array( '@type' => 'Person', 'name' => 'Marvin Meyer' ),
						),
						'copyrightNotice'   => 'Used with permission',
					),
				),
			);
		case 'quran/passages':
			$id = home_url( '/quran/passages/' ) . '#translation';
			return array(
				'part_of' => $id,
				'nodes'   => array(
					array(
						'@type'             => 'CreativeWork',
						'@id'               => $id,
						'name'              => 'The Clear Quran',
						'inLanguage'        => 'en',
						'translationOfWork' => array( '@id' => $quran_id ),
						'translator'        => array( '@type' => 'Person', 'name' => 'Mustafa Khattab' ),
						'editor'            => array( '@type' => 'Person', 'name' => 'Alham Ahmed' ),
					),
				),
			);
	}
	return array();
}

/** The subjects of a page from inc/data/schema-entities.json, as @id references: about (the part it belongs to) and mentions (every page). */
function injil_help_schema_entities( $path ) {
	static $data = null;
	if ( null === $data ) {
		$data = injil_help_data_file( 'schema-entities' );
	}
	$entities = (array) ( $data['entities'] ?? array() );
	$paths    = (array) ( $data['paths'] ?? array() );
	$match    = '';
	foreach ( array_keys( $paths ) as $prefix ) {
		if ( '*' !== $prefix && ( $path === $prefix || 0 === strpos( $path, $prefix . '/' ) ) && strlen( $prefix ) > strlen( $match ) ) {
			$match = $prefix;
		}
	}
	$pick = function ( $keys ) use ( $entities ) {
		$out = array();
		foreach ( (array) $keys as $key ) {
			if ( isset( $entities[ $key ] ) ) {
				$out[] = array( '@id' => injil_help_schema_entity_id( $key ) );
			}
		}
		return $out;
	};
	return array(
		'about'    => '' !== $match ? $pick( $paths[ $match ] ) : array(),
		'mentions' => $pick( $paths['*'] ?? array() ),
	);
}

// The last line: functions.php checks for it, so a file an upload cut short is caught.
$GLOBALS['injil_help_loaded'][ basename( __FILE__, '.php' ) ] = true;
