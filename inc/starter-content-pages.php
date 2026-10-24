<?php
/**
 * Starter pages for injil.help, in site order. Parents come before children.
 *
 * The pages are data, kept as JSON in inc/data/pages/, one file per part of the
 * site. Each entry has "path", "title" and "excerpt", and either "pattern" (the
 * block pattern of that slug supplies the content) or "body" (lines of the compact
 * markup described in injil_help_markup_to_blocks()); "template" names a page
 * template. The 27 sections of The Gospel According to Islam and the page of
 * Qur'anic passages are built from their own data files and spliced in here.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

require_once get_stylesheet_directory() . '/inc/gospel-islam.php';

/** One JSON file of page definitions, or an empty list if it is missing or malformed. */
function injil_help_pages_file( $name ) {
	$file = get_stylesheet_directory() . '/inc/data/pages/' . $name . '.json';
	if ( ! is_readable( $file ) ) {
		return array();
	}
	$rows = json_decode( (string) file_get_contents( $file ), true );
	return is_array( $rows ) ? $rows : array();
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
		injil_help_pages_file( 'gospel' ),
		injil_help_gospel_islam_pages(),
		injil_help_pages_file( 'bonus' ),
		injil_help_pages_file( 'resources' ),
		injil_help_pages_file( 'site' )
	);
	return $pages;
}
