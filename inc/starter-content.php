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
 * "## " heading, "- " list item, "> " quote, "@consensus|@minority|@islamic"
 * claim label, anything else a paragraph. Blank lines are ignored.
 */
function injil_help_markup_to_blocks( $lines ) {
	$labels = array(
		'consensus' => __( 'Scholarly consensus', 'injil-help' ),
		'minority'  => __( 'Minority view', 'injil-help' ),
		'islamic'   => __( 'Islamic position', 'injil-help' ),
	);
	$out    = '';
	$list   = array();
	$flush  = function () use ( &$out, &$list ) {
		if ( ! $list ) {
			return;
		}
		$out .= "<!-- wp:list -->\n<ul class=\"wp-block-list\">";
		foreach ( $list as $item ) {
			$out .= "<!-- wp:list-item -->\n<li>" . $item . "</li>\n<!-- /wp:list-item -->";
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
			$list[] = wp_kses_post( substr( $line, 2 ) );
			continue;
		}
		$flush();
		if ( 0 === strpos( $line, '## ' ) ) {
			$out .= "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">" . wp_kses_post( substr( $line, 3 ) ) . "</h2>\n<!-- /wp:heading -->\n";
		} elseif ( 0 === strpos( $line, '> ' ) ) {
			$out .= "<!-- wp:quote -->\n<blockquote class=\"wp-block-quote\"><!-- wp:paragraph -->\n<p>" . wp_kses_post( substr( $line, 2 ) ) . "</p>\n<!-- /wp:paragraph --></blockquote>\n<!-- /wp:quote -->\n";
		} elseif ( preg_match( '/^@(consensus|minority|islamic)$/', $line, $m ) ) {
			$out .= '<!-- wp:paragraph {"className":"is-style-label-' . $m[1] . "\"} -->\n<p class=\"is-style-label-" . $m[1] . '">' . esc_html( $labels[ $m[1] ] ) . "</p>\n<!-- /wp:paragraph -->\n";
		} elseif ( '[page-list]' === $line ) {
			$out .= "<!-- wp:page-list /-->\n";
		} else {
			$out .= "<!-- wp:paragraph -->\n<p>" . wp_kses_post( $line ) . "</p>\n<!-- /wp:paragraph -->\n";
		}
	}
	$flush();
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

/**
 * Create every missing page. Returns the number created.
 */
function injil_help_populate() {
	$created = 0;

	foreach ( injil_help_starter_pages() as $order => $def ) {
		if ( injil_help_find_page( $def['path'] ) ) {
			continue;
		}
		$content = injil_help_page_content( $def );
		if ( '' === $content ) {
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

/** Run from the Theme Options > Content tab. */
add_action(
	'admin_post_injil_help_populate',
	function () {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'injil-help' ), 403 );
		}
		check_admin_referer( 'injil_help_populate' );
		$n = injil_help_populate();
		wp_safe_redirect( add_query_arg( array( 'page' => 'injil-help-options', 'ih_created' => $n ), admin_url( 'themes.php' ) ) . '#content' );
		exit;
	}
);
