<?php
/**
 * The Gospel According to Islam: the Introduction and the 27 section pages, built
 * from inc/data/gospel-islam.json (section titles, front matter), narrative.json
 * (the text and notes), thomas.json (the Gospel of Thomas sections) and substitution.json
 * (the early Christian witnesses to the substitution at the cross, at section 26). Credits are on the separate Credits page.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

require_once get_stylesheet_directory() . '/inc/quran-jesus.php';

/** Decode one JSON data file, or an empty array if it is missing or malformed. */
function injil_help_data_file( $name ) {
	$file = get_stylesheet_directory() . '/inc/data/' . $name . '.json';
	if ( ! is_readable( $file ) ) {
		return array();
	}
	$data = json_decode( (string) file_get_contents( $file ), true );
	return is_array( $data ) ? $data : array();
}

/**
 * A piece of page text from inc/data/text.json, { group: { key: text } }: a string (format
 * strings keep their sprintf placeholders) or a list of markup lines. The words the builders put
 * into page content live there, never in PHP. Returns '' for a key that is not there.
 */
function injil_help_text( $group, $key ) {
	static $text = null;
	if ( null === $text ) {
		$text = injil_help_data_file( 'text' );
	}
	return $text[ $group ][ $key ] ?? '';
}

/** Gospel of Thomas sections woven into the chapters, keyed by section number; each a list of page-markup lines. */
function injil_help_gospel_thomas_sections() {
	return injil_help_data_file( 'thomas' );
}

/** Everything woven into a section after Shafaat's text: Thomas, the infancy gospels, then the substitution witnesses. Keyed by section number. */
function injil_help_gospel_woven_sections() {
	$out = array();
	foreach ( array( injil_help_gospel_thomas_sections(), injil_help_data_file( 'infancy' ), injil_help_data_file( 'substitution' ) ) as $set ) {
		foreach ( (array) $set as $n => $lines ) {
			$out[ (string) $n ] = array_merge( $out[ (string) $n ] ?? array(), array_values( (array) $lines ) );
		}
	}
	return $out;
}

/**
 * Section titles, section slugs and front matter from gospel-islam.json:
 * { "section_titles": { "1": "..." }, "section_slugs": { "1": "family-of-amran-zacharias-and-john" },
 *   "front": { "introduction": { "title", "paragraphs": [...] } } }.
 * The slugs are the words of each section's URL (/gospel-according-to-islam/{slug}/); the
 * section number is never a URL segment.
 */
function injil_help_gospel_islam_data() {
	static $data = null;
	if ( null !== $data ) {
		return $data;
	}
	$json = injil_help_data_file( 'gospel-islam' );
	$data = array(
		'chapters' => array(),
		'front'    => array(),
	);
	foreach ( (array) ( $json['section_titles'] ?? array() ) as $n => $title ) {
		$data['chapters'][ (int) $n ] = array(
			'title' => (string) $title,
			'slug'  => (string) ( $json['section_slugs'][ $n ] ?? sanitize_title( (string) $title ) ),
		);
	}
	foreach ( (array) ( $json['front'] ?? array() ) as $key => $f ) {
		$data['front'][ $key ] = array(
			'title' => (string) ( $f['title'] ?? '' ),
			'paras' => array_values( (array) ( $f['paragraphs'] ?? array() ) ),
		);
	}
	return $data;
}

/** The URL slug of gospel section $n (the words of its title, from gospel-islam.json). */
function injil_help_gospel_section_slug( $n ) {
	$data = injil_help_gospel_islam_data();
	return (string) ( $data['chapters'][ (int) $n ]['slug'] ?? (string) (int) $n );
}

/** The site path of gospel section $n, without slashes: gospel-according-to-islam/{slug}. */
function injil_help_gospel_section_path( $n ) {
	return 'gospel-according-to-islam/' . injil_help_gospel_section_slug( $n );
}

/** Section number by slug, or 0 when the slug is not a section. */
function injil_help_gospel_section_number( $slug ) {
	foreach ( injil_help_gospel_islam_data()['chapters'] as $n => $c ) {
		if ( $c['slug'] === (string) $slug ) {
			return (int) $n;
		}
	}
	return 0;
}

/**
 * The Narrative of Jesus, Son of Mary, from narrative.json:
 * { "chapters": [ { "number", "title", "sections": [ { "number", "lines": [...] } ] } ], "notes": { "n": "note" } }.
 * Lines carry "### " for the Qur'anic headings and [^n] note markers; the note
 * numbering runs through the whole narrative.
 */
function injil_help_narrative_data() {
	static $d = null;
	if ( null !== $d ) {
		return $d;
	}
	$d    = array(
		'sections' => array(),
		'chapters' => array(),
		'notes'    => array(),
	);
	$json = injil_help_data_file( 'narrative' );
	foreach ( (array) ( $json['chapters'] ?? array() ) as $chapter ) {
		$num                   = (int) ( $chapter['number'] ?? 0 );
		$d['chapters'][ $num ] = (string) ( $chapter['title'] ?? '' );
		foreach ( (array) ( $chapter['sections'] ?? array() ) as $section ) {
			$d['sections'][ (int) ( $section['number'] ?? 0 ) ] = array(
				'chapter' => $num,
				'lines'   => array_values( (array) ( $section['lines'] ?? array() ) ),
			);
		}
	}
	foreach ( (array) ( $json['notes'] ?? array() ) as $k => $note ) {
		$d['notes'][ (int) $k ] = (string) $note;
	}
	ksort( $d['sections'] );
	return $d;
}

/** Where each numbered note lands: note => array( section, number on that page ). */
function injil_help_narrative_refs() {
	static $refs = null;
	if ( null !== $refs ) {
		return $refs;
	}
	$refs = array();
	foreach ( injil_help_narrative_data()['sections'] as $n => $s ) {
		$local = 0;
		foreach ( $s['lines'] as $line ) {
			if ( preg_match_all( '/\[\^(\d+)\]/', $line, $mm ) ) {
				foreach ( $mm[1] as $k ) {
					$refs[ (int) $k ] = array( $n, ++$local );
				}
			}
		}
	}
	return $refs;
}

/** Turn "note 19" and "notes 49, 50" inside a note into links to those notes. */
function injil_help_narrative_link_notes( $text, $current ) {
	$refs = injil_help_narrative_refs();
	return preg_replace_callback(
		'/\b(notes?) (\d+(?:(?:, and |, | and )\d+)*)/',
		function ( $m ) use ( $refs, $current ) {
			$parts = preg_split( '/(, and |, | and )/', $m[2], -1, PREG_SPLIT_DELIM_CAPTURE );
			$out   = '';
			foreach ( $parts as $i => $p ) {
				$k = (int) $p;
				if ( $i % 2 || ! isset( $refs[ $k ] ) ) {
					$out .= $p;
					continue;
				}
				$href = ( $refs[ $k ][0] === $current ? '' : '/' . injil_help_gospel_section_path( $refs[ $k ][0] ) . '/' ) . '#fn-' . $refs[ $k ][1];
				$out .= '<a href="' . $href . '">' . $k . '</a>';
			}
			return $m[1] . ' ' . $out;
		},
		$text
	);
}

/** Page definitions for the front matter and the 27 sections. */
/**
 * The head of a section page in the Bible layout: the running head, the opening of the text block,
 * the argument (title, verse count and the Qur'anic passages set in the section) and the numeral.
 * Verses are counted from the "[v]" markers in the narrative data; the Qur'anic references from "[qS:A]".
 */
function injil_help_gospel_section_head( $n, $title, $sec, $nar ) {
	$verses = 0;
	$refs   = array();
	foreach ( $sec['lines'] as $line ) {
		$verses += substr_count( $line, '[v]' );
		if ( preg_match_all( '/\[q(\d+):(\d+)\]/', $line, $m, PREG_SET_ORDER ) ) {
			$first  = $m[0];
			$last   = end( $m );
			$refs[] = $first[1] . ':' . $first[2] . ( $last[2] !== $first[2] ? '-' . $last[2] : '' );
		}
	}
	$chapter  = sprintf( injil_help_text( 'gospel', 'chapter' ), $sec['chapter'], $nar['chapters'][ $sec['chapter'] ] );
	$argument = sprintf( injil_help_text( 'gospel', 'argument' ), $title, $verses );
	if ( $refs ) {
		$argument .= sprintf( injil_help_text( 'gospel', 'argument_quran' ), implode( '; ', $refs ) );
	}
	return array(
		'[p.ih-running-head] ' . sprintf( injil_help_text( 'gospel', 'running_head_section' ), $chapter, $n ),
		'[bible]',
		'[p.ih-argument] ' . $argument,
		'[p.ih-chapter-num] ' . $n,
	);
}

function injil_help_gospel_islam_pages() {
	$data  = injil_help_gospel_islam_data();
	$base  = 'gospel-according-to-islam';
	$thomas = injil_help_gospel_woven_sections();
	$pages = array();

	$front = array(
		'introduction' => array( 'introduction', injil_help_text( 'gospel', 'introduction_excerpt' ) ),
	);
	foreach ( $front as $k => $f ) {
		if ( empty( $data['front'][ $k ] ) ) {
			continue;
		}
		// The Introduction is set like the sections: running head and the text in the Bible columns.
		$body   = array_merge(
			array( '[p.ih-running-head] ' . sprintf( injil_help_text( 'gospel', 'running_head' ), $data['front'][ $k ]['title'] ), '[bible]' ),
			$data['front'][ $k ]['paras'],
			array( '[/bible]' )
		);
		$pages[] = array(
			'path'    => $base . '/' . $f[0],
			'title'   => $data['front'][ $k ]['title'],
			'excerpt' => $f[1],
			'body'    => $body,
		);
	}

	$nar  = injil_help_narrative_data();
	$last = count( $nar['sections'] );
	foreach ( $nar['sections'] as $n => $sec ) {
		$title = isset( $data['chapters'][ $n ]['title'] ) ? $data['chapters'][ $n ]['title'] : '';
		$body  = injil_help_gospel_section_head( $n, $title, $sec, $nar );
		foreach ( $sec['lines'] as $line ) {
			$body[] = preg_replace_callback(
				'/\[\^(\d+)\]/',
				function ( $m ) use ( $nar, $n ) {
					$k = (int) $m[1];
					return isset( $nar['notes'][ $k ] ) ? '{{fn:' . injil_help_narrative_link_notes( $nar['notes'][ $k ], $n ) . '}}' : '';
				},
				$line
			);
		}
		$body[] = '[/bible]';
		$body[] = '[notes]';
		if ( ! empty( $thomas[ (string) $n ] ) ) {
			$body = array_merge( $body, $thomas[ (string) $n ] );
		}
		if ( function_exists( 'injil_help_tafsir_section_lines' ) ) {
			$body = array_merge( $body, injil_help_tafsir_section_lines( (int) $n ) );
		}
		$nav = array();
		if ( $n > 1 ) {
			$nav[] = '<a href="/' . injil_help_gospel_section_path( $n - 1 ) . '/">' . sprintf( injil_help_text( 'gospel', 'prev' ), $n - 1, esc_html( $data['chapters'][ $n - 1 ]['title'] ?? '' ) ) . '</a>';
		}
		$nav[] = '<a href="/' . $base . '/">' . injil_help_text( 'gospel', 'contents' ) . '</a>';
		if ( $n < $last ) {
			$nav[] = '<a href="/' . injil_help_gospel_section_path( $n + 1 ) . '/">' . sprintf( injil_help_text( 'gospel', 'next' ), $n + 1, esc_html( $data['chapters'][ $n + 1 ]['title'] ?? '' ) ) . '</a>';
		}
		$body[]  = implode( ' · ', $nav );
		$related = array();
		if ( function_exists( 'injil_help_tafsir_groups_for_section' ) ) {
			foreach ( injil_help_tafsir_groups_for_section( (int) $n ) as $group ) {
				$related[] = 'quran/commentaries/' . $group;
			}
		}
		$pages[] = array(
			'path'    => injil_help_gospel_section_path( $n ),
			'related' => $related,
			'former'  => array( $base . '/' . $n ), // the numeric URL of 3.5 and earlier; redirected and renamed in place
			'title'   => sprintf( injil_help_text( 'gospel', 'title' ), $n, $title ),
			'excerpt' => sprintf( injil_help_text( 'gospel', 'excerpt' ), $n, $title ),
			'body'    => $body,
		);
	}
	return $pages;
}
