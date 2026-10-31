<?php
/**
 * Starter content: creates the site's pages on theme activation, and on demand
 * from Appearance > Theme Options > Content. Existing pages are never changed.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

require_once get_stylesheet_directory() . '/inc/starter-content-pages.php';

/**
 * Convert the compact page markup to blocks.
 *
 * "## " heading, "### " sub-heading, "- " list item, "> " quote, "@consensus|@minority|@islamic"
 * claim label, anything else a paragraph. Blank lines are ignored. Inline
 * "{{fn:text}}" becomes a numbered footnote; the notes are listed at the end.
 * A list item may start with [agree], [beyond], [unclear], [conflict] or
 * [notneeded] to show a colour-coded status badge.
 */
function injil_help_markup_to_blocks( $lines ) {
	$labels = array(
		'consensus' => __( 'Scholarly consensus', 'injil-help' ),
		'minority'  => __( 'Minority view', 'injil-help' ),
		'islamic'   => __( 'Islamic position', 'injil-help' ),
	);
	$status = array(
		'agree'     => __( 'Agrees', 'injil-help' ),
		'beyond'    => __( 'Beyond the measure', 'injil-help' ),
		'unclear'   => __( 'Unclear', 'injil-help' ),
		'conflict'  => __( 'Conflicts', 'injil-help' ),
		'notneeded' => __( 'Not needed', 'injil-help' ),
	);
	$out    = '';
	$notes  = array();
	$list   = array();
	// Replace {{fn:...}} with a numbered superscript link and collect the note.
	$footnote = function ( $text ) use ( &$notes ) {
		return preg_replace_callback(
			'/\{\{fn:(.*?)\}\}/s',
			function ( $m ) use ( &$notes ) {
				$notes[] = trim( $m[1] );
				$n       = count( $notes );
				return sprintf( '<sup class="ih-fnref"><a href="#fn-%1$d" id="fnref-%1$d">%1$d</a></sup>', $n );
			},
			$text
		);
	};
	$flush  = function () use ( &$out, &$list, &$notes, $status, &$footnote ) {
		if ( ! $list ) {
			return;
		}
		$out .= "<!-- wp:list -->\n<ul class=\"wp-block-list\">";
		foreach ( $list as $item ) {
			$class = '';
			$badge = '';
			if ( preg_match( '/^\[(agree|beyond|unclear|conflict|notneeded)\]\s*/', $item, $m ) ) {
				$class = 'ih-s ih-s-' . $m[1];
				$badge = '<span class="ih-badge">' . esc_html( $status[ $m[1] ] ) . '</span> ';
				$item  = substr( $item, strlen( $m[0] ) );
			}
			$item = $badge . wp_kses_post( $footnote( $item ) );
			if ( $class ) {
				$out .= '<!-- wp:list-item {"className":"' . $class . "\"} -->\n<li class=\"" . $class . '">' . $item . "</li>\n<!-- /wp:list-item -->";
			} else {
				$out .= "<!-- wp:list-item -->\n<li>" . $item . "</li>\n<!-- /wp:list-item -->";
			}
		}
		$out .= "</ul>\n<!-- /wp:list -->\n";
		$list = array();
	};

	foreach ( $lines as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		if ( 0 === strpos( $line, '- ' ) ) {
			$list[] = substr( $line, 2 );
			continue;
		}
		$flush();
		if ( 0 === strpos( $line, '### ' ) ) {
			$out .= "<!-- wp:heading {\"level\":3} -->\n<h3 class=\"wp-block-heading\">" . wp_kses_post( $footnote( substr( $line, 4 ) ) ) . "</h3>\n<!-- /wp:heading -->\n";
		} elseif ( 0 === strpos( $line, '## ' ) ) {
			$out .= "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">" . wp_kses_post( $footnote( substr( $line, 3 ) ) ) . "</h2>\n<!-- /wp:heading -->\n";
		} elseif ( 0 === strpos( $line, '> ' ) ) {
			$out .= "<!-- wp:quote -->\n<blockquote class=\"wp-block-quote\"><!-- wp:paragraph -->\n<p>" . wp_kses_post( $footnote( substr( $line, 2 ) ) ) . "</p>\n<!-- /wp:paragraph --></blockquote>\n<!-- /wp:quote -->\n";
		} elseif ( preg_match( '/^@(consensus|minority|islamic)$/', $line, $m ) ) {
			$out .= '<!-- wp:paragraph {"className":"is-style-label-' . $m[1] . "\"} -->\n<p class=\"is-style-label-" . $m[1] . '">' . esc_html( $labels[ $m[1] ] ) . "</p>\n<!-- /wp:paragraph -->\n";
		} elseif ( '[page-list]' === $line ) {
			$out .= "<!-- wp:page-list /-->\n";
		} else {
			$out .= "<!-- wp:paragraph -->\n<p>" . wp_kses_post( $footnote( $line ) ) . "</p>\n<!-- /wp:paragraph -->\n";
		}
	}
	$flush();

	if ( $notes ) {
		$out .= "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\" id=\"notes\">" . esc_html__( 'Notes', 'injil-help' ) . "</h2>\n<!-- /wp:heading -->\n";
		$out .= "<!-- wp:list {\"ordered\":true,\"className\":\"ih-footnotes\"} -->\n<ol class=\"wp-block-list ih-footnotes\">";
		foreach ( $notes as $i => $note ) {
			$n    = $i + 1;
			$out .= sprintf( "<!-- wp:list-item -->\n<li id=\"fn-%1\$d\">%2\$s <a href=\"#fnref-%1\$d\" aria-label=\"%3\$s\">↩</a></li>\n<!-- /wp:list-item -->", $n, wp_kses_post( $note ), esc_attr__( 'Back to the text', 'injil-help' ) );
		}
		$out .= "</ol>\n<!-- /wp:list -->\n";
	}
	return $out;
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

/** Images chosen for each page: inc/data/images.json, keyed by page path. */
function injil_help_page_images( $path ) {
	static $all = null;
	if ( null === $all ) {
		$raw = file_get_contents( get_stylesheet_directory() . '/inc/data/images.json' );
		$all = $raw ? json_decode( $raw, true ) : array();
	}
	return $all[ $path ] ?? array();
}

/** One captioned figure. The first image of a page is the featured one. */
function injil_help_figure( $img, $featured ) {
	$url     = get_stylesheet_directory_uri() . '/assets/images/articles/' . $img['file'];
	$class   = 'ih-plate ' . ( $featured ? 'ih-feature' : 'ih-fig' );
	$caption = esc_html( $img['caption'] ) . ' ' . esc_html( $img['credit'] ) . ' <a href="' . esc_url( $img['source'] ) . '">Wikimedia Commons</a>.';
	return '<!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"' . $class . '"} -->' . "\n"
		. '<figure class="wp-block-image size-large ' . $class . '"><img src="' . esc_url( $url ) . '" alt="' . esc_attr( $img['alt'] ) . '" title="' . esc_attr( $img['title'] ) . '" width="' . (int) $img['w'] . '" height="' . (int) $img['h'] . '" loading="' . ( $featured ? 'eager' : 'lazy' ) . '" decoding="async"/><figcaption class="wp-element-caption">' . $caption . '</figcaption></figure>' . "\n"
		. '<!-- /wp:image -->' . "\n";
}

/**
 * Place a page's images: the featured one after the opening paragraph, the others
 * spread through the text, each after a paragraph and clear of the notes.
 */
function injil_help_insert_images( $content, $path ) {
	$images = injil_help_page_images( $path );
	if ( ! $images || false !== strpos( $content, '<img ' ) ) {
		return $content;
	}
	$blocks = injil_help_top_blocks( $content );
	$n      = count( $blocks );
	$end    = $n;
	foreach ( $blocks as $i => $b ) {
		if ( false !== strpos( $b, 'id="notes"' ) ) {
			$end = $i;
			break;
		}
	}
	$after = array();
	$first = 0;
	foreach ( $blocks as $i => $b ) {
		if ( 0 === strpos( $b, '<!-- wp:paragraph' ) ) {
			$first = $i;
			break;
		}
	}
	$after[ $first ] = 0;
	$rest            = count( $images ) - 1;
	$prev            = $first;
	for ( $k = 1; $k <= $rest; $k++ ) {
		$target = (int) round( $first + ( $end - 1 - $first ) * $k / ( $rest + 1 ) );
		$idx    = max( $target, $prev + 2 );
		while ( $idx < $end - 1 && 0 !== strpos( $blocks[ $idx ], '<!-- wp:paragraph' ) ) {
			++$idx;
		}
		if ( $idx >= $end || isset( $after[ $idx ] ) ) {
			break;
		}
		$after[ $idx ] = $k;
		$prev          = $idx;
	}
	$out = '';
	foreach ( $blocks as $i => $b ) {
		$out .= $b . "\n";
		if ( isset( $after[ $i ] ) && isset( $images[ $after[ $i ] ] ) ) {
			$out .= injil_help_figure( $images[ $after[ $i ] ], 0 === $after[ $i ] );
		}
	}
	return $out;
}

/** Block content for one page definition, or '' when its pattern is missing. */
function injil_help_page_content( $def ) {
	if ( ! empty( $def['pattern'] ) ) {
		$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'injil-help/' . $def['pattern'] );
		return $pattern ? $pattern['content'] : '';
	}
	return injil_help_insert_images( injil_help_markup_to_blocks( $def['body'] ?? array() ), $def['path'] );
}

function injil_help_find_page( $path ) {
	$page = get_page_by_path( $path, OBJECT, 'page' );
	return $page && 'trash' !== $page->post_status ? $page : null;
}

/**
 * Create every missing page. Returns the number created. With $overwrite, pages
 * that already exist are rewritten from the theme's text (title, content, excerpt).
 */
function injil_help_populate( $overwrite = false ) {
	// add_option() is an atomic insert, so only one request can take the lock; a lock older than a minute is stale.
	if ( ! add_option( 'injil_help_populating', time(), '', false ) ) {
		if ( time() - (int) get_option( 'injil_help_populating' ) < MINUTE_IN_SECONDS ) {
			return 0;
		}
		update_option( 'injil_help_populating', time(), false );
	}

	$created = 0;

	foreach ( injil_help_starter_pages() as $order => $def ) {
		$existing = injil_help_find_page( $def['path'] );
		if ( $existing && ! $overwrite ) {
			continue;
		}
		$content = injil_help_page_content( $def );
		if ( '' === $content ) {
			continue;
		}
		if ( $existing ) {
			$done = wp_update_post(
				array(
					'ID'           => $existing->ID,
					'post_title'   => $def['title'],
					'post_content' => $content,
					'post_excerpt' => $def['excerpt'] ?? '',
				)
			);
			if ( $done && ! is_wp_error( $done ) ) {
				++$created;
			}
			continue;
		}
		$parts  = explode( '/', $def['path'] );
		$slug   = array_pop( $parts );
		$parent = $parts ? injil_help_find_page( implode( '/', $parts ) ) : null;

		// The privacy page WordPress drafts for new sites is filled in rather than duplicated.
		$id = 'privacy-policy' === $def['path'] ? (int) get_option( 'wp_page_for_privacy_policy' ) : 0;
		if ( $id && 'draft' !== get_post_status( $id ) ) {
			$id = 0;
		}

		$args = array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $def['title'],
			'post_name'    => $slug,
			'post_parent'  => $parent ? $parent->ID : 0,
			'post_content' => $content,
			'post_excerpt' => $def['excerpt'] ?? '',
			'menu_order'   => $order,
		);
		if ( $id ) {
			$args['ID'] = $id;
			wp_update_post( $args );
		} else {
			$id = wp_insert_post( $args );
		}
		if ( $id && ! is_wp_error( $id ) ) {
			if ( ! empty( $def['template'] ) ) {
				update_post_meta( $id, '_wp_page_template', $def['template'] );
			}
			++$created;
		}
	}

	// Pretty permalinks are required for the section URLs.
	if ( ! get_option( 'permalink_structure' ) ) {
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/%postname%/' );
	}
	flush_rewrite_rules();

	update_option( 'injil_help_populated', INJIL_HELP_VERSION );
	delete_option( 'injil_help_populating' );
	return $created;
}

add_action(
	'after_switch_theme',
	function () {
		if ( ! get_option( 'injil_help_populated' ) ) {
			injil_help_populate();
		}
	}
);

/**
 * After a theme update, rewrite the starter pages from the new text. Runs once per
 * version, on the first admin screen load by someone who can edit theme options.
 */
add_action(
	'admin_init',
	function () {
		$done = get_option( 'injil_help_populated' );
		if ( $done && version_compare( (string) $done, INJIL_HELP_VERSION, '<' ) && current_user_can( 'edit_theme_options' ) ) {
			injil_help_populate( true );
		}
	}
);

/** Run from the Theme Options > Content tab. The update button passes overwrite=1. */
add_action(
	'admin_post_injil_help_populate',
	function () {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'injil-help' ), 403 );
		}
		check_admin_referer( 'injil_help_populate' );
		$overwrite = ! empty( $_GET['overwrite'] );
		$n         = injil_help_populate( $overwrite );
		wp_safe_redirect( add_query_arg( array( 'page' => 'injil-help-options', $overwrite ? 'ih_updated' : 'ih_created' => $n ), admin_url( 'themes.php' ) ) . '#content' );
		exit;
	}
);
