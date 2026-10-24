<?php
/**
 * injil.help child theme functions.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

define( 'INJIL_HELP_VERSION', '3.3.0' );

require_once get_stylesheet_directory() . '/inc/options.php';
require_once get_stylesheet_directory() . '/inc/starter-content.php';
require_once get_stylesheet_directory() . '/inc/seo.php';

/**
 * Styles: front end and editor.
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_style( 'injil-help', get_stylesheet_uri(), array(), INJIL_HELP_VERSION );
		if ( 'off' !== injil_help_get_option( 'ornament' ) ) {
			wp_enqueue_style( 'injil-help-ornament', get_stylesheet_directory_uri() . '/assets/css/ornament.css', array( 'injil-help' ), INJIL_HELP_VERSION );
			if ( injil_help_get_option( 'animate' ) ) {
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

/**
 * Structured data: Article on singular content, FAQPage from core/details blocks
 * styled "FAQ item". Skipped when an SEO plugin already outputs schema.
 */
function injil_help_schema_enabled() {
	$plugin_active = defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || defined( 'SEOPRESS_VERSION' );
	return (bool) apply_filters( 'injil_help_output_schema', ! $plugin_active );
}

function injil_help_collect_faq( $blocks, &$items ) {
	foreach ( $blocks as $block ) {
		if ( 'core/details' === $block['blockName'] && false !== strpos( $block['attrs']['className'] ?? '', 'is-style-faq' ) ) {
			preg_match( '#<summary[^>]*>(.*?)</summary>#s', $block['innerHTML'], $m );
			$question = trim( wp_strip_all_tags( $m[1] ?? '' ) );
			$answer   = '';
			foreach ( $block['innerBlocks'] as $inner ) {
				$answer .= render_block( $inner );
			}
			$answer = trim( wp_strip_all_tags( $answer ) );
			if ( $question && $answer ) {
				$items[] = array(
					'@type'          => 'Question',
					'name'           => $question,
					'acceptedAnswer' => array(
						'@type' => 'Answer',
						'text'  => $answer,
					),
				);
			}
		}
		if ( 'core/group' === $block['blockName'] && false !== strpos( $block['attrs']['className'] ?? '', 'is-style-answer-item' ) ) {
			$question = '';
			$answer   = '';
			foreach ( $block['innerBlocks'] as $inner ) {
				$class = $inner['attrs']['className'] ?? '';
				if ( 'core/heading' === $inner['blockName'] && '' === $question ) {
					$question = trim( wp_strip_all_tags( render_block( $inner ) ) );
				} elseif ( false !== strpos( $class, 'ih-answer-text' ) ) {
					$answer .= ' ' . wp_strip_all_tags( render_block( $inner ) );
				}
			}
			$answer = trim( $answer );
			if ( $question && $answer ) {
				$items[] = array(
					'@type'          => 'Question',
					'name'           => html_entity_decode( $question, ENT_QUOTES ),
					'acceptedAnswer' => array(
						'@type' => 'Answer',
						'text'  => html_entity_decode( $answer, ENT_QUOTES ),
					),
				);
			}
			continue;
		}
		if ( ! empty( $block['innerBlocks'] ) ) {
			injil_help_collect_faq( $block['innerBlocks'], $items );
		}
	}
}

/**
 * Replace core/pattern references with the pattern's blocks, so schema sees
 * answers placed through template patterns.
 */
function injil_help_expand_patterns( $blocks ) {
	$out = array();
	foreach ( $blocks as $block ) {
		if ( 'core/pattern' === $block['blockName'] && ! empty( $block['attrs']['slug'] ) ) {
			$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( $block['attrs']['slug'] );
			if ( $pattern ) {
				$out = array_merge( $out, injil_help_expand_patterns( parse_blocks( $pattern['content'] ) ) );
			}
			continue;
		}
		if ( ! empty( $block['innerBlocks'] ) ) {
			$block['innerBlocks'] = injil_help_expand_patterns( $block['innerBlocks'] );
		}
		$out[] = $block;
	}
	return $out;
}

add_action(
	'wp_head',
	function () {
		if ( ! ( is_singular() || is_front_page() ) || ! injil_help_schema_enabled() ) {
			return;
		}
		$post  = get_queried_object();
		$graph = array();

		$content = $post instanceof WP_Post ? $post->post_content : '';
		if ( is_front_page() ) {
			$template = get_block_template( get_stylesheet() . '//front-page' );
			$content  = $template ? $template->content : $content;
		}

		if ( ! is_front_page() && $post instanceof WP_Post ) {
			$graph[] = array(
			'@type'            => 'Article',
			'headline'         => wp_strip_all_tags( get_the_title( $post ) ),
			'description'      => wp_strip_all_tags( get_the_excerpt( $post ) ),
			'datePublished'    => get_the_date( 'c', $post ),
			'dateModified'     => get_the_modified_date( 'c', $post ),
			'inLanguage'       => get_bloginfo( 'language' ),
			'mainEntityOfPage' => get_permalink( $post ),
			'author'           => array(
				'@type' => 'Person',
				'name'  => get_the_author_meta( 'display_name', $post->post_author ),
			),
			'publisher'        => array(
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
				'url'   => home_url( '/' ),
			),
		);
		}

		$faq = array();
		injil_help_collect_faq( injil_help_expand_patterns( parse_blocks( $content ) ), $faq );
		if ( $faq ) {
			$graph[] = array(
				'@type'      => 'FAQPage',
				'mainEntity' => $faq,
			);
		}

		$graph = apply_filters( 'injil_help_schema_graph', $graph );
		if ( ! $graph ) {
			return;
		}

		printf(
			"<script type=\"application/ld+json\">%s</script>\n",
			wp_json_encode(
				array(
					'@context' => 'https://schema.org',
					'@graph'   => $graph,
				),
				JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
			)
		);
	},
	5
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
