<?php
/**
 * The error pages as plain HTML: the three wp-content drop-ins and the static
 * pages in errors/, from one builder.
 *
 * Pure PHP, no WordPress function: the drop-ins run while WordPress is half
 * loaded or has no database, and bin/build-error-pages.php runs this file on
 * the command line. The style is inline for the same reason, in the theme's
 * colours and fonts with its brandmark, light and dark, so no file of the
 * theme is needed to show a page.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

/**
 * Status, title and wording of each page, from inc/data/text.json
 * (errors), read once.
 *
 * @return array<string,array> Kind => status, retry, eyebrow, title, lines, again.
 */
function injil_help_error_page_kinds() {
	static $kinds = null;
	if ( null === $kinds ) {
		$text  = json_decode( (string) file_get_contents( __DIR__ . '/data/text.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		$kinds = isset( $text['errors'] ) && is_array( $text['errors'] ) ? $text['errors'] : array();
	}
	return $kinds;
}

/**
 * One page as a complete HTML document.
 *
 * @param string $kind A key of injil_help_error_page_kinds().
 * @param string $site Site name, plain text.
 * @param string $home Address of the home page.
 * @return string
 */
function injil_help_error_page_html( $kind, $site, $home = '/' ) {
	$kinds = injil_help_error_page_kinds();
	$k     = isset( $kinds[ $kind ] ) ? $kinds[ $kind ] : $kinds['500'];
	$e     = static function ( $s ) {
		return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' );
	};
	$lines = '';
	foreach ( $k['lines'] as $line ) {
		$lines .= '<p>' . $e( $line ) . "</p>\n";
	}
	if ( ! empty( $k['alt'] ) ) {
		$lines .= '<p class="alt" lang="en">' . $e( $k['alt'] ) . "</p>\n";
	}
	$css = <<<'CSS'
:root{--paper:#f6f1e6;--card:#fffdf8;--ink:#2a2420;--muted:#5f564e;--green:#1f5c4a;--green-deep:#174538;--gold:#c39a3f;--line:#d8cbb2;--on-green:#ffffff}
@media (prefers-color-scheme:dark){:root{--paper:#14201b;--card:#1b2b24;--ink:#f6f1e6;--muted:#cfc5b6;--green:#8cc4ab;--green-deep:#b5dccb;--gold:#d9b260;--line:#2f4a3e;--on-green:#10241d}}
*{box-sizing:border-box}
html,body{height:100%;margin:0}
body{display:flex;align-items:center;justify-content:center;padding:24px 16px;background:var(--paper);color:var(--ink);font:1.1rem/1.6 "Sabon Next LT","EB Garamond",Garamond,Georgia,"Times New Roman",serif}
main{width:min(36rem,100%);padding:2.6rem clamp(1.25rem,6vw,2.4rem) 2.4rem;background:var(--card);border:1px solid var(--line);border-top:4px solid var(--green);border-radius:18px;text-align:center;box-shadow:0 18px 50px -28px rgba(42,36,32,.5)}
.mark{display:inline-block;width:4.5rem;height:4.5rem}
.mark svg{display:block;width:100%;height:100%}
.name{margin:.8rem 0 0;font:1.5rem/1.2 Kagnue,"EB Garamond",Georgia,serif;letter-spacing:.01em}
.code{margin:1.5rem 0 .2rem;font:600 .78rem/1.2 system-ui,sans-serif;letter-spacing:.22em;text-transform:uppercase;color:var(--green)}
h1{margin:.3rem 0 1rem;font-size:clamp(1.6rem,5vw,2.1rem);font-weight:600;line-height:1.2}
p{margin:0 auto .6rem;max-width:32rem;color:var(--muted)}
.again{display:inline-block;margin-top:1.4rem;padding:.7rem 1.8rem;border-radius:999px;background:var(--green);color:var(--on-green);font:600 1rem system-ui,sans-serif;text-decoration:none}
.again:hover{background:var(--green-deep)}
.again:focus-visible{outline:3px solid var(--gold);outline-offset:3px}
CSS;
	$mark = <<<'MARK'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 40" focusable="false"><g stroke="#c39a3f" stroke-width="2.2" stroke-linecap="round"><path d="M20 3.5v6"></path><path d="M12.5 5.5l2.6 5"></path><path d="M27.5 5.5l-2.6 5"></path><path d="M6.5 10l4 3.4"></path><path d="M33.5 10l-4 3.4"></path></g><path d="M20 16.2c-4.6-2.6-10.4-3-15.5-1.4v19.6c5.1-1.6 10.9-1.2 15.5 1.4 4.6-2.6 10.4-3 15.5-1.4V14.8c-5.1-1.6-10.9-1.2-15.5 1.4z" fill="#1f5c4a"></path><path d="M20 16.2v19.4" stroke="#f6f1e6" stroke-width="1.6"></path><g stroke="#dcebe3" stroke-width="1.1" stroke-linecap="round" opacity=".75"><path d="M8 20.2c2.9-.6 6-.4 8.8.6M8 24.4c2.9-.6 6-.4 8.8.6M8 28.6c2.9-.6 6-.4 8.8.6"></path><path d="M32 20.2c-2.9-.6-6-.4-8.8.6M32 24.4c-2.9-.6-6-.4-8.8.6M32 28.6c-2.9-.6-6-.4-8.8.6"></path></g></svg>
MARK;
	return '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>' . $e( $k['title'] ) . ' | ' . $e( $site ) . '</title>
<style>
' . $css . '
</style>
</head>
<body>
<main role="main">
<div class="mark" aria-hidden="true">' . $mark . '</div>
<p class="name">' . $e( $site ) . '</p>
<p class="code">' . $e( $k['eyebrow'] ) . '</p>
<h1>' . $e( $k['title'] ) . '</h1>
' . $lines . '<a class="again" href="' . $e( $home ) . '">' . $e( $k['again'] ) . '</a>
</main>
</body>
</html>';
}

/**
 * The PHP header of a drop-in: the marker line, then the status headers.
 *
 * @param string $kind    Drop-in kind.
 * @param string $version Theme version.
 * @return string
 */
function injil_help_error_dropin_head( $kind, $version ) {
	$k      = injil_help_error_page_kinds()[ $kind ];
	$reason = array(
		500 => 'Internal Server Error',
		503 => 'Service Unavailable',
	);
	return "<?php\n// The Injil error page drop-in (" . $kind . ') ' . $version . ". Written by the The Injil theme; safe to delete.\n"
		. "// Self-contained: no theme file, no WordPress function and no database.\n"
		. "if ( ! headers_sent() ) {\n"
		. "\theader( 'HTTP/1.1 " . $k['status'] . ' ' . $reason[ $k['status'] ] . "', true, " . $k['status'] . " );\n"
		. "\theader( 'Content-Type: text/html; charset=utf-8' );\n"
		. ( $k['retry'] ? "\theader( 'Retry-After: " . (int) $k['retry'] . "' );\n" : '' )
		. "\theader( 'X-Robots-Tag: noindex' );\n"
		. "\theader( 'Cache-Control: no-store, max-age=0' );\n"
		. "}\n?>\n";
}
