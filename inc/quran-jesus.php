<?php
/**
 * The Qur'anic passages on Jesus, with headings and footnotes, from
 * inc/data/quran-jesus.json, gathered on one page under /quran/. The narrative of
 * The Gospel According to Islam carries its own selection of passages in place.
 * Credits are on the separate Credits page.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

/**
 * The compilation from quran-jesus.json:
 * { "surahs": { "n": "Title" }, "passages": [ { "surah", "verses_range", "heading", "verses": { "n": "text" } } ] }.
 * Verse text carries inline {{fn:...}} footnotes.
 *
 * @return array{surahs: array<int,string>, blocks: array<string,array>}
 */
function injil_help_quran_data() {
	static $data = null;
	if ( null !== $data ) {
		return $data;
	}
	$data = array(
		'surahs' => array(),
		'blocks' => array(),
	);
	$json = injil_help_data_file( 'quran-jesus' );
	foreach ( (array) ( $json['surahs'] ?? array() ) as $n => $title ) {
		$data['surahs'][ (int) $n ] = (string) $title;
	}
	foreach ( (array) ( $json['passages'] ?? array() ) as $p ) {
		$surah  = (int) ( $p['surah'] ?? 0 );
		$key    = $surah . ':' . ( $p['verses_range'] ?? '' );
		$verses = array();
		foreach ( (array) ( $p['verses'] ?? array() ) as $v => $text ) {
			$verses[ (int) $v ] = (string) $text;
		}
		$data['blocks'][ $key ] = array(
			'surah'        => $surah,
			'verses_start' => $verses ? min( array_keys( $verses ) ) : 0,
			'heading'      => (string) ( $p['heading'] ?? '' ),
			'verses'       => $verses,
		);
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
	// The compilation closes with 59:22-24 after 112; this page follows the order of the Qur'an.
	$blocks = $data['blocks'];
	uksort(
		$blocks,
		function ( $a, $b ) use ( $data ) {
			$sa = $data['blocks'][ $a ]['surah'];
			$sb = $data['blocks'][ $b ]['surah'];
			if ( $sa !== $sb ) {
				return $sa <=> $sb;
			}
			return (int) $data['blocks'][ $a ]['verses_start'] <=> (int) $data['blocks'][ $b ]['verses_start'];
		}
	);
	foreach ( $blocks as $key => $b ) {
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
