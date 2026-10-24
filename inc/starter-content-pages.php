<?php
/**
 * Starter pages for injil.help, in site order. Parents come before children.
 *
 * The pages are data, kept as JSON in inc/data/pages/, one file per part of the
 * site. Each entry has "path", "title" and "excerpt", and either "pattern" (the
 * block pattern of that slug supplies the content) or "body" (lines of the compact
 * markup described in injil_help_markup_to_blocks()); "body_file" names a JSON
 * file in inc/data/ whose list of lines is appended to "body" (the full texts of
 * the Gospel of Thomas are kept that way); "template" names a page template. The 27 sections of The Gospel According to Islam and the page of
 * Qur'anic passages are built from their own data files and spliced in here.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

require_once get_stylesheet_directory() . '/inc/gospel-islam.php';
require_once get_stylesheet_directory() . '/inc/tafsir.php';

/** One JSON file of page definitions, or an empty list if it is missing or malformed. */
function injil_help_pages_file( $name ) {
	$file = get_stylesheet_directory() . '/inc/data/pages/' . $name . '.json';
	if ( ! is_readable( $file ) ) {
		return array();
	}
	$rows = json_decode( (string) file_get_contents( $file ), true );
	if ( ! is_array( $rows ) ) {
		return array();
	}
	foreach ( $rows as &$row ) {
		if ( ! empty( $row['body_file'] ) ) {
			$row['body'] = array_merge( array_values( (array) ( $row['body'] ?? array() ) ), array_values( injil_help_data_file( (string) $row['body_file'] ) ) );
			unset( $row['body_file'] );
		}
	}
	unset( $row );
	return $rows;
}

function injil_help_starter_pages() {
	static $pages = null;
	if ( null !== $pages ) {
		return $pages;
	}
	$pages = array_merge(
		injil_help_pages_file( 'pillar' ),
		injil_help_pages_file( 'gospels' ),
		injil_help_pages_file( 'paul' ),
		injil_help_pages_file( 'quran' ),
		array( injil_help_quran_page() ),
		injil_help_tafsir_pages(),
		injil_help_pages_file( 'gospel' ),
		injil_help_gospel_islam_pages(),
		injil_help_pages_file( 'bonus' ),
		injil_help_pages_file( 'resources' ),
		injil_help_pages_file( 'site' )
	);
	return $pages;
}
