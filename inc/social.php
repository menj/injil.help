<?php
/**
 * Profile links (3.21.0): the site's own profiles as a row of icons from the Minimalist Social
 * Icons Pack (GPL; LinkedIn from Font Awesome Free, CC BY 4.0), inlined as SVG so they take
 * currentColor from the palette. The addresses are set in Theme Options › Sharing. The row
 * shows in the footer when "Show in the footer" is on, and anywhere through the shortcode
 * [injil_help_social] or the "Profile links" pattern. A network without an address is skipped,
 * and with no address at all nothing is printed.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

/** The networks a profile can be on: key => name. Each has assets/icons/social/{key}.svg. */
function injil_help_social_networks() {
	return apply_filters(
		'injil_help_social_networks',
		array(
			'x'         => 'X',
			'facebook'  => 'Facebook',
			'instagram' => 'Instagram',
			'youtube'   => 'YouTube',
			'tiktok'    => 'TikTok',
			'telegram'  => 'Telegram',
			'whatsapp'  => 'WhatsApp',
			'linkedin'  => 'LinkedIn',
			'bluesky'   => 'Bluesky',
			'threads'   => 'Threads',
			'mastodon'  => 'Mastodon',
			'github'    => 'GitHub',
			'substack'  => 'Substack',
		)
	);
}

/**
 * One icon, inline, marked decorative. The file's own fill and title are dropped.
 *
 * @param string $key Network key.
 * @return string
 */
function injil_help_social_svg( $key ) {
	static $cache = array();
	if ( ! isset( $cache[ $key ] ) ) {
		$file = get_stylesheet_directory() . '/assets/icons/social/' . sanitize_key( $key ) . '.svg';
		$svg  = is_readable( $file ) ? (string) file_get_contents( $file ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- a bundled file.
		$svg  = preg_replace( array( '#<title>.*?</title>#s', '#\s(?:role|fill|width|height)="[^"]*"#' ), '', $svg );
		$cache[ $key ] = preg_replace( '#^<svg\b#', '<svg class="ih-social__icon" width="24" height="24" fill="currentColor" aria-hidden="true" focusable="false"', trim( (string) $svg ) );
	}
	return $cache[ $key ];
}

/**
 * The row's markup, or '' when no address is set.
 *
 * @param array $profiles key => URL.
 * @return string
 */
function injil_help_social_markup( $profiles = null ) {
	$profiles = null === $profiles ? (array) injil_help_get_option( 'social_profiles' ) : (array) $profiles;
	$items    = '';
	foreach ( injil_help_social_networks() as $key => $name ) {
		$url = isset( $profiles[ $key ] ) ? esc_url( $profiles[ $key ] ) : '';
		$svg = '' !== $url ? injil_help_social_svg( $key ) : '';
		if ( '' === $svg ) {
			continue;
		}
		$items .= sprintf(
			'<li class="ih-social__item"><a class="ih-social__link ih-social--%1$s" href="%2$s" rel="me noopener" aria-label="%3$s">%4$s</a></li>',
			esc_attr( $key ),
			$url,
			esc_attr( $name ),
			$svg
		);
	}
	if ( '' === $items ) {
		return '';
	}
	wp_enqueue_style( 'injil-help-social', get_stylesheet_directory_uri() . '/assets/css/social.css', array( 'injil-help' ), INJIL_HELP_VERSION );
	return '<nav class="ih-social" aria-label="' . esc_attr__( 'Our profiles', 'injil-help' ) . '"><ul class="ih-social__list">' . $items . '</ul></nav>';
}

add_shortcode(
	'injil_help_social',
	function () {
		return injil_help_social_markup();
	}
);

/* The footer: after the legal links, when the option is on. The footer is a template part, so the row is added as it renders. */
add_filter(
	'render_block_core/group',
	function ( $html, $block ) {
		if ( ! injil_help_get_option( 'social_footer' ) || 'ih-legal' !== ( $block['attrs']['className'] ?? '' ) ) {
			return $html;
		}
		return $html . injil_help_social_markup();
	},
	10,
	2
);

/* The stylesheet is small; enqueued early so it is in the head when the footer has the row. */
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( injil_help_get_option( 'social_footer' ) && array_filter( (array) injil_help_get_option( 'social_profiles' ) ) ) {
			wp_enqueue_style( 'injil-help-social', get_stylesheet_directory_uri() . '/assets/css/social.css', array( 'injil-help' ), INJIL_HELP_VERSION );
		}
	}
);
