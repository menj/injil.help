<?php
/**
 * Theme Options: Appearance > Theme Options, a tabbed settings page.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

const INJIL_HELP_OPTION = 'injil_help_options';

/** Palette slugs that can be overridden, with labels and theme.json defaults. */
function injil_help_color_fields() {
	return array(
		'base'     => array( __( 'Page background (parchment)', 'injil-help' ), '#f6f1e6' ),
		'contrast' => array( __( 'Text (ink)', 'injil-help' ), '#2a2420' ),
		'accent-1' => array( __( "Primary (Qur'anic green)", 'injil-help' ), '#1f5c4a' ),
		'accent-2' => array( __( 'Headings and labels (rubric red)', 'injil-help' ), '#9b2c2c' ),
		'accent-3' => array( __( 'Highlights (gold)', 'injil-help' ), '#c39a3f' ),
		'accent-4' => array( __( 'Links and notes (lapis)', 'injil-help' ), '#2d4a7a' ),
		'accent-5' => array( __( 'Panels (vellum)', 'injil-help' ), '#ece3d1' ),
		'accent-6' => array( __( 'Rules and borders', 'injil-help' ), '#d8cbb2' ),
		'muted'    => array( __( 'Secondary text', 'injil-help' ), '#6b6158' ),
	);
}

function injil_help_option_defaults() {
	return array(
		'colors'           => array(),
		'footer_mark'      => 1,
		'ornament'         => 'rich',
		'animate'          => 1,
		'parallax'         => 'strong',
		'gospel_layout'    => 'columns',
		'content_width'    => 'full',
		'block_app_passwords' => 1,
		'favicon_force'    => 0,
		'favicon_url'      => '',
		'share'            => 1,
		'share_networks'   => array( 'x', 'facebook', 'whatsapp', 'telegram', 'reddit', 'linkedin', 'bluesky', 'threads' ),
		'share_types'      => array( 'post', 'page' ),
		'share_label'      => '',
		'schema'           => 'auto',
		'home_title'       => '',
		'home_description' => '',
		'pillar_title'     => '',
		'og_image'         => '',
		'author_name'      => '',
		'author_url'       => '',
		'same_as'          => "https://menj.org\nhttps://apostleofdoom.org",
		'org_logo'         => '',
		'ctx_own'          => 1,
		'ctx_sister'       => 1,
		'ctx_own_max'      => 4,
		'ctx_sister_max'   => 3,
		'ctx_extra'        => '',
		'ctx_exclude'      => '',
	);
}

function injil_help_get_option( $key = null ) {
	$opts = wp_parse_args( (array) get_option( INJIL_HELP_OPTION, array() ), injil_help_option_defaults() );
	return null === $key ? $opts : ( $opts[ $key ] ?? null );
}

/** The submitted value of a select when it is one of the allowed values, else the default; never null. */
function injil_help_pick( $in, $key, $allowed, $default ) {
	$value = $in[ $key ] ?? $default;
	return in_array( $value, $allowed, true ) ? $value : $default;
}

function injil_help_sanitize_options( $in ) {
	$in  = is_array( $in ) ? $in : array();
	$out = injil_help_option_defaults();

	foreach ( array_keys( injil_help_color_fields() ) as $slug ) {
		$hex = sanitize_hex_color( $in['colors'][ $slug ] ?? '' );
		if ( $hex ) {
			$out['colors'][ $slug ] = $hex;
		}
	}
	$out['footer_mark']      = empty( $in['footer_mark'] ) ? 0 : 1;
	$out['ornament']         = injil_help_pick( $in, 'ornament', array( 'rich', 'subtle', 'off' ), 'rich' );
	$out['animate']          = empty( $in['animate'] ) ? 0 : 1;
	$out['parallax']         = injil_help_pick( $in, 'parallax', array( 'strong', 'gentle', 'off' ), 'strong' );
	$out['gospel_layout']    = injil_help_pick( $in, 'gospel_layout', array( 'columns', 'single', 'plain' ), 'columns' );
	$out['content_width']    = injil_help_pick( $in, 'content_width', array( 'full', 'wide', 'reading' ), 'full' );
	$out['block_app_passwords'] = empty( $in['block_app_passwords'] ) ? 0 : 1;
	$out['favicon_force']    = empty( $in['favicon_force'] ) ? 0 : 1;
	$out['favicon_url']      = esc_url_raw( $in['favicon_url'] ?? '' );
	$out['share']            = empty( $in['share'] ) ? 0 : 1;
	$out['share_networks']   = array_values( array_intersect( array_keys( injil_help_share_networks() ), array_map( 'sanitize_key', (array) ( $in['share_networks'] ?? array() ) ) ) );
	$out['share_types']      = array_values( array_intersect( array( 'post', 'page' ), array_map( 'sanitize_key', (array) ( $in['share_types'] ?? array() ) ) ) );
	$out['share_label']      = sanitize_text_field( $in['share_label'] ?? '' );
	$out['schema']           = injil_help_pick( $in, 'schema', array( 'auto', 'on', 'off' ), 'auto' );
	$out['home_title']       = sanitize_text_field( $in['home_title'] ?? '' );
	$out['home_description'] = sanitize_textarea_field( $in['home_description'] ?? '' );
	$out['pillar_title']     = sanitize_text_field( $in['pillar_title'] ?? '' );
	$out['og_image']         = esc_url_raw( $in['og_image'] ?? '' );
	$out['author_name']      = sanitize_text_field( $in['author_name'] ?? '' );
	$out['author_url']       = esc_url_raw( $in['author_url'] ?? '' );
	$out['same_as']          = implode( "\n", array_filter( array_map( 'esc_url_raw', array_map( 'trim', explode( "\n", (string) ( $in['same_as'] ?? '' ) ) ) ) ) );
	$out['org_logo']         = esc_url_raw( $in['org_logo'] ?? '' );
	$out['ctx_own']          = empty( $in['ctx_own'] ) ? 0 : 1;
	$out['ctx_sister']       = empty( $in['ctx_sister'] ) ? 0 : 1;
	$out['ctx_own_max']      = max( 0, min( 15, (int) ( $in['ctx_own_max'] ?? 4 ) ) );
	$out['ctx_sister_max']   = max( 0, min( 10, (int) ( $in['ctx_sister_max'] ?? 3 ) ) );
	$out['ctx_extra']        = implode( "\n", array_filter( array_map( 'sanitize_text_field', preg_split( '/\R/u', (string) ( $in['ctx_extra'] ?? '' ) ) ) ) );
	$out['ctx_exclude']      = implode( "\n", array_filter( array_map( 'sanitize_text_field', preg_split( '/\R/u', (string) ( $in['ctx_exclude'] ?? '' ) ) ) ) );
	return $out;
}

add_action(
	'admin_init',
	function () {
		register_setting(
			'injil_help',
			INJIL_HELP_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => 'injil_help_sanitize_options',
				'default'           => injil_help_option_defaults(),
			)
		);
	}
);

add_action(
	'admin_menu',
	function () {
		$hook = add_theme_page(
			__( 'Theme Options', 'injil-help' ),
			__( 'Theme Options', 'injil-help' ),
			'edit_theme_options',
			'injil-help-options',
			'injil_help_render_options'
		);
		add_action(
			'admin_enqueue_scripts',
			function ( $screen_hook ) use ( $hook ) {
				if ( $screen_hook !== $hook ) {
					return;
				}
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_style( 'injil-help-admin', get_stylesheet_directory_uri() . '/assets/css/admin.css', array( 'wp-color-picker' ), INJIL_HELP_VERSION );
				// The page's own colours follow the Colours tab.
				wp_add_inline_style( 'injil-help-admin', injil_help_color_css() );
				wp_enqueue_script( 'injil-help-admin', get_stylesheet_directory_uri() . '/assets/js/admin.js', array( 'wp-color-picker' ), INJIL_HELP_VERSION, true );
				wp_localize_script(
					'injil-help-admin',
					'injilHelpPopulate',
					array(
						'ajaxUrl' => admin_url( 'admin-ajax.php' ),
						'nonce'   => wp_create_nonce( 'injil_help_populate' ),
						/* translators: 1: pages done, 2: pages in all */
						'working' => __( 'Writing the pages: %1$d of %2$d done. Keep this screen open.', 'injil-help' ),
						'busy'    => __( 'Another update is running. Trying again in a few seconds…', 'injil-help' ),
						'failed'  => __( 'The update stopped before it finished. Nothing is lost: press the button again and it carries on.', 'injil-help' ),
					)
				);
			}
		);
	}
);

function injil_help_render_options() {
	$o    = injil_help_get_option();
	$name = INJIL_HELP_OPTION;
	$tabs = array(
		'colours'  => __( 'Colours', 'injil-help' ),
		'branding' => __( 'Branding', 'injil-help' ),
		'sharing'  => __( 'Sharing', 'injil-help' ),
		'links'    => __( 'Contextual links', 'injil-help' ),
		'seo'      => __( 'SEO', 'injil-help' ),
		'security' => __( 'Security', 'injil-help' ),
		'content'  => __( 'Content', 'injil-help' ),
	);
	?>
	<div class="wrap ih-options">
		<h1><?php esc_html_e( 'Theme Options', 'injil-help' ); ?></h1>
		<?php
		settings_errors();
		// The result of the Content tab's buttons, at the top of the screen whichever tab is open.
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['ih_done'] ) && function_exists( 'injil_help_populate_summary' ) ) {
			$done  = sanitize_key( wp_unslash( $_GET['ih_done'] ) );
			$tally = array();
			foreach ( array( 'created', 'updated', 'unchanged', 'kept', 'failed' ) as $k ) {
				$tally[ $k ] = isset( $_GET[ 'ih_' . $k ] ) ? absint( $_GET[ 'ih_' . $k ] ) : 0;
			}
			if ( 'busy' === $done ) {
				printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', esc_html__( 'Another update was already running, so nothing was done. Wait a minute and press the button again.', 'injil-help' ) );
			} else {
				$head = 'update' === $done ? __( 'Update finished.', 'injil-help' ) : __( 'Check finished.', 'injil-help' );
				printf( '<div class="notice %1$s is-dismissible"><p><strong>%2$s</strong> %3$s</p></div>', $tally['failed'] ? 'notice-warning' : 'notice-success', esc_html( $head ), esc_html( injil_help_populate_summary( $tally ) ) );
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
		?>
		<div id="ih-populate-status" class="notice notice-info inline" hidden aria-live="polite"><p></p></div>
		<form method="post" action="options.php">
			<?php settings_fields( 'injil_help' ); ?>
			<nav class="ih-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Settings sections', 'injil-help' ); ?>">
				<?php foreach ( $tabs as $id => $label ) : ?>
					<a href="#<?php echo esc_attr( $id ); ?>" role="tab" id="tab-<?php echo esc_attr( $id ); ?>" aria-controls="panel-<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></a>
				<?php endforeach; ?>
			</nav>

			<section class="ih-panel" id="panel-colours" role="tabpanel" aria-labelledby="tab-colours">
				<p class="description"><?php esc_html_e( 'Override the theme palette. Leave a field empty to keep the default. Changes apply to the site and the editor.', 'injil-help' ); ?></p>
				<table class="form-table" role="presentation">
					<?php foreach ( injil_help_color_fields() as $slug => $f ) : ?>
						<tr>
							<th scope="row"><label for="ih-c-<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $f[0] ); ?></label></th>
							<td><input type="text" class="ih-color" id="ih-c-<?php echo esc_attr( $slug ); ?>" name="<?php echo esc_attr( $name ); ?>[colors][<?php echo esc_attr( $slug ); ?>]" value="<?php echo esc_attr( $o['colors'][ $slug ] ?? '' ); ?>" data-default-color="<?php echo esc_attr( $f[1] ); ?>"></td>
						</tr>
					<?php endforeach; ?>
				</table>
			</section>

			<section class="ih-panel" id="panel-branding" role="tabpanel" aria-labelledby="tab-branding">
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="ih-content-width"><?php esc_html_e( 'Content width', 'injil-help' ); ?></label></th>
						<td>
							<select id="ih-content-width" name="<?php echo esc_attr( $name ); ?>[content_width]">
								<option value="full" <?php selected( $o['content_width'], 'full' ); ?>><?php esc_html_e( 'Full width: the content fills the page between the side gutters', 'injil-help' ); ?></option>
								<option value="wide" <?php selected( $o['content_width'], 'wide' ); ?>><?php esc_html_e( 'Wide: 1240px, the width of the header and footer', 'injil-help' ); ?></option>
								<option value="reading" <?php selected( $o['content_width'], 'reading' ); ?>><?php esc_html_e( 'Reading column: 720px', 'injil-help' ); ?></option>
							</select>
							<p class="description"><?php esc_html_e( 'How wide the text and tables of every page may run. Full width is the default, and the header, footer and front page widen with it. Wide is the 1240px of the header and footer. Reading column restores the former layout: a 720px column of text under the 1240px header, footer and front page. Tables and plates marked wide fill the width in every case.', 'injil-help' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="ih-ornament"><?php esc_html_e( 'Ornament', 'injil-help' ); ?></label></th>
						<td>
							<select id="ih-ornament" name="<?php echo esc_attr( $name ); ?>[ornament]">
								<option value="rich" <?php selected( $o['ornament'], 'rich' ); ?>><?php esc_html_e( 'Rich: pattern, icons, banners and frames', 'injil-help' ); ?></option>
								<option value="subtle" <?php selected( $o['ornament'], 'subtle' ); ?>><?php esc_html_e( 'Subtle: the same, with a fainter pattern', 'injil-help' ); ?></option>
								<option value="off" <?php selected( $o['ornament'], 'off' ); ?>><?php esc_html_e( 'Off: plain pages', 'injil-help' ); ?></option>
							</select>
							<p class="description"><?php esc_html_e( 'The star pattern, section icons, tinted banners, badges and image frames. Everything takes its colour from the Colours tab.', 'injil-help' ); ?></p>
							<label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[animate]" value="1" <?php checked( $o['animate'] ); ?>> <?php esc_html_e( 'Let cards and answers rise into view as the page scrolls', 'injil-help' ); ?></label>
							<p class="ih-inline-choices">
								<label for="ih-parallax"><?php esc_html_e( 'Parallax depth', 'injil-help' ); ?></label>
								<select id="ih-parallax" name="<?php echo esc_attr( $name ); ?>[parallax]">
									<option value="strong" <?php selected( $o['parallax'], 'strong' ); ?>><?php esc_html_e( 'Strong', 'injil-help' ); ?></option>
									<option value="gentle" <?php selected( $o['parallax'], 'gentle' ); ?>><?php esc_html_e( 'Gentle', 'injil-help' ); ?></option>
									<option value="off" <?php selected( $o['parallax'], 'off' ); ?>><?php esc_html_e( 'Off', 'injil-help' ); ?></option>
								</select>
							</p>
							<p class="description"><?php esc_html_e( 'Layers move at different speeds as the page scrolls: the star lattice behind the page, the hero image and text, the section banners and their watermark icons, the manuscript plates, the card grids and the footer pattern. Switched off for visitors who ask for reduced motion.', 'injil-help' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Footer brandmark', 'injil-help' ); ?></th>
						<td><label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[footer_mark]" value="1" <?php checked( $o['footer_mark'] ); ?>> <?php esc_html_e( 'Show the brandmark at the top of the footer', 'injil-help' ); ?></label></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Favicon', 'injil-help' ); ?></th>
						<td>
							<label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[favicon_force]" value="1" <?php checked( $o['favicon_force'] ); ?>> <?php esc_html_e( 'Always use the brandmark, even when a Site Icon is set', 'injil-help' ); ?></label>
							<p class="description"><?php esc_html_e( 'By default the brandmark is used only when no Site Icon is set (Settings > General > Site Icon).', 'injil-help' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="ih-favicon-url"><?php esc_html_e( 'Custom favicon URL', 'injil-help' ); ?></label></th>
						<td><input type="url" class="regular-text" id="ih-favicon-url" name="<?php echo esc_attr( $name ); ?>[favicon_url]" value="<?php echo esc_attr( $o['favicon_url'] ); ?>" placeholder="https://"><p class="description"><?php esc_html_e( 'Optional. Replaces the brandmark favicon. SVG, PNG or ICO.', 'injil-help' ); ?></p></td>
					</tr>
				</table>
			</section>

			<section class="ih-panel" id="panel-sharing" role="tabpanel" aria-labelledby="tab-sharing">
				<p class="description"><?php esc_html_e( 'A row of share links under the content of posts and pages. Where the browser allows it, a Copy link button and a More… button (the device’s own share sheet) are added. Icons take their colour from the Colours tab.', 'injil-help' ); ?></p>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Share row', 'injil-help' ); ?></th>
						<td>
							<label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[share]" value="1" <?php checked( $o['share'] ); ?>> <?php esc_html_e( 'Show share links', 'injil-help' ); ?></label>
							<p class="ih-inline-choices">
								<label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[share_types][]" value="post" <?php checked( in_array( 'post', (array) $o['share_types'], true ) ); ?>> <?php esc_html_e( 'On posts', 'injil-help' ); ?></label>
								<label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[share_types][]" value="page" <?php checked( in_array( 'page', (array) $o['share_types'], true ) ); ?>> <?php esc_html_e( 'On pages', 'injil-help' ); ?></label>
							</p>
							<p class="description"><?php esc_html_e( 'The front page never shows the row.', 'injil-help' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Networks', 'injil-help' ); ?></th>
						<td>
							<div class="ih-networks">
								<?php foreach ( injil_help_share_networks() as $key => $net ) : ?>
									<label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[share_networks][]" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, (array) $o['share_networks'], true ) ); ?>> <span class="ih-net-icon ih-share-<?php echo esc_attr( $key ); ?>" aria-hidden="true"></span><?php echo esc_html( $net[0] ); ?></label>
								<?php endforeach; ?>
							</div>
							<p class="description"><?php esc_html_e( 'Shown in this order. Pinterest uses the page’s featured image, or the default social image from the SEO tab.', 'injil-help' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="ih-share-label"><?php esc_html_e( 'Label', 'injil-help' ); ?></label></th>
						<td><input type="text" class="regular-text" id="ih-share-label" name="<?php echo esc_attr( $name ); ?>[share_label]" value="<?php echo esc_attr( $o['share_label'] ); ?>" placeholder="<?php esc_attr_e( 'Share', 'injil-help' ); ?>"></td>
					</tr>
				</table>
			</section>

			<section class="ih-panel" id="panel-links" role="tabpanel" aria-labelledby="tab-links">
				<p class="description"><?php esc_html_e( 'In the running text of a page, the first mention of a phrase the network map knows becomes a link to the inner page that treats it: a page on this site, or a page on one of the six sister sites (bestofislam.org, apostleofdoom.org, abrahamic-religions.com, compelling-evidence.com, murtadd.org, lailatulqadar.guide). Links are made as the page is shown; the stored text is never changed. Never in a heading, a quotation, a verse, the gospel text, Arabic, a note, a table or an existing link; at most one new link in a paragraph, and each page linked once.', 'injil-help' ); ?></p>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Pages on this site', 'injil-help' ); ?></th>
						<td>
							<label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[ctx_own]" value="1" <?php checked( $o['ctx_own'] ); ?>> <?php esc_html_e( 'Link to pages on injil.help', 'injil-help' ); ?></label>
							<p class="ih-inline-choices"><label for="ih-ctx-own-max"><?php esc_html_e( 'At most, on one page', 'injil-help' ); ?></label> <input type="number" class="small-text" min="0" max="15" id="ih-ctx-own-max" name="<?php echo esc_attr( $name ); ?>[ctx_own_max]" value="<?php echo esc_attr( $o['ctx_own_max'] ); ?>"></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Pages on the sister sites', 'injil-help' ); ?></th>
						<td>
							<label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[ctx_sister]" value="1" <?php checked( $o['ctx_sister'] ); ?>> <?php esc_html_e( 'Link to pages on the six sister sites', 'injil-help' ); ?></label>
							<p class="ih-inline-choices"><label for="ih-ctx-sister-max"><?php esc_html_e( 'At most, on one page', 'injil-help' ); ?></label> <input type="number" class="small-text" min="0" max="10" id="ih-ctx-sister-max" name="<?php echo esc_attr( $name ); ?>[ctx_sister_max]" value="<?php echo esc_attr( $o['ctx_sister_max'] ); ?>"></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="ih-ctx-extra"><?php esc_html_e( 'More phrases', 'injil-help' ); ?></label></th>
						<td><textarea class="large-text code" rows="6" id="ih-ctx-extra" name="<?php echo esc_attr( $name ); ?>[ctx_extra]" placeholder="Gospel of Peter | /gospels/peter/"><?php echo esc_textarea( $o['ctx_extra'] ); ?></textarea><p class="description"><?php esc_html_e( 'One per line: phrase | address. A path such as /paul/james/ is a page on this site; a full address must be on one of the sister sites. These come before the shared map, so a phrase here wins.', 'injil-help' ); ?></p></td>
					</tr>
					<tr>
						<th scope="row"><label for="ih-ctx-exclude"><?php esc_html_e( 'Pages without links', 'injil-help' ); ?></label></th>
						<td><textarea class="large-text code" rows="3" id="ih-ctx-exclude" name="<?php echo esc_attr( $name ); ?>[ctx_exclude]" placeholder="terms"><?php echo esc_textarea( $o['ctx_exclude'] ); ?></textarea><p class="description"><?php esc_html_e( 'One per line: a page slug (terms) or its path (paul/james).', 'injil-help' ); ?></p></td>
					</tr>
				</table>
			</section>

			<section class="ih-panel" id="panel-seo" role="tabpanel" aria-labelledby="tab-seo">
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="ih-schema"><?php esc_html_e( 'Theme SEO output', 'injil-help' ); ?></label></th>
						<td>
							<select id="ih-schema" name="<?php echo esc_attr( $name ); ?>[schema]">
								<option value="auto" <?php selected( $o['schema'], 'auto' ); ?>><?php esc_html_e( 'Automatic (with Rank Math, the theme works inside it; off with Yoast or SEOPress)', 'injil-help' ); ?></option>
								<option value="on" <?php selected( $o['schema'], 'on' ); ?>><?php esc_html_e( 'Always on', 'injil-help' ); ?></option>
								<option value="off" <?php selected( $o['schema'], 'off' ); ?>><?php esc_html_e( 'Always off', 'injil-help' ); ?></option>
							</select>
							<p class="description"><?php esc_html_e( 'Titles, meta description (trimmed to 160 characters), the breadcrumb row, Open Graph, and the JSON-LD graph with every schema.org type the content supports: Organization, Person, WebSite with search, site navigation, the page node (WebPage, CollectionPage, ProfilePage and AboutPage, ContactPage or SearchResultsPage), BreadcrumbList, Article with citations, Book and Chapter, the commentaries and their translations, DefinedTermSet, Table, Dataset, Event, Quotation, FAQPage and ImageObject licence metadata.', 'injil-help' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="ih-home-title"><?php esc_html_e( 'Home title', 'injil-help' ); ?></label></th>
						<td><input type="text" class="large-text" id="ih-home-title" name="<?php echo esc_attr( $name ); ?>[home_title]" value="<?php echo esc_attr( $o['home_title'] ); ?>" placeholder="<?php echo esc_attr( injil_help_seo_defaults()['home_title'] ); ?>"></td>
					</tr>
					<tr>
						<th scope="row"><label for="ih-home-desc"><?php esc_html_e( 'Home description', 'injil-help' ); ?></label></th>
						<td><textarea class="large-text" rows="3" maxlength="320" id="ih-home-desc" name="<?php echo esc_attr( $name ); ?>[home_description]" placeholder="<?php echo esc_attr( injil_help_seo_defaults()['home_description'] ); ?>"><?php echo esc_textarea( $o['home_description'] ); ?></textarea><p class="description"><?php esc_html_e( 'Keep under 160 characters for search results.', 'injil-help' ); ?></p></td>
					</tr>
					<tr>
						<th scope="row"><label for="ih-pillar-title"><?php esc_html_e( 'Pillar page title', 'injil-help' ); ?></label></th>
						<td><input type="text" class="large-text" id="ih-pillar-title" name="<?php echo esc_attr( $name ); ?>[pillar_title]" value="<?php echo esc_attr( $o['pillar_title'] ); ?>" placeholder="<?php echo esc_attr( injil_help_seo_defaults()['pillar_title'] ); ?>"></td>
					</tr>
					<tr>
						<th scope="row"><label for="ih-og"><?php esc_html_e( 'Default social image URL', 'injil-help' ); ?></label></th>
						<td><input type="url" class="large-text" id="ih-og" name="<?php echo esc_attr( $name ); ?>[og_image]" value="<?php echo esc_attr( $o['og_image'] ); ?>" placeholder="https://"><p class="description"><?php esc_html_e( 'Used when a page has no featured image. Empty uses the Birmingham Qur’an manuscript bundled with the theme (1280 × 833).', 'injil-help' ); ?></p></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Structured data', 'injil-help' ); ?></th>
						<td>
							<p class="description"><?php esc_html_e( 'The Organization and Person nodes in the JSON-LD graph on every page. Empty name and URL fall back to the author of each page.', 'injil-help' ); ?></p>
							<p><label for="ih-author-name"><?php esc_html_e( 'Author name', 'injil-help' ); ?></label><br><input type="text" class="regular-text" id="ih-author-name" name="<?php echo esc_attr( $name ); ?>[author_name]" value="<?php echo esc_attr( $o['author_name'] ); ?>"></p>
							<p><label for="ih-author-url"><?php esc_html_e( 'Author URL', 'injil-help' ); ?></label><br><input type="url" class="regular-text" id="ih-author-url" name="<?php echo esc_attr( $name ); ?>[author_url]" value="<?php echo esc_attr( $o['author_url'] ); ?>" placeholder="https://"></p>
							<p><label for="ih-same-as"><?php esc_html_e( 'Profile links (sameAs), one per line', 'injil-help' ); ?></label><br><textarea class="large-text code" rows="3" id="ih-same-as" name="<?php echo esc_attr( $name ); ?>[same_as]"><?php echo esc_textarea( $o['same_as'] ); ?></textarea></p>
							<p><label for="ih-org-logo"><?php esc_html_e( 'Organization logo URL', 'injil-help' ); ?></label><br><input type="url" class="regular-text" id="ih-org-logo" name="<?php echo esc_attr( $name ); ?>[org_logo]" value="<?php echo esc_attr( $o['org_logo'] ); ?>" placeholder="https://"><br><span class="description"><?php esc_html_e( 'Empty uses the Site Icon, or the brandmark when none is set. Google asks for at least 112 × 112 pixels.', 'injil-help' ); ?></span></p>
						</td>
					</tr>
				</table>
			</section>

			<section class="ih-panel" id="panel-security" role="tabpanel" aria-labelledby="tab-security">
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Application passwords', 'injil-help' ); ?></th>
						<td>
							<label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[block_app_passwords]" value="1" <?php checked( $o['block_app_passwords'] ); ?>> <?php esc_html_e( 'Block application passwords (on by default)', 'injil-help' ); ?></label>
							<p class="description"><?php esc_html_e( 'Application passwords let a client (a mobile app, a publishing tool, a script) log in through the REST API or XML-RPC with a password made for it alone. Nothing on this site needs that door, so the theme keeps it shut: the panel disappears from user profiles and such log-ins are refused. Untick the box only while a client you trust needs it.', 'injil-help' ); ?></p>
						</td>
					</tr>
				</table>
			</section>

			<section class="ih-panel" id="panel-content" role="tabpanel" aria-labelledby="tab-content">
				<?php
				$missing = 0;
				?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="ih-gospel-layout"><?php esc_html_e( 'Gospel text layout', 'injil-help' ); ?></label></th>
						<td>
							<select id="ih-gospel-layout" name="<?php echo esc_attr( $name ); ?>[gospel_layout]">
								<option value="columns" <?php selected( $o['gospel_layout'], 'columns' ); ?>><?php esc_html_e( 'Bible: two columns, verse numbers, references at the foot', 'injil-help' ); ?></option>
								<option value="single" <?php selected( $o['gospel_layout'], 'single' ); ?>><?php esc_html_e( 'Single column with verse numbers', 'injil-help' ); ?></option>
								<option value="plain" <?php selected( $o['gospel_layout'], 'plain' ); ?>><?php esc_html_e( 'Plain paragraphs, no verse numbers', 'injil-help' ); ?></option>
							</select>
							<p class="description"><?php esc_html_e( 'How the 27 sections of The Gospel According to Islam are set. The verse numbers and the Qur’anic references are in the page markup whichever layout is chosen; the layout only shows or hides them. Below 900px the text is always one column.', 'injil-help' ); ?></p>
						</td>
					</tr>
				</table>
				<p class="description"><?php esc_html_e( 'The theme creates these pages when it is activated. When the theme is updated, the pages whose text changed are rewritten automatically, a few at a time as you move between admin screens, and pages you have edited are kept. The first button creates any page that is missing; the second rewrites every page now, edited ones included. Both work in small batches, show their progress here, and report what they did at the top of the screen.', 'injil-help' ); ?></p>
				<ul class="ih-pages">
					<?php foreach ( injil_help_starter_pages() as $def ) : ?>
						<?php
						$page = injil_help_find_page( $def['path'] );
						$missing += $page ? 0 : 1;
						?>
						<li class="<?php echo $page ? 'is-ok' : 'is-missing'; ?>">
							<span class="ih-dot" aria-hidden="true"></span>
							<?php if ( $page ) : ?>
								<a href="<?php echo esc_url( get_permalink( $page ) ); ?>"><?php echo esc_html( $def['title'] ); ?></a>
							<?php else : ?>
								<?php echo esc_html( $def['title'] ); ?>
							<?php endif; ?>
							<code>/<?php echo esc_html( $def['path'] ); ?>/</code>
						</li>
					<?php endforeach; ?>
				</ul>
				<p>
					<a class="button button-primary" data-ih-populate="create" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=injil_help_populate' ), 'injil_help_populate' ) ); ?>"><?php echo esc_html( $missing ? sprintf( /* translators: %d: missing pages */ __( 'Create %d missing pages', 'injil-help' ), $missing ) : __( 'Check again', 'injil-help' ) ); ?></a>
					<a class="button" data-ih-populate="update" data-confirm="<?php echo esc_attr__( 'Rewrite every listed page from the theme? Edits you made to those pages will be lost.', 'injil-help' ); ?>" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=injil_help_populate&overwrite=1' ), 'injil_help_populate' ) ); ?>"><?php esc_html_e( 'Update all pages from the theme', 'injil-help' ); ?></a>
				</p>
			</section>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/** Colour overrides as CSS custom properties, front end and editor. */
function injil_help_color_css() {
	$colors = injil_help_get_option( 'colors' );
	$rules  = '';
	foreach ( (array) $colors as $slug => $hex ) {
		if ( isset( injil_help_color_fields()[ $slug ] ) && sanitize_hex_color( $hex ) ) {
			$rules .= sprintf( '--wp--preset--color--%s:%s;', $slug, $hex );
		}
	}
	return $rules ? ':root:root{' . $rules . '}' : '';
}

add_action(
	'enqueue_block_assets',
	function () {
		$css = injil_help_color_css();
		if ( $css ) {
			wp_register_style( 'injil-help-options', false, array(), INJIL_HELP_VERSION );
			wp_enqueue_style( 'injil-help-options' );
			wp_add_inline_style( 'injil-help-options', $css );
		}
	}
);

add_filter(
	'body_class',
	function ( $classes ) {
		if ( ! injil_help_get_option( 'footer_mark' ) ) {
			$classes[] = 'ih-no-footer-mark';
		}
		$classes[] = 'ih-gospel-' . injil_help_get_option( 'gospel_layout' );
		$classes[] = 'ih-width-' . injil_help_get_option( 'content_width' );
		$orn = injil_help_get_option( 'ornament' );
		if ( 'off' !== $orn ) {
			$classes[] = 'ih-orn';
			$classes[] = 'ih-orn-' . ( 'subtle' === $orn ? 'subtle' : 'rich' );
			$px = injil_help_get_option( 'parallax' );
			if ( 'off' !== $px ) {
				$classes[] = 'ih-px-' . ( 'gentle' === $px ? 'gentle' : 'strong' );
			}
		}
		return $classes;
	}
);

/* Feed options into the SEO filters. */
add_filter(
	'injil_help_seo_defaults',
	function ( $d ) {
		foreach ( array( 'home_title', 'home_description', 'pillar_title' ) as $k ) {
			$v = injil_help_get_option( $k );
			if ( $v ) {
				$d[ $k ] = $v;
			}
		}
		return $d;
	}
);

add_filter(
	'injil_help_default_og_image',
	function ( $img ) {
		return injil_help_get_option( 'og_image' ) ?: $img;
	}
);

add_filter(
	'injil_help_output_schema',
	function ( $enabled ) {
		$mode = injil_help_get_option( 'schema' );
		return 'on' === $mode ? true : ( 'off' === $mode ? false : $enabled );
	}
);

/* "Always use the brandmark": suppress WordPress's own Site Icon tags. */
add_filter(
	'site_icon_meta_tags',
	function ( $tags ) {
		return injil_help_get_option( 'favicon_force' ) || injil_help_get_option( 'favicon_url' ) ? array() : $tags;
	}
);
