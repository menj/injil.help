<?php
/**
 * Starter content: creates the site's pages on theme activation, and on demand
 * from Appearance > Theme Options > Content. After a theme update, and from the
 * "Update all pages" button, existing pages are rewritten from the theme's text.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

require_once get_stylesheet_directory() . '/inc/images.php';

require_once get_stylesheet_directory() . '/inc/starter-content-pages.php';

/**
 * Convert the compact page markup to blocks.
 *
 * "## " heading, "### " sub-heading, "- " list item, "> " quote, "@consensus|@minority|@islamic"
 * claim label, anything else a paragraph. Blank lines are ignored. Inline
 * "{{fn:text}}" becomes a numbered footnote; the notes are listed at the end, or at a "[notes]"
 * token as a reference apparatus. "[v]" opens a numbered verse and "[qS:A]" marks a Qur'anic
 * verse; "[bible]" ... "[/bible]" wraps a gospel section for the Bible layout; "[p.classes] text"
 * is a paragraph with those classes.
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
	$flushed  = 0; // notes already printed by an earlier [notes] token on this page
	$verse    = 0; // "[v]" verse breaks, numbered per page
	$footnote = function ( $text ) use ( &$notes, &$flushed, &$verse ) {
		// "[v]" opens a numbered verse; "[qS:A]" marks a Qur'anic verse by chapter and verse.
		$text = preg_replace_callback(
			'/\[v\]/',
			function () use ( &$verse ) {
				$verse++;
				return '<span class="ih-v" id="v' . $verse . '">' . $verse . '</span>';
			},
			$text
		);
		$text = preg_replace_callback(
			'/\[q(\d+):(\d+)\]/',
			function ( $m ) {
				return '<span class="ih-v ih-vq" id="q' . $m[1] . '-' . $m[2] . '">' . $m[1] . ':' . $m[2] . '</span>';
			},
			$text
		);
		return preg_replace_callback(
			'/\{\{fn:(.*?)\}\}/s',
			function ( $m ) use ( &$notes, &$flushed ) {
				$notes[] = trim( $m[1] );
				$n       = $flushed + count( $notes );
				return sprintf( '<sup class="ih-fnref"><a href="#fn-%1$d" id="fnref-%1$d">%1$d</a></sup>', $n );
			},
			$text
		);
	};
	// The collected notes as an ordered list: with the "Notes" heading at the end of a page, or, for the
	// "[notes]" token, as a reference apparatus at the foot of the text, numbered on from the last list.
	$print_notes = function ( $heading ) use ( &$out, &$notes, &$flushed ) {
		if ( ! $notes ) {
			return;
		}
		$start = $flushed + 1;
		$class = $heading ? 'ih-footnotes' : 'ih-footnotes ih-apparatus';
		if ( $heading ) {
			$out .= "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\" id=\"notes\">" . esc_html__( 'Notes', 'injil-help' ) . "</h2>\n<!-- /wp:heading -->\n";
		}
		$out .= '<!-- wp:list {"ordered":true' . ( $start > 1 ? ',"start":' . $start : '' ) . ',"className":"' . $class . '"} -->' . "\n" . '<ol' . ( $start > 1 ? ' start="' . $start . '"' : '' ) . ' class="wp-block-list ' . $class . '">';
		foreach ( $notes as $i => $note ) {
			$n    = $flushed + $i + 1;
			$out .= sprintf( "<!-- wp:list-item -->\n<li id=\"fn-%1\$d\">%2\$s <a href=\"#fnref-%1\$d\" aria-label=\"%3\$s\">↩</a></li>\n<!-- /wp:list-item -->", $n, wp_kses_post( $note ), esc_attr__( 'Back to the text', 'injil-help' ) );
		}
		$out     .= "</ol>\n<!-- /wp:list -->\n";
		$flushed += count( $notes );
		$notes    = array();
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

	$question = '';
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
		// "? Question" followed by its answer paragraph renders as a collapsible FAQ entry, which the structured data collects as FAQPage.
		if ( '' !== $question ) {
			$out .= '<!-- wp:details {"className":"is-style-faq"} -->' . "\n" . '<details class="wp-block-details is-style-faq"><summary>' . wp_kses_post( $question ) . "</summary><!-- wp:paragraph -->\n<p>" . wp_kses_post( $footnote( $line ) ) . "</p>\n<!-- /wp:paragraph --></details>\n<!-- /wp:details -->\n";
			$question = '';
			continue;
		}
		if ( 0 === strpos( $line, '? ' ) ) {
			$question = substr( $line, 2 );
			continue;
		}
		// "[details] Title" ... "[/details]" folds the lines between into a collapsible block.
		if ( 0 === strpos( $line, '[details] ' ) ) {
			$out .= '<!-- wp:details {"className":"is-style-fold"} -->' . "\n" . '<details class="wp-block-details is-style-fold"><summary>' . wp_kses_post( $footnote( substr( $line, 10 ) ) ) . "</summary>\n";
			continue;
		}
		if ( '[/details]' === $line ) {
			$out .= "</details>\n<!-- /wp:details -->\n";
			continue;
		}
		// "[bible]" ... "[/bible]" wraps the text of a gospel section in the block the Bible layout styles.
		if ( '[bible]' === $line ) {
			$out .= '<!-- wp:group {"className":"ih-bible","layout":{"type":"default"}} -->' . "\n" . '<div class="wp-block-group ih-bible">';
			continue;
		}
		if ( '[/bible]' === $line ) {
			$out .= "</div>\n<!-- /wp:group -->\n";
			continue;
		}
		// "[notes]" prints the notes collected so far as a reference apparatus at this point.
		if ( '[notes]' === $line ) {
			$print_notes( false );
			continue;
		}
		// "[p.class-names] text" is a paragraph with those classes.
		if ( preg_match( '/^\[p\.([a-z0-9 -]+)\]\s*(.*)$/s', $line, $m ) ) {
			$out .= '<!-- wp:paragraph {"className":"' . esc_attr( $m[1] ) . '"} -->' . "\n<p class=\"" . esc_attr( $m[1] ) . '">' . wp_kses_post( $footnote( $m[2] ) ) . "</p>\n<!-- /wp:paragraph -->\n";
			continue;
		}
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
	$print_notes( true );
	return $out;
}

/** Block content for one page definition, or '' when its pattern is missing. */
function injil_help_page_content( $def ) {
	if ( ! empty( $def['pattern'] ) ) {
		$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'injil-help/' . $def['pattern'] );
		return $pattern ? injil_help_add_page_image( $def['path'], $pattern['content'] . injil_help_related_blocks( $def, $pattern['content'] ), true ) : '';
	}
	$content = injil_help_markup_to_blocks( $def['body'] ?? array() );
	return injil_help_add_page_image( $def['path'], $content . injil_help_related_blocks( $def, $content ), false );
}

/**
 * The "Read next" row under a page's content: the pages named in the definition's "related"
 * list (the builders and inc/data/related.json), linked by their titles. Content that already
 * ends in its own "ih-next" aside (the section indexes) gets no second row.
 */
function injil_help_related_blocks( $def, $content = '' ) {
	if ( empty( $def['related'] ) || false !== strpos( $content, 'ih-aside ih-next' ) ) {
		return '';
	}
	static $titles = null;
	if ( null === $titles ) {
		$titles = array();
		foreach ( injil_help_starter_pages() as $p ) {
			$titles[ $p['path'] ] = $p['title'];
		}
	}
	$links = array();
	$sites = array();
	foreach ( (array) $def['related'] as $item ) {
		if ( is_array( $item ) && ! empty( $item['url'] ) && ! empty( $item['title'] ) ) {
			$sites[] = '<a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['title'] ) . '</a>' . ( empty( $item['site'] ) ? '' : ' <span class="ih-site">(' . esc_html( $item['site'] ) . ')</span>' );
		} elseif ( is_string( $item ) && $item !== $def['path'] && isset( $titles[ $item ] ) ) {
			$links[] = '<a href="/' . esc_attr( $item ) . '/">' . esc_html( $titles[ $item ] ) . '</a>';
		}
	}
	if ( ! $links && ! $sites ) {
		return '';
	}
	$out = "\n<!-- wp:group {\"className\":\"ih-aside ih-next\",\"layout\":{\"type\":\"default\"}} -->\n<div class=\"wp-block-group ih-aside ih-next\"><!-- wp:paragraph {\"className\":\"ih-k\"} -->\n<p class=\"ih-k\">Read next</p>\n<!-- /wp:paragraph -->";
	if ( $links ) {
		$out .= "\n<!-- wp:paragraph -->\n<p>" . implode( ' · ', $links ) . "</p>\n<!-- /wp:paragraph -->";
	}
	if ( $sites ) {
		$out .= "\n<!-- wp:paragraph {\"className\":\"ih-sites\"} -->\n<p class=\"ih-sites\">On the writer’s other websites: " . implode( ' · ', $sites ) . "</p>\n<!-- /wp:paragraph -->";
	}
	return $out . "</div>\n<!-- /wp:group -->";
}

/**
 * Every URL on the site is made of words (/gospel-according-to-islam/john-the-baptist/,
 * /quran/commentaries/birth-of-jesus/tabari-part-two/), as the Google SEO Starter Guide asks.
 * Versions up to 3.5 numbered the gospel sections and the commentary parts and put the verse
 * numbers in the group slugs; populate() renames those existing pages in place (see
 * injil_help_adopt_former_page()), so no page is duplicated. The old URLs are not redirected.
 */
const INJIL_HELP_RULES = '3'; // bump when the rewrite rules change; populate() flushes when the stored value differs

/**
 * A page that an earlier version published under one of the definition's "former" paths (a numeric
 * slug, a group slug with verse numbers, or a copy WordPress renamed "2-2") is moved to its present
 * path: the slug and the parent change, the ID, the edits and the featured image stay. Returns the
 * moved page or null.
 */
function injil_help_adopt_former_page( $def ) {
	foreach ( (array) ( $def['former'] ?? array() ) as $old ) {
		$page = injil_help_find_page( $old );
		if ( ! $page ) {
			$page = injil_help_repair_numeric_slug( $old );
		}
		if ( ! $page ) {
			continue;
		}
		$parts  = explode( '/', $def['path'] );
		$slug   = array_pop( $parts );
		$parent = $parts ? injil_help_find_page( implode( '/', $parts ) ) : null;
		if ( $parts && ! $parent ) {
			return null;
		}
		wp_update_post(
			array(
				'ID'          => $page->ID,
				'post_name'   => $slug,
				'post_parent' => $parent ? $parent->ID : 0,
			)
		);
		clean_post_cache( $page->ID );
		$moved = injil_help_find_page( $def['path'] );
		if ( $moved ) {
			return $moved;
		}
	}
	return null;
}

/**
 * Versions before 3.5.1 created the numbered pages under slugs WordPress renamed ("2-2", and
 * "2-3", "2-4" from later runs that could not find them): rename the oldest copy back to the
 * numeric slug, so that injil_help_adopt_former_page() can move it on, and bin the duplicates
 * that were never edited. Returns the repaired page or null.
 */
function injil_help_repair_numeric_slug( $path ) {
	$parts = explode( '/', $path );
	$slug  = array_pop( $parts );
	if ( ! $parts || ! preg_match( '/^\d+$/', $slug ) ) {
		return null;
	}
	$parent = injil_help_find_page( implode( '/', $parts ) );
	if ( ! $parent ) {
		return null;
	}
	$ids = get_posts(
		array(
			'post_type'        => 'page',
			'post_parent'      => $parent->ID,
			'post_status'      => 'publish',
			'posts_per_page'   => -1,
			'orderby'          => 'ID',
			'order'            => 'ASC',
			'fields'           => 'ids',
			'suppress_filters' => true,
		)
	);
	// The copy to keep is the oldest edited one, or the oldest of all when none was edited; unedited duplicates are binned, edited ones left alone.
	$copies = array();
	foreach ( $ids as $id ) {
		if ( preg_match( '/^' . preg_quote( $slug, '/' ) . '-\d+$/', (string) get_post_field( 'post_name', $id ) ) ) {
			$copies[ (int) $id ] = injil_help_page_is_edited( get_post( $id ) );
		}
	}
	if ( ! $copies ) {
		return null;
	}
	$edited = array_keys( array_filter( $copies ) );
	$keep   = $edited ? (int) $edited[0] : (int) array_key_first( $copies );
	foreach ( $copies as $id => $is_edited ) {
		if ( $id !== $keep && ! $is_edited ) {
			wp_trash_post( $id );
		}
	}
	wp_update_post(
		array(
			'ID'        => $keep,
			'post_name' => $slug,
		)
	);
	clean_post_cache( $keep );
	$page = get_post( $keep );
	return $page && $slug === $page->post_name ? $page : null;
}

function injil_help_find_page( $path ) {
	$page = get_page_by_path( $path, OBJECT, 'page' );
	return $page && 'trash' !== $page->post_status ? $page : null;
}

const INJIL_HELP_HASH_META  = '_injil_help_hash'; // md5 of the content the theme wrote
const INJIL_HELP_HEAD_META  = '_injil_help_head'; // md5 of the title and excerpt it wrote (from 3.6.1; absent on older pages)

/** The hash of a page's title and excerpt as the theme wrote them. */
function injil_help_head_hash( $title, $excerpt ) {
	return md5( (string) $title . "\n" . (string) $excerpt );
}

/** Record what the theme wrote, so a later edit to the content, the title or the excerpt is detected. */
function injil_help_stamp_page( $id, $content, $title, $excerpt ) {
	update_post_meta( $id, INJIL_HELP_HASH_META, md5( $content ) );
	update_post_meta( $id, INJIL_HELP_HEAD_META, injil_help_head_hash( $title, $excerpt ) );
}

/**
 * True when a starter page has been edited since the theme last wrote it: the content differs
 * from the content hash, or the title or excerpt differs from the head hash. Pages written before
 * 3.6.1 carry no head hash, so for them only the content counts until the theme rewrites them.
 */
function injil_help_page_is_edited( $post ) {
	$hash = get_post_meta( $post->ID, INJIL_HELP_HASH_META, true );
	if ( $hash && $hash !== md5( $post->post_content ) ) {
		return true;
	}
	$head = get_post_meta( $post->ID, INJIL_HELP_HEAD_META, true );
	return $hash && $head && $head !== injil_help_head_hash( $post->post_title, $post->post_excerpt );
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
		if ( ! $existing ) {
			$existing = injil_help_adopt_former_page( $def );
		}
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
				injil_help_stamp_page( $existing->ID, $content, $def['title'], $excerpt );
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
			injil_help_stamp_page( $id, $content, $def['title'], $excerpt );
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
	// Flush once when the theme's rewrite rules change (3.6.0 removed the rules for the numeric slugs).
	if ( get_option( 'injil_help_rules' ) !== INJIL_HELP_RULES ) {
		flush_rewrite_rules();
		update_option( 'injil_help_rules', INJIL_HELP_RULES, false );
	}

	update_option( 'injil_help_populated', INJIL_HELP_VERSION );
	delete_option( 'injil_help_featured' ); // the featured-image pass runs again on the next admin loads
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
