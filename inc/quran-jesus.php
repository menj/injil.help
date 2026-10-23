<?php
/**
 * The Qur'anic passages on Jesus, with headings and footnotes, from
 * inc/data/quran-jesus.txt. Passages are placed in the matching chapters of
 * The Gospel According to Islam and gathered on one page under /quran/.
 * Credits are on the separate Credits page.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

/**
 * Parse the data file.
 *
 * Lines: "@surah N Title", "## a-b Heading" (the verse range of the passage),
 * and "n verse text" with inline {{fn:...}} footnotes.
 *
 * @return array{surahs: array<int,string>, blocks: array<string,array>}
 */
function injil_help_quran_data() {
	static $data = null;
	if ( null !== $data ) {
		return $data;
	}
	$data  = array(
		'surahs' => array(),
		'blocks' => array(),
	);
	$lines = file( get_stylesheet_directory() . '/inc/data/quran-jesus.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
	if ( ! $lines ) {
		return $data;
	}
	$surah = 0;
	$key   = '';
	foreach ( $lines as $line ) {
		if ( preg_match( '/^@surah (\d+) (.*)$/', $line, $m ) ) {
			$surah                  = (int) $m[1];
			$data['surahs'][ $surah ] = $m[2];
		} elseif ( preg_match( '/^## ([\d-]+) (.*)$/', $line, $m ) ) {
			$key                    = $surah . ':' . $m[1];
			$data['blocks'][ $key ] = array(
				'surah'   => $surah,
				'heading' => $m[2],
				'verses'  => array(),
			);
		} elseif ( $key && preg_match( '/^(\d+) (.*)$/', $line, $m ) ) {
			$data['blocks'][ $key ]['verses'][ (int) $m[1] ] = $m[2];
		}
	}
	return $data;
}

/** Markup lines for one passage: a sub-heading and one paragraph per verse. */
function injil_help_quran_block_lines( $key ) {
	$data = injil_help_quran_data();
	if ( empty( $data['blocks'][ $key ] ) ) {
		return array();
	}
	$b     = $data['blocks'][ $key ];
	$lines = array( '### ' . $b['heading'] );
	foreach ( $b['verses'] as $v => $text ) {
		$lines[] = '<span class="ih-v ih-vq">' . $b['surah'] . ':' . $v . '</span> ' . $text;
	}
	return $lines;
}

/** Passages that follow each chapter of The Gospel According to Islam. */
function injil_help_quran_chapter_map() {
	return array(
		1  => array( '3:35-37', '3:38-41', '19:2-6', '19:7-11', '21:89-90' ),
		2  => array( '3:42-44', '3:45-47', '19:16-21', '19:22-26', '19:27-29', '19:30-33', '21:91' ),
		3  => array( '61:6-7', '3:59-60', '112:1-4' ),
		5  => array( '19:12-15' ),
		7  => array( '3:48-51', '43:63-65' ),
		11 => array( '3:78', '3:79-80' ),
		13 => array( '3:52-53' ),
		14 => array( '5:110-111' ),
		16 => array( '5:112-115' ),
		24 => array( '43:59-62' ),
		26 => array( '3:54-58', '4:157-158' ),
		27 => array( '5:116-120' ),
	);
}

/** Lines for the "The Qur’anic text" section of a chapter, or an empty array. */
function injil_help_quran_chapter_lines( $chapter ) {
	$map = injil_help_quran_chapter_map();
	if ( empty( $map[ $chapter ] ) ) {
		return array();
	}
	$lines = array(
		'## The Qur’anic text',
		'The passages of the Qur’an that this chapter follows. All the passages are gathered on <a href="/quran/passages/">The Qur’an on Jesus</a>.',
	);
	foreach ( $map[ $chapter ] as $key ) {
		$lines = array_merge( $lines, injil_help_quran_block_lines( $key ) );
	}
	return $lines;
}

/** The page that gathers every passage, in the order of the Qur’an. */
function injil_help_quran_page() {
	$data  = injil_help_quran_data();
	$body  = array( 'Every passage of the Qur’an on Jesus and his teachings, with the headings and notes of the compilation. Each verse is numbered by chapter and verse. The same passages appear beside the chapters of <a href="/gospel-according-to-islam/">The Gospel According to Islam</a> that follow them.' );
	$last  = 0;
	foreach ( $data['blocks'] as $key => $b ) {
		if ( $b['surah'] !== $last ) {
			$last   = $b['surah'];
			$body[] = '## Chapter ' . $last . ': ' . $data['surahs'][ $last ];
		}
		$body = array_merge( $body, injil_help_quran_block_lines( $key ) );
	}
	return array(
		'path'    => 'quran/passages',
		'title'   => 'The Qur’an on Jesus',
		'excerpt' => 'Every passage of the Qur’an on Jesus and his teachings, with headings and notes.',
		'body'    => $body,
	);
}
