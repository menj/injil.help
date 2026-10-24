<?php
/**
 * The Gospel According to Islam: chapter and introduction pages built from
 * inc/data/gospel-islam.txt. Credits are on the separate Credits page.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

require_once get_stylesheet_directory() . '/inc/gospel-islam-thomas.php';
require_once get_stylesheet_directory() . '/inc/quran-jesus.php';

/**
 * Parse the data file.
 *
 * Lines: "@chapter N", "@title ...", "@notes", "@front key"; in a chapter,
 * "N verse text" (with [[k]] note markers) and, after @notes, "k|note text".
 * In a front section every other line is a paragraph.
 */
function injil_help_gospel_islam_data() {
	static $data = null;
	if ( null !== $data ) {
		return $data;
	}
	$data  = array(
		'chapters' => array(),
		'front'    => array(),
	);
	$lines = file( get_stylesheet_directory() . '/inc/data/gospel-islam.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
	if ( ! $lines ) {
		return $data;
	}
	$kind = '';
	$key  = '';
	$mode = '';
	foreach ( $lines as $line ) {
		if ( preg_match( '/^@chapter (\d+)$/', $line, $m ) ) {
			$kind = 'chapters';
			$key  = (int) $m[1];
			$mode = 'verses';
			$data['chapters'][ $key ] = array(
				'title'  => '',
				'verses' => array(),
				'notes'  => array(),
			);
		} elseif ( preg_match( '/^@front (\w+)$/', $line, $m ) ) {
			$kind = 'front';
			$key  = $m[1];
			$data['front'][ $key ] = array(
				'title' => '',
				'paras' => array(),
			);
		} elseif ( preg_match( '/^@title (.*)$/', $line, $m ) ) {
			$data[ $kind ][ $key ]['title'] = $m[1];
		} elseif ( '@notes' === $line ) {
			$mode = 'notes';
		} elseif ( 'chapters' === $kind && 'verses' === $mode && preg_match( '/^(\d+) (.*)$/', $line, $m ) ) {
			$data['chapters'][ $key ]['verses'][ (int) $m[1] ] = $m[2];
		} elseif ( 'chapters' === $kind && 'notes' === $mode && preg_match( '/^(\d+)\|(.*)$/', $line, $m ) ) {
			$data['chapters'][ $key ]['notes'][ (int) $m[1] ] = $m[2];
		} elseif ( 'front' === $kind ) {
			$data['front'][ $key ]['paras'][] = $line;
		}
	}
	return $data;
}

/**
 * Load the Narrative of Jesus, Son of Mary (inc/data/narrative/).
 *
 * chN.txt hold "@chapter N Title", "@section N" and the paragraphs, "### " for the
 * Qur'anic headings, with [^n] note markers. notes-N.txt hold "n|note" lines, the
 * numbering running through the whole narrative.
 */
function injil_help_narrative_data() {
	static $d = null;
	if ( null !== $d ) {
		return $d;
	}
	$d       = array(
		'sections' => array(),
		'chapters' => array(),
		'notes'    => array(),
	);
	$dir     = get_stylesheet_directory() . '/inc/data/narrative/';
	$chapter = 0;
	$sec     = 0;
	foreach ( array( 'ch1', 'ch2', 'ch3', 'ch4', 'ch5', 'ch6' ) as $f ) {
		$lines = file( $dir . $f . '.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
		if ( ! $lines ) {
			continue;
		}
		foreach ( $lines as $line ) {
			if ( preg_match( '/^@chapter (\d+) (.+)$/', $line, $m ) ) {
				$chapter                   = (int) $m[1];
				$d['chapters'][ $chapter ] = $m[2];
			} elseif ( preg_match( '/^@section (\d+)$/', $line, $m ) ) {
				$sec                   = (int) $m[1];
				$d['sections'][ $sec ] = array(
					'chapter' => $chapter,
					'lines'   => array(),
				);
			} elseif ( $sec ) {
				$d['sections'][ $sec ]['lines'][] = $line;
			}
		}
	}
	foreach ( array( 'notes-1', 'notes-2' ) as $f ) {
		$lines = file( $dir . $f . '.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
		foreach ( $lines ? $lines : array() as $line ) {
			if ( preg_match( '/^(\d+)\|(.*)$/', $line, $m ) ) {
				$d['notes'][ (int) $m[1] ] = $m[2];
			}
		}
	}
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
	$thomas = injil_help_gospel_thomas_sections();
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
