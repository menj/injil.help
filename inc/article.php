<?php
/**
 * The Injil: the pieces of a Journal post (3.25.7).
 *
 * The single template already prints the title, the date, the category, the breadcrumb row (inc/seo.php),
 * the Notes, the Sources list, the Read next row, the share row and the corrections line. This file adds:
 *
 *   [injil_help_article_meta]  the reading time, and the updated date when it differs, in the meta row
 *   the_content 12             ids on the level-two headings, for the contents list
 *   the_content 16             "In short" (the key points) and the contents list after the breadcrumb row,
 *                              and the Sources list and the Notes framed as one block at the end
 *
 * The FAQ is the theme's FAQ style ("? Question" in the compact markup, or a Details block with the FAQ
 * style); the FAQPage in the structured data is built from those same blocks, so it is there only when
 * the questions are on the page. No byline: the owner's name stays in the structured data alone.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

/**
 * The Journal post a piece is about: the queried post, when it has no password.
 *
 * @return WP_Post|null
 */
function injil_help_article_post() {
	if ( ! is_singular( 'post' ) ) {
		return null;
	}
	$post = get_queried_object();
	return ( $post instanceof WP_Post && '' === $post->post_password ) ? $post : null;
}

/**
 * Whether a the_content call is the main text of the queried post. Block templates render the Post
 * Content block outside a classic loop, so the queried object is matched, as in inc/seo.php.
 *
 * @return bool
 */
function injil_help_article_is_main() {
	$post = injil_help_article_post();
	return $post && ! is_admin() && ! is_feed() && is_main_query() && ! doing_filter( 'get_the_excerpt' ) && get_the_ID() === $post->ID;
}

/** Plain text of a piece of HTML, on one line. */
function injil_help_article_text( $html ) {
	return trim( (string) preg_replace( '/\s+/u', ' ', html_entity_decode( wp_strip_all_tags( (string) $html ), ENT_QUOTES, 'UTF-8' ) ) );
}

/* The meta row ----------------------------------------------------------------------------------- */

/**
 * [injil_help_article_meta]: the reading time at 220 words a minute, and "Updated" with the date when
 * the post changed a day or more after it was published.
 *
 * @return string
 */
function injil_help_article_meta() {
	$post = injil_help_article_post();
	if ( ! $post ) {
		return '';
	}
	// Counted as runs of letters and digits in any script: str_word_count() splits "Injīl" at the ī.
	$words   = (int) preg_match_all( '/[\p{L}\p{N}][\p{L}\p{N}\p{M}\'’-]*/u', injil_help_article_text( strip_shortcodes( $post->post_content ) ) );
	$minutes = max( 1, (int) round( $words / 220 ) );
	/* translators: %d: minutes. */
	$out = '<span class="ih-meta-item ih-reading-time">' . esc_html( sprintf( _n( '%d minute to read', '%d minutes to read', $minutes, 'injil-help' ), $minutes ) ) . '</span>';
	if ( (int) get_post_modified_time( 'U', true, $post ) - (int) get_post_time( 'U', true, $post ) >= DAY_IN_SECONDS ) {
		$out .= '<span class="ih-meta-item ih-updated">' . esc_html__( 'Updated', 'injil-help' ) . ' <time datetime="' . esc_attr( (string) get_post_modified_time( 'c', true, $post ) ) . '">' . esc_html( (string) get_the_modified_date( 'j F Y', $post ) ) . '</time></span>';
	}
	return $out;
}
add_shortcode( 'injil_help_article_meta', 'injil_help_article_meta' );

/* Headings and the contents ---------------------------------------------------------------------- */

/**
 * The level-two headings of a piece of HTML, in order: the id each has or the one made from its text.
 * The author's ids are reserved first, so a made id never repeats one.
 *
 * @param string $html Rendered text.
 * @return array List of array( id, text, whether the heading already has the id ).
 */
function injil_help_article_headings( $html ) {
	if ( ! preg_match_all( '#<h2(\s[^>]*)?>(.*?)</h2>#is', (string) $html, $found, PREG_SET_ORDER ) ) {
		return array();
	}
	$seen = array();
	$own  = array();
	foreach ( $found as $i => $m ) {
		if ( preg_match( '/\sid\s*=\s*["\']([^"\']+)["\']/i', (string) ( $m[1] ?? '' ), $idm ) ) {
			$own[ $i ]       = $idm[1];
			$seen[ $idm[1] ] = true;
		}
	}
	$out = array();
	foreach ( $found as $i => $m ) {
		$text = injil_help_article_text( $m[2] );
		if ( isset( $own[ $i ] ) ) {
			$out[] = array( $own[ $i ], $text, true );
			continue;
		}
		$base = sanitize_title( $text );
		if ( '' === $base ) {
			$out[] = array( '', $text, false );
			continue;
		}
		$slug = $base;
		for ( $n = 2; isset( $seen[ $slug ] ); $n++ ) {
			$slug = $base . '-' . $n;
		}
		$seen[ $slug ] = true;
		$out[]         = array( $slug, $text, false );
	}
	return $out;
}

/**
 * Gives each level-two heading without an id the one injil_help_article_headings() made for it.
 *
 * @param string $html Rendered text.
 * @return string
 */
function injil_help_article_heading_ids( $html ) {
	$list = injil_help_article_headings( $html );
	$i    = 0;
	return (string) preg_replace_callback(
		'#<h2(\s[^>]*)?>(.*?)</h2>#is',
		static function ( $m ) use ( $list, &$i ) {
			$h = $list[ $i++ ] ?? array( '', '', true );
			if ( $h[2] || '' === $h[0] ) {
				return $m[0];
			}
			return '<h2 id="' . esc_attr( $h[0] ) . '"' . (string) ( $m[1] ?? '' ) . '>' . $m[2] . '</h2>';
		},
		(string) $html
	);
}

/**
 * The contents list: the post's sections, from three level-two headings up.
 *
 * @param string $html Rendered text, its headings already given ids.
 * @return string
 */
function injil_help_article_contents( $html ) {
	$items = '';
	$count = 0;
	foreach ( injil_help_article_headings( $html ) as $h ) {
		if ( '' === $h[0] || '' === $h[1] ) {
			continue;
		}
		$items .= '<li><a href="#' . esc_attr( $h[0] ) . '">' . esc_html( $h[1] ) . '</a></li>';
		++$count;
	}
	if ( $count < 3 ) {
		return '';
	}
	return '<nav class="ih-contents" aria-labelledby="ih-contents-title"><p class="ih-k" id="ih-contents-title">' . esc_html__( 'Contents', 'injil-help' ) . '</p><ol>' . $items . '</ol></nav>';
}

/* "In short" ------------------------------------------------------------------------------------- */

/**
 * Key points from text: one a line, or separated by ";;".
 *
 * @param string $text Text.
 * @return string[]
 */
function injil_help_article_points( $text ) {
	return array_values( array_filter( array_map( 'trim', (array) preg_split( '/\r\n|\r|\n|\s*;;\s*/', (string) $text ) ), 'strlen' ) );
}

/**
 * Whether "In short" shows on a post: it has key points.
 *
 * @param WP_Post|null $post Post.
 * @return bool
 */
function injil_help_article_points_show( $post ) {
	return $post instanceof WP_Post && '' === $post->post_password && (bool) injil_help_article_points( get_post_meta( $post->ID, '_injil_help_points', true ) );
}

/**
 * "In short": the key points in the answer capsule's dress, near the top of the post.
 *
 * @param WP_Post $post Post.
 * @return string
 */
function injil_help_article_capsule( $post ) {
	$points = injil_help_article_points( get_post_meta( $post->ID, '_injil_help_points', true ) );
	if ( ! $points ) {
		return '';
	}
	return '<section class="wp-block-group is-style-answer-capsule ih-points" aria-labelledby="ih-points-title"><h2 class="ih-capsule-q" id="ih-points-title">' . esc_html__( 'In short', 'injil-help' ) . '</h2><ul><li>' . implode( '</li><li>', array_map( 'esc_html', $points ) ) . '</li></ul></section>';
}

/* Sources ---------------------------------------------------------------------------------------- */

/**
 * The Sources list and the Notes that follow it, framed as one block. The headings and lists stay as
 * they are, so the citations read from them (injil_help_collect_citations()) and the links to the
 * notes are unchanged.
 *
 * @param string $html Rendered text.
 * @return string
 */
function injil_help_article_sources_block( $html ) {
	if ( false !== strpos( (string) $html, 'class="ih-sources"' ) ) {
		return $html;
	}
	return (string) preg_replace(
		'#<h2(\s[^>]*)?>\s*Sources\s*</h2>\s*<ul\b[^>]*>.*?</ul>(?:\s*<h2\b[^>]*\bid="notes"[^>]*>.*?</h2>\s*<ol\b[^>]*>.*?</ol>)?#s',
		'<section class="ih-sources" aria-label="' . esc_attr__( 'Sources and notes', 'injil-help' ) . '">$0</section>',
		(string) $html,
		1
	);
}

/* The filters ------------------------------------------------------------------------------------ */

add_filter(
	'the_content',
	static function ( $content ) {
		return injil_help_article_is_main() ? injil_help_article_heading_ids( $content ) : $content;
	},
	12
);

/*
 * Priority 16: after the contextual links (15), which so leave "In short" and the contents alone, and
 * before the share row (20).
 */
add_filter(
	'the_content',
	static function ( $content ) {
		if ( ! injil_help_article_is_main() ) {
			return $content;
		}
		$post    = injil_help_article_post();
		$content = injil_help_article_sources_block( $content );
		$top     = injil_help_article_capsule( $post ) . injil_help_article_contents( $content );
		if ( '' === $top ) {
			return $content;
		}
		// After the breadcrumb row, which opens the content (inc/seo.php).
		$at = preg_match( '#<nav class="ih-breadcrumb"[^>]*>.*?</nav>#s', $content, $m, PREG_OFFSET_CAPTURE ) ? $m[0][1] + strlen( $m[0][0] ) : 0;
		return substr( $content, 0, $at ) . $top . substr( $content, $at );
	},
	16
);

/* The edit screen: key points -------------------------------------------------------------------- */

add_action(
	'init',
	static function () {
		register_post_meta(
			'post',
			'_injil_help_points',
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_textarea_field',
				'auth_callback'     => static function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}
);

add_action(
	'add_meta_boxes_post',
	static function () {
		add_meta_box( 'injil_help_points', __( 'Key points', 'injil-help' ), 'injil_help_article_meta_box', 'post', 'normal', 'default' );
	}
);

/**
 * The Key points box.
 *
 * @param WP_Post $post Post.
 */
function injil_help_article_meta_box( $post ) {
	wp_nonce_field( 'injil_help_points', 'injil_help_points_nonce' );
	echo '<p><label for="ih-points"><strong>' . esc_html__( 'Key points, one a line', 'injil-help' ) . '</strong></label>';
	echo '<textarea id="ih-points" name="injil_help_points" rows="4" class="widefat">' . esc_textarea( (string) get_post_meta( $post->ID, '_injil_help_points', true ) ) . '</textarea>';
	echo '<span class="description">' . esc_html__( 'Shown in the "In short" box under the breadcrumb row. Leave it empty to show nothing.', 'injil-help' ) . '</span></p>';
}

add_action(
	'save_post_post',
	static function ( $post_id ) {
		if ( ! isset( $_POST['injil_help_points_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['injil_help_points_nonce'] ) ), 'injil_help_points' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$value = implode( "\n", injil_help_article_points( sanitize_textarea_field( wp_unslash( $_POST['injil_help_points'] ?? '' ) ) ) );
		if ( '' !== $value ) {
			update_post_meta( $post_id, '_injil_help_points', $value );
		} else {
			delete_post_meta( $post_id, '_injil_help_points' );
		}
	}
);

add_action(
	'wp_enqueue_scripts',
	static function () {
		if ( is_singular( 'post' ) ) {
			wp_enqueue_style( 'injil-help-article', get_stylesheet_directory_uri() . '/assets/css/article.css', array( 'injil-help' ), INJIL_HELP_VERSION );
		}
	},
	20
);

// The last line: functions.php checks for it, so a file an upload cut short is caught.
$GLOBALS['injil_help_loaded'][ basename( __FILE__, '.php' ) ] = true;
