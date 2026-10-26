<?php
/**
 * Contextual links: in the running text of a single post or page, the first mention of a
 * phrase the network map knows links to the inner page that treats it, here or on one of the
 * six sister sites. The map is inc/data/network-links.json, one file identical in all seven
 * themes; Theme Options › Contextual links switches each kind on or off, caps them, and adds
 * phrases of its own. The stored text is never touched: links are made as the page renders.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

/** This site's key in network-links.json. */
const INJIL_HELP_SITE_KEY = 'injil';

/** Normalise a phrase or matched text for lookup: straight apostrophes, single spaces, lower case. */
function injil_help_ctx_key( $text ) {
	$text = str_replace( array( '&#8217;', '&#039;', '&#39;', '&rsquo;', '’', 'ʼ' ), "'", $text );
	$text = preg_replace( '/(?:\s|&nbsp;|&#160;|\x{00A0})+/u', ' ', $text );
	return function_exists( 'mb_strtolower' ) ? mb_strtolower( trim( $text ), 'UTF-8' ) : strtolower( trim( $text ) );
}

/** A URL reduced to host and path, for comparing targets and the current page. */
function injil_help_ctx_norm_url( $url ) {
	$parts = wp_parse_url( html_entity_decode( (string) $url ) );
	if ( empty( $parts['path'] ) && empty( $parts['host'] ) ) {
		return '';
	}
	$host = strtolower( preg_replace( '/^www\./i', '', $parts['host'] ?? (string) wp_parse_url( home_url(), PHP_URL_HOST ) ) );
	return $host . '/' . trim( strtolower( $parts['path'] ?? '' ), '/' );
}

/**
 * The compiled map: phrase (normalised) => array( url, own ), and the combined pattern.
 * Lines added on the Contextual links tab come first, so they win over the shared map.
 *
 * @return array{phrases:array<string,array{0:string,1:bool}>,pattern:string}
 */
function injil_help_ctx_compiled() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}
	$file = get_stylesheet_directory() . '/inc/data/network-links.json';
	$map  = is_readable( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : array(); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$map  = apply_filters( 'injil_help_contextual_links_map', is_array( $map ) ? $map : array() );
	$sites = array_map( 'untrailingslashit', (array) ( $map['sites'] ?? array() ) );
	$hosts = array();
	foreach ( $sites as $key => $base ) {
		$hosts[ strtolower( preg_replace( '/^www\./i', '', (string) wp_parse_url( $base, PHP_URL_HOST ) ) ) ] = $key;
	}

	$phrases = array();
	$add     = static function ( $phrase, $url, $own ) use ( &$phrases ) {
		$key = injil_help_ctx_key( $phrase );
		if ( '' !== $key && ! isset( $phrases[ $key ] ) ) {
			$phrases[ $key ] = array( $url, $own );
		}
	};

	foreach ( preg_split( '/\R/u', (string) injil_help_get_option( 'ctx_extra' ) ) as $line ) {
		$parts = array_map( 'trim', explode( '|', $line, 2 ) );
		if ( count( $parts ) < 2 || '' === $parts[0] ) {
			continue;
		}
		if ( '/' === substr( $parts[1], 0, 1 ) && '/' !== substr( $parts[1], 1, 1 ) ) {
			$add( $parts[0], home_url( $parts[1] ), true );
			continue;
		}
		$host = strtolower( preg_replace( '/^www\./i', '', (string) wp_parse_url( $parts[1], PHP_URL_HOST ) ) );
		if ( ! preg_match( '#^https?://#i', $parts[1] ) || ! isset( $hosts[ $host ] ) ) {
			continue; // Only this site and the sister domains in the map.
		}
		$own = INJIL_HELP_SITE_KEY === $hosts[ $host ];
		$add( $parts[0], $own ? home_url( (string) wp_parse_url( $parts[1], PHP_URL_PATH ) ) : $parts[1], $own );
	}

	foreach ( (array) ( $map['links'] ?? array() ) as $link ) {
		$site = (string) ( $link['site'] ?? '' );
		$path = '/' . ltrim( (string) ( $link['path'] ?? '' ), '/' );
		if ( '/' === $path || ! isset( $sites[ $site ] ) ) {
			continue;
		}
		$own = INJIL_HELP_SITE_KEY === $site;
		$url = $own ? home_url( $path ) : $sites[ $site ] . $path;
		foreach ( (array) ( $link['phrases'] ?? array() ) as $phrase ) {
			$add( (string) $phrase, $url, $own );
		}
	}

	// Several patterns of 150 phrases each: one pattern of the whole map is more than PCRE compiles.
	$patterns = array();
	$keys     = array_keys( $phrases );
	usort(
		$keys,
		static function ( $a, $b ) {
			return strlen( $b ) <=> strlen( $a );
		}
	);
	foreach ( array_chunk( $keys, 150 ) as $chunk ) {
		$alts = array();
		foreach ( $chunk as $key ) {
			$alt    = preg_quote( $key, '/' );
			$alt    = str_replace( "'", "(?:'|’|ʼ|&\\#8217;|&\\#0?39;|&rsquo;)", $alt );
			$alts[] = str_replace( ' ', '(?:\s|&nbsp;|&\\#160;|\x{00A0})+', $alt );
		}
		// A whole phrase: no letter or digit on either side, and never part of an entity.
		$patterns[] = '/(?<![\p{L}\p{N}&#])(?:' . implode( '|', $alts ) . ')(?![\p{L}\p{N}])/iu';
	}
	return $cache = array(
		'phrases'  => $phrases,
		'patterns' => $patterns,
	);
}

/** Whether a tag opens an element that no link may be made inside. */
function injil_help_ctx_forbids( $tag, $piece ) {
	static $tags = array( 'a', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'q', 'figure', 'figcaption', 'cite', 'code', 'pre', 'sup', 'sub', 'nav', 'table', 'button', 'label', 'select', 'textarea', 'summary', 'script', 'style', 'aside', 'header', 'footer' );
	// Arabic, the Qur'an, hadith, verses and the gospel text itself, notes and apparatus, running heads, the breadcrumb, share row and index lists.
	static $classes = array( 'ih-ar', 'ih-quran', 'ih-hadith', 'ih-verse', 'ih-v', 'ih-vq', 'ih-bible', 'ih-translit', 'ih-fnref', 'ih-footnotes', 'ih-foot-list', 'ih-apparatus', 'ih-lanah', 'ih-cite', 'ih-running-head', 'ih-chapter-head', 'ih-chapter-num', 'ih-eyebrow', 'ih-meta', 'ih-numeral', 'ih-mark', 'ih-breadcrumb', 'ih-share', 'ih-next', 'ih-entries-list', 'ih-answers-list', 'is-style-gospel-verse', 'wp-block-quote', 'wp-block-pullquote', 'wp-block-table' );
	if ( in_array( $tag, $tags, true ) ) {
		return true;
	}
	if ( preg_match( '/\blang\s*=\s*["\']?(?:ar|he|el|syr)\b/i', $piece ) || preg_match( '/\bdir\s*=\s*["\']?rtl\b/i', $piece ) ) {
		return true;
	}
	if ( preg_match( '/\bclass\s*=\s*"([^"]*)"/i', $piece, $m ) || preg_match( "/\\bclass\\s*=\\s*'([^']*)'/i", $piece, $m ) ) {
		return (bool) array_intersect( preg_split( '/\s+/', trim( $m[1] ) ), $classes );
	}
	return false;
}

/**
 * Add the links to rendered content.
 *
 * @param string $content Rendered content.
 * @param string $here    The current page's URL (never linked to).
 * @return string
 */
function injil_help_ctx_apply( $content, $here ) {
	$own_on    = (bool) injil_help_get_option( 'ctx_own' );
	$sister_on = (bool) injil_help_get_option( 'ctx_sister' );
	$caps      = array(
		1 => $own_on ? max( 0, min( 15, (int) injil_help_get_option( 'ctx_own_max' ) ) ) : 0,
		0 => $sister_on ? max( 0, min( 10, (int) injil_help_get_option( 'ctx_sister_max' ) ) ) : 0,
	);
	$map = injil_help_ctx_compiled();
	if ( ! $map['patterns'] || ( ! $caps[0] && ! $caps[1] ) || false === strpos( $content, '<' ) ) {
		return $content;
	}
	$pieces = preg_split( '/(<[^>]+>)/', $content, -1, PREG_SPLIT_DELIM_CAPTURE );
	if ( false === $pieces ) {
		return $content;
	}

	// Targets the text already links to count as done, as does the page itself.
	$done = array( injil_help_ctx_norm_url( $here ) => true );
	if ( preg_match_all( '/\bhref\s*=\s*["\']([^"\']+)["\']/i', $content, $hrefs ) ) {
		foreach ( $hrefs[1] as $href ) {
			$done[ injil_help_ctx_norm_url( $href ) ] = true;
		}
	}
	$made   = array( 0 => 0, 1 => 0 );
	$stack  = array(); // Open elements that forbid links.
	$blocks = array(); // Open <p> and <li>, true once one has a link.
	$void   = array( 'br', 'img', 'hr', 'wbr', 'input', 'source', 'meta', 'link', 'col', 'area', 'embed', 'track' );
	$out    = '';

	foreach ( $pieces as $piece ) {
		if ( '' === $piece ) {
			continue;
		}
		if ( '<' === $piece[0] ) {
			if ( preg_match( '#^<(/?)([a-zA-Z][a-zA-Z0-9]*)#', $piece, $m ) ) {
				$tag = strtolower( $m[2] );
				if ( '/' === $m[1] ) {
					for ( $i = count( $stack ) - 1; $i >= 0; $i-- ) {
						if ( $stack[ $i ] === $tag ) {
							array_splice( $stack, $i, 1 );
							break;
						}
					}
					if ( ( 'p' === $tag || 'li' === $tag ) && $blocks ) {
						array_pop( $blocks );
					}
				} elseif ( ! in_array( $tag, $void, true ) && '/' !== substr( $piece, -2, 1 ) ) {
					// Inside a skip region every element is tracked, so a nested
					// element of the same name cannot close the region early.
					if ( $stack || injil_help_ctx_forbids( $tag, $piece ) ) {
						$stack[] = $tag;
					}
					if ( 'p' === $tag || 'li' === $tag ) {
						$blocks[] = false;
					}
				}
			}
			$out .= $piece;
			continue;
		}
		if ( $stack || ( $blocks && end( $blocks ) ) || '' === trim( $piece ) || ( $made[0] >= $caps[0] && $made[1] >= $caps[1] ) ) {
			$out .= $piece;
			continue;
		}
		// Every pattern's matches, in the order of the text; at one place the longest wins, and
		// a phrase inside one already passed over is not tried, as with a single pattern.
		$hits = array();
		foreach ( $map['patterns'] as $pattern ) {
			if ( preg_match_all( $pattern, $piece, $found, PREG_OFFSET_CAPTURE ) ) {
				foreach ( $found[0] as $f ) {
					$hits[] = $f;
				}
			}
		}
		usort(
			$hits,
			static function ( $a, $b ) {
				return $a[1] <=> $b[1] ?: strlen( $b[0] ) <=> strlen( $a[0] );
			}
		);
		$linked = false;
		$pos    = 0;
		foreach ( $hits as $h ) {
			list( $match, $at ) = $h;
			if ( $at < $pos ) {
				continue;
			}
			$hit = $map['phrases'][ injil_help_ctx_key( $match ) ] ?? null;
			if ( ! $hit ) {
				continue;
			}
			list( $url, $own ) = $hit;
			$kind = $own ? 1 : 0;
			$norm = injil_help_ctx_norm_url( $url );
			$pos  = $at + strlen( $match );
			if ( $made[ $kind ] >= $caps[ $kind ] || isset( $done[ $norm ] ) ) {
				continue;
			}
			$done[ $norm ] = true;
			++$made[ $kind ];
			$linked = true;
			$piece  = substr( $piece, 0, $at ) . ( $own
				? '<a class="ih-ctx-link" href="' . esc_url( $url ) . '">' . $match . '</a>'
				: '<a class="ih-ctx-link ih-ctx-link--sister" href="' . esc_url( $url ) . '" rel="noopener">' . $match . '</a>' ) . substr( $piece, $pos );
			break; // One new link in a piece of text, so at most one in a paragraph.
		}
		if ( $linked && $blocks ) {
			$blocks[ count( $blocks ) - 1 ] = true;
		}
		$out .= $piece;
	}
	return $out;
}

/** Whether the current page gets contextual links. */
function injil_help_ctx_wanted( $post ) {
	if ( ! $post instanceof WP_Post ) {
		return false;
	}
	$excluded = array_filter( array_map( 'trim', preg_split( '/\R/u', (string) injil_help_get_option( 'ctx_exclude' ) ) ) );
	$excluded = array_map( static function ( $s ) { return trim( strtolower( $s ), '/' ); }, $excluded );
	$wanted   = ! in_array( strtolower( $post->post_name ), $excluded, true ) && ! in_array( strtolower( trim( (string) get_page_uri( $post ), '/' ) ), $excluded, true );
	return (bool) apply_filters( 'injil_help_contextual_links_enabled', $wanted, $post );
}

/*
 * Priority 15: after do_blocks (9), wpautop and wptexturize (10) and shortcodes (11), so the
 * paragraphs exist and links made by shortcodes are skipped; before the share row (20), which
 * is not scanned. The breadcrumb (5) is a <nav> and is skipped. Block templates render the
 * Post Content block outside a classic loop, so in_the_loop() is not checked (as in inc/seo.php);
 * the queried post is matched instead.
 */
add_filter(
	'the_content',
	function ( $content ) {
		if ( is_admin() || is_feed() || ! is_singular() || is_front_page() || ! is_main_query() || doing_filter( 'get_the_excerpt' ) ) {
			return $content;
		}
		$post = get_queried_object();
		if ( ! $post instanceof WP_Post || get_the_ID() !== $post->ID || ! injil_help_ctx_wanted( $post ) ) {
			return $content;
		}
		return injil_help_ctx_apply( $content, (string) get_permalink( $post ) );
	},
	15
);
