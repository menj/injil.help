<?php
/**
 * Accessibility helpers for front-end output (WCAG 2.2 AA).
 *
 * A table inside a figure that scrolls sideways must take keyboard focus, or a keyboard user cannot
 * reach the clipped columns (WCAG 2.1.1). The filter below gives each core table figure
 * tabindex="0", role="region" and an accessible name, so a screen reader announces the scroll area.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

/**
 * Make a table figure a focusable, named region.
 *
 * @param string $html  Rendered block HTML.
 * @param array  $block Parsed block.
 * @return string
 */
function injil_help_a11y_table_region( $html, $block ) {
	if ( ! is_string( $html ) || '' === $html || ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
		return $html;
	}
	$p = new WP_HTML_Tag_Processor( $html );
	if ( ! $p->next_tag( 'figure' ) || ! $p->has_class( 'wp-block-table' ) ) {
		return $html;
	}
	if ( null === $p->get_attribute( 'tabindex' ) ) {
		$p->set_attribute( 'tabindex', '0' );
	}
	if ( null === $p->get_attribute( 'role' ) ) {
		$p->set_attribute( 'role', 'region' );
	}
	if ( null === $p->get_attribute( 'aria-label' ) && null === $p->get_attribute( 'aria-labelledby' ) ) {
		static $n = 0;
		++$n;
		/* translators: %d: the table's position on the page. */
		$p->set_attribute( 'aria-label', sprintf( __( 'Table %d, scrollable', 'injil-help' ), $n ) );
	}
	return $p->get_updated_html();
}
add_filter( 'render_block_core/table', 'injil_help_a11y_table_region', 10, 2 );

// The last line: functions.php checks for it, so a file an upload cut short is caught.
$GLOBALS['injil_help_loaded'][ basename( __FILE__, '.php' ) ] = true;
