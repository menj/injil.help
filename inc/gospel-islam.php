<?php
/**
 * The Gospel According to Islam: the Introduction and the 27 section pages, built
 * from inc/data/gospel-islam.json (section titles, front matter), narrative.json
 * (the text and notes), thomas.json (the Gospel of Thomas sections) and basilides.json
 * (the Gospel of Basilides, at the crucifixion). Credits are on the separate Credits page.
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

/** Gospel of Thomas sections woven into the chapters, keyed by section number; each a list of page-markup lines. */
function injil_help_gospel_thomas_sections() {
	return injil_help_data_file( 'thomas' );
}

/** Everything woven into a section after Shafaat's text: Thomas, then Basilides. Keyed by section number. */
function injil_help_gospel_woven_sections() {
	$out = array();
	foreach ( array( injil_help_gospel_thomas_sections(), injil_help_data_file( 'basilides' ) ) as $set ) {
		foreach ( (array) $set as $n => $lines ) {
			$out[ (string) $n ] = array_merge( $out[ (string) $n ] ?? array(), array_values( (array) $lines ) );
		}
	}
	return $out;
}

/**
 * Section titles and front matter from gospel-islam.json:
 * { "section_titles": { "1": "...", ... }, "front": { "introduction": { "title", "paragraphs": [...] } } }.
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
		$data['chapters'][ (int) $n ] = array( 'title' => (string) $title );
	}
	foreach ( (array) ( $json['front'] ?? array() ) as $key => $f ) {
		$data['front'][ $key ] = array(
			'title' => (string) ( $f['title'] ?? '' ),
			'paras' => array_values( (array) ( $f['paragraphs'] ?? array() ) ),
		);
	}
	return $data;
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
				$href = ( $refs[ $k ][0] === $current ? '' : '/gospel-according-to-islam/' . $refs[ $k ][0] . '/' ) . '#fn-' . $refs[ $k ][1];
				$out .= '<a href="' . $href . '">' . $k . '</a>';
			}
			return $m[1] . ' ' . $out;
		},
		$text
	);
}

/** Page definitions for the front matter and the 27 sections. */
function injil_help_gospel_islam_pages() {
	$data  = injil_help_gospel_islam_data();
	$base  = 'gospel-according-to-islam';
	$thomas = injil_help_gospel_woven_sections();
	$pages = array();

	$front = array(
		'introduction' => array( 'introduction', 'Introduction to The Gospel According to Islam.' ),
	);
	foreach ( $front as $k => $f ) {
		if ( empty( $data['front'][ $k ] ) ) {
			continue;
		}
		$body   = $data['front'][ $k ]['paras'];
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
		$body  = array( '<em>' . sprintf( 'Chapter %1$d. %2$s', $sec['chapter'], $nar['chapters'][ $sec['chapter'] ] ) . '</em>' );
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
		if ( ! empty( $thomas[ (string) $n ] ) ) {
			$body = array_merge( $body, $thomas[ (string) $n ] );
		}
		$nav = array();
		if ( $n > 1 ) {
			$nav[] = '<a href="/' . $base . '/' . ( $n - 1 ) . '/">← Section ' . ( $n - 1 ) . '</a>';
		}
		$nav[] = '<a href="/' . $base . '/">Contents</a>';
		if ( $n < $last ) {
			$nav[] = '<a href="/' . $base . '/' . ( $n + 1 ) . '/">Section ' . ( $n + 1 ) . ' →</a>';
		}
		$body[]  = implode( ' · ', $nav );
		$pages[] = array(
			'path'    => $base . '/' . $n,
			'title'   => sprintf( 'Section %1$d: %2$s', $n, $title ),
			'excerpt' => sprintf( 'Section %d of The Narrative of Jesus, Son of Mary: %s.', $n, $title ),
			'body'    => $body,
		);
	}
	return $pages;
}
