<?php
/**
 * injil.help child theme functions.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

define( 'INJIL_HELP_VERSION', '3.8.0' );

require_once get_stylesheet_directory() . '/inc/options.php';
require_once get_stylesheet_directory() . '/inc/starter-content.php';
require_once get_stylesheet_directory() . '/inc/seo.php';
require_once get_stylesheet_directory() . '/inc/schema.php';
require_once get_stylesheet_directory() . '/inc/rank-math.php';
require_once get_stylesheet_directory() . '/inc/share.php';
require_once get_stylesheet_directory() . '/inc/contextual-links.php';

/**
 * Hardening. Application passwords (REST API and XML-RPC log-ins with a per-application
 * password) are blocked for the whole site by default, the same as
 * add_filter( 'wp_is_application_passwords_available', '__return_false' ), until the block is
 * switched off under Theme Options › Security: nothing on injil.help is written through the
 * REST API from outside, so the feature is one more door than the site needs.
 */
add_filter(
	'wp_is_application_passwords_available',
	function ( $available ) {
		return injil_help_get_option( 'block_app_passwords' ) ? false : $available;
	}
);

/**
 * Styles: front end and editor.
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_style( 'injil-help', get_stylesheet_uri(), array(), INJIL_HELP_VERSION );
		if ( is_singular() && false !== strpos( (string) get_post_field( 'post_content', get_queried_object_id() ), 'ih-bible' ) ) {
			wp_enqueue_style( 'injil-help-bible', get_stylesheet_directory_uri() . '/assets/css/bible.css', array( 'injil-help' ), INJIL_HELP_VERSION );
		}
		if ( 'off' !== injil_help_get_option( 'ornament' ) ) {
			wp_enqueue_style( 'injil-help-ornament', get_stylesheet_directory_uri() . '/assets/css/ornament.css', array( 'injil-help' ), INJIL_HELP_VERSION );
			if ( injil_help_get_option( 'animate' ) || 'off' !== injil_help_get_option( 'parallax' ) ) {
				wp_enqueue_script( 'injil-help-ornament', get_stylesheet_directory_uri() . '/assets/js/ornament.js', array(), INJIL_HELP_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
			}
		}
	}
);

add_action(
	'after_setup_theme',
	function () {
		load_child_theme_textdomain( 'injil-help', get_stylesheet_directory() . '/languages' );
		add_editor_style( 'style.css' );
	}
);

/**
 * Pattern category.
 */
add_action(
	'init',
	function () {
		register_block_pattern_category(
			'injil-help',
			array(
				'label'       => __( 'injil.help', 'injil-help' ),
				'description' => __( 'Tables, capsules, labels and verse layouts for injil.help.', 'injil-help' ),
			)
		);

		// The patterns that supply whole pages are kept as JSON in inc/data/patterns/ and registered from there.
		$keys = array_flip( array( 'title', 'description', 'content', 'categories', 'keywords', 'blockTypes', 'postTypes', 'templateTypes', 'viewportWidth', 'inserter' ) );
		foreach ( (array) glob( get_stylesheet_directory() . '/inc/data/patterns/*.json' ) as $file ) {
			$pattern = injil_help_pattern_file( basename( (string) $file, '.json' ) );
			if ( $pattern && ! WP_Block_Patterns_Registry::get_instance()->is_registered( $pattern['slug'] ) ) {
				register_block_pattern( $pattern['slug'], array_intersect_key( $pattern, $keys ) );
			}
		}
	}
);

/**
 * Block style variations. CSS lives in style.css.
 */
add_action(
	'init',
	function () {
		$styles = array(
			'core/paragraph' => array(
				'label-consensus' => __( 'Label: Scholarly consensus', 'injil-help' ),
				'label-minority'  => __( 'Label: Minority view', 'injil-help' ),
				'label-islamic'   => __( 'Label: Islamic position', 'injil-help' ),
				'label-quran'     => __( "Source: Qur'an", 'injil-help' ),
				'label-nt'        => __( 'Source: NT-attested', 'injil-help' ),
				'label-editorial' => __( 'Source: Editorial', 'injil-help' ),
			),
			'core/group'     => array(
				'answer-capsule'  => __( 'Answer capsule', 'injil-help' ),
				'gospel-verse'    => __( 'Gospel verse', 'injil-help' ),
				'objection-reply' => __( 'Objection and reply', 'injil-help' ),
				'criterion'       => __( 'Criterion box', 'injil-help' ),
				'answer-item'     => __( 'Short answer (adds FAQPage schema)', 'injil-help' ),
				'read-first'      => __( 'Read first', 'injil-help' ),
			),
			'core/table'     => array(
				'discrepancy' => __( 'Discrepancy table', 'injil-help' ),
			),
			'core/separator' => array(
				'ornament' => __( 'Ornament', 'injil-help' ),
			),
			'core/details'   => array(
				'faq' => __( 'FAQ item (adds FAQPage schema)', 'injil-help' ),
			),
		);
		foreach ( $styles as $block => $variants ) {
			foreach ( $variants as $name => $label ) {
				register_block_style(
					$block,
					array(
						'name'  => $name,
						'label' => $label,
					)
				);
			}
		}
	}
);

/* Favicon: custom URL, else the brandmark (always, or when no Site Icon is set). */
add_action(
	'wp_head',
	function () {
		$custom = injil_help_get_option( 'favicon_url' );
		if ( $custom ) {
			printf( '<link rel="icon" href="%s">' . "\n", esc_url( $custom ) );
		} elseif ( injil_help_get_option( 'favicon_force' ) || ! has_site_icon() ) {
			printf( '<link rel="icon" type="image/svg+xml" href="%s">' . "\n", esc_url( get_stylesheet_directory_uri() . '/assets/brandmark.svg' ) );
		}
	}
);
