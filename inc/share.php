<?php
/**
 * Share row: links to the sharing endpoints of each network, a native Web Share
 * button and a copy-link button (the last two appear only with JavaScript).
 * Icons are the Minimalist Social Icons Pack, drawn as CSS masks so they take
 * their colour from the palette. Controlled by Theme Options > Sharing.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

/**
 * Networks with a share endpoint. {url}, {title} and {image} are replaced with
 * the encoded values. Filter injil_help_share_networks to add or remove one;
 * an added network needs an icon at assets/icons/social/{key}.svg and a
 * matching rule in assets/css/share.css.
 */
function injil_help_share_networks() {
	return apply_filters(
		'injil_help_share_networks',
		array(
			'x'         => array( 'X', 'https://twitter.com/intent/tweet?url={url}&text={title}' ),
			'facebook'  => array( 'Facebook', 'https://www.facebook.com/sharer/sharer.php?u={url}' ),
			'whatsapp'  => array( 'WhatsApp', 'https://api.whatsapp.com/send?text={title}%20{url}' ),
			'telegram'  => array( 'Telegram', 'https://t.me/share/url?url={url}&text={title}' ),
			'reddit'    => array( 'Reddit', 'https://www.reddit.com/submit?url={url}&title={title}' ),
			'linkedin'  => array( 'LinkedIn', 'https://www.linkedin.com/sharing/share-offsite/?url={url}' ),
			'bluesky'   => array( 'Bluesky', 'https://bsky.app/intent/compose?text={title}%20{url}' ),
			'threads'   => array( 'Threads', 'https://www.threads.net/intent/post?text={title}%20{url}' ),
			'pinterest' => array( 'Pinterest', 'https://pinterest.com/pin/create/button/?url={url}&description={title}&media={image}' ),
			'line'      => array( 'LINE', 'https://social-plugins.line.me/lineit/share?url={url}' ),
		)
	);
}

/** Whether the row belongs on the page being rendered. */
function injil_help_share_wanted() {
	if ( ! injil_help_get_option( 'share' ) || ! is_singular() || is_front_page() ) {
		return false;
	}
	$types = (array) injil_help_get_option( 'share_types' );
	return in_array( get_post_type(), $types, true ) && injil_help_get_option( 'share_networks' );
}

/** The row's markup for a post. */
function injil_help_share_markup( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}
	$networks = injil_help_share_networks();
	$chosen   = (array) injil_help_get_option( 'share_networks' );
	$url      = rawurlencode( get_permalink( $post ) );
	$title    = rawurlencode( wp_strip_all_tags( get_the_title( $post ) ) );
	$image    = rawurlencode( has_post_thumbnail( $post ) ? get_the_post_thumbnail_url( $post, 'large' ) : injil_help_default_og_image() );
	$label    = injil_help_get_option( 'share_label' ) ?: __( 'Share', 'injil-help' );

	$items = '';
	foreach ( $chosen as $key ) {
		if ( empty( $networks[ $key ] ) ) {
			continue;
		}
		list( $name, $endpoint ) = $networks[ $key ];
		$href = str_replace( array( '{url}', '{title}', '{image}' ), array( $url, $title, $image ), $endpoint );
		$items .= sprintf(
			'<li class="ih-share-item ih-share-%1$s"><a class="ih-share-link" href="%2$s" target="_blank" rel="noopener noreferrer nofollow" aria-label="%3$s"><span class="ih-share-icon" aria-hidden="true"></span><span class="ih-share-name">%4$s</span></a></li>',
			esc_attr( $key ),
			esc_url( $href ),
			/* translators: %s: network name */
			esc_attr( sprintf( __( 'Share on %s', 'injil-help' ), $name ) ),
			esc_html( $name )
		);
	}
	if ( ! $items ) {
		return '';
	}
	// Buttons for the Web Share API and the clipboard; share.js removes "hidden" when each is available.
	$items .= sprintf(
		'<li class="ih-share-item ih-share-copy" hidden><button type="button" class="ih-share-link" data-copy="%1$s" data-done="%2$s" data-fail="%4$s"><span class="ih-share-icon" aria-hidden="true"></span><span class="ih-share-name">%3$s</span></button></li>',
		esc_attr( get_permalink( $post ) ),
		esc_attr__( 'Copied', 'injil-help' ),
		esc_html__( 'Copy link', 'injil-help' ),
		esc_attr__( 'Could not copy; copy the address by hand', 'injil-help' )
	);
	$items .= sprintf(
		'<li class="ih-share-item ih-share-native" hidden><button type="button" class="ih-share-link" data-share-title="%1$s" data-share-url="%2$s"><span class="ih-share-icon" aria-hidden="true"></span><span class="ih-share-name">%3$s</span></button></li>',
		esc_attr( wp_strip_all_tags( get_the_title( $post ) ) ),
		esc_attr( get_permalink( $post ) ),
		esc_html__( 'More…', 'injil-help' )
	);

	return sprintf(
		'<nav class="ih-share" aria-label="%1$s"><span class="ih-share-label">%2$s</span><ul class="ih-share-list">%3$s</ul></nav>',
		esc_attr__( 'Share this page', 'injil-help' ),
		esc_html( $label ),
		$items
	);
}

/*
 * After the content of posts and pages. Block templates render the Post Content
 * block outside a classic loop, so the check is on the queried post rather than
 * in_the_loop(). Excerpts built from the content (wp_trim_excerpt) must not pick
 * up the row.
 */
add_filter(
	'the_content',
	function ( $content ) {
		if ( ! is_main_query() || doing_filter( 'get_the_excerpt' ) || get_the_ID() !== get_queried_object_id() || ! injil_help_share_wanted() ) {
			return $content;
		}
		return $content . injil_help_share_markup();
	},
	20
);

add_action(
	'wp_enqueue_scripts',
	function () {
		if ( ! injil_help_share_wanted() ) {
			return;
		}
		wp_enqueue_style( 'injil-help-share', get_stylesheet_directory_uri() . '/assets/css/share.css', array( 'injil-help' ), INJIL_HELP_VERSION );
		wp_enqueue_script( 'injil-help-share', get_stylesheet_directory_uri() . '/assets/js/share.js', array(), INJIL_HELP_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	}
);
