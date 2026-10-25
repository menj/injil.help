<?php
/**
 * Starter pages for injil.help, in site order. Parents come before children.
 *
 * The pages are data, kept as JSON in inc/data/pages/, one file per part of the
 * site. Each entry has "path", "title" and "excerpt", and either "pattern" (the
 * JSON pattern of that name in inc/data/patterns/ supplies the content, see
 * injil_help_pattern_file()) or "body" (lines of the compact
 * markup described in injil_help_markup_to_blocks()); "body_file" names a JSON
 * file in inc/data/ whose list of lines is appended to "body" (the full texts of
 * the Gospel of Thomas are kept that way); "template" names a page template;
 * "related" lists the paths of the pages linked in the "Read next" row under the
 * content (merged with inc/data/related.json, the curated map of related pages);
 * "former" lists paths the page had in earlier versions, so populate() can rename it. The 27 sections of The Gospel According to Islam and the page of
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
			$lines = injil_help_data_file( (string) $row['body_file'] );
			if ( ! $lines ) {
				return array(); // a missing or malformed text file fails the whole page file, so nothing half-made is published
			}
			$row['body'] = array_merge( array_values( (array) ( $row['body'] ?? array() ) ), array_values( $lines ) );
			unset( $row['body_file'] );
		}
	}
	unset( $row );
	return $rows;
}

/**
 * A block pattern kept as JSON in inc/data/patterns/{name}.json: "title", "slug", "description",
 * "categories", "keywords" and "content", the block markup as a list of lines. The pages whose
 * definition names a "pattern" take their content from here, and functions.php registers the
 * same files as block patterns for the editor. Content meant for the database is kept only as
 * JSON. Returns the pattern with its content joined, or null if the name is not a plain slug or
 * the file is missing or malformed.
 */
function injil_help_pattern_file( $name ) {
	static $cache = array();
	$name = (string) $name;
	if ( array_key_exists( $name, $cache ) ) {
		return $cache[ $name ];
	}
	$cache[ $name ] = null;
	$file           = get_stylesheet_directory() . '/inc/data/patterns/' . $name . '.json';
	if ( ! preg_match( '/^[a-z0-9-]+$/', $name ) || ! is_readable( $file ) ) {
		return null;
	}
	$data = json_decode( (string) file_get_contents( $file ), true );
	if ( ! is_array( $data ) || empty( $data['title'] ) || empty( $data['slug'] ) || ! isset( $data['content'] ) || ! is_array( $data['content'] ) ) {
		return null;
	}
	foreach ( $data['content'] as $line ) {
		if ( ! is_string( $line ) ) {
			return null;
		}
	}
	$data['content'] = implode( "\n", $data['content'] );
	$cache[ $name ]  = $data;
	return $data;
}

/**
 * The curated map of related pages, inc/data/related.json: { "path": [ "path", ... ] }. Every
 * article links onward to the pages nearest it in subject, so no page depends on the menus
 * alone to be found (the internal linking the Google SEO Starter Guide asks for).
 */
function injil_help_related_map() {
	static $map = null;
	if ( null === $map ) {
		$map = injil_help_data_file( 'related' );
	}
	return $map;
}

function injil_help_starter_pages() {
	static $pages = null;
	if ( null !== $pages ) {
		return $pages;
	}
	$pages = injil_help_starter_pages_list();
	$map   = injil_help_related_map();
	foreach ( $pages as &$def ) {
		$paths = array();
		foreach ( array_merge( (array) ( $def['related'] ?? array() ), (array) ( $map[ $def['path'] ] ?? array() ) ) as $item ) {
			if ( is_string( $item ) && $item !== $def['path'] && ! in_array( $item, $paths, true ) ) {
				$paths[] = $item;
			}
		}
		$def['related'] = $paths;
	}
	unset( $def );
	return $pages;
}

function injil_help_starter_pages_list() {
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
