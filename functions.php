<?php
/**
 * injil.help child theme functions.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

define( 'INJIL_HELP_VERSION', '3.25.4' );

/**
 * The theme's PHP files in inc/, in the order functions.php loads them. The files they pull in
 * themselves (starter-content-pages, gospel-islam, tafsir, quran-jesus, images, schema-content,
 * schema-extend, security-detector, unlist-admin, error-page-html) are listed after them, so the
 * check below sees every file before any is read.
 *
 * @return array{load: string[], also: string[]}
 */
function injil_help_theme_files() {
	return array(
		'load' => array( 'options', 'starter-content', 'journal', 'seo', 'schema', 'rank-math', 'share', 'contextual-links', 'security', 'login', 'social', 'progress', 'unlist', 'search-permalinks', 'error-pages' ),
		'also' => array( 'starter-content-pages', 'gospel-islam', 'tafsir', 'quran-jesus', 'images', 'schema-content', 'schema-extend', 'security-detector', 'unlist-admin', 'error-page-html' ),
	);
}

/**
 * Send a 503 "being updated" page and stop: the static 503 page the theme writes into
 * wp-content, then the theme's own copy, then a few lines of HTML. Nothing here needs the rest of
 * the theme.
 */
function injil_help_serve_incomplete() {
	if ( ! headers_sent() ) {
		status_header( 503 );
		header( 'Retry-After: 300' );
		header( 'Cache-Control: no-store, max-age=0' );
		header( 'Content-Type: text/html; charset=utf-8' );
	}
	foreach ( array( WP_CONTENT_DIR . '/injil-help-errors/503.html', __DIR__ . '/errors/503.html' ) as $page ) {
		if ( is_readable( $page ) && filesize( $page ) > 0 ) {
			readfile( $page ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			exit;
		}
	}
	echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="robots" content="noindex"><meta http-equiv="refresh" content="60"><title>Back shortly</title></head><body><h1>Back shortly</h1><p>The site is being updated. Please try again in a few minutes.</p></body></html>';
	exit;
}

/**
 * Load the theme's files, or report that the theme is incomplete.
 *
 * A theme uploaded file by file (by FTP) is incomplete for a few minutes: a file not there yet,
 * or there and half written. A failed require_once ends the request in a fatal error for every
 * visitor (25 October 2026: functions.php line 12, inc/options.php). So every file is checked
 * first, each is loaded inside a try that catches the ParseError of a half-written file, and each
 * must reach the flag on its last line.
 * While anything is wrong, visitors and the admin get a 503 page and the theme stops loading.
 * The login screen, cron, AJAX and the command line go on without the theme. What was wrong goes
 * to the error log.
 *
 * @return bool Whether every file loaded.
 */
function injil_help_load_theme_files() {
	$files = injil_help_theme_files();
	$dir   = __DIR__ . '/inc/';
	try {
		foreach ( array_merge( $files['load'], $files['also'] ) as $name ) {
			if ( ! is_readable( $dir . $name . '.php' ) ) {
				throw new RuntimeException( 'inc/' . $name . '.php is missing' );
			}
		}
		foreach ( $files['load'] as $name ) {
			require_once $dir . $name . '.php';
		}
		// A file cut short at the end of a function still parses. Every file sets a flag on its last
		// line; a file that was read and set none is incomplete. unlist-admin.php loads later, in the admin.
		$read = array_flip( array_map( 'wp_normalize_path', get_included_files() ) );
		foreach ( array_merge( $files['load'], $files['also'] ) as $name ) {
			if ( isset( $read[ wp_normalize_path( $dir . $name . '.php' ) ] ) && empty( $GLOBALS['injil_help_loaded'][ $name ] ) ) {
				throw new RuntimeException( 'inc/' . $name . '.php is incomplete' );
			}
		}
		return true;
	} catch ( Throwable $e ) {
		error_log( 'The Injil: the theme is incomplete or being uploaded (' . $e->getMessage() . '); a 503 page is sent until it is whole.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
		if ( ! wp_doing_cron() && ! wp_doing_ajax() && 'wp-login.php' !== ( $GLOBALS['pagenow'] ?? '' ) && 'cli' !== PHP_SAPI ) {
			injil_help_serve_incomplete();
		}
		return false;
	}
}

if ( ! injil_help_load_theme_files() ) {
	return;
}

/**
 * Hardening lives in inc/security.php: XML-RPC, application passwords (the block_app_passwords
 * setting, on by default since 3.6.0) and REST writes from outside a browser session are shut,
 * new content is checked for gambling spam, and Theme Options › Security carries the switches,
 * the clean-up tools and the reports.
 */

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
		// Phone layout, touch targets and the focus ring; after the ornament so its rules come last.
		wp_enqueue_style( 'injil-help-responsive', get_stylesheet_directory_uri() . '/assets/css/responsive.css', array( 'injil-help' ), INJIL_HELP_VERSION );
	}
);

add_action(
	'after_setup_theme',
	function () {
		load_child_theme_textdomain( 'injil-help', get_stylesheet_directory() . '/languages' );
		// Block themes get both from core; stated here so the theme does not depend on that.
		add_theme_support( 'title-tag' );
		add_theme_support( 'automatic-feed-links' );
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

		// Every pattern, those that supply whole pages, the front-page sections and the editor's building blocks, is kept as JSON in inc/data/patterns/ and registered from there.
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
