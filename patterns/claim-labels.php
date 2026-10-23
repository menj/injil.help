<?php
/**
 * Title: Claim labels
 * Slug: injil-help/claim-labels
 * Categories: injil-help
 * Description: The three claim-status labels. Place one above each contested claim.
 */
?>
<!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap"},"style":{"spacing":{"blockGap":"var:preset|spacing|10"}}} -->
<div class="wp-block-group"><!-- wp:paragraph {"className":"is-style-label-consensus"} -->
<p class="is-style-label-consensus"><?php esc_html_e( 'Scholarly consensus', 'injil-help' ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"className":"is-style-label-minority"} -->
<p class="is-style-label-minority"><?php esc_html_e( 'Minority view', 'injil-help' ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"className":"is-style-label-islamic"} -->
<p class="is-style-label-islamic"><?php esc_html_e( 'Islamic position', 'injil-help' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->