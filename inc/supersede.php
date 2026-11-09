<?php
/**
 * Superseded pages and Journal posts, renamed Journal posts, retired items, the sync log and the dry run.
 *
 * An address is a path without slashes at the ends: a page's path ("resources/glossary"), or a Journal post's
 * "journal/<slug>". Two data files ship empty:
 *
 *   - inc/data/supersedes.json: a map of old address => new address.
 *   - a "former" list of earlier slugs on a Journal entry in inc/data/posts.json. (Pages already have
 *     "former" and inc/data/slug-history.json.)
 *
 * While the new item is published, the old one:
 *
 *   - answers with a 301 to the new one (template_redirect, priority 1);
 *   - is left out of lists, search, feeds and sitemaps (it joins the unlisted posts, so noindex applies);
 *   - is moved to the Trash, never deleted, when nobody has edited it.
 *
 * An item the theme wrote that its data no longer holds is a dropped item: unedited, it goes to the Trash;
 * edited, it is kept. Only items that carry the theme's content hash are ever considered, so nothing the owner
 * created is touched, and a post without the hash counts as edited.
 *
 * A Journal post whose entry lists a former slug is renamed in place, and its old address redirects.
 *
 * Guards. Removals stop when the number of items the data holds fell by more than 10 percent since the last
 * good run, when the page or Journal data cannot be read, or when INJIL_HELP_DISABLE_SUPERSEDE is true in
 * wp-config.php (which also ends the redirect, the hiding and the renames).
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

const INJIL_HELP_LOG_OPTION       = 'injil_help_sync_log';
const INJIL_HELP_REDIRECT_OPTION  = 'injil_help_retired_redirects';
const INJIL_HELP_COUNT_OPTION     = 'injil_help_item_count';

/** The kill switch. */
function injil_help_supersede_disabled() {
	return defined( 'INJIL_HELP_DISABLE_SUPERSEDE' ) && INJIL_HELP_DISABLE_SUPERSEDE;
}

/**
 * Clean a raw supersedes map: lower case addresses without slashes at the ends, no empty address, none that
 * replaces itself, and no cycle (A to B, B to A): none of a cycle's addresses is superseded. Pure.
 *
 * @param mixed $raw Decoded JSON.
 * @return array<string,string>
 */
function injil_help_clean_supersedes( $raw ) {
	$map = array();
	foreach ( is_array( $raw ) ? $raw : array() as $old => $new ) {
		$old = strtolower( trim( (string) $old, '/ ' ) );
		$new = strtolower( trim( (string) $new, '/ ' ) );
		if ( '' !== $old && '' !== $new && $old !== $new ) {
			$map[ $old ] = $new;
		}
	}
	foreach ( array_keys( $map ) as $start ) {
		$at   = $start;
		$seen = array();
		while ( isset( $map[ $at ] ) && ! in_array( $at, $seen, true ) ) {
			$seen[] = $at;
			$at     = $map[ $at ];
		}
		if ( $at === $start ) {
			foreach ( $seen as $address ) {
				unset( $map[ $address ] );
			}
		}
	}
	return $map;
}

/** The supersedes map from inc/data/supersedes.json. */
function injil_help_supersedes_map() {
	static $map = null;
	if ( null === $map ) {
		$map = injil_help_clean_supersedes( injil_help_data_file( 'supersedes' ) );
	}
	return $map;
}

/**
 * Whether removals may run. Pure.
 *
 * @param bool $disabled    The kill switch.
 * @param bool $readable    The page and Journal data were read.
 * @param int  $count_now   Items the data holds now.
 * @param int  $count_prior Items at the last good run, 0 when none.
 * @return string '' when allowed, otherwise the reason.
 */
function injil_help_removal_guard( $disabled, $readable, $count_now, $count_prior ) {
	if ( $disabled ) {
		return __( 'the kill switch INJIL_HELP_DISABLE_SUPERSEDE is on', 'injil-help' );
	}
	if ( ! $readable ) {
		return __( 'the page or Journal data could not be read', 'injil-help' );
	}
	if ( $count_prior > 0 && $count_now < $count_prior * 0.9 ) {
		return __( 'the data lists more than 10 percent fewer items than at the last good run', 'injil-help' );
	}
	return '';
}

/** The number of items the data holds: starter pages and Journal entries. */
function injil_help_item_count() {
	return count( injil_help_starter_pages() ) + count( injil_help_journal_entries() );
}

/** The reason removals are stopped now, or ''. */
function injil_help_removal_blocked() {
	return injil_help_removal_guard( injil_help_supersede_disabled(), (bool) injil_help_starter_pages() && (bool) injil_help_journal_entries(), injil_help_item_count(), (int) get_option( INJIL_HELP_COUNT_OPTION, 0 ) );
}

/** The address of a post: a page's path, or "journal/<slug>" for a Journal post. */
function injil_help_post_address( $post ) {
	if ( 'page' === $post->post_type ) {
		return get_page_uri( $post );
	}
	return INJIL_HELP_JOURNAL_BASE . '/' . $post->post_name;
}

/**
 * The published item at an address, or null.
 *
 * @param string $address Page path or "journal/<slug>".
 * @return WP_Post|null
 */
function injil_help_post_at( $address, $any_status = false ) {
	$prefix = INJIL_HELP_JOURNAL_BASE . '/';
	if ( 0 === strpos( $address, $prefix ) ) {
		$found = get_posts(
			array(
				'name'             => substr( $address, strlen( $prefix ) ),
				'post_type'        => 'post',
				'post_status'      => $any_status ? array( 'publish', 'future', 'draft', 'pending', 'private' ) : 'publish',
				'numberposts'      => 1,
				'suppress_filters' => true,
			)
		);
		return $found ? $found[0] : null;
	}
	$page = get_page_by_path( $address, OBJECT, 'page' );
	return $page && ( $any_status ? 'trash' !== $page->post_status : 'publish' === $page->post_status ) ? $page : null;
}

/**
 * Whether a post still holds exactly what the theme wrote. Fails closed: with no content hash, or with a
 * different content, title or excerpt, it counts as the owner's.
 *
 * @param WP_Post $post Post.
 * @return bool
 */
function injil_help_is_unedited( $post ) {
	return '' !== (string) get_post_meta( $post->ID, INJIL_HELP_HASH_META, true ) && ! injil_help_page_is_edited( $post );
}

/**
 * Old post ID => new post ID, for every pair where both are published and the old one is unedited.
 *
 * @param bool $reset Read the pairs again.
 * @return array<int,int>
 */
function injil_help_superseded_pairs( $reset = false ) {
	static $cache = null;
	if ( $reset ) {
		$cache = null;
	}
	if ( null !== $cache ) {
		return $cache;
	}
	$cache = array();
	if ( injil_help_supersede_disabled() ) {
		return $cache;
	}
	foreach ( injil_help_supersedes_map() as $old => $new ) {
		$old_p = injil_help_post_at( $old );
		$new_p = injil_help_post_at( $new );
		if ( $old_p && $new_p && $old_p->ID !== $new_p->ID && injil_help_is_unedited( $old_p ) ) {
			$cache[ (int) $old_p->ID ] = (int) $new_p->ID;
		}
	}
	return $cache;
}

/** IDs of the superseded posts. */
function injil_help_superseded_ids() {
	return array_keys( injil_help_superseded_pairs() );
}

/**
 * Remember that an old address now belongs to a post, so it answers with a 301 even when the old post is in
 * the Trash. Earlier entries that pointed at the old post now point at its replacement, so a chain ends at
 * the item that is live.
 *
 * @param string $address Old address.
 * @param int    $new_id  Post that replaces it.
 * @param int    $old_id  The old post's ID, when it has one.
 */
function injil_help_note_redirect( $address, $new_id, $old_id = 0 ) {
	$address = strtolower( trim( (string) $address, '/' ) );
	if ( '' === $address || (int) $new_id === (int) $old_id ) {
		return;
	}
	$all = (array) get_option( INJIL_HELP_REDIRECT_OPTION, array() );
	if ( $old_id ) {
		foreach ( $all as $path => $target ) {
			if ( (int) $target === (int) $old_id ) {
				$all[ $path ] = (int) $new_id;
			}
		}
	}
	$all[ $address ] = (int) $new_id;
	update_option( INJIL_HELP_REDIRECT_OPTION, array_slice( $all, -500, null, true ), false );
}

/** The 301 from a superseded item to its replacement. */
function injil_help_supersede_redirect() {
	if ( injil_help_supersede_disabled() || is_admin() || is_preview() || is_feed() || ! is_singular() ) {
		return;
	}
	$pairs = injil_help_superseded_pairs();
	$id    = (int) get_queried_object_id();
	if ( isset( $pairs[ $id ] ) ) {
		wp_safe_redirect( get_permalink( $pairs[ $id ] ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'injil_help_supersede_redirect', 1 );

/**
 * An address that is a 404 now (the item is in the Trash, or a Journal post took a new slug) still reaches the
 * item that took its place.
 */
function injil_help_supersede_redirect_gone() {
	if ( injil_help_supersede_disabled() || ! is_404() ) {
		return;
	}
	$path = isset( $GLOBALS['wp']->request ) ? strtolower( trim( (string) $GLOBALS['wp']->request, '/' ) ) : '';
	if ( '' === $path ) {
		return;
	}
	$all = (array) get_option( INJIL_HELP_REDIRECT_OPTION, array() );
	if ( ! empty( $all[ $path ] ) && 'publish' === get_post_status( (int) $all[ $path ] ) ) {
		wp_safe_redirect( get_permalink( (int) $all[ $path ] ), 301 );
		exit;
	}
	foreach ( injil_help_journal_entries() as $entry ) {
		foreach ( (array) ( $entry['former'] ?? array() ) as $old ) {
			if ( INJIL_HELP_JOURNAL_BASE . '/' . strtolower( (string) $old ) === $path ) {
				$post = injil_help_post_at( INJIL_HELP_JOURNAL_BASE . '/' . $entry['slug'] );
				if ( $post ) {
					wp_safe_redirect( get_permalink( $post ), 301 );
					exit;
				}
			}
		}
	}
}
add_action( 'template_redirect', 'injil_help_supersede_redirect_gone', 2 );

/**
 * Plugin mode: with the Unlist Posts plugin active, or the theme's unlisting off, the hiding of superseded
 * items is done here, in listing queries (feeds and search included), the list of pages, the adjacent links
 * and the robots tag.
 *
 * @param WP_Query $query Query.
 */
function injil_help_supersede_pre_get_posts( $query ) {
	if ( injil_help_unlist_on() || injil_help_supersede_disabled() || $query->is_singular() || ( is_admin() && ! wp_doing_ajax() ) ) {
		return;
	}
	$ids = injil_help_superseded_ids();
	if ( $ids ) {
		$query->set( 'post__not_in', array_values( array_unique( array_merge( array_filter( array_map( 'intval', (array) $query->get( 'post__not_in' ) ) ), $ids ) ) ) );
	}
}
add_action( 'pre_get_posts', 'injil_help_supersede_pre_get_posts', 20 );

add_filter(
	'wp_list_pages_excludes',
	static function ( $exclude ) {
		if ( injil_help_unlist_on() || injil_help_supersede_disabled() || is_admin() ) {
			return $exclude;
		}
		return array_values( array_unique( array_merge( array_map( 'intval', (array) $exclude ), injil_help_superseded_ids() ) ) );
	}
);

/**
 * Add the NOT IN clause for superseded posts to an adjacent-post query (plugin mode only).
 *
 * @param string $where Where clause.
 * @return string
 */
function injil_help_supersede_adjacent_where( $where ) {
	$ids = ( injil_help_unlist_on() || injil_help_supersede_disabled() ) ? array() : injil_help_superseded_ids();
	return $ids ? $where . ' AND p.ID NOT IN (' . implode( ',', array_map( 'intval', $ids ) ) . ')' : $where;
}
add_filter( 'get_next_post_where', 'injil_help_supersede_adjacent_where', 20 );
add_filter( 'get_previous_post_where', 'injil_help_supersede_adjacent_where', 20 );

/**
 * Noindex on the page of a superseded item that can still be reached, in plugin mode.
 *
 * @param array $robots Robots directives.
 * @return array
 */
function injil_help_supersede_robots( $robots ) {
	if ( ! injil_help_unlist_on() && is_singular() && in_array( (int) get_queried_object_id(), injil_help_superseded_ids(), true ) ) {
		return wp_robots_no_robots( $robots );
	}
	return $robots;
}
add_filter( 'wp_robots', 'injil_help_supersede_robots', 20 );

/**
 * Append a line to the sync log (the last 100 lines).
 *
 * @param string $item   Item.
 * @param string $action What happened.
 */
function injil_help_log( $item, $action ) {
	$log   = (array) get_option( INJIL_HELP_LOG_OPTION, array() );
	$log[] = array( 't' => time(), 'item' => (string) $item, 'action' => (string) $action );
	update_option( INJIL_HELP_LOG_OPTION, array_slice( $log, -100 ), false );
}

/**
 * Rename a Journal post in place when its entry lists a former slug: the post keeps its ID, its text and its
 * edits, and the old address redirects. An edited post keeps its address.
 *
 * @param array $entry Journal entry.
 * @return WP_Post|null The renamed post, or null when nothing was renamed.
 */
function injil_help_journal_rename( $entry ) {
	if ( injil_help_supersede_disabled() || injil_help_journal_find( $entry['slug'] ) ) {
		return null;
	}
	foreach ( (array) ( $entry['former'] ?? array() ) as $old ) {
		$post = injil_help_journal_find( sanitize_title( (string) $old ) );
		if ( ! $post || 'trash' === $post->post_status || ! injil_help_is_unedited( $post ) ) {
			continue;
		}
		$id = wp_update_post( wp_slash( array( 'ID' => $post->ID, 'post_name' => $entry['slug'] ) ), true );
		if ( is_wp_error( $id ) || ! $id ) {
			return null;
		}
		update_post_meta( $post->ID, INJIL_HELP_POST_META, $entry['slug'] );
		injil_help_note_redirect( INJIL_HELP_JOURNAL_BASE . '/' . sanitize_title( (string) $old ), $post->ID );
		injil_help_log( $entry['title'], __( 'renamed in place; the old address redirects', 'injil-help' ) );
		return get_post( $post->ID );
	}
	return null;
}

/**
 * Items the theme wrote that its data no longer holds: starter pages and Journal posts.
 *
 * @return array[] Each: post (WP_Post), address.
 */
function injil_help_dropped_items() {
	$paths = array();
	foreach ( injil_help_starter_pages() as $def ) {
		$paths[ $def['path'] ] = true;
		foreach ( (array) ( $def['former'] ?? array() ) as $old ) {
			$paths[ trim( (string) $old, '/' ) ] = true;
		}
	}
	foreach ( array_keys( injil_help_former_map() ) as $old ) {
		$paths[ $old ] = true;
	}
	$slugs = array();
	foreach ( injil_help_journal_entries() as $entry ) {
		$slugs[ $entry['slug'] ] = true;
		foreach ( (array) ( $entry['former'] ?? array() ) as $old ) {
			$slugs[ sanitize_title( (string) $old ) ] = true;
		}
	}
	$out   = array();
	$posts = get_posts(
		array(
			'post_type'        => array( 'page', 'post' ),
			'post_status'      => array( 'publish', 'future', 'draft', 'pending', 'private' ),
			'numberposts'      => -1,
			'meta_key'         => INJIL_HELP_HASH_META, // phpcs:ignore WordPress.DB.SlowDBQuery
			'suppress_filters' => true,
		)
	);
	foreach ( $posts as $post ) {
		if ( 'page' === $post->post_type ) {
			// The path the theme gave the page, never its present address: a page the owner moved is not a dropped one,
			// and a page written before the path was recorded is left alone until a run records it.
			$wrote = (string) get_post_meta( $post->ID, INJIL_HELP_PATH_META, true );
			if ( '' !== $wrote && empty( $paths[ $wrote ] ) ) {
				$out[] = array( 'post' => $post, 'address' => get_page_uri( $post ) );
			}
		} else {
			$slug = (string) get_post_meta( $post->ID, INJIL_HELP_POST_META, true );
			if ( '' !== $slug && empty( $slugs[ $slug ] ) ) {
				$out[] = array( 'post' => $post, 'address' => INJIL_HELP_JOURNAL_BASE . '/' . $post->post_name );
			}
		}
	}
	return $out;
}

/**
 * Run the retirement: superseded items and dropped items go to the Trash unless edited, unless a guard stops it.
 *
 * @return array{trashed:string[],kept:string[],failed:string[],blocked:string}
 */
function injil_help_retire_run() {
	$out    = array( 'trashed' => array(), 'kept' => array(), 'failed' => array(), 'blocked' => '' );
	$reason = injil_help_removal_blocked();
	if ( '' !== $reason ) {
		$out['blocked'] = $reason;
		update_option( 'injil_help_supersede_blocked', $reason, false );
		return $out;
	}
	delete_option( 'injil_help_supersede_blocked' );
	$pairs = injil_help_superseded_pairs( true );
	foreach ( $pairs as $old_id => $new_id ) {
		$post = get_post( $old_id );
		if ( ! $post || 'publish' !== $post->post_status ) {
			continue;
		}
		$address = injil_help_post_address( $post );
		$target  = $new_id;
		$seen    = array( $old_id );
		while ( isset( $pairs[ $target ] ) && ! in_array( $target, $seen, true ) ) {
			$seen[] = $target;
			$target = $pairs[ $target ];
		}
		if ( wp_trash_post( $old_id ) ) {
			injil_help_note_redirect( $address, $target, $old_id );
			/* translators: 1: item title, 2: address of the replacing item */
			$out['trashed'][] = sprintf( __( '%1$s (replaced by %2$s)', 'injil-help' ), $post->post_title, injil_help_post_address( get_post( $new_id ) ) );
			/* translators: %s: address of the replacing item */
			injil_help_log( $post->post_title, sprintf( __( 'moved to the Trash, replaced by %s', 'injil-help' ), injil_help_post_address( get_post( $new_id ) ) ) );
		} else {
			/* translators: %s: item title */
			$out['failed'][] = sprintf( __( '%s: could not move to the Trash.', 'injil-help' ), $post->post_title );
		}
	}
	// An old item already in the Trash before its pair existed still needs its redirect.
	foreach ( injil_help_supersedes_map() as $old => $new ) {
		$new_p = injil_help_post_at( $new );
		$old_p = injil_help_post_at( $old, true );
		if ( $new_p && ! $old_p ) {
			$trashed = 0 === strpos( $old, INJIL_HELP_JOURNAL_BASE . '/' ) ? injil_help_journal_find( substr( $old, strlen( INJIL_HELP_JOURNAL_BASE ) + 1 ) ) : null;
			if ( $trashed && 'trash' === $trashed->post_status ) {
				injil_help_note_redirect( $old, $new_p->ID, $trashed->ID );
			}
		}
	}
	injil_help_superseded_pairs( true );
	// Dropped items need a baseline from an earlier good run: the first run only records it.
	$dropped = (int) get_option( INJIL_HELP_COUNT_OPTION, 0 ) > 0 ? injil_help_dropped_items() : array();
	foreach ( $dropped as $item ) {
		$post = $item['post'];
		if ( isset( $pairs[ $post->ID ] ) ) {
			continue;
		}
		if ( ! injil_help_is_unedited( $post ) ) {
			/* translators: %s: item title */
			$out['kept'][] = sprintf( __( '%s (no longer in the theme)', 'injil-help' ), $post->post_title );
			continue;
		}
		if ( wp_trash_post( $post->ID ) ) {
			/* translators: %s: item title */
			$out['trashed'][] = sprintf( __( '%s (removed from the theme)', 'injil-help' ), $post->post_title );
			injil_help_log( $post->post_title, __( 'moved to the Trash, removed from the theme', 'injil-help' ) );
		} else {
			/* translators: %s: item title */
			$out['failed'][] = sprintf( __( '%s: could not move to the Trash.', 'injil-help' ), $post->post_title );
		}
	}
	if ( ! $out['failed'] ) {
		// The baseline moves only after a run that was free to remove things, so a held drop is still seen later.
		update_option( INJIL_HELP_COUNT_OPTION, injil_help_item_count(), false );
	}
	if ( $out['trashed'] || $out['kept'] || $out['failed'] ) {
		set_transient( 'injil_help_retire_report', $out, DAY_IN_SECONDS );
	}
	return $out;
}

/**
 * What the next update would do with one item. Pure: the facts in, the action out.
 *
 * @param array $f Facts: defined (the data still holds it), exists, trashed, edited, renamable, retired
 *                 (superseded and the replacement is live), blocked (a guard stops removals).
 * @return string create, current, kept, rename, supersede, supersede-kept, dropped, dropped-kept or held.
 */
function injil_help_classify( $f ) {
	if ( empty( $f['defined'] ) ) {
		if ( empty( $f['exists'] ) || ! empty( $f['trashed'] ) ) {
			return 'current';
		}
		return ! empty( $f['edited'] ) ? 'dropped-kept' : ( ! empty( $f['blocked'] ) ? 'held' : 'dropped' );
	}
	if ( ! empty( $f['retired'] ) && ! empty( $f['exists'] ) && empty( $f['trashed'] ) ) {
		return ! empty( $f['edited'] ) ? 'supersede-kept' : ( ! empty( $f['blocked'] ) ? 'held' : 'supersede' );
	}
	if ( empty( $f['exists'] ) || ! empty( $f['trashed'] ) ) {
		return ! empty( $f['trashed'] ) ? 'kept' : ( ! empty( $f['renamable'] ) ? 'rename' : 'create' );
	}
	return ! empty( $f['edited'] ) ? 'kept' : 'current';
}

/**
 * What the next update would do with the items worth a line: the Journal entries, the pages you edited, the
 * missing pages, the superseded items and the dropped ones. Changes nothing.
 *
 * @return array[] Each row: title, address, action, id.
 */
function injil_help_sync_preview() {
	$rows    = array();
	$blocked = '' !== injil_help_removal_blocked();
	$retired = array_keys( injil_help_supersedes_map() );
	foreach ( injil_help_starter_pages() as $def ) {
		$page = injil_help_post_at( $def['path'], true );
		$f    = array( 'defined' => true, 'exists' => (bool) $page, 'trashed' => false, 'edited' => $page && ! injil_help_is_unedited( $page ), 'retired' => in_array( $def['path'], $retired, true ) && injil_help_post_at( injil_help_supersedes_map()[ $def['path'] ] ), 'blocked' => $blocked );
		if ( ! $page ) {
			foreach ( (array) ( $def['former'] ?? array() ) as $old ) {
				if ( injil_help_find_page( trim( (string) $old, '/' ) ) ) {
					$f['renamable'] = true;
				}
			}
		}
		$action = injil_help_classify( $f );
		if ( 'current' !== $action ) {
			$rows[] = array( 'title' => $def['title'], 'address' => $def['path'], 'action' => $action, 'id' => $page ? (int) $page->ID : 0 );
		}
	}
	foreach ( injil_help_journal_entries() as $entry ) {
		$post    = injil_help_journal_find( $entry['slug'] );
		$address = INJIL_HELP_JOURNAL_BASE . '/' . $entry['slug'];
		$f       = array(
			'defined'   => true,
			'exists'    => (bool) $post,
			'trashed'   => $post && 'trash' === $post->post_status,
			'edited'    => $post && 'trash' !== $post->post_status && ! injil_help_is_unedited( $post ),
			'retired'   => in_array( $address, $retired, true ) && injil_help_post_at( injil_help_supersedes_map()[ $address ] ),
			'blocked'   => $blocked,
			'renamable' => ! $post && array_filter( array_map( static function ( $old ) {
				$former = injil_help_journal_find( sanitize_title( (string) $old ) );
				return $former && 'trash' !== $former->post_status && injil_help_is_unedited( $former );
			}, (array) ( $entry['former'] ?? array() ) ) ),
		);
		$action = injil_help_classify( $f );
		if ( 'current' === $action && $post && 'trash' !== $post->post_status && ( md5( injil_help_journal_content( $entry ) ) !== get_post_meta( $post->ID, INJIL_HELP_HASH_META, true ) || injil_help_head_hash( $entry['title'], (string) ( $entry['excerpt'] ?? '' ) ) !== get_post_meta( $post->ID, INJIL_HELP_HEAD_META, true ) ) ) {
			$action = 'refresh';
		}
		if ( 'current' !== $action ) {
			$rows[] = array( 'title' => $entry['title'], 'address' => $address, 'action' => $action, 'id' => $post ? (int) $post->ID : 0 );
		}
	}
	foreach ( injil_help_dropped_items() as $item ) {
		$edited = ! injil_help_is_unedited( $item['post'] );
		$rows[] = array( 'title' => $item['post']->post_title, 'address' => $item['address'], 'action' => $edited ? 'dropped-kept' : ( $blocked ? 'held' : 'dropped' ), 'id' => (int) $item['post']->ID );
	}
	return $rows;
}

/** The labels of the dry-run table. */
function injil_help_preview_labels() {
	return array(
		'create'         => __( 'Would be created', 'injil-help' ),
		'refresh'        => __( 'Would take the theme\'s new text, because nobody has edited it', 'injil-help' ),
		'kept'           => __( 'Stays as it is: you edited it, or you moved it to the Trash', 'injil-help' ),
		'rename'         => __( 'Would be renamed in place; the old address redirects', 'injil-help' ),
		'supersede'      => __( 'Would be redirected, hidden and moved to the Trash, because its replacement is live', 'injil-help' ),
		'supersede-kept' => __( 'Replaced, but you edited it, so it stays as it is', 'injil-help' ),
		'dropped'        => __( 'No longer in the theme; would be moved to the Trash', 'injil-help' ),
		'dropped-kept'   => __( 'No longer in the theme, but you edited it, so it stays', 'injil-help' ),
		'held'           => __( 'Would be removed, but removals are stopped now, so it stays', 'injil-help' ),
	);
}

/**
 * A line-by-line comparison of an edited page or Journal post with the text the theme ships.
 *
 * @param WP_Post $post Post.
 * @return string HTML table, or ''.
 */
function injil_help_text_diff( $post ) {
	if ( 'page' === $post->post_type ) {
		$theme = '';
		foreach ( injil_help_starter_pages() as $def ) {
			if ( get_page_uri( $post ) === $def['path'] ) {
				$theme = injil_help_page_content( $def );
				break;
			}
		}
	} else {
		$theme = '';
		foreach ( injil_help_journal_entries() as $entry ) {
			if ( get_post_meta( $post->ID, INJIL_HELP_POST_META, true ) === $entry['slug'] ) {
				$theme = injil_help_journal_content( $entry );
				break;
			}
		}
	}
	if ( '' === $theme ) {
		return '';
	}
	$mine  = preg_replace( '#(<!-- /wp:[^>]*-->)#', "$1\n", $post->post_content );
	$theme = preg_replace( '#(<!-- /wp:[^>]*-->)#', "$1\n", $theme );
	return (string) wp_text_diff( $mine, $theme, array( 'title_left' => __( 'Your copy', 'injil-help' ), 'title_right' => __( 'The theme\'s copy', 'injil-help' ) ) );
}

/** The dry-run table, the comparison of one edited item and the log, for the Content tab. */
function injil_help_render_dry_run() {
	$labels  = injil_help_preview_labels();
	$rows    = injil_help_sync_preview();
	$blocked = injil_help_removal_blocked();
	echo '<h3>' . esc_html__( 'Next update, as a dry run', 'injil-help' ) . '</h3>';
	if ( '' !== $blocked ) {
		echo '<p class="description">' . esc_html( sprintf( /* translators: %s: reason */ __( 'Removals are stopped: %s.', 'injil-help' ), $blocked ) ) . '</p>';
	}
	if ( ! $rows ) {
		echo '<p class="description">' . esc_html__( 'Nothing would change.', 'injil-help' ) . '</p>';
	} else {
		echo '<table class="widefat striped"><thead><tr><th scope="col">' . esc_html__( 'Item', 'injil-help' ) . '</th><th scope="col">' . esc_html__( 'What the update would do', 'injil-help' ) . '</th></tr></thead><tbody>';
		foreach ( $rows as $row ) {
			$cell = esc_html( $row['title'] ) . ' <code>/' . esc_html( $row['address'] ) . '/</code>';
			if ( 'kept' === $row['action'] && $row['id'] && 'trash' !== get_post_status( $row['id'] ) ) {
				$cell .= ' <a href="' . esc_url( add_query_arg( array( 'page' => 'injil-help-options', 'tab' => 'content', 'compare' => (int) $row['id'] ), admin_url( 'themes.php' ) ) ) . '">' . esc_html__( 'Compare', 'injil-help' ) . '</a>';
			}
			echo '<tr><td>' . $cell . '</td><td>' . esc_html( $labels[ $row['action'] ] ?? $row['action'] ) . '</td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput -- every part is escaped above.
		}
		echo '</tbody></table>';
	}
	$log = array_reverse( (array) get_option( INJIL_HELP_LOG_OPTION, array() ) );
	if ( $log ) {
		echo '<h3>' . esc_html__( 'What changed', 'injil-help' ) . '</h3><table class="widefat striped"><tbody>';
		foreach ( array_slice( $log, 0, 20 ) as $line ) {
			echo '<tr><td>' . esc_html( wp_date( 'Y-m-d H:i', (int) $line['t'] ) ) . '</td><td>' . esc_html( (string) $line['item'] ) . '</td><td>' . esc_html( (string) $line['action'] ) . '</td></tr>';
		}
		echo '</tbody></table>';
	}
}

/** The comparison of one edited item, shown above the page list when ?compare=<id> is in the address. */
function injil_help_render_compare() {
	$id = isset( $_GET['compare'] ) ? absint( $_GET['compare'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- a read-only view.
	if ( ! $id || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$post = get_post( $id );
	if ( ! $post || '' === (string) get_post_meta( $post->ID, INJIL_HELP_HASH_META, true ) ) {
		return; // Only items the theme wrote have a copy to compare with.
	}
	echo '<h3>' . esc_html( sprintf( /* translators: %s: title */ __( 'Your copy and the theme\'s copy: %s', 'injil-help' ), $post->post_title ) ) . '</h3>';
	$diff = injil_help_text_diff( $post );
	echo '' !== $diff ? $diff : '<p class="description">' . esc_html__( 'The two copies hold the same text.', 'injil-help' ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput -- wp_text_diff() returns escaped HTML.
	echo '<p class="description">' . esc_html__( 'To take the theme\'s copy, use "Update all pages from the theme" below. To keep yours, do nothing.', 'injil-help' ) . '</p>';
}

// The last line: functions.php checks for it, so a file an upload cut short is caught.
$GLOBALS['injil_help_loaded'][ basename( __FILE__, '.php' ) ] = true;
