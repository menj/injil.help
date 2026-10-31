<?php
/**
 * Images for the starter pages, from Wikimedia Commons or Pexels.
 *
 * inc/data/images.json maps a page path to one image: { "path": { "file", "alt", "caption", "credit" } },
 * where "file" is the Commons file name without the "File:" prefix. A Pexels photo adds "source": "pexels",
 * "url" (the photo on images.pexels.com) and "page" (its page on pexels.com), and "file" is then only the
 * key the Media Library copy is found by, "pexels-{id}". Each page that has an entry gets a
 * plate (a linked figure with the credit in its caption) near the top of its content, and, once the page
 * exists, the same image sideloaded into the Media Library and set as its featured image, which the
 * Open Graph tags and the structured data then use. The sideload runs a few pages at a time on admin
 * screen loads, so a slow host never waits on forty downloads at once.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

const INJIL_HELP_COMMONS_META = '_injil_help_commons';

/** The image map, or an empty array when the file is missing or malformed. */
function injil_help_images() {
	static $map = null;
	if ( null === $map ) {
		$map = injil_help_data_file( 'images' );
	}
	return $map;
}

/** The page an image is credited to and licensed from: its Commons page, or its Pexels page. */
function injil_help_image_page( $img ) {
	return 'pexels' === ( $img['source'] ?? '' ) ? (string) ( $img['page'] ?? '' ) : injil_help_commons_page( $img['file'] );
}

/** A copy of an image at most $width pixels wide: from Commons, or from the Pexels image server, which scales on request. */
function injil_help_image_url( $img, $width ) {
	return 'pexels' === ( $img['source'] ?? '' ) ? (string) ( $img['url'] ?? '' ) . '?auto=compress&cs=tinysrgb&w=' . (int) $width : injil_help_commons_url( $img['file'], $width );
}

/** The Commons page of a file. */
function injil_help_commons_page( $file ) {
	return 'https://commons.wikimedia.org/wiki/File:' . rawurlencode( str_replace( ' ', '_', $file ) );
}

/** A scaled copy of a Commons file, through the Special:FilePath redirect. */
function injil_help_commons_url( $file, $width ) {
	return 'https://commons.wikimedia.org/wiki/Special:FilePath/' . rawurlencode( str_replace( ' ', '_', $file ) ) . '?width=' . (int) $width;
}

/** The plate block for an image entry: a linked figure with the caption and credit, in the style the patterns use. */
function injil_help_image_block( $img ) {
	$caption = trim( (string) ( $img['caption'] ?? '' ) );
	$credit  = trim( (string) ( $img['credit'] ?? '' ) );
	$text    = $caption . ( $caption && $credit ? ' ' : '' ) . $credit . ( $credit && '.' !== substr( $credit, -1 ) ? '.' : '' );
	$text   .= ' <a href="' . esc_url( injil_help_image_page( $img ) ) . '">' . ( 'pexels' === ( $img['source'] ?? '' ) ? 'Pexels' : 'Wikimedia Commons' ) . '</a>.';
	return '<!-- wp:image {"sizeSlug":"large","linkDestination":"custom","className":"ih-plate"} -->' . "\n"
		. '<figure class="wp-block-image size-large ih-plate"><a href="' . esc_url( injil_help_image_page( $img ) ) . '"><img src="' . esc_url( injil_help_image_url( $img, 900 ) ) . '" alt="' . esc_attr( (string) ( $img['alt'] ?? '' ) ) . '"/></a><figcaption class="wp-element-caption">' . wp_kses_post( $text ) . '</figcaption></figure>' . "\n"
		. "<!-- /wp:image -->\n";
}

/**
 * Put a page's image into its block content, unless the content already carries a plate. A pattern page
 * takes it after its first group (the section head); a body page after its first ordinary paragraph.
 */
function injil_help_add_page_image( $path, $content, $is_pattern ) {
	$images = injil_help_images();
	if ( empty( $images[ $path ]['file'] ) || false !== strpos( $content, 'ih-plate' ) ) {
		return $content;
	}
	$block = injil_help_image_block( $images[ $path ] );
	if ( $is_pattern ) {
		$pos = strpos( $content, "<!-- /wp:group -->\n" );
		return false === $pos ? $block . $content : substr( $content, 0, $pos + 19 ) . $block . substr( $content, $pos + 19 );
	}
	if ( preg_match_all( '/<!-- wp:paragraph( \{[^}]*\})? -->.*?<!-- \/wp:paragraph -->\n/s', $content, $m, PREG_OFFSET_CAPTURE ) ) {
		foreach ( $m[0] as $i => $hit ) {
			if ( false !== strpos( (string) ( $m[1][ $i ][0] ?? '' ), 'is-style-label-' ) ) {
				continue;
			}
			$end = $hit[1] + strlen( $hit[0] );
			return substr( $content, 0, $end ) . $block . substr( $content, $end );
		}
	}
	return $block . $content;
}

/** The attachment already sideloaded for a Commons file, if any. */
function injil_help_find_attachment( $file ) {
	$ids = get_posts(
		array(
			'post_type'        => 'attachment',
			'post_status'      => 'inherit',
			'posts_per_page'   => 1,
			'fields'           => 'ids',
			'meta_key'         => INJIL_HELP_COMMONS_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'       => $file, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'suppress_filters' => true,
		)
	);
	return $ids ? (int) $ids[0] : 0;
}

/**
 * Sideload a page's Commons image into the Media Library and set it as the featured image.
 * Returns true when the page has a featured image afterwards. Failures are recorded so the
 * file is not retried for a day.
 */
function injil_help_feature_page( $post_id, $path ) {
	$images = injil_help_images();
	if ( empty( $images[ $path ]['file'] ) ) {
		return false;
	}
	if ( has_post_thumbnail( $post_id ) ) {
		return true;
	}
	$img  = $images[ $path ];
	$file = $img['file'];
	$att  = injil_help_find_attachment( $file );
	if ( ! $att ) {
		$failed = (array) get_option( 'injil_help_image_failures', array() );
		if ( ! empty( $failed[ $file ] ) && time() - (int) $failed[ $file ] < DAY_IN_SECONDS ) {
			return false;
		}
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$att = media_sideload_image( injil_help_image_url( $img, 1600 ), $post_id, (string) ( $img['alt'] ?? $file ), 'id' );
		if ( ! $att || is_wp_error( $att ) ) {
			$failed[ $file ] = time();
			update_option( 'injil_help_image_failures', $failed, false );
			return false;
		}
		$credit = trim( (string) ( $img['credit'] ?? '' ) );
		wp_update_post(
			array(
				'ID'           => $att,
				'post_excerpt' => trim( (string) ( $img['caption'] ?? '' ) . ( $credit ? ' ' . $credit . ( '.' === substr( $credit, -1 ) ? '' : '.' ) : '' ) ),
				'post_content' => 'Source: ' . injil_help_image_page( $img ),
			)
		);
		update_post_meta( $att, '_wp_attachment_image_alt', (string) ( $img['alt'] ?? '' ) );
		update_post_meta( $att, INJIL_HELP_COMMONS_META, $file );
	}
	return (bool) set_post_thumbnail( $post_id, $att );
}

/**
 * On admin screen loads, give featured images to a few starter pages at a time until every page with
 * an image entry has one. A version stamp stops the scan once it finds nothing to do; populate() clears it.
 */
add_action(
	'admin_init',
	function () {
		if ( ! current_user_can( 'edit_theme_options' ) || wp_doing_ajax() || get_option( 'injil_help_populating' ) ) {
			return;
		}
		if ( get_option( 'injil_help_featured' ) === INJIL_HELP_VERSION || ! injil_help_images() ) {
			return;
		}
		$budget  = 6;
		$pending = false;
		foreach ( injil_help_images() as $path => $img ) {
			$page = injil_help_find_page( $path );
			if ( ! $page || has_post_thumbnail( $page->ID ) ) {
				continue;
			}
			if ( $budget <= 0 ) {
				$pending = true;
				break;
			}
			$budget--;
			if ( ! injil_help_feature_page( $page->ID, $path ) ) {
				$pending = true;
			}
		}
		if ( ! $pending ) {
			update_option( 'injil_help_featured', INJIL_HELP_VERSION, false );
		}
	},
	20
);
