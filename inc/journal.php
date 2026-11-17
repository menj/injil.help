<?php
/**
 * The Journal: dated posts at /journal/<slug>/, listed on the Journal page /journal/
 * (a starter page with the template templates/page-journal.html; the reading settings are left alone).
 *
 * Posts are standard WordPress posts. Their text is JSON in inc/data/posts.json (the same compact
 * markup as the pages), and injil_help_journal_seed() writes them once per theme version with the
 * fixed post_date each entry carries: a date in the past is published, a date still to come is
 * scheduled (status "future") and WordPress publishes it on the day. A re-run finds each post by
 * its _injil_help_post meta and changes nothing unless the theme's text changed and the owner has
 * not edited the post. A post the owner binned stays in the bin.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

const INJIL_HELP_JOURNAL_BASE = 'journal';     // the posts page slug and the prefix of every post address
const INJIL_HELP_POST_META    = '_injil_help_post'; // the slug of the entry in posts.json a post was written from

/** The Journal entries from inc/data/posts.json, each with slug, title, excerpt, date, category, related and body. */
function injil_help_journal_entries() {
	static $entries = null;
	if ( null === $entries ) {
		$entries = array();
		foreach ( (array) injil_help_data_file( 'posts' ) as $row ) {
			if ( is_array( $row ) && ! empty( $row['slug'] ) && ! empty( $row['title'] ) && ! empty( $row['date'] ) && ! empty( $row['body'] ) ) {
				$entries[] = $row;
			}
		}
	}
	return $entries;
}

/**
 * The status a post gets from its fixed date: "publish" when the date (site time) has passed,
 * "future" when it is still to come. $now is a Unix time, for the tests; default the present.
 */
function injil_help_journal_status( $date, $now = null ) {
	$now  = null === $now ? time() : (int) $now;
	$when = strtotime( get_gmt_from_date( $date ) . ' UTC' );
	return ( false !== $when && $when > $now ) ? 'future' : 'publish';
}

/** Block content of one entry: its body in the compact markup and its "Read next" row. */
function injil_help_journal_content( $entry ) {
	$def     = array(
		'path'    => INJIL_HELP_JOURNAL_BASE . '/' . $entry['slug'],
		'related' => (array) ( $entry['related'] ?? array() ),
	);
	$content = injil_help_markup_to_blocks( (array) $entry['body'] );
	return $content . injil_help_related_blocks( $def, $content );
}

/** The Journal page (a starter page with the "Journal" template, which lists the posts), or null. */
function injil_help_journal_page() {
	$page = get_page_by_path( INJIL_HELP_JOURNAL_BASE );
	return $page instanceof WP_Post ? $page : null;
}

/** The post an entry was written to, in any status the bin included, or null. */
function injil_help_journal_find( $slug ) {
	$found = get_posts(
		array(
			'post_type'        => 'post',
			'post_status'      => array( 'publish', 'future', 'draft', 'pending', 'private', 'trash' ),
			'meta_key'         => INJIL_HELP_POST_META, // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'       => $slug, // phpcs:ignore WordPress.DB.SlowDBQuery
			'numberposts'      => 1,
			'suppress_filters' => true,
		)
	);
	return $found ? $found[0] : null;
}

/** The category of an entry, created when missing; 0 when none is named. */
function injil_help_journal_category( $name ) {
	$name = trim( (string) $name );
	if ( '' === $name ) {
		return 0;
	}
	$term = term_exists( $name, 'category' );
	if ( ! $term ) {
		$term = wp_insert_term( $name, 'category' );
	}
	return is_array( $term ) ? (int) $term['term_id'] : 0;
}

/**
 * Write every Journal entry. Returns a tally of created, updated, up to
 * date, kept (edited or binned by the owner) and failed. $now is for the tests.
 */
function injil_help_journal_seed( $now = null ) {
	$tally = array( 'created' => 0, 'updated' => 0, 'current' => 0, 'kept' => 0, 'failed' => 0 );

	$GLOBALS['injil_help_seeding'] = true; // the spam gate exempts the theme's own writing (injil_help_sec_is_seeded() in inc/security.php)
	foreach ( injil_help_journal_entries() as $entry ) {
		$content = injil_help_journal_content( $entry );
		$excerpt = (string) ( $entry['excerpt'] ?? '' );
		$status  = injil_help_journal_status( $entry['date'], $now );
		$post    = injil_help_journal_find( $entry['slug'] );
		if ( ! $post ) {
			// An entry that took a new slug keeps its post: renamed in place, the old address redirects.
			$post = injil_help_journal_rename( $entry );
		}
		if ( $post && ( 'trash' === $post->post_status || injil_help_page_is_edited( $post ) ) ) {
			++$tally['kept'];
			continue;
		}
		if ( $post && md5( $content ) === get_post_meta( $post->ID, INJIL_HELP_HASH_META, true ) && injil_help_head_hash( $entry['title'], $excerpt ) === get_post_meta( $post->ID, INJIL_HELP_HEAD_META, true ) ) {
			++$tally['current'];
			continue;
		}
		$data = array(
			'post_type'     => 'post',
			'post_name'     => $entry['slug'],
			'post_title'    => $entry['title'],
			'post_excerpt'  => $excerpt,
			'post_content'  => $content,
			'post_date'     => $entry['date'],
			'post_date_gmt' => get_gmt_from_date( $entry['date'] ),
			'post_status'   => $status,
			'comment_status' => 'closed',
			'ping_status'   => 'closed',
		);
		$category = injil_help_journal_category( $entry['category'] ?? '' );
		if ( $category ) {
			$data['post_category'] = array( $category );
		}
		if ( $post ) {
			$data['ID'] = $post->ID;
			// The date and the status stay as WordPress has them once written, so a published post is never pulled back.
			unset( $data['post_date'], $data['post_date_gmt'], $data['post_status'] );
			$id = wp_update_post( wp_slash( $data ), true );
		} else {
			$id = wp_insert_post( wp_slash( $data ), true );
		}
		if ( is_wp_error( $id ) || ! $id ) {
			++$tally['failed'];
			continue;
		}
		update_post_meta( $id, INJIL_HELP_POST_META, $entry['slug'] );
		injil_help_stamp_page( $id, $content, $entry['title'], $excerpt );
		++$tally[ $post ? 'updated' : 'created' ];
		injil_help_log( $entry['title'], $post ? __( 'took the theme\'s new text', 'injil-help' ) : __( 'created', 'injil-help' ) );
	}
	$GLOBALS['injil_help_seeding'] = false;
	return $tally;
}

/** Seed once per theme version, on the first admin load by someone who can edit theme options, and on activation. */
function injil_help_journal_maybe_seed() {
	if ( get_option( 'injil_help_journal_seeded' ) === INJIL_HELP_VERSION ) {
		return;
	}
	$tally = injil_help_journal_seed();
	if ( ! $tally['failed'] ) {
		update_option( 'injil_help_journal_seeded', INJIL_HELP_VERSION, false );
		// A replacement that this seeding just wrote is live now, so retire what it replaces (inc/supersede.php).
		if ( function_exists( 'injil_help_retire_run' ) ) {
			injil_help_retire_run();
		}
	}
}

add_action(
	'admin_init',
	function () {
		if ( ! wp_doing_ajax() && current_user_can( 'edit_theme_options' ) ) {
			injil_help_journal_maybe_seed();
		}
	},
	20
);

/* ---------- Addresses: /journal/<slug>/ ---------- */

add_action(
	'init',
	function () {
		add_rewrite_rule( '^' . INJIL_HELP_JOURNAL_BASE . '/([^/]+)/?$', 'index.php?name=$matches[1]', 'top' );
	}
);

/** A post's address under the Journal. */
function injil_help_journal_permalink( $post ) {
	return home_url( user_trailingslashit( INJIL_HELP_JOURNAL_BASE . '/' . $post->post_name ) );
}

add_filter(
	'post_link',
	function ( $link, $post ) {
		if ( $post instanceof WP_Post && 'post' === $post->post_type && '' !== $post->post_name && get_option( 'permalink_structure' ) ) {
			return injil_help_journal_permalink( $post );
		}
		return $link;
	},
	10,
	2
);

/* ---------- Breadcrumb: Home › Journal › post ---------- */

/** The trail of a Journal post gains the Journal page; called by injil_help_breadcrumb_parents() in inc/seo.php. */
function injil_help_journal_breadcrumb( $items, $post ) {
	$journal = $post instanceof WP_Post && 'post' === $post->post_type ? injil_help_journal_page() : null;
	if ( $journal ) {
		$items[] = array( wp_strip_all_tags( get_the_title( $journal ) ), get_permalink( $journal ) );
	}
	return $items;
}

/* ---------- Styles ---------- */

add_action(
	'wp_enqueue_scripts',
	function () {
		if ( is_home() || is_archive() || is_singular( 'post' ) || is_page( 'journal' ) || ( is_page() && 'page-journal' === get_page_template_slug() ) ) {
			wp_enqueue_style( 'injil-help-journal', get_stylesheet_directory_uri() . '/assets/css/journal.css', array( 'injil-help' ), INJIL_HELP_VERSION );
		}
	}
);

// The last line: functions.php checks for it, so a file an upload cut short is caught.
$GLOBALS['injil_help_loaded'][ basename( __FILE__, '.php' ) ] = true;
