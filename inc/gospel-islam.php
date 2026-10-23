<?php
/**
 * The Gospel According to Islam: chapter and introduction pages built from
 * inc/data/gospel-islam.txt. Credits are on the separate Credits page.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

require_once get_stylesheet_directory() . '/inc/gospel-islam-thomas.php';

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

/** Page definitions for the front matter and the 27 chapters. */
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

	$last = count( $data['chapters'] );
	foreach ( $data['chapters'] as $n => $ch ) {
		$body = array();
		foreach ( $ch['verses'] as $v => $text ) {
			$text   = preg_replace_callback(
				'/\[\[(\d+)\]\]/',
				function ( $m ) use ( $ch ) {
					$k = (int) $m[1];
					return isset( $ch['notes'][ $k ] ) ? '{{fn:' . $ch['notes'][ $k ] . '}}' : '';
				},
				$text
			);
			$body[] = '<span class="ih-v">' . $v . '</span> ' . $text;
		}
		if ( ! empty( $thomas[ (string) $n ] ) ) {
			$body = array_merge( $body, $thomas[ (string) $n ] );
		}
		$nav = array();
		if ( $n > 1 ) {
			$nav[] = '<a href="/' . $base . '/' . ( $n - 1 ) . '/">← Chapter ' . ( $n - 1 ) . '</a>';
		}
		$nav[]  = '<a href="/' . $base . '/">Contents</a>';
		if ( $n < $last ) {
			$nav[] = '<a href="/' . $base . '/' . ( $n + 1 ) . '/">Chapter ' . ( $n + 1 ) . ' →</a>';
		}
		$body[] = implode( ' · ', $nav );
		$pages[] = array(
			'path'    => $base . '/' . $n,
			/* translators: 1: chapter number, 2: chapter title */
			'title'   => sprintf( 'Chapter %1$d: %2$s', $n, $ch['title'] ),
			'excerpt' => sprintf( 'Chapter %d of The Gospel According to Islam: %s.', $n, $ch['title'] ),
			'body'    => $body,
		);
	}
	return $pages;
}
