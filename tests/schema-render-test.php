<?php
/**
 * Regression test for the fatal error of 29 October and 1 November 2026 ("Attempt to assign
 * property "parsed_block" on null" in class-wp-block.php, from render_block() in inc/schema.php),
 * for the taxonomy.php warning from get_term_link(), and for the guard in functions.php that
 * sends a 503 page while the theme is half uploaded. Run: php tests/schema-render-test.php
 *
 * WordPress is stubbed. render_block() here follows core's WP_Block::render(): it walks
 * innerContent and takes the next inner block for every null, and when there is none it assigns
 * to a property of null, which throws the same Error core throws. The test shows that a malformed
 * block (more nulls than inner blocks, or more inner blocks than nulls) breaks the raw render, and
 * that the theme's expansion and render for the structured data keep every inner block and never
 * throw. Exit code 0 on success.
 *
 * @package injil-help
 */

// A command-line script: a web request to it gets a 404 and nothing runs.
if ( 'cli' !== PHP_SAPI ) {
	http_response_code( 404 );
	exit;
}

$ih_theme = dirname( __DIR__ );

// ---------- Child runs: functions.php loaded from a copy of the theme with inc/ broken ----------
if ( isset( $argv[1] ) && 'child' === $argv[1] ) {
	define( 'ABSPATH', $argv[2] . '/' );
	define( 'WP_CONTENT_DIR', $argv[2] . '/wp-content' );
	$GLOBALS['ih_status'] = 0;
	function wp_doing_cron() { return false; }
	function wp_doing_ajax() { return false; }
	function status_header( $code ) { $GLOBALS['ih_status'] = $code; }
	function wp_normalize_path( $p ) { return str_replace( '\\', '/', $p ); }
	ini_set( 'error_log', $argv[2] . '/error.log' );
	if ( 'serve' === $argv[3] ) {
		// Load the guard, then send the page as a visitor would get it.
		register_shutdown_function(
			function () {
				$out = ob_get_clean();
				echo json_encode( array( 'status' => $GLOBALS['ih_status'], 'body' => $out ) );
			}
		);
		require $argv[2] . '/theme/functions.php';
		ob_start();
		injil_help_serve_incomplete();
	}
	$loaded = require $argv[2] . '/theme/functions.php';
	echo json_encode(
		array(
			'files'  => injil_help_theme_files(),
			'loaded' => function_exists( 'injil_help_child_loaded' ),
			'status' => $GLOBALS['ih_status'],
			'log'    => (string) @file_get_contents( $argv[2] . '/error.log' ),
		)
	);
	exit;
}

define( 'ABSPATH', __DIR__ . '/' );

class WP_Term {
	public $term_id  = 7;
	public $slug     = 'news';
	public $taxonomy = 'category';
}
class WP_Post_Type {
	public $name = 'book';
}
class WP_Error {}
class WP_Block_Patterns_Registry {
	public static function get_instance() {
		return new self();
	}
	public function get_registered( $slug ) {
		return $GLOBALS['ih_patterns'][ $slug ] ?? null;
	}
}
$GLOBALS['ih_patterns'] = array(
	// One section: a group and the whitespace after it, as parse_blocks() returns a pattern.
	'injil-help/one' => array( 'content' => '<!-- wp:group --><div class="one"><!-- wp:paragraph --><p>Alpha</p><!-- /wp:paragraph --></div><!-- /wp:group -->' . "\n" ),
	// Two sections and a pattern that names itself.
	'injil-help/two' => array( 'content' => '<!-- wp:paragraph --><p>Beta</p><!-- /wp:paragraph -->' . "\n" . '<!-- wp:paragraph --><p>Gamma</p><!-- /wp:paragraph --><!-- wp:pattern {"slug":"injil-help/two"} /-->' ),
);
$GLOBALS['ih_taxonomies'] = array( 'category' );
$GLOBALS['ih_warnings']   = array();
set_error_handler(
	function ( $no, $msg, $file, $line ) {
		$GLOBALS['ih_warnings'][] = $msg . ' (' . basename( $file ) . ':' . $line . ')';
		return true;
	}
);

function add_action() {}
function add_filter() {}
function apply_filters( $tag, $value ) {
	return $value;
}
function get_stylesheet_directory() {
	return $GLOBALS['ih_theme'];
}
function wp_strip_all_tags( $s ) {
	return trim( strip_tags( (string) $s ) );
}
function taxonomy_exists( $t ) {
	return in_array( $t, $GLOBALS['ih_taxonomies'], true );
}
function is_wp_error( $v ) {
	return $v instanceof WP_Error;
}
/** As core: the term's taxonomy object is read without a check, and is false when not registered. */
function get_term_link( $term ) {
	if ( ! is_object( $term ) ) {
		return new WP_Error();
	}
	$t = taxonomy_exists( $term->taxonomy ?? '' ) ? (object) array( 'query_var' => $term->taxonomy ) : false;
	return 'https://example.test/?' . $t->query_var . '=' . $term->slug; // warns on false, as taxonomy.php:4751 does
}

/** Enough of WordPress's block grammar for the test's markup, with core's whitespace blocks. */
function parse_blocks( $content ) {
	preg_match_all( '#<!--\s+(/)?wp:([a-z0-9/-]+)\s*(\{.*?\})?\s*(/)?-->#s', $content, $m, PREG_SET_ORDER | PREG_OFFSET_CAPTURE | PREG_UNMATCHED_AS_NULL );
	$frames = array( array( 'innerBlocks' => array(), 'innerContent' => array(), 'innerHTML' => '' ) );
	$pos    = 0;
	$text   = function ( $html ) use ( &$frames ) {
		if ( '' === $html ) {
			return;
		}
		$k = count( $frames ) - 1;
		if ( 0 === $k ) {
			$frames[0]['innerBlocks'][] = array( 'blockName' => null, 'attrs' => array(), 'innerBlocks' => array(), 'innerHTML' => $html, 'innerContent' => array( $html ) );
			return;
		}
		$frames[ $k ]['innerHTML']     .= $html;
		$frames[ $k ]['innerContent'][] = $html;
	};
	foreach ( $m as $t ) {
		$text( substr( $content, $pos, $t[0][1] - $pos ) );
		$pos   = $t[0][1] + strlen( $t[0][0] );
		$name  = false === strpos( $t[2][0], '/' ) ? 'core/' . $t[2][0] : $t[2][0];
		$attrs = null !== $t[3][0] ? (array) json_decode( $t[3][0], true ) : array();
		if ( null !== $t[1][0] ) {
			$block = array_pop( $frames );
			$k     = count( $frames ) - 1;
			$frames[ $k ]['innerBlocks'][] = $block;
			if ( $k > 0 ) {
				$frames[ $k ]['innerContent'][] = null;
			}
			continue;
		}
		$block = array( 'blockName' => $name, 'attrs' => $attrs, 'innerBlocks' => array(), 'innerHTML' => '', 'innerContent' => array() );
		if ( null !== $t[4][0] ) {
			$k = count( $frames ) - 1;
			$frames[ $k ]['innerBlocks'][] = $block;
			if ( $k > 0 ) {
				$frames[ $k ]['innerContent'][] = null;
			}
		} else {
			$frames[] = $block;
		}
	}
	$text( substr( $content, $pos ) );
	return $frames[0]['innerBlocks'];
}

/** Core's WP_Block::render(), reduced to how it pairs innerContent with innerBlocks. */
class IH_Test_Block {
	public $parsed_block;
	public $inner_blocks = array();
	public $inner_content;
	public function __construct( $block ) {
		$this->parsed_block  = $block;
		$this->inner_content = $block['innerContent'];
		foreach ( $block['innerBlocks'] as $inner ) {
			$this->inner_blocks[] = new self( $inner );
		}
	}
	public function render() {
		if ( 'test/explode' === $this->parsed_block['blockName'] ) {
			throw new RuntimeException( 'a block callback failed' );
		}
		$out   = '';
		$index = 0;
		foreach ( $this->inner_content as $chunk ) {
			if ( is_string( $chunk ) ) {
				$out .= $chunk;
				continue;
			}
			$inner = $this->inner_blocks[ $index ] ?? null; // core: "Undefined array key" here
			$inner->parsed_block = $inner->parsed_block; // core, class-wp-block.php:614: an Error on null
			$out .= $inner->render();
			++$index;
		}
		return $out;
	}
}
function render_block( $block ) {
	return ( new IH_Test_Block( $block ) )->render();
}

$GLOBALS['ih_theme'] = $ih_theme;
require $ih_theme . '/inc/seo.php';
require $ih_theme . '/inc/schema.php';

$fail = 0;
function ih_check( $ok, $what ) {
	global $fail;
	if ( ! $ok ) {
		++$fail;
		fwrite( STDERR, "FAIL: $what\n" );
	}
}
/** Whether innerContent holds exactly one null per inner block, at every depth. */
function ih_balanced( $block ) {
	$nulls = count( array_filter( $block['innerContent'], 'is_null' ) );
	if ( count( $block['innerBlocks'] ) !== $nulls ) {
		return false;
	}
	foreach ( $block['innerBlocks'] as $inner ) {
		if ( ! ih_balanced( $inner ) ) {
			return false;
		}
	}
	return true;
}
function ih_para( $text ) {
	return array( 'blockName' => 'core/paragraph', 'attrs' => array(), 'innerBlocks' => array(), 'innerHTML' => "<p>$text</p>", 'innerContent' => array( "<p>$text</p>" ) );
}

// 1. More nulls than inner blocks: the shape of the production fatal.
$short = array(
	'blockName'    => 'core/group',
	'attrs'        => array(),
	'innerBlocks'  => array( ih_para( 'First' ) ),
	'innerHTML'    => '<div></div>',
	'innerContent' => array( '<div>', null, null, null, '</div>' ),
);
$error = '';
try {
	render_block( $short );
} catch ( Throwable $e ) {
	$error = $e->getMessage();
}
ih_check( false !== strpos( $error, 'Attempt to assign property "parsed_block" on null' ), "the stub reproduces the fatal (got: $error)" );
$GLOBALS['ih_warnings'] = array();
$html = '';
try {
	$html = injil_help_schema_render( $short );
} catch ( Throwable $e ) {
	ih_check( false, 'injil_help_schema_render() threw: ' . $e->getMessage() );
}
ih_check( '<div><p>First</p></div>' === $html, "a short block renders its one inner block ($html)" );
ih_check( array() === $GLOBALS['ih_warnings'], 'no warning while rendering a short block: ' . implode( '; ', $GLOBALS['ih_warnings'] ) );

// 2. More inner blocks than nulls: core renders only the first and drops the rest.
$long = array(
	'blockName'    => 'core/details',
	'attrs'        => array( 'className' => 'is-style-faq' ),
	'innerBlocks'  => array( ih_para( 'One' ), ih_para( 'Two' ), ih_para( 'Three' ) ),
	'innerHTML'    => '<details><summary>Why?</summary></details>',
	'innerContent' => array( '<details><summary>Why?</summary>', null, '</details>' ),
);
ih_check( false === strpos( render_block( $long ), 'Three' ), 'the raw render of a long block loses its last inner block' );
$html = injil_help_schema_render( $long );
ih_check( false !== strpos( $html, 'One' ) && false !== strpos( $html, 'Two' ) && false !== strpos( $html, 'Three' ), "a long block renders all three inner blocks ($html)" );
ih_check( ih_balanced( injil_help_schema_normalise_block( $long ) ), 'a long block is balanced after normalising' );
$items = array();
injil_help_collect_faq( injil_help_expand_patterns( array( $long ) ), $items );
ih_check( array( array( 'Why?', 'One Two Three' ) ) === $items, 'the FAQ answer holds every inner block: ' . json_encode( $items ) );

// 3. A short block deep inside a page reaches the FAQ and quotation readers without a fatal.
$quote = array(
	'blockName'    => 'core/quote',
	'attrs'        => array(),
	'innerBlocks'  => array( $short ),
	'innerHTML'    => '<blockquote><cite>Someone</cite></blockquote>',
	'innerContent' => array( '<blockquote>', null, null, '<cite>Someone</cite></blockquote>' ),
);
$quotes = array();
try {
	injil_help_collect_quotes( array( $quote ), $quotes );
} catch ( Throwable $e ) {
	ih_check( false, 'injil_help_collect_quotes() threw: ' . $e->getMessage() );
}
ih_check( 1 === count( $quotes ) && 'First' === $quotes[0]['text'], 'the quotation is read from a short block: ' . json_encode( $quotes ) );

// 4. The front-page template: a group of pattern references, one of them not registered.
$template = '<!-- wp:group {"tagName":"main"} --><main class="wp-block-group"><!-- wp:pattern {"slug":"injil-help/one"} /-->' . "\n"
	. '<!-- wp:pattern {"slug":"injil-help/gone"} /-->' . "\n"
	. '<!-- wp:pattern {"slug":"injil-help/two"} /-->' . "\n"
	. '<!-- wp:pattern {"slug":"injil-help/one"} /--></main><!-- /wp:group -->';
$parsed = parse_blocks( $template );
// What the expansion did until 3.25.3 when patterns were missing: inner blocks dropped, the four
// nulls of innerContent left as they were.
$main                = $parsed[0];
$main['innerBlocks'] = array_slice( $main['innerBlocks'], 0, 2 );
$error = '';
try {
	render_block( $main );
} catch ( Throwable $e ) {
	$error = $e->getMessage();
}
ih_check( '' !== $error, 'a front page with patterns missing fatals under the old expansion' );
$expanded = injil_help_expand_patterns( $parsed );
ih_check( 1 === count( $expanded ) && ih_balanced( $expanded[0] ), 'the expanded front page has one null per inner block' );
$GLOBALS['ih_warnings'] = array();
$html = '';
try {
	foreach ( $expanded as $b ) {
		$html .= render_block( $b ); // the raw render, as core would run it
	}
} catch ( Throwable $e ) {
	ih_check( false, 'the expanded front page threw: ' . $e->getMessage() );
}
ih_check( 2 === substr_count( $html, 'Alpha' ) && false !== strpos( $html, 'Beta' ) && false !== strpos( $html, 'Gamma' ), "every registered pattern is rendered in its place ($html)" );
ih_check( strpos( $html, 'Beta' ) < strpos( $html, 'Gamma' ) && strpos( $html, 'Gamma' ) < strrpos( $html, 'Alpha' ), 'the patterns keep their order' );
ih_check( array() === $GLOBALS['ih_warnings'], 'no warning on the front page: ' . implode( '; ', $GLOBALS['ih_warnings'] ) );

// 5. A render that throws for its own reasons falls back to the block's markup.
$explode = array(
	'blockName'    => 'test/explode',
	'attrs'        => array(),
	'innerBlocks'  => array( ih_para( 'Kept' ) ),
	'innerHTML'    => '<div></div>',
	'innerContent' => array( '<div>', null, '</div>' ),
);
ini_set( 'error_log', '/dev/null' );
ih_check( '<div><p>Kept</p></div>' === injil_help_schema_render( $explode ), 'a block that throws is read from its markup' );
ih_check( '' === injil_help_schema_render( null ), 'a value that is no block renders as nothing' );

// 6. get_term_link() is called only for a term of a registered taxonomy.
$GLOBALS['ih_warnings'] = array();
$term                   = new WP_Term();
ih_check( 'https://example.test/?category=news' === injil_help_term_link( $term ), 'a category links to its archive' );
$orphan           = new WP_Term();
$orphan->taxonomy = 'topic';
ih_check( '' === injil_help_term_link( $orphan ), 'a term of a taxonomy no longer registered has no link' );
ih_check( '' === injil_help_term_link( new WP_Post_Type() ), 'a post type archive has no term link' );
ih_check( '' === injil_help_term_link( null ), 'a date archive has no term link' );
ih_check( array() === $GLOBALS['ih_warnings'], 'no taxonomy warning: ' . implode( '; ', $GLOBALS['ih_warnings'] ) );

// 7. functions.php with the theme half uploaded sends a 503 page.
$inc = array_map( fn( $f ) => basename( $f, '.php' ), glob( $ih_theme . '/inc/*.php' ) );
sort( $inc );
$child = function ( $mode, $setup ) use ( $ih_theme ) {
	$dir = sys_get_temp_dir() . '/ih-render-' . getmypid() . '-' . bin2hex( random_bytes( 4 ) );
	mkdir( $dir . '/theme/inc', 0700, true );
	mkdir( $dir . '/wp-content', 0700 );
	copy( $ih_theme . '/functions.php', $dir . '/theme/functions.php' );
	$setup( $dir );
	$out = shell_exec( escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( __FILE__ ) . ' child ' . escapeshellarg( $dir ) . ' ' . escapeshellarg( $mode ) . ' 2>&1' );
	foreach ( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::CHILD_FIRST ) as $f ) {
		$f->isDir() ? rmdir( (string) $f ) : unlink( (string) $f );
	}
	rmdir( $dir );
	return json_decode( (string) $out, true ) ?? array( 'raw' => $out );
};
$all = function ( $dir ) use ( $inc ) {
	foreach ( $inc as $name ) {
		file_put_contents( $dir . '/theme/inc/' . $name . '.php', "<?php\n\$GLOBALS['injil_help_loaded'][ basename( __FILE__, '.php' ) ] = true;\n" );
	}
};

$r     = $child( 'load', function ( $dir ) {} );
$files = array_merge( $r['files']['load'] ?? array(), $r['files']['also'] ?? array() );
sort( $files );
ih_check( $inc === $files, 'injil_help_theme_files() names every file in inc/: ' . json_encode( array_values( array_diff( $inc, $files ) ) ) . ' missing, ' . json_encode( array_values( array_diff( $files, $inc ) ) ) . ' extra' );
ih_check( false !== strpos( $r['log'] ?? '', 'inc/options.php is missing' ), 'a missing inc/options.php is logged and the theme stops loading: ' . json_encode( $r ) );

$r = $child(
	'load',
	function ( $dir ) use ( $all ) {
		$all( $dir );
		unlink( $dir . '/theme/inc/tafsir.php' ); // a file another file pulls in
	}
);
ih_check( false !== strpos( $r['log'] ?? '', 'inc/tafsir.php is missing' ), 'a missing file pulled in by another is found before any is read: ' . json_encode( $r ) );

$r = $child(
	'load',
	function ( $dir ) use ( $all ) {
		$all( $dir );
		file_put_contents( $dir . '/theme/inc/seo.php', "<?php\nfunction injil_help_half(" ); // half written
	}
);
ih_check( false !== strpos( $r['log'] ?? '', 'incomplete or being uploaded' ), 'a half-written file is caught: ' . json_encode( $r ) );

$r = $child(
	'load',
	function ( $dir ) use ( $all ) {
		$all( $dir );
		file_put_contents( $dir . '/theme/inc/schema.php', "<?php\nfunction injil_help_cut_here() {}\n" ); // cut short where it still parses
	}
);
ih_check( false !== strpos( $r['log'] ?? '', 'inc/schema.php is incomplete' ), 'a file cut short where it still parses is caught: ' . json_encode( $r ) );

$r = $child( 'load', $all );
ih_check( '' === ( $r['log'] ?? 'x' ) && ! empty( $r['files'] ), 'a whole theme loads without a word in the log: ' . json_encode( $r ) );
foreach ( $inc as $name ) {
	$tail = substr( rtrim( (string) file_get_contents( $ih_theme . '/inc/' . $name . '.php' ) ), -80 );
	ih_check( false !== strpos( $tail, "\$GLOBALS['injil_help_loaded'][ basename( __FILE__, '.php' ) ] = true;" ), "inc/$name.php ends with its loaded flag" );
}

$r = $child(
	'serve',
	function ( $dir ) {
		mkdir( $dir . '/wp-content/injil-help-errors' );
		file_put_contents( $dir . '/wp-content/injil-help-errors/503.html', '<p>Static 503</p>' );
	}
);
ih_check( 503 === ( $r['status'] ?? 0 ) && '<p>Static 503</p>' === ( $r['body'] ?? '' ), 'the static 503 page is sent: ' . substr( json_encode( $r ), 0, 300 ) );
$r = $child( 'serve', function ( $dir ) {} );
ih_check( 503 === ( $r['status'] ?? 0 ) && false !== strpos( $r['body'] ?? '', 'The site is being updated.' ), 'with no static page, a short 503 page is sent: ' . substr( json_encode( $r ), 0, 300 ) );

if ( $fail ) {
	fwrite( STDERR, "$fail check(s) failed\n" );
	exit( 1 );
}
echo "OK\n";
