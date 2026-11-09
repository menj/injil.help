<?php
/**
 * Unlisted posts (3.21.0): the behaviour of the Unlist Posts & Pages plugin, built in.
 *
 * An unlisted post, page or public custom post keeps its address and opens for anyone who has
 * it. Every listing leaves it out: the front page and archives, feeds, search, the core and
 * Rank Math sitemaps, the image sitemap, wp_list_pages(), the page-list block, the previous and
 * next links, the "Read next" row and REST collections. Its head carries noindex, through Rank
 * Math's robots filter when Rank Math is active.
 *
 * Each post's state is post meta (_injil_help_unlisted); the option injil_help_unlisted holds
 * the list built from it, mirrored to the plugin's unlist_posts. A change the plugin makes to
 * its list while it is active is taken over when the theme next reads it. While the plugin is active this file
 * does nothing, so the two never run twice. Filter injil_help_unlist_enabled to switch it off.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

const INJIL_HELP_UNLISTED = 'injil_help_unlisted';

/** Whether the theme's unlisting runs: off while the plugin is active, or when the filter says so. */
function injil_help_unlist_on() {
	if ( class_exists( 'Unlist_Posts' ) || defined( 'UNLIST_POSTS_VER' ) ) {
		return false;
	}
	return (bool) apply_filters( 'injil_help_unlist_enabled', true );
}

/**
 * A clean list of positive, unique integer IDs from whatever is stored. Older plugin installs
 * stored an empty string, so anything that is not an array counts as empty.
 *
 * @param mixed $stored Stored value.
 * @return int[]
 */
function injil_help_unlist_normalise( $stored ) {
	if ( ! is_array( $stored ) ) {
		return array();
	}
	$ids = array_filter(
		array_map( 'intval', $stored ),
		function ( $id ) {
			return $id > 0;
		}
	);
	return array_values( array_unique( $ids ) );
}

/** Post meta that holds the truth for each post: 1 when it is unlisted. */
const INJIL_HELP_UNLISTED_META = '_injil_help_unlisted';

/**
 * A fingerprint of a list of IDs, order aside.
 *
 * @param int[] $ids IDs.
 * @return string
 */
function injil_help_unlist_hash( $ids ) {
	$ids = injil_help_unlist_normalise( $ids );
	sort( $ids );
	return md5( implode( ',', $ids ) );
}

/** The IDs whose post meta says unlisted, read from the database each time. */
function injil_help_unlist_meta_ids() {
	global $wpdb;
	$ids = injil_help_unlist_normalise( $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = '1'", INJIL_HELP_UNLISTED_META ) ) );
	sort( $ids );
	return $ids;
}

/**
 * Write the list from the post meta: the theme option, and the plugin's unlist_posts as a
 * mirror, so the plugin sees the same list if it is switched on again. The fingerprint of
 * what was written is kept, so a later change made by the plugin can be told apart.
 *
 * @return int[]
 */
function injil_help_unlist_rebuild() {
	$ids = injil_help_unlist_meta_ids();
	update_option( INJIL_HELP_UNLISTED, $ids, true );
	update_option( 'unlist_posts', $ids );
	update_option( 'injil_help_unlisted_src', injil_help_unlist_hash( $ids ), false );
	return $ids;
}

/**
 * Take over the plugin's list when it differs from what the theme last wrote: the first time,
 * and again whenever the plugin has been active and changed it.
 */
function injil_help_unlist_sync() {
	$plugin = injil_help_unlist_normalise( get_option( 'unlist_posts', array() ) );
	if ( get_option( 'injil_help_unlisted_src' ) === injil_help_unlist_hash( $plugin ) && null !== get_option( INJIL_HELP_UNLISTED, null ) ) {
		return;
	}
	foreach ( array_diff( injil_help_unlist_meta_ids(), $plugin ) as $id ) {
		delete_post_meta( $id, INJIL_HELP_UNLISTED_META );
	}
	foreach ( $plugin as $id ) {
		update_post_meta( $id, INJIL_HELP_UNLISTED_META, '1' );
	}
	injil_help_unlist_rebuild();
}

/** The unlisted IDs the theme applies, after taking over any change the plugin made. */
function injil_help_unlisted_ids() {
	injil_help_unlist_sync();
	$ids = (array) get_option( INJIL_HELP_UNLISTED, array() );
	// Items that a newer item supersedes are hidden the same way (inc/supersede.php).
	if ( function_exists( 'injil_help_superseded_ids' ) ) {
		$ids = array_merge( $ids, injil_help_superseded_ids() );
	}
	return injil_help_unlist_normalise( $ids );
}

/**
 * The unlisted IDs from whichever side is live: the plugin's list while it is active, else the
 * theme's. For exclusions that run either way, such as the image sitemap.
 *
 * @return int[]
 */
function injil_help_unlisted_live_ids() {
	if ( class_exists( 'Unlist_Posts' ) || defined( 'UNLIST_POSTS_VER' ) ) {
		return (bool) apply_filters( 'unlist_posts_enabled', true ) ? injil_help_unlist_normalise( get_option( 'unlist_posts', array() ) ) : array();
	}
	return injil_help_unlist_on() ? injil_help_unlisted_ids() : array();
}

/**
 * Whether a post is unlisted.
 *
 * @param int $post_id Post ID.
 * @return bool
 */
function injil_help_is_unlisted( $post_id ) {
	return (int) $post_id > 0 && in_array( (int) $post_id, injil_help_unlisted_ids(), true );
}

/**
 * Add a post to the list or take it off. The post meta is the truth and the list is rebuilt
 * from it, so two saves at the same moment cannot undo each other.
 *
 * @param int  $post_id Post ID.
 * @param bool $unlist  True to unlist.
 */
function injil_help_set_unlisted( $post_id, $unlist ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 ) {
		return;
	}
	injil_help_unlist_sync();
	if ( $unlist ) {
		update_post_meta( $post_id, INJIL_HELP_UNLISTED_META, '1' );
	} else {
		delete_post_meta( $post_id, INJIL_HELP_UNLISTED_META );
	}
	injil_help_unlist_rebuild();
}

/** The post types the feature covers: every public one, as in the plugin. */
function injil_help_unlist_post_types() {
	return get_post_types( array( 'public' => true ), 'names', 'and' );
}

/**
 * Whether an AJAX request came from an admin screen of this site, from someone who can edit
 * posts. Such requests see unlisted posts, as in the plugin (LearnDash and the like).
 */
function injil_help_unlist_admin_referer() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return false;
	}
	$referer = wp_get_referer();
	if ( ! $referer ) {
		return false;
	}
	$ref   = wp_parse_url( $referer );
	$admin = wp_parse_url( admin_url() );
	if ( ! is_array( $ref ) || ! is_array( $admin ) || empty( $ref['host'] ) || empty( $admin['host'] ) || empty( $ref['path'] ) || empty( $admin['path'] ) ) {
		return false;
	}
	if ( strtolower( $ref['host'] ) !== strtolower( $admin['host'] ) ) {
		return false;
	}
	return 0 === strpos( trailingslashit( $ref['path'] ), trailingslashit( $admin['path'] ) );
}

/** Whether the request is one in which listings keep unlisted posts: an admin screen or an admin AJAX call. */
function injil_help_unlist_admin_context() {
	return is_admin() && ( ! wp_doing_ajax() || injil_help_unlist_admin_referer() );
}

/**
 * The SQL fragment that leaves the unlisted IDs out.
 *
 * @param string $column Column, such as wp_posts.ID or p.ID.
 * @param int[]  $ids    The IDs.
 * @return string
 */
function injil_help_unlist_sql( $column, $ids ) {
	$ids = injil_help_unlist_normalise( $ids );
	return $ids ? ' AND ' . $column . ' NOT IN ( ' . implode( ', ', $ids ) . ' ) ' : '';
}

/**
 * posts_where: every query that is not singular and not from the admin leaves them out. That
 * covers the home page, archives, feeds, search, REST collections, Query Loop blocks and the
 * core sitemaps.
 *
 * @param string   $where SQL.
 * @param WP_Query $query Query.
 * @return string
 */
function injil_help_unlist_where( $where, $query ) {
	$ids = injil_help_unlisted_ids();
	if ( ! $ids || injil_help_unlist_admin_context() || ! empty( $query->is_singular ) ) {
		return $where;
	}
	global $wpdb;
	return $where . injil_help_unlist_sql( $wpdb->posts . '.ID', $ids );
}

/**
 * The previous and next post links.
 *
 * @param string $where SQL with the posts table aliased p.
 * @return string
 */
function injil_help_unlist_adjacent( $where ) {
	$ids = injil_help_unlisted_ids();
	return ( ! $ids || injil_help_unlist_admin_context() ) ? $where : $where . injil_help_unlist_sql( 'p.ID', $ids );
}

/** The queried post when it is unlisted, for the robots filters. */
function injil_help_unlist_current() {
	$id = is_singular() ? get_queried_object_id() : 0;
	return $id && injil_help_is_unlisted( $id );
}

/**
 * The "Read next" row is built into the page when it is written, so links in it to an unlisted
 * page are taken out as the block renders.
 *
 * @param string $html  Rendered block.
 * @param array  $block Block.
 * @return string
 */
function injil_help_unlist_related_row( $html, $block ) {
	if ( false === strpos( (string) ( $block['attrs']['className'] ?? '' ), 'ih-next' ) ) {
		return $html;
	}
	$paths = array();
	foreach ( injil_help_unlisted_ids() as $id ) {
		$link = get_permalink( $id );
		if ( $link ) {
			$paths[] = trailingslashit( (string) wp_parse_url( $link, PHP_URL_PATH ) );
		}
	}
	return injil_help_unlist_strip_links( $html, $paths );
}

/**
 * Remove the links to the given paths from a row of links separated by " · ".
 *
 * @param string   $html  Markup.
 * @param string[] $paths Paths with a trailing slash.
 * @return string
 */
function injil_help_unlist_strip_links( $html, $paths ) {
	if ( ! $paths ) {
		return $html;
	}
	$html = preg_replace_callback(
		'#<a href="([^"]+)"[^>]*>.*?</a>#s',
		function ( $m ) use ( $paths ) {
			$path = trailingslashit( (string) wp_parse_url( html_entity_decode( $m[1] ), PHP_URL_PATH ) );
			return in_array( $path, $paths, true ) ? '' : $m[0];
		},
		$html
	);
	// Tidy the separators the removed links leave behind.
	$html = preg_replace( '#(?:\s*·\s*){2,}#u', ' · ', $html );
	return preg_replace( array( '#<p>\s*·\s*#u', '#\s*·\s*</p>#u' ), array( '<p>', '</p>' ), $html );
}

if ( injil_help_unlist_on() ) {
	add_action(
		'init',
		function () {
			add_filter( 'posts_where', 'injil_help_unlist_where', 20, 2 );
			add_filter( 'get_next_post_where', 'injil_help_unlist_adjacent', 20 );
			add_filter( 'get_previous_post_where', 'injil_help_unlist_adjacent', 20 );
		}
	);

	// noindex on the unlisted post itself; Yoast's own robots array is emptied so it cannot undo it.
	add_filter(
		'wp_robots',
		function ( $robots ) {
			if ( ! injil_help_unlist_current() ) {
				return $robots;
			}
			add_filter( 'wpseo_robots_array', '__return_empty_array' );
			return wp_robots_no_robots( $robots );
		}
	);
	add_filter(
		'rank_math/frontend/robots',
		function ( $robots ) {
			if ( injil_help_unlist_current() ) {
				$robots['index'] = 'noindex';
			}
			return $robots;
		}
	);

	add_filter(
		'wp_list_pages_excludes',
		function ( $exclude ) {
			return array_merge( (array) $exclude, injil_help_unlisted_ids() );
		}
	);

	// The page-list block and anything else built on get_pages(), which runs its own SQL.
	add_filter(
		'get_pages',
		function ( $pages ) {
			if ( injil_help_unlist_admin_context() || ! injil_help_unlisted_ids() ) {
				return $pages;
			}
			return array_values(
				array_filter(
					(array) $pages,
					function ( $p ) {
						return ! ( $p instanceof WP_Post && injil_help_is_unlisted( $p->ID ) );
					}
				)
			);
		}
	);

	// Core sitemaps, stated as well as filtered by posts_where.
	add_filter(
		'wp_sitemaps_posts_query_args',
		function ( $args ) {
			$args['post__not_in'] = array_merge( (array) ( $args['post__not_in'] ?? array() ), injil_help_unlisted_ids() );
			return $args;
		}
	);

	// Rank Math's sitemap runs its own SQL, so each entry is checked, and the exclusion list is given too.
	add_filter(
		'rank_math/sitemap/entry',
		function ( $url, $type, $object ) {
			return ( 'post' === $type && is_object( $object ) && isset( $object->ID ) && injil_help_is_unlisted( $object->ID ) ) ? false : $url;
		},
		10,
		3
	);
	add_filter(
		'rank_math/sitemap/posts_to_exclude',
		function ( $ids ) {
			return array_merge( (array) $ids, injil_help_unlisted_ids() );
		}
	);

	add_filter( 'render_block_core/group', 'injil_help_unlist_related_row', 10, 2 );

	if ( is_admin() ) {
		require_once __DIR__ . '/unlist-admin.php';
	}
}

// The last line: functions.php checks for it, so a file an upload cut short is caught.
$GLOBALS['injil_help_loaded'][ basename( __FILE__, '.php' ) ] = true;
