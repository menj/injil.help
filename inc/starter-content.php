<?php
/**
 * Starter content: creates the site's pages on theme activation, and on demand
 * from Appearance > Theme Options > Content. After a theme update, and from the
 * "Update all pages" button, existing pages are rewritten from the theme's text.
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

/** Block content for one page definition, or '' when its pattern is missing. */
function injil_help_page_content( $def ) {
	if ( ! empty( $def['pattern'] ) ) {
		$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'injil-help/' . $def['pattern'] );
		return $pattern ? $pattern['content'] : '';
	}
	return injil_help_markup_to_blocks( $def['body'] ?? array() );
}

function injil_help_find_page( $path ) {
	$page = get_page_by_path( $path, OBJECT, 'page' );
	return $page && 'trash' !== $page->post_status ? $page : null;
}

const INJIL_HELP_HASH_META = '_injil_help_hash';

/** True when a starter page's stored content has been edited since the theme last wrote it. */
function injil_help_page_is_edited( $post ) {
	$hash = get_post_meta( $post->ID, INJIL_HELP_HASH_META, true );
	return $hash && $hash !== md5( $post->post_content );
}

/**
 * Create every missing page. Returns the number created or rewritten. With $overwrite,
 * pages that already exist are rewritten from the theme's text (title, content,
 * excerpt), except pages edited since the theme wrote them, which are kept unless
 * $force is set (the "Update all pages" button). A page whose stored text already
 * matches is left alone, and no revision is kept for the rewrite. Each page the
 * theme writes carries a hash of that content, which is how an edit is detected.
 */
function injil_help_populate( $overwrite = false, $force = false ) {
	// add_option() is an atomic insert, so only one request can take the lock; a lock older than a minute is stale.
	if ( ! add_option( 'injil_help_populating', time(), '', false ) ) {
		if ( time() - (int) get_option( 'injil_help_populating' ) < MINUTE_IN_SECONDS ) {
			return 0;
		}
		update_option( 'injil_help_populating', time(), false );
	}

	// No page definitions (a missing or malformed data file): do nothing and do not record the version, so the run is retried.
	$pages = injil_help_starter_pages();
	if ( ! $pages ) {
		delete_option( 'injil_help_populating' );
		return 0;
	}

	$created = 0;
	add_filter( 'wp_revisions_to_keep', '__return_zero', 99 );

	foreach ( $pages as $order => $def ) {
		$existing = injil_help_find_page( $def['path'] );
		if ( $existing && ! $overwrite ) {
			continue;
		}
		$content = injil_help_page_content( $def );
		if ( '' === $content ) {
			continue;
		}
		$excerpt = $def['excerpt'] ?? '';
		if ( $existing ) {
			if ( $existing->post_title === $def['title'] && $existing->post_content === $content && $existing->post_excerpt === $excerpt ) {
				continue;
			}
			if ( ! $force && injil_help_page_is_edited( $existing ) ) {
				continue;
			}
			$done = wp_update_post(
				wp_slash(
					array(
						'ID'           => $existing->ID,
						'post_title'   => $def['title'],
						'post_content' => $content,
						'post_excerpt' => $excerpt,
					)
				)
			);
			if ( $done && ! is_wp_error( $done ) ) {
				update_post_meta( $existing->ID, INJIL_HELP_HASH_META, md5( $content ) );
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
			'post_excerpt' => $excerpt,
			'menu_order'   => $order,
		);
		if ( $id ) {
			$args['ID'] = $id;
			$done       = wp_update_post( wp_slash( $args ) );
			if ( ! $done || is_wp_error( $done ) ) {
				$id = 0;
			}
		} else {
			$id = wp_insert_post( wp_slash( $args ) );
		}
		if ( $id && ! is_wp_error( $id ) ) {
			if ( ! empty( $def['template'] ) ) {
				update_post_meta( $id, '_wp_page_template', $def['template'] );
			}
			update_post_meta( $id, INJIL_HELP_HASH_META, md5( $content ) );
			++$created;
		}
	}

	remove_filter( 'wp_revisions_to_keep', '__return_zero', 99 );

	// Pretty permalinks are required for the section URLs. Pages need no new rewrite rules, so flush only when the structure changed.
	if ( ! get_option( 'permalink_structure' ) ) {
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/%postname%/' );
		flush_rewrite_rules();
	}

	update_option( 'injil_help_populated', INJIL_HELP_VERSION );
	delete_option( 'injil_help_populating' );
	return $created;
}

/**
 * A new WordPress site ships a "Hello world!" post, a "Sample Page" and a sample
 * comment, and the tagline "Just another WordPress site". On first activation
 * the three are moved to the bin (if they were never edited) and the tagline is
 * replaced, so nothing from the installer reaches a visitor.
 */
function injil_help_remove_sample_content() {
	foreach ( array( 'post' => 'hello-world', 'page' => 'sample-page' ) as $type => $slug ) {
		$sample = get_page_by_path( $slug, OBJECT, $type );
		if ( $sample && 'trash' !== $sample->post_status && $sample->post_modified_gmt === $sample->post_date_gmt ) {
			wp_trash_post( $sample->ID );
		}
	}
	foreach ( get_comments( array( 'author_email' => 'wapuu@wordpress.example', 'status' => 'approve' ) ) as $comment ) {
		wp_trash_comment( $comment );
	}
	if ( 'Just another WordPress site' === get_option( 'blogdescription' ) ) {
		update_option( 'blogdescription', __( 'The Islamic Dilemma, answered', 'injil-help' ) );
	}
}

add_action(
	'after_switch_theme',
	function () {
		if ( ! get_option( 'injil_help_populated' ) ) {
			injil_help_remove_sample_content();
			injil_help_populate();
		}
	}
);

/**
 * After a theme update, rewrite the starter pages from the new text. Runs once per
 * version, on the first admin screen load by someone who can edit theme options.
 * Pages edited since the theme wrote them are kept; Theme Options > Content lists
 * them and its "Update all pages" button rewrites them too.
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
		$n         = injil_help_populate( $overwrite, $overwrite );
		wp_safe_redirect( add_query_arg( array( 'page' => 'injil-help-options', $overwrite ? 'ih_updated' : 'ih_created' => $n ), admin_url( 'themes.php' ) ) . '#content' );
		exit;
	}
);
