<?php
/**
 * Self-test for the structured data (inc/schema.php, inc/schema-content.php, inc/schema-extend.php,
 * inc/rank-math.php). Run: php tests/schema-graph-test.php
 *
 * WordPress is stubbed. Every starter page the theme writes (inc/starter-content-pages.php) is built
 * from its definition, rendered and given its graph, once on its own and once merged into a stand-in
 * Rank Math graph. Each graph must encode as JSON and decode again; every @id must be unique; every
 * {"@id"} reference must name a node of the same graph, image addresses included, and an image
 * that is referenced must be an ImageObject with contentUrl, creditText and licence; every node must carry @type; the page, breadcrumb and article must carry their required
 * properties; and no value may hold the calligraphic mark, the owner's name or the banned word. Exit code 0 on success.
 *
 * @package injil-help
 */

// A command-line script: a web request to it gets a 404 and nothing runs.
if ( 'cli' !== PHP_SAPI ) {
	http_response_code( 404 );
	exit;
}

define( 'ABSPATH', __DIR__ . '/' );
define( 'RANK_MATH_VERSION', 'stub' );
define( 'IH_STANDIN_OWNER', 'Zzqowner' );
define( 'IH_STANDIN_DIGEST', sha1( 'zzqowner' ) );
// Theme Options with an Author name that holds the stand-in owner's name.
$GLOBALS['ih_options'] = array( 'injil_help_options' => array( 'author_name' => IH_STANDIN_OWNER . ' Abdullah' ) );
$GLOBALS['ih_theme'] = dirname( __DIR__ );
$GLOBALS['ih_post']  = null;
$GLOBALS['ih_posts'] = array();

class WP_Post {
	public $ID;
	public $post_name;
	public $post_type = 'page';
	public $post_parent = 0;
	public $post_title;
	public $post_content;
	public $post_excerpt = '';
	public $post_date = '2026-10-01 10:00:00';
	public $post_modified = '2026-11-01 10:00:00';
}
class WP_Error {}
class WP_Block_Patterns_Registry {
	public static function get_instance() {
		return new self();
	}
	public function get_registered( $slug ) {
		$name = preg_replace( '#^injil-help/#', '', $slug );
		$p    = function_exists( 'injil_help_pattern_file' ) ? injil_help_pattern_file( $name ) : null;
		return $p ? array( 'content' => $p['content'] ) : null;
	}
}

// ---------- WordPress stubs ----------
function add_action() {}
function add_filter() {}
function remove_action() {}
function register_activation_hook() {}
function apply_filters( $tag, $value ) {
	if ( 'injil_help_owner_digests' === $tag ) {
		return array( IH_STANDIN_DIGEST ); // a stand-in owner's name, so the test can put it in the input
	}
	return 'injil_help_output_schema' === $tag ? true : $value;
}
function do_action() {}
function get_stylesheet_directory() {
	return $GLOBALS['ih_theme'];
}
function get_stylesheet_directory_uri() {
	return 'https://injil.help/wp-content/themes/injil-help';
}
function get_stylesheet() {
	return 'injil-help';
}
function home_url( $path = '' ) {
	return 'https://injil.help' . ( '' === $path ? '' : '/' . ltrim( $path, '/' ) );
}
function get_bloginfo( $what ) {
	return array(
		'name'     => 'The Rightly-Guided Injīl',
		'language' => 'en-GB',
	)[ $what ] ?? '';
}
function __( $s ) {
	return $s;
}
function esc_html( $s ) {
	return htmlspecialchars( (string) $s, ENT_QUOTES );
}
function esc_attr__( $s ) {
	return $s;
}
function esc_attr( $s ) {
	return htmlspecialchars( (string) $s, ENT_QUOTES );
}
function esc_url( $s ) {
	return (string) $s;
}
function wp_kses_post( $s ) {
	return (string) $s;
}
function wp_strip_all_tags( $s ) {
	return trim( strip_tags( preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', (string) $s ) ) );
}
function wp_json_encode( $v, $f = 0 ) {
	return json_encode( $v, $f );
}
function wp_parse_url( $u, $c = -1 ) {
	return parse_url( $u, $c );
}
function wp_list_pluck( $list, $field ) {
	return array_map(
		function ( $i ) use ( $field ) {
			return $i[ $field ] ?? null;
		},
		$list
	);
}
function remove_accents( $s ) {
	$t = @iconv( 'UTF-8', 'ASCII//TRANSLIT//IGNORE', $s );
	return false === $t ? $s : $t;
}
function is_wp_error( $v ) {
	return $v instanceof WP_Error;
}
function number_format_i18n( $n ) {
	return number_format( (float) $n );
}
function sanitize_title( $s ) {
	return strtolower( trim( preg_replace( '/[^A-Za-z0-9]+/', '-', $s ), '-' ) );
}
function wp_parse_args( $a, $d ) {
	return array_merge( $d, $a );
}
function get_option( $k, $d = false ) {
	return $GLOBALS['ih_options'][ $k ] ?? $d;
}
function get_theme_mod( $k, $d = false ) {
	return $d;
}
function get_post_meta( $id = 0, $key = '', $single = false ) {
	if ( '_injil_help_commons' === $key ) {
		return $GLOBALS['ih_attachments'][ $id ] ?? '';
	}
	return '';
}
function has_site_icon() {
	return false;
}
function get_site_icon_url() {
	return '';
}
function get_queried_object() {
	return $GLOBALS['ih_post'];
}
function is_front_page() {
	return ! empty( $GLOBALS['ih_front'] );
}
function is_home() {
	return false;
}
function is_search() {
	return false;
}
function is_archive() {
	return false;
}
function is_404() {
	return false;
}
function is_singular() {
	return null !== $GLOBALS['ih_post'];
}
function is_page( $slug = '' ) {
	return $GLOBALS['ih_post'] && ( '' === $slug || $GLOBALS['ih_post']->post_name === $slug );
}
function get_post( $id ) {
	return $GLOBALS['ih_posts'][ $id ] ?? null;
}
function get_post_ancestors( $post ) {
	$out = array();
	while ( $post && $post->post_parent ) {
		$out[] = $post->post_parent;
		$post  = get_post( $post->post_parent );
	}
	return $out;
}
function get_post_field( $field, $id ) {
	$p = get_post( $id );
	return $p ? $p->$field : '';
}
function get_the_title( $post ) {
	$post = is_object( $post ) ? $post : get_post( $post );
	return $post ? $post->post_title : '';
}
function get_permalink( $post ) {
	$post  = is_object( $post ) ? $post : get_post( $post );
	$slugs = array();
	foreach ( array_reverse( get_post_ancestors( $post ) ) as $a ) {
		$slugs[] = get_post( $a )->post_name;
	}
	$slugs[] = $post->post_name;
	return home_url( '/' . implode( '/', $slugs ) . '/' );
}
function get_the_date( $f, $post ) {
	return date( 'c' === $f ? DATE_ATOM : $f, strtotime( $post->post_date ) );
}
function get_the_modified_date( $f, $post ) {
	return date( 'c' === $f ? DATE_ATOM : $f, strtotime( $post->post_modified ) );
}
// Featured images: $GLOBALS['ih_thumbs'][ post ID ] = array( attachment ID, uploaded address ).
// $GLOBALS['ih_attachments'][ attachment ID ] = the Commons file it was sideloaded from.
$GLOBALS['ih_thumbs']       = array();
$GLOBALS['ih_attachments']  = array();
function ih_thumb( $post ) {
	$id = is_object( $post ) ? $post->ID : (int) $post;
	return $GLOBALS['ih_thumbs'][ $id ] ?? null;
}
function has_post_thumbnail( $post = null ) {
	return null !== ih_thumb( $post ?? $GLOBALS['ih_post'] );
}
function get_the_post_thumbnail_url( $post, $size = '' ) {
	$t = ih_thumb( $post );
	return $t ? $t[1] : false;
}
function get_post_thumbnail_id( $post ) {
	$t = ih_thumb( $post );
	return $t ? $t[0] : 0;
}
function attachment_url_to_postid( $url ) {
	foreach ( $GLOBALS['ih_thumbs'] as $t ) {
		if ( $t[1] === $url ) {
			return $t[0];
		}
	}
	return 0;
}
function wp_get_attachment_caption( $id ) {
	return '';
}
function has_excerpt( $post ) {
	return '' !== (string) $post->post_excerpt;
}
function get_the_excerpt( $post ) {
	return (string) $post->post_excerpt;
}
function strip_shortcodes( $s ) {
	return $s;
}
function wp_get_document_title() {
	return '';
}
function get_block_template() {
	if ( empty( $GLOBALS['ih_front'] ) ) {
		return null;
	}
	return (object) array( 'content' => (string) file_get_contents( $GLOBALS['ih_theme'] . '/templates/front-page.html' ) );
}
function add_query_arg() {
	return '/';
}
function get_search_link() {
	return home_url( '/?s=' );
}
function wp_get_attachment_image_src() {
	return false;
}

/** A small block parser: enough of WordPress's grammar for the theme's own markup. */
function parse_blocks( $content ) {
	preg_match_all( '#<!--\s+(/)?wp:([a-z0-9/-]+)\s*(\{.*?\})?\s*(/)?-->#s', $content, $m, PREG_SET_ORDER | PREG_OFFSET_CAPTURE | PREG_UNMATCHED_AS_NULL );
	$frames = array( array( 'innerBlocks' => array(), 'innerContent' => array(), 'innerHTML' => '' ) );
	$pos    = 0;
	$text   = function ( $html ) use ( &$frames ) {
		if ( '' === $html ) {
			return;
		}
		$k = count( $frames ) - 1;
		$frames[ $k ]['innerHTML']     .= $html;
		$frames[ $k ]['innerContent'][] = $html;
	};
	foreach ( $m as $t ) {
		$text( substr( $content, $pos, $t[0][1] - $pos ) );
		$pos   = $t[0][1] + strlen( $t[0][0] );
		$name  = false === strpos( $t[2][0], '/' ) ? 'core/' . $t[2][0] : $t[2][0];
		$attrs = null !== $t[3][0] ? (array) json_decode( $t[3][0], true ) : array();
		if ( null !== $t[1][0] ) {
			if ( count( $frames ) > 1 ) {
				$block = array_pop( $frames );
				$k     = count( $frames ) - 1;
				$frames[ $k ]['innerBlocks'][]  = $block;
				$frames[ $k ]['innerContent'][] = null;
			}
			continue;
		}
		$block = array( 'blockName' => $name, 'attrs' => $attrs, 'innerBlocks' => array(), 'innerContent' => array(), 'innerHTML' => '' );
		if ( null !== $t[4][0] ) {
			$k = count( $frames ) - 1;
			$frames[ $k ]['innerBlocks'][]  = $block;
			$frames[ $k ]['innerContent'][] = null;
		} else {
			$frames[] = $block;
		}
	}
	$text( substr( $content, $pos ) );
	while ( count( $frames ) > 1 ) {
		$block = array_pop( $frames );
		$k     = count( $frames ) - 1;
		$frames[ $k ]['innerBlocks'][]  = $block;
		$frames[ $k ]['innerContent'][] = null;
	}
	return $frames[0]['innerBlocks'];
}
function render_block( $block ) {
	if ( 'core/pattern' === $block['blockName'] ) {
		$out = '';
		foreach ( injil_help_expand_patterns( array( $block ) ) as $b ) {
			$out .= render_block( $b );
		}
		return $out;
	}
	$out = '';
	$j   = 0;
	foreach ( $block['innerContent'] as $piece ) {
		$out .= null === $piece ? render_block( $block['innerBlocks'][ $j++ ] ) : $piece;
	}
	return $out;
}

// ---------- The theme ----------
require $GLOBALS['ih_theme'] . '/inc/options.php';
require $GLOBALS['ih_theme'] . '/inc/starter-content.php';
require $GLOBALS['ih_theme'] . '/inc/seo.php';
require $GLOBALS['ih_theme'] . '/inc/schema.php';
require $GLOBALS['ih_theme'] . '/inc/rank-math.php';
require $GLOBALS['ih_theme'] . '/inc/journal.php';

/** The starter page at a path, from the pages the test has built. */
function get_page_by_path( $path ) {
	return $GLOBALS['ih_by_path'][ $path ] ?? null;
}

$fail = 0;
$n    = 0;
function ih_fail( $page, $msg ) {
	global $fail;
	$fail++;
	echo "FAIL {$page}: {$msg}\n";
}

/** Check one graph. */
function ih_check_graph( $page, $graph, $rank_math = array() ) {
	$json = json_encode( array( '@context' => 'https://schema.org', '@graph' => array_values( $graph ) ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	if ( false === $json || null === json_decode( $json, true ) ) {
		ih_fail( $page, 'graph does not encode as JSON' );
		return;
	}
	if ( preg_match( '/لعنة|ih-lanah/u', $json ) ) {
		ih_fail( $page, 'the calligraphic mark is in the structured data' );
	}
	// SHA-1 of the owner's names, lower case, and of the stand-in: no word of any string may match.
	$owner = array_keys( injil_help_schema_owner_digests() );
	if ( ! in_array( IH_STANDIN_DIGEST, $owner, true ) ) {
		ih_fail( $page, 'the stand-in owner digest is not in the withheld list' );
	}
	// The author Person is the one node that names the owner (owner's decision, 4 November 2026):
	// it must carry the schema author's name, url and sameAs, and no other value may hold the name.
	$author_id   = injil_help_schema_ids()['author'];
	$author_seen = 0;
	$scan        = $graph;
	foreach ( $scan as $i => $node ) {
		if ( is_array( $node ) && ( $node['@id'] ?? '' ) === $author_id ) {
			$author_seen++;
			if ( 'Person' !== ( $node['@type'] ?? '' ) || INJIL_HELP_SCHEMA_AUTHOR_NAME !== ( $node['name'] ?? '' ) || 'https://menj.bio' !== ( $node['url'] ?? '' ) || ! in_array( 'https://menj.bio', (array) ( $node['sameAs'] ?? array() ), true ) ) {
				ih_fail( $page, 'the author Person lacks the schema author name, url or sameAs' );
			}
			unset( $scan[ $i ]['name'], $scan[ $i ]['url'], $scan[ $i ]['sameAs'] );
		} elseif ( is_array( $node ) && in_array( 'Person', (array) ( $node['@type'] ?? array() ), true ) && isset( $node['@id'], $rank_math[ $node['@id'] ] ) ) {
			ih_fail( $page, "Rank Math's own Person node survived the merge: " . $node['@id'] );
		}
	}
	// With Rank Math, the theme's subject Persons (ʿĪsā and the rest) keep their own @id.
	if ( ! empty( $rank_math ) ) {
		$subject = injil_help_schema_ids()['jesus'] ?? '';
		$found   = false;
		foreach ( $graph as $node ) {
			$found = $found || ( $subject && ( $node['@id'] ?? '' ) === $subject );
		}
		if ( $subject && ! $found ) {
			ih_fail( $page, 'the ʿĪsā subject node was lost in the Rank Math merge' );
		}
	}
	if ( 1 !== $author_seen ) {
		ih_fail( $page, "{$author_seen} author Person nodes, expected 1" );
	}
	// Every page, article and post names that Person as author.
	foreach ( $graph as $node ) {
		$types = (array) ( $node['@type'] ?? array() );
		if ( array_intersect( $types, array( 'WebPage', 'Article', 'BlogPosting', 'ScholarlyArticle', 'CollectionPage', 'ProfilePage', 'AboutPage', 'ContactPage', 'SearchResultsPage' ) ) ) {
			if ( ( $node['author']['@id'] ?? '' ) !== $author_id ) {
				ih_fail( $page, 'content node without the author Person: ' . ( $node['@id'] ?? implode( '/', $types ) ) );
			}
		}
	}
	$strings = array();
	array_walk_recursive(
		$scan,
		function ( $v, $k ) use ( &$strings ) {
			$strings[] = (string) $k;
			if ( is_string( $v ) ) {
				$strings[] = $v;
			}
		}
	);
	foreach ( $strings as $str ) {
		if ( array_intersect( $owner, array_map( 'sha1', preg_split( '/[^\p{L}]+/u', mb_strtolower( $str ), -1, PREG_SPLIT_NO_EMPTY ) ) ) ) {
			ih_fail( $page, "the owner's name is in the structured data" );
			break;
		}
	}
	if ( preg_match( '/\bhorn/i', $json ) ) {
		ih_fail( $page, 'the banned word is in the structured data' );
	}
	$ids = array();
	foreach ( $graph as $node ) {
		if ( empty( $node['@type'] ) ) {
			ih_fail( $page, 'node without @type: ' . substr( json_encode( $node ), 0, 120 ) );
		}
		if ( isset( $node['@id'] ) ) {
			if ( isset( $ids[ $node['@id'] ] ) ) {
				ih_fail( $page, 'duplicate @id ' . $node['@id'] );
			}
			$ids[ $node['@id'] ] = $node;
		}
	}
	// Nested nodes with an @id and more than the @id are definitions too.
	// A second definition of an @id, nested or not, is a duplicate.
	$nested = function ( $v ) use ( &$nested, &$ids, $page ) {
		if ( ! is_array( $v ) ) {
			return;
		}
		foreach ( $v as $c ) {
			if ( is_array( $c ) && isset( $c['@id'] ) && count( $c ) > 1 ) {
				if ( isset( $ids[ $c['@id'] ] ) ) {
					ih_fail( $page, 'duplicate @id (nested) ' . $c['@id'] );
				}
				$ids[ $c['@id'] ] = $c;
			}
			$nested( $c );
		}
	};
	foreach ( $graph as $node ) {
		$nested( $node );
	}
	$refs = array();
	injil_help_schema_refs( $graph, $refs );
	foreach ( array_keys( $refs ) as $ref ) {
		if ( ! isset( $ids[ $ref ] ) && ! isset( $rank_math[ $ref ] ) ) {
			ih_fail( $page, 'unresolved @id reference ' . $ref );
		} elseif ( isset( $ids[ $ref ] ) && preg_match( '#/wp-content/uploads/|Special:FilePath/#', $ref ) ) {
			$img = $ids[ $ref ];
			if ( 'ImageObject' !== ( $img['@type'] ?? '' ) ) {
				ih_fail( $page, 'image reference is not an ImageObject ' . $ref );
			}
			foreach ( array( 'contentUrl', 'creditText', 'license' ) as $p ) {
				if ( empty( $img[ $p ] ) ) {
					ih_fail( $page, "image {$ref} without {$p}" );
				}
			}
		}
	}
	// Article and every subtype of it (those Rank Math emits among them), nested ones included, carry a headline.
	$heads = function ( $v ) use ( &$heads, $page ) {
		if ( ! is_array( $v ) ) {
			return;
		}
		if ( array_intersect( (array) ( $v['@type'] ?? array() ), array( 'Article', 'BlogPosting', 'NewsArticle', 'ScholarlyArticle', 'TechArticle', 'Report', 'SocialMediaPosting', 'LiveBlogPosting', 'DiscussionForumPosting', 'AdvertiserContentArticle', 'SatiricalArticle', 'OpinionNewsArticle', 'ReportageNewsArticle', 'AnalysisNewsArticle', 'BackgroundNewsArticle', 'ReviewNewsArticle', 'AskPublicNewsArticle', 'APIReference' ) ) && empty( $v['headline'] ) ) {
			ih_fail( $page, 'article node without headline ' . ( $v['@id'] ?? ( $v['name'] ?? '' ) ) );
		}
		foreach ( $v as $c ) {
			$heads( $c );
		}
	};
	$heads( $graph );
	if ( false !== $rank_math && array() !== $rank_math ) {
		return;
	}
	$page_node = null;
	foreach ( $graph as $node ) {
		$types = (array) $node['@type'];
		if ( array_intersect( $types, array( 'WebPage', 'CollectionPage', 'AboutPage', 'ContactPage', 'ProfilePage', 'SearchResultsPage' ) ) ) {
			$page_node = $node;
		}
		if ( array_intersect( $types, array( 'Article', 'BlogPosting' ) ) ) {
			foreach ( array( 'headline', 'author', 'datePublished', 'image', 'mainEntityOfPage' ) as $p ) {
				if ( empty( $node[ $p ] ) ) {
					ih_fail( $page, "Article without {$p}" );
				}
			}
		}
		if ( in_array( 'BreadcrumbList', $types, true ) && empty( $node['itemListElement'] ) ) {
			ih_fail( $page, 'BreadcrumbList without items' );
		}
		if ( in_array( 'Quotation', $types, true ) && empty( $node['text'] ) ) {
			ih_fail( $page, 'Quotation without text ' . ( $node['@id'] ?? '' ) );
		}
		if ( in_array( 'ImageObject', $types, true ) && empty( $node['contentUrl'] ) ) {
			ih_fail( $page, 'ImageObject without contentUrl' );
		}
	}
	if ( ! $page_node ) {
		ih_fail( $page, 'no page node' );
	} else {
		foreach ( array( 'url', 'name', 'isPartOf', 'inLanguage', 'breadcrumb' ) as $p ) {
			if ( empty( $page_node[ $p ] ) ) {
				ih_fail( $page, "page node without {$p}" );
			}
		}
	}
}

// Every starter page, its parents first.
$defs   = injil_help_starter_pages();
$by     = array();
$id     = 1;
$counts = array( 'Quotation' => 0, 'DefinedTerm' => 0, 'Chapter' => 0, 'Event' => 0 );
foreach ( $defs as $def ) {
	$p               = new WP_Post();
	$p->ID           = $id++;
	$parts           = explode( '/', $def['path'] );
	$p->post_name    = end( $parts );
	$p->post_title   = $def['title'];
	$p->post_excerpt = (string) ( $def['excerpt'] ?? '' );
	$parent          = implode( '/', array_slice( $parts, 0, -1 ) );
	$p->post_parent  = isset( $by[ $parent ] ) ? $by[ $parent ]->ID : 0;
	$by[ $def['path'] ] = $p;
	$GLOBALS['ih_posts'][ $p->ID ] = $p;
}
$GLOBALS['ih_by_path'] = $by;
// Every page with an images.json entry has a sideloaded featured image, as on the live site. Half
// are stored in another format than the source (3.17.0 writes AVIF or WebP), and the figures in the
// body keep their Commons address, since the stub renders no localised <img>.
$att = 9000;
foreach ( injil_help_images() as $path => $img ) {
	if ( empty( $img['file'] ) || ! isset( $by[ $path ] ) ) {
		continue;
	}
	$att++;
	$name = str_replace( ' ', '_', $img['file'] );
	if ( 0 === $att % 2 ) {
		$name = preg_replace( '/\.[A-Za-z]+$/', '.avif', $name );
	}
	$GLOBALS['ih_attachments'][ $att ]           = $img['file'];
	$GLOBALS['ih_thumbs'][ $by[ $path ]->ID ]    = array( $att, 'https://injil.help/wp-content/uploads/2026/10/' . $name );
}
$featured_n = count( $GLOBALS['ih_thumbs'] );
/**
 * Every speakable cssSelector of a graph must match the page's markup: the content as rendered, inside
 * the .entry-content wrapper the page templates put round it (the front page has none). h1 comes from
 * the template's title block and is not looked for here.
 *
 * @return bool Whether the graph has a SpeakableSpecification.
 */
function ih_check_speakable( $where, $graph, $content, $is_front ) {
	$selectors = null;
	$find = function ( $v ) use ( &$find, &$selectors ) {
		if ( ! is_array( $v ) ) {
			return;
		}
		if ( ( $v['@type'] ?? '' ) === 'SpeakableSpecification' ) {
			$selectors = (array) $v['cssSelector'];
			return;
		}
		foreach ( $v as $c ) {
			$find( $c );
		}
	};
	$find( $graph );
	if ( null === $selectors ) {
		ih_fail( $where, 'no SpeakableSpecification' );
		return false;
	}
	$html = '';
	foreach ( injil_help_expand_patterns( parse_blocks( $content ) ) as $b ) {
		$html .= render_block( $b );
	}
	$doc = new DOMDocument();
	libxml_use_internal_errors( true );
	$doc->loadHTML( '<?xml encoding="utf-8"?><body>' . ( $is_front ? $html : '<div class="entry-content">' . $html . '</div>' ) . '</body>' );
	libxml_clear_errors();
	$xp = new DOMXPath( $doc );
	foreach ( $selectors as $sel ) {
		if ( 'h1' === $sel ) {
			continue;
		}
		if ( '.entry-content > p' === $sel ) {
			$q = "//div[@class='entry-content']/p";
		} elseif ( preg_match( '/^\.([a-z0-9-]+)$/', $sel, $m ) ) {
			$q = "//*[contains(concat(' ', normalize-space(@class), ' '), ' {$m[1]} ')]";
		} else {
			ih_fail( $where, "speakable selector {$sel} is not one the test can check" );
			continue;
		}
		if ( 0 === $xp->query( $q )->length ) {
			ih_fail( $where, "speakable selector {$sel} matches nothing on the page" );
		}
	}
	return true;
}

foreach ( $defs as $def ) {
	$p               = $by[ $def['path'] ];
	$p->post_content = injil_help_page_content( $def );
	$GLOBALS['ih_post'] = $p;
	$GLOBALS['ih_rm']   = false;
	$graph = injil_help_schema_graph();
	ih_check_graph( $def['path'], $graph );
	ih_check_speakable( $def['path'], $graph, $p->post_content, false );
	$json = json_encode( $graph, JSON_UNESCAPED_UNICODE );
	$counts['Quotation']   += substr_count( $json, '"@type":"Quotation"' );
	$counts['DefinedTerm'] += substr_count( $json, '"@type":"DefinedTerm"' );
	$counts['Chapter']     += substr_count( $json, '"@type":"Chapter"' );
	$counts['Event']       += substr_count( $json, '"@type":"Event"' );

	// Merged into a stand-in Rank Math graph: one graph, no second copy of any @id.
	$GLOBALS['ih_rm'] = true;
	$url  = get_permalink( $p );
	$rm   = array(
		'publisher' => array( '@type' => 'Organization', '@id' => 'rm#org', 'name' => 'x' ),
		'WebSite'   => array( '@type' => 'WebSite', '@id' => 'rm#website' ),
		'WebPage'   => array( '@type' => 'WebPage', '@id' => $url . '#webpage', 'url' => $url ),
		'Article'   => array( '@type' => 'Article', '@id' => $url . '#richSnippet', 'headline' => $def['title'], 'author' => array( '@id' => 'rm#author' ) ),
		'author'    => array( '@type' => 'Person', '@id' => 'rm#author', 'name' => IH_STANDIN_OWNER, 'description' => 'Written by ' . IH_STANDIN_OWNER . '.' ),
	);
	// Rank Math refers to the featured image as its primary image without defining it.
	$thumb = ih_thumb( $p );
	if ( $thumb ) {
		$rm['WebPage']['primaryImageOfPage'] = array( '@id' => $thumb[1] );
		$rm['Article']['image']              = array( '@id' => $thumb[1] );
	}
	$rm_ids = array();
	foreach ( $rm as $node ) {
		$rm_ids[ $node['@id'] ] = true;
	}
	$merged = injil_help_rank_math_json_ld( $rm );
	ih_check_graph( $def['path'] . ' (Rank Math)', $merged, $rm_ids );
	$n++;
}

// masthead and verificationFactCheckingPolicy are NewsMediaOrganization properties: the node that carries them has that type.
foreach ( injil_help_schema_graph() as $node ) {
	if ( ( isset( $node['masthead'] ) || isset( $node['verificationFactCheckingPolicy'] ) ) && ! in_array( 'NewsMediaOrganization', (array) $node['@type'], true ) ) {
		ih_fail( 'organization', ( $node['@id'] ?? '' ) . ' carries masthead or verificationFactCheckingPolicy without the NewsMediaOrganization type' );
	}
	if ( ( $node['@id'] ?? '' ) === injil_help_schema_ids()['org'] && ( ( $node['masthead'] ?? '' ) !== home_url( '/about/' ) || ( $node['verificationFactCheckingPolicy'] ?? '' ) !== home_url( '/method/' ) || ! in_array( 'Organization', (array) $node['@type'], true ) ) ) {
		ih_fail( 'organization', 'the Organization lost its type or its policy addresses' );
	}
}

// The front page is rendered from its template, which has no .entry-content.
$GLOBALS['ih_front'] = true;
$GLOBALS['ih_rm']    = false;
$front_graph         = injil_help_schema_graph();
$front_html          = (string) file_get_contents( $GLOBALS['ih_theme'] . '/templates/front-page.html' );
ih_check_speakable( 'front page', $front_graph, $front_html, true );

// A class token is matched whole: a paragraph with only "ih-intro-extra" gives no .ih-intro selector.
$near = injil_help_schema_speakable_selectors( array(), '<p class="ih-intro-extra wp-block-paragraph">x</p><p class="lead ih-cover-lede-x">y</p>', false );
$exact = injil_help_schema_speakable_selectors( array(), '<p class="wp-block-paragraph ih-intro">x</p><p class="ih-cover-lede">y</p>', false );
if ( array( 'h1' ) !== $near || array( 'h1', '.ih-cover-lede', '.ih-intro' ) !== $exact ) {
	ih_fail( 'speakable', 'class tokens are not matched whole: ' . json_encode( array( $near, $exact ) ) );
}
$GLOBALS['ih_front'] = false;

if ( $featured_n < 60 ) {
	ih_fail( 'images', "{$featured_n} pages with a featured image, expected at least 60" );
}
// The two pages reported on the live site: their primary image resolves, in the format it was stored in.
foreach ( array( 'islamic-dilemma', 'gospels/mark' ) as $path ) {
	$GLOBALS['ih_post'] = $by[ $path ];
	$GLOBALS['ih_rm']   = false;
	$thumb = ih_thumb( $by[ $path ] );
	$found = null;
	foreach ( injil_help_schema_graph() as $node ) {
		if ( ( $node['@id'] ?? '' ) === $thumb[1] ) {
			$found = $node;
		}
	}
	$want = preg_match( '/\.avif$/', $thumb[1] ) ? 'image/avif' : '';
	if ( ! $found ) {
		ih_fail( $path, 'no ImageObject for the featured image' );
	} elseif ( $want && ( $found['encodingFormat'] ?? '' ) !== $want ) {
		ih_fail( $path, 'featured image encodingFormat is not ' . $want );
	}
}
// One image, one node: a body figure of the featured file is that node, matched on the file and
// never on its licence link, so a figure whose link is missing or different still merges.
$keys = array(
	'https://commons.wikimedia.org/wiki/Special:FilePath/Maghribi_script_sura_5.jpg?width=900' => 'maghribi_script_sura_5',
	'https://injil.help/wp-content/uploads/2026/10/Maghribi_script_sura_5-1024x768.avif'    => 'maghribi_script_sura_5',
	'https://injil.help/wp-content/uploads/2026/10/Maghribi_script_sura_5-scaled.jpg'       => 'maghribi_script_sura_5',
	'Maghribi script sura 5.jpg'                                                            => 'maghribi_script_sura_5',
);
foreach ( $keys as $src => $want ) {
	if ( injil_help_schema_file_key( $src ) !== $want ) {
		ih_fail( 'file key', $src . ' gives ' . injil_help_schema_file_key( $src ) );
	}
}
foreach ( array( 'islamic-dilemma' => 'changed', 'gospels/mark' => 'removed' ) as $path => $link ) {
	$p    = $by[ $path ];
	$keep = $p->post_content;
	if ( 'removed' === $link ) {
		$p->post_content = preg_replace( '#<a href="https://commons\.wikimedia\.org/wiki/File:[^"]*">(.*?)</a>#s', '$1', $keep );
	} else {
		$p->post_content = str_replace( 'https://commons.wikimedia.org/wiki/File:', 'https://commons.wikimedia.org/wiki/File:Other_', $keep );
	}
	$GLOBALS['ih_post'] = $p;
	$GLOBALS['ih_rm']   = false;
	$thumb = ih_thumb( $p );
	$same  = 0;
	foreach ( injil_help_schema_graph() as $node ) {
		if ( 'ImageObject' === ( $node['@type'] ?? '' ) && injil_help_schema_file_key( $node['contentUrl'] ?? '' ) === injil_help_schema_file_key( $thumb[1] ) ) {
			$same++;
		}
	}
	if ( 1 !== $same ) {
		ih_fail( $path, "{$same} ImageObjects for the featured file with its licence link {$link}, expected 1" );
	}
	$p->post_content = $keep;
}
echo "Featured images: {$featured_n}\n";

// Pages that must carry the new nodes.
$expect = array(
	'resources/glossary'                  => array( '"inDefinedTermSet"' ),
	'resources/timeline'                  => array( '#timeline"', '"startDate"' ),
	'gospels/thomas/sayings'              => array( '#sayings"', '"Saying 1' ),
	'gospels/thomas/sayings-patterson-meyer' => array( '#sayings"' ),
	'quran/passages'                      => array( '#quran-surah-', '"translationOfWork"' ),
);
foreach ( $expect as $path => $needles ) {
	if ( ! isset( $by[ $path ] ) ) {
		ih_fail( $path, 'page missing from the starter pages' );
		continue;
	}
	$GLOBALS['ih_post'] = $by[ $path ];
	$GLOBALS['ih_rm']   = false;
	$json = json_encode( injil_help_schema_graph(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	foreach ( $needles as $needle ) {
		if ( false === strpos( $json, $needle ) ) {
			ih_fail( $path, "expected {$needle} in the graph" );
		}
	}
}

// The Journal posts (inc/data/posts.json): each a BlogPosting in the one graph, its date the entry's,
// and its breadcrumb Home, Journal, the post.
$journal_n = 0;
foreach ( injil_help_journal_entries() as $entry ) {
	$p               = new WP_Post();
	$p->ID           = $id++;
	$p->post_type    = 'post';
	$p->post_name    = $entry['slug'];
	$p->post_title   = $entry['title'];
	$p->post_excerpt = (string) $entry['excerpt'];
	$p->post_date    = $entry['date'];
	$p->post_modified = $entry['date'];
	$p->post_content = injil_help_journal_content( $entry );
	$GLOBALS['ih_posts'][ $p->ID ] = $p;
	$GLOBALS['ih_post'] = $p;
	$GLOBALS['ih_rm']   = false;
	$label = 'journal/' . $entry['slug'];
	$graph = injil_help_schema_graph();
	ih_check_graph( $label, $graph );
	$posting = null;
	$crumbs  = null;
	foreach ( $graph as $node ) {
		if ( in_array( 'BlogPosting', (array) $node['@type'], true ) ) {
			$posting = $node;
		}
		if ( in_array( 'BreadcrumbList', (array) $node['@type'], true ) ) {
			$crumbs = $node;
		}
	}
	if ( ! $posting ) {
		ih_fail( $label, 'no BlogPosting node' );
	} elseif ( 0 !== strpos( $posting['datePublished'], substr( $entry['date'], 0, 10 ) ) ) {
		ih_fail( $label, 'datePublished is not the entry date' );
	} elseif ( empty( $posting['citation'] ) ) {
		ih_fail( $label, 'BlogPosting without citation' );
	}
	if ( ! $crumbs || 3 !== count( $crumbs['itemListElement'] ) || 'Journal' !== $crumbs['itemListElement'][1]['name'] ) {
		ih_fail( $label, 'breadcrumb is not Home, Journal, post' );
	}
	if ( false === strpos( injil_help_breadcrumb_html( $p ), '>Journal</a>' ) ) {
		ih_fail( $label, 'visible breadcrumb lacks the Journal' );
	}
	$GLOBALS['ih_rm'] = true;
	$url    = get_permalink( $p );
	$rm     = array(
		'publisher' => array( '@type' => 'Organization', '@id' => 'rm#org', 'name' => 'x' ),
		'WebSite'   => array( '@type' => 'WebSite', '@id' => 'rm#website' ),
		'WebPage'   => array( '@type' => 'WebPage', '@id' => $url . '#webpage', 'url' => $url ),
		'Article'   => array( '@type' => 'BlogPosting', '@id' => $url . '#richSnippet', 'headline' => $entry['title'], 'author' => array( '@id' => 'rm#author' ) ),
		'author'    => array( '@type' => 'Person', '@id' => 'rm#author', 'name' => 'x' ),
	);
	$rm_ids = array();
	foreach ( $rm as $node ) {
		$rm_ids[ $node['@id'] ] = true;
	}
	ih_check_graph( $label . ' (Rank Math)', injil_help_rank_math_json_ld( $rm ), $rm_ids );
	$journal_n++;
}
if ( $journal_n < 8 ) {
	ih_fail( 'journal', "{$journal_n} Journal posts, expected at least 8" );
}
echo "Journal posts: {$journal_n}\n";

// sameAs: the Organization and the author Person carry https://menj.bio.
// A stored list without the profile still gets it: the fallback is tested, not the default.
$ih_saved_options = $GLOBALS['ih_options'];
$GLOBALS['ih_options']['injil_help_options']['same_as'] = 'https://apostleofdoom.org';
$ih_same_graph = injil_help_schema_site_nodes();
$GLOBALS['ih_options'] = $ih_saved_options;
foreach ( array( 'org', 'author' ) as $ih_same_key ) {
	$ih_same_found = false;
	foreach ( $ih_same_graph as $node ) {
		if ( isset( $node['@id'] ) && injil_help_schema_ids()[ $ih_same_key ] === $node['@id'] ) {
			$ih_same_found = in_array( 'https://menj.bio', (array) ( $node['sameAs'] ?? array() ), true );
		}
	}
	if ( ! $ih_same_found ) {
		ih_fail( 'sameAs', "the {$ih_same_key} node does not list https://menj.bio" );
	}
}

// The header menu: each SiteNavigationElement is named by its label alone, without the Greek or Arabic gloss.
$nav_names = array();
foreach ( injil_help_schema_navigation()['itemListElement'] as $li ) {
	$nav_names[] = $li['item']['name'];
}
$nav_want = array( 'Islamic Dilemma', 'The Gospels', 'Paul of Tarsus', "Jesus in the Qur'an", 'The Gospel', 'Resources', 'Journal', 'FAQs' );
if ( $nav_names !== $nav_want ) {
	ih_fail( 'navigation', 'SiteNavigationElement names are ' . implode( ' / ', $nav_names ) );
}
if ( ! in_array( 'Journal', $nav_names, true ) ) {
	ih_fail( 'navigation', 'no SiteNavigationElement is named "Journal"' );
}

// The totals of 3.15.0 are the floor: fewer means a page lost nodes.
$floor = array( 'pages' => 181, 'Quotation' => 632, 'DefinedTerm' => 71, 'Chapter' => 51, 'Event' => 49 );
if ( $n < $floor['pages'] ) {
	ih_fail( 'totals', "{$n} pages, expected at least {$floor['pages']}" );
}
foreach ( $counts as $type => $count ) {
	if ( $count < $floor[ $type ] ) {
		ih_fail( 'totals', "{$count} {$type} nodes, expected at least {$floor[ $type ]}" );
	}
}
echo "Pages: {$n}; Quotation {$counts['Quotation']}, DefinedTerm {$counts['DefinedTerm']}, Chapter {$counts['Chapter']}, Event {$counts['Event']}\n";
echo $fail ? "FAILED: {$fail}\n" : "OK\n";
exit( $fail ? 1 : 0 );
