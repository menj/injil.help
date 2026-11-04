<?php
/**
 * Search addresses (3.21.0): the behaviour of the Pretty Search Permalinks plugin, built in.
 *
 * /?s=term answers with a 301 to /search/term/, and /?s=term&paged=2 to /search/term/page/2/.
 * The base ("search") is set under Settings › Permalinks, in the Optional section, as in the
 * plugin, and kept in the option injil_help_search_base. An empty search is never redirected.
 * WordPress already has the rewrite rules for the base; the theme sets the base on init and
 * flushes the rules only when the theme version changes (INJIL_HELP_RULES) or the base is saved
 * on the Permalinks screen, which flushes them itself. While the plugin is active this file
 * does nothing.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

/** Whether the theme handles search addresses: off while the plugin is active. */
function injil_help_search_permalinks_on() {
	return ! function_exists( 'wpseosearch_base' ) && (bool) apply_filters( 'injil_help_search_permalinks_enabled', true );
}

/**
 * The first path segment of every rewrite base set on this site: the category and tag bases
 * from Settings › Permalinks (their defaults are in the fixed list), and the rewrite slug of
 * each registered post type and taxonomy.
 *
 * @return string[]
 */
function injil_help_search_live_slugs() {
	$slugs = array();
	foreach ( array( 'category_base', 'tag_base' ) as $opt ) {
		$value = function_exists( 'get_option' ) ? get_option( $opt ) : '';
		if ( is_string( $value ) ) {
			$slugs[] = $value;
		}
	}
	$objects = array();
	if ( function_exists( 'get_post_types' ) ) {
		$objects = array_merge( $objects, array_values( (array) get_post_types( array(), 'objects' ) ) );
	}
	if ( function_exists( 'get_taxonomies' ) ) {
		$objects = array_merge( $objects, array_values( (array) get_taxonomies( array(), 'objects' ) ) );
	}
	foreach ( $objects as $obj ) {
		if ( is_object( $obj ) && is_array( $obj->rewrite ?? null ) && isset( $obj->rewrite['slug'] ) && is_string( $obj->rewrite['slug'] ) ) {
			$slugs[] = $obj->rewrite['slug'];
		}
	}
	$out = array();
	foreach ( $slugs as $slug ) {
		$first = strtolower( (string) strtok( trim( $slug, '/' ), '/' ) );
		if ( '' !== $first ) {
			$out[] = $first;
		}
	}
	return array_values( array_unique( $out ) );
}

/**
 * Slugs the search base may not take: the paths WordPress keeps for itself and the Journal's
 * base. Filter injil_help_search_reserved_bases.
 *
 * @return string[]
 */
function injil_help_search_reserved_bases() {
	$journal = defined( 'INJIL_HELP_JOURNAL_BASE' ) ? array( INJIL_HELP_JOURNAL_BASE ) : array();
	return (array) apply_filters(
		'injil_help_search_reserved_bases',
		array_merge(
			injil_help_search_live_slugs(), array( 'wp-admin', 'wp-content', 'wp-includes', 'wp-json', 'feed', 'page', 'comments', 'category', 'tag', 'author', 'embed', 'trackback', 'attachment', 'type' ), $journal )
	);
}

/**
 * A base as it is stored: lower-case letters, digits and hyphens. An empty or reserved base,
 * or one a page already uses as its address, gives ''; the search rule would take that page's
 * children (a base of gospels would turn /gospels/mark/ into a search).
 *
 * @param mixed $raw Submitted or stored value.
 * @return string
 */
function injil_help_search_clean_base( $raw ) {
	if ( ! is_string( $raw ) ) {
		return '';
	}
	$base = str_replace( '_', '', sanitize_key( $raw ) );
	if ( '' === $base || in_array( $base, injil_help_search_reserved_bases(), true ) ) {
		return '';
	}
	return ( 'search' !== $base && function_exists( 'get_page_by_path' ) && get_page_by_path( $base ) ) ? '' : $base;
}

/** The search base; "search" when the stored value is empty or reserved. */
function injil_help_search_base() {
	$base = injil_help_search_clean_base( get_option( 'injil_help_search_base', 'search' ) );
	return '' === $base ? 'search' : $base;
}

/**
 * The pretty address of a search, or '' when there is nothing to redirect: an empty term.
 *
 * @param string $home  Home URL with a trailing slash.
 * @param string $base  Search base.
 * @param string $term  Search term, unslashed.
 * @param int    $paged Page number.
 * @param array  $extra Other query arguments to carry over.
 * @param bool   $slash Whether the permalink structure ends in a slash.
 * @return string
 */
function injil_help_search_pretty_url( $home, $base, $term, $paged = 1, $extra = array(), $slash = true ) {
	$term = trim( (string) $term );
	if ( '' === $term ) {
		return '';
	}
	// A slash stays a slash, as in get_search_link(): many servers refuse %2F in a path, and the rule search/(.+)/ takes it whole.
	$path = $base . '/' . str_replace( '%2F', '/', rawurlencode( $term ) );
	if ( $paged > 1 ) {
		$path .= '/page/' . (int) $paged;
	}
	$url = rtrim( $home, '/' ) . '/' . $path . ( $slash ? '/' : '' );
	return $extra ? $url . '?' . http_build_query( $extra, '', '&', PHP_QUERY_RFC3986 ) : $url;
}

/**
 * The other query arguments of a search, sanitised, arrays such as post_type[] kept whole.
 *
 * @param array $args Unslashed arguments.
 * @return array
 */
function injil_help_search_clean_args( $args ) {
	$out = array();
	foreach ( (array) $args as $key => $value ) {
		if ( is_array( $value ) ) {
			$out[ $key ] = injil_help_search_clean_args( $value );
		} elseif ( is_scalar( $value ) ) {
			$out[ $key ] = sanitize_text_field( (string) $value );
		}
	}
	return $out;
}

if ( injil_help_search_permalinks_on() ) {
	add_action(
		'init',
		function () {
			global $wp_rewrite;
			$wp_rewrite->search_base = injil_help_search_base();
		},
		1
	);

	// The saved rules carry the base they were built with. When the base in force differs (a
	// stored base newly refused, or one changed outside the Permalinks screen), flush once.
	add_action(
		'init',
		function () {
			$base = injil_help_search_base();
			if ( get_option( 'injil_help_search_rules_base' ) !== $base ) {
				flush_rewrite_rules( false );
				update_option( 'injil_help_search_rules_base', $base, false );
			}
		},
		99
	);

	// The field under Settings › Permalinks › Optional; saved with the screen's own nonce.
	add_action(
		'load-options-permalink.php',
		function () {
			if ( isset( $_POST['injil_help_search_base'] ) && current_user_can( 'manage_options' ) ) {
				check_admin_referer( 'update-permalink' );
				// A posted array (injil_help_search_base[]=x) would make sanitize_key() throw on PHP 8.
				$posted = wp_unslash( $_POST['injil_help_search_base'] );
				$raw    = is_string( $posted ) ? sanitize_key( $posted ) : '';
				$base = injil_help_search_clean_base( $raw );
				if ( '' !== $raw && '' === $base ) {
					add_settings_error(
						'injil_help_search_base',
						'injil_help_search_base_reserved',
						/* translators: %s: the refused base */
						sprintf( __( 'The search base "%s" is taken by WordPress or by a page of the site, so "search" is used instead.', 'injil-help' ), str_replace( '_', '', $raw ) ),
						'warning'
					);
					// The Permalinks screen redirects after saving; settings_errors() reads this transient there.
					set_transient( 'settings_errors', get_settings_errors(), 30 );
				}
				update_option( 'injil_help_search_base', '' === $base ? 'search' : $base );
				global $wp_rewrite;
				$wp_rewrite->search_base = injil_help_search_base();
			}
			add_settings_field(
				'injil_help_search_base',
				__( 'Search base', 'injil-help' ),
				function () {
					printf(
						'<input type="text" name="injil_help_search_base" id="injil_help_search_base" value="%1$s" class="regular-text code" aria-describedby="injil-help-search-base-help"><p class="description" id="injil-help-search-base-help">%2$s</p>',
						esc_attr( injil_help_search_base() ),
						esc_html__( 'Search results open at /search/term/ by default. Lower-case letters, digits and hyphens; anything else is removed. Empty, a path WordPress keeps (feed, page, tag and the like) or the address of a page keeps "search".', 'injil-help' )
					);
				},
				'permalink',
				'optional',
				array( 'label_for' => 'injil_help_search_base' )
			);
		}
	);

	// /?s=term to /search/term/ with a 301, the page number and any other query arguments kept.
	add_action(
		'template_redirect',
		function () {
			global $wp_rewrite;
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- a public search.
			if ( ! isset( $_GET['s'] ) || ! is_search() || is_admin() || is_feed() || ! $wp_rewrite->using_permalinks() ) {
				return;
			}
			$extra = wp_unslash( $_GET ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			unset( $extra['s'], $extra['paged'] );
			$url = injil_help_search_pretty_url(
				home_url( '/' ),
				injil_help_search_base(),
				(string) get_query_var( 's' ),
				max( 1, (int) get_query_var( 'paged' ) ),
				injil_help_search_clean_args( $extra ),
				'/' === substr( (string) get_option( 'permalink_structure' ), -1 )
			);
			if ( '' !== $url ) {
				wp_safe_redirect( $url, 301 );
				exit;
			}
		}
	);
}
