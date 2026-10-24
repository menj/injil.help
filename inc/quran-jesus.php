<?php
/**
 * The Qur'anic passages on Jesus, with headings and footnotes, from
 * inc/data/quran-jesus.txt, gathered on one page under /quran/. The narrative of
 * The Gospel According to Islam carries its own selection of passages in place.
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

/** The page that gathers every passage, in the order of the Qur’an. */
function injil_help_quran_page() {
	$data  = injil_help_quran_data();
	$body  = array( 'Every passage of the Qur’an on Jesus and his teachings, with the headings and notes of the compilation. Each verse is numbered by chapter and verse. Many of them are set among the sections of <a href="/gospel-according-to-islam/">The Gospel According to Islam</a>.' );
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
