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
 * An entry may also carry "more": up to three further images ({ "file", "alt", "caption", "credit" }, or a Pexels
 * photo as above). They are placed after paragraphs spread through the page, clear of the notes, and sideloaded the
 * same way. A sideloaded image is stored as AVIF, or as WebP when it is under 1000 pixels wide, if the server's
 * image library can write that format; otherwise it keeps its own format.
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

/** Split block markup into its top-level blocks. */
function injil_help_top_blocks( $html ) {
	preg_match_all( '/<!-- (\/?)wp:[a-z0-9\/-]+(?: \{.*?\})? (\/?)-->/', $html, $m, PREG_OFFSET_CAPTURE );
	$blocks = array();
	$depth  = 0;
	$start  = 0;
	foreach ( $m[0] as $i => $tag ) {
		$closing = '/' === $m[1][ $i ][0];
		$self    = '/' === $m[2][ $i ][0];
		if ( ! $closing && ! $self ) {
			if ( 0 === $depth ) {
				$start = $tag[1];
			}
			++$depth;
		} elseif ( $closing ) {
			--$depth;
			if ( 0 === $depth ) {
				$blocks[] = substr( $html, $start, $tag[1] + strlen( $tag[0] ) - $start );
			}
		} elseif ( 0 === $depth ) {
			$blocks[] = $tag[0];
		}
	}
	return $blocks;
}

/**
 * Put a page's images into its block content, unless the content already carries a plate. The first image
 * (the featured one) goes where it always has: a pattern page takes it after its first group (the section
 * head); a body page after its first ordinary paragraph. The "more" images follow, each after a paragraph,
 * spread through the text and clear of the notes.
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
	$done = false;
	if ( preg_match_all( '/<!-- wp:paragraph( \{[^}]*\})? -->.*?<!-- \/wp:paragraph -->\n/s', $content, $m, PREG_OFFSET_CAPTURE ) ) {
		foreach ( $m[0] as $i => $hit ) {
			if ( false !== strpos( (string) ( $m[1][ $i ][0] ?? '' ), 'is-style-label-' ) ) {
				continue;
			}
			$end     = $hit[1] + strlen( $hit[0] );
			$content = substr( $content, 0, $end ) . $block . substr( $content, $end );
			$done    = true;
			break;
		}
	}
	if ( ! $done ) {
		$content = $block . $content;
	}
	return injil_help_add_more_images( $content, (array) ( $images[ $path ]['more'] ?? array() ) );
}

/** Spread a page's further plates through its content: each after a paragraph, at even steps, before the notes. */
function injil_help_add_more_images( $content, $more ) {
	$more = array_slice( array_values( array_filter( $more, fn( $m ) => ! empty( $m['file'] ) ) ), 0, 3 );
	if ( ! $more ) {
		return $content;
	}
	$blocks = injil_help_top_blocks( $content );
	$count  = count( $blocks );
	$end    = $count;
	$first  = 0;
	foreach ( $blocks as $i => $b ) {
		if ( false !== strpos( $b, 'ih-plate' ) && ! $first ) {
			$first = $i;
		}
		if ( false !== strpos( $b, 'id="notes"' ) || false !== strpos( $b, 'ih-footnotes' ) ) {
			$end = min( $end, $i );
		}
	}
	$after = array();
	$prev  = $first;
	$n     = count( $more );
	for ( $k = 1; $k <= $n; $k++ ) {
		$idx = max( (int) round( $first + ( $end - 1 - $first ) * $k / ( $n + 1 ) ), $prev + 2 );
		while ( $idx < $end && 0 !== strpos( $blocks[ $idx ], '<!-- wp:paragraph' ) ) {
			++$idx;
		}
		if ( $idx >= $end || isset( $after[ $idx ] ) ) {
			break;
		}
		$after[ $idx ] = $more[ $k - 1 ];
		$prev          = $idx;
	}
	if ( ! $after ) {
		return $content;
	}
	$out = '';
	foreach ( $blocks as $i => $b ) {
		$out .= $b . "\n";
		if ( isset( $after[ $i ] ) ) {
			$out .= injil_help_image_block( $after[ $i ] );
		}
	}
	return $out;
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
 * Download an image and store it in the Media Library as AVIF, or as WebP when it is under 1000 pixels
 * wide, scaled to at most 1600 pixels. Where the server's image library cannot write the format, the
 * image keeps its own. Returns the attachment ID, or 0 on failure. Failures are recorded so the file is
 * not retried for a day.
 */
function injil_help_sideload_image( $img, $post_id ) {
	$file = (string) $img['file'];
	$att  = injil_help_find_attachment( $file );
	if ( $att ) {
		return $att;
	}
	$failed = (array) get_option( 'injil_help_image_failures', array() );
	if ( ! empty( $failed[ $file ] ) && time() - (int) $failed[ $file ] < DAY_IN_SECONDS ) {
		return 0;
	}
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$tmp = download_url( injil_help_image_url( $img, 1600 ), 60 );
	if ( is_wp_error( $tmp ) ) {
		$failed[ $file ] = time();
		update_option( 'injil_help_image_failures', $failed, false );
		return 0;
	}
	$stem = sanitize_title( pathinfo( $file, PATHINFO_FILENAME ) );
	$stem = '' !== $stem ? $stem : 'plate';
	$name = $stem . '.' . ( pathinfo( (string) wp_parse_url( injil_help_image_url( $img, 1 ), PHP_URL_PATH ), PATHINFO_EXTENSION ) ?: 'jpg' );
	$path = $tmp;
	$mime = '';
	$editor = wp_get_image_editor( $tmp );
	if ( ! is_wp_error( $editor ) ) {
		$size = $editor->get_size();
		$want = ( $size['width'] ?? 0 ) < 1000 ? array( 'image/webp', 'image/avif' ) : array( 'image/avif', 'image/webp' );
		foreach ( $want as $candidate ) {
			if ( wp_image_editor_supports( array( 'mime_type' => $candidate ) ) ) {
				$mime = $candidate;
				break;
			}
		}
		if ( $mime ) {
			if ( ( $size['width'] ?? 0 ) > 1600 ) {
				$editor->resize( 1600, null );
			}
			$ext   = 'image/avif' === $mime ? 'avif' : 'webp';
			$saved = $editor->save( trailingslashit( get_temp_dir() ) . wp_unique_filename( get_temp_dir(), $stem . '.' . $ext ), $mime );
			if ( ! is_wp_error( $saved ) && ! empty( $saved['path'] ) ) {
				wp_delete_file( $tmp );
				$path = $saved['path'];
				$name = $stem . '.' . $ext;
			}
		}
	}
	$att = media_handle_sideload(
		array(
			'name'     => $name,
			'tmp_name' => $path,
		),
		$post_id,
		(string) ( $img['alt'] ?? $file )
	);
	if ( is_wp_error( $att ) ) {
		wp_delete_file( $path );
		$failed[ $file ] = time();
		update_option( 'injil_help_image_failures', $failed, false );
		return 0;
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
	return (int) $att;
}

/**
 * Sideload a page's image into the Media Library and set it as the featured image.
 * Returns true when the page has a featured image afterwards.
 */
function injil_help_feature_page( $post_id, $path ) {
	$images = injil_help_images();
	if ( empty( $images[ $path ]['file'] ) ) {
		return false;
	}
	if ( has_post_thumbnail( $post_id ) ) {
		return true;
	}
	$att = injil_help_sideload_image( $images[ $path ], $post_id );
	return $att ? (bool) set_post_thumbnail( $post_id, $att ) : false;
}

/**
 * On admin screen loads, give featured images to a few starter pages at a time, and copy each page's
 * further images into the Media Library, until every entry is done. A version stamp stops the scan once
 * it finds nothing to do; populate() clears it.
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
			if ( ! $page ) {
				continue;
			}
			if ( ! has_post_thumbnail( $page->ID ) ) {
				if ( $budget <= 0 ) {
					$pending = true;
					break;
				}
				$budget--;
				if ( ! injil_help_feature_page( $page->ID, $path ) ) {
					$pending = true;
				}
			}
			foreach ( (array) ( $img['more'] ?? array() ) as $extra ) {
				if ( empty( $extra['file'] ) || injil_help_find_attachment( $extra['file'] ) ) {
					continue;
				}
				if ( $budget <= 0 ) {
					$pending = true;
					break 2;
				}
				$budget--;
				if ( ! injil_help_sideload_image( $extra, $page->ID ) ) {
					$pending = true;
				}
			}
		}
		if ( ! $pending ) {
			update_option( 'injil_help_featured', INJIL_HELP_VERSION, false );
		}
	},
	20
);

/* ---------- Plates served from the Media Library ---------- */

/** The images.json key ("file") an image address points at: a Commons file name or a Pexels key; '' for any other address. */
function injil_help_image_key_from_src( $src ) {
	if ( preg_match( '#commons\.wikimedia\.org/wiki/Special:FilePath/([^?"\#]+)#', (string) $src, $m ) ) {
		return str_replace( '_', ' ', rawurldecode( $m[1] ) );
	}
	foreach ( injil_help_images() as $img ) {
		if ( 'pexels' === ( $img['source'] ?? '' ) && ! empty( $img['url'] ) && 0 === strpos( (string) $src, (string) $img['url'] ) ) {
			return (string) $img['file'];
		}
	}
	return '';
}

/**
 * Point a plate's <img> at the copy already sideloaded into the Media Library, with its class
 * wp-image-{id}, so WordPress adds width, height, srcset and lazy loading when it filters the
 * content (Commons serves no size with its redirect, and the browser cannot reserve the space).
 * An image with no local copy keeps its address and gets lazy loading and async decoding,
 * except the front page's cover image, which is the first thing on screen: it loads eagerly,
 * decodes in step with the page and is fetched at high priority.
 */
function injil_help_localise_img( $tag, $is_cover = false ) {
	if ( preg_match( '/\swidth=/i', $tag ) || ! preg_match( '/\ssrc="([^"]+)"/i', $tag, $src ) ) {
		return $tag;
	}
	static $found = array();
	$key = injil_help_image_key_from_src( html_entity_decode( $src[1] ) );
	if ( '' === $key ) {
		return $tag;
	}
	if ( ! isset( $found[ $key ] ) ) {
		$found[ $key ] = injil_help_find_attachment( $key );
	}
	$att  = (int) $found[ $key ];
	$file = $att ? wp_get_attachment_image_src( $att, 'large' ) : false;
	if ( $file ) {
		$tag = str_replace( $src[0], ' src="' . esc_url( $file[0] ) . '" width="' . (int) $file[1] . '" height="' . (int) $file[2] . '"', $tag );
		$tag = preg_match( '/\sclass="/i', $tag )
			? preg_replace( '/\sclass="/i', ' class="wp-image-' . $att . ' ', $tag, 1 )
			: str_replace( '<img', '<img class="wp-image-' . $att . '"', $tag );
		return $tag;
	}
	$extra = $is_cover
		? ( preg_match( '/\sfetchpriority=/i', $tag ) ? '' : ' fetchpriority="high"' )
		: ' decoding="async"' . ( preg_match( '/\sloading=/i', $tag ) ? '' : ' loading="lazy"' );
	return preg_replace( '/^<img\b/i', '<img' . $extra, $tag );
}

add_filter(
	'render_block_core/image',
	function ( $html ) {
		if ( false === strpos( (string) $html, '<img' ) ) {
			return $html;
		}
		$cover = false !== strpos( (string) $html, 'ih-cover-image' );
		return preg_replace_callback(
			'/<img\b[^>]*>/i',
			function ( $m ) use ( $cover ) {
				return injil_help_localise_img( $m[0], $cover );
			},
			(string) $html
		);
	}
);

/* ---------- Image sitemap ---------- */

/**
 * The front page's cover image, resolved as the hero pattern's <img> is when it renders: the
 * Media Library copy when the file has been sideloaded, else the address in the pattern.
 */
function injil_help_front_cover_url() {
	$pattern = function_exists( 'injil_help_pattern_file' ) ? injil_help_pattern_file( 'hero' ) : null;
	if ( ! $pattern || ! preg_match( '/<figure\b[^>]*ih-cover-image[^>]*>.*?<img\b[^>]*\ssrc="([^"]+)"/is', $pattern['content'], $m ) ) {
		return '';
	}
	$src = html_entity_decode( $m[1] );
	$key = injil_help_image_key_from_src( $src );
	$att = '' !== $key ? injil_help_find_attachment( $key ) : 0;
	$url = $att ? wp_get_attachment_image_url( $att, 'full' ) : false;
	return $url ? (string) $url : $src;
}

/** Each page with an image entry and its image: the Media Library copy when there is one, else the source file. The front page comes first, with its cover. */
function injil_help_image_sitemap_entries() {
	$out   = array();
	$cover = injil_help_front_cover_url();
	if ( '' !== $cover ) {
		$out[] = array(
			'loc'   => home_url( '/' ),
			'image' => $cover,
		);
	}
	foreach ( injil_help_images() as $path => $img ) {
		$page = function_exists( 'injil_help_find_page' ) ? injil_help_find_page( $path ) : null;
		if ( ! $page || 'publish' !== $page->post_status || empty( $img['file'] ) ) {
			continue;
		}
		$url = has_post_thumbnail( $page->ID ) ? (string) get_the_post_thumbnail_url( $page->ID, 'full' ) : injil_help_image_url( $img, 1600 );
		if ( '' !== $url ) {
			$out[] = array(
				'loc'   => (string) get_permalink( $page ),
				'image' => $url,
			);
		}
	}
	return $out;
}

/** The address of the image sitemap. */
function injil_help_image_sitemap_url() {
	return home_url( '/image-sitemap.xml' );
}

/** Whether Rank Math's sitemap, which lists images itself, is serving. */
function injil_help_rank_math_sitemap_on() {
	if ( ! class_exists( 'RankMath' ) ) {
		return false;
	}
	return ! ( class_exists( '\RankMath\Helper' ) && method_exists( '\RankMath\Helper', 'is_module_active' ) ) || (bool) \RankMath\Helper::is_module_active( 'sitemap' );
}

add_action(
	'template_redirect',
	function () {
		$request = isset( $GLOBALS['wp']->request ) ? trim( (string) $GLOBALS['wp']->request, '/' ) : '';
		if ( 'image-sitemap.xml' !== $request || injil_help_rank_math_sitemap_on() ) {
			return;
		}
		status_header( 200 );
		header( 'Content-Type: application/xml; charset=UTF-8' );
		header( 'X-Robots-Tag: noindex, follow' );
		echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
		foreach ( injil_help_image_sitemap_entries() as $e ) {
			echo "\t<url><loc>" . esc_xml( $e['loc'] ) . '</loc><image:image><image:loc>' . esc_xml( $e['image'] ) . "</image:loc></image:image></url>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped with esc_xml().
		}
		echo "</urlset>\n";
		exit;
	},
	0
);

add_filter(
	'robots_txt',
	function ( $output, $public ) {
		if ( ! $public || injil_help_rank_math_sitemap_on() ) {
			return $output;
		}
		return rtrim( (string) $output ) . "\nSitemap: " . esc_url_raw( injil_help_image_sitemap_url() ) . "\n";
	},
	20,
	2
);
