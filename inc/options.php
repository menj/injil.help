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
		'colors'         => array(),
		'footer_mark'    => 1,
		'ornament'       => 'rich',
		'animate'        => 1,
		'favicon_force'  => 0,
		'favicon_url'    => '',
		'schema'         => 'auto',
		'home_title'     => '',
		'home_description' => '',
		'pillar_title'   => '',
		'og_image'       => '',
	);
}

function injil_help_get_option( $key = null ) {
	$opts = wp_parse_args( (array) get_option( INJIL_HELP_OPTION, array() ), injil_help_option_defaults() );
	return null === $key ? $opts : ( $opts[ $key ] ?? null );
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
	$out['ornament']         = in_array( $in['ornament'] ?? 'rich', array( 'rich', 'subtle', 'off' ), true ) ? $in['ornament'] : 'rich';
	$out['animate']          = empty( $in['animate'] ) ? 0 : 1;
	$out['favicon_force']    = empty( $in['favicon_force'] ) ? 0 : 1;
	$out['favicon_url']      = esc_url_raw( $in['favicon_url'] ?? '' );
	$out['schema']           = in_array( $in['schema'] ?? 'auto', array( 'auto', 'on', 'off' ), true ) ? $in['schema'] : 'auto';
	$out['home_title']       = sanitize_text_field( $in['home_title'] ?? '' );
	$out['home_description'] = sanitize_textarea_field( $in['home_description'] ?? '' );
	$out['pillar_title']     = sanitize_text_field( $in['pillar_title'] ?? '' );
	$out['og_image']         = esc_url_raw( $in['og_image'] ?? '' );
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
				wp_enqueue_script( 'injil-help-admin', get_stylesheet_directory_uri() . '/assets/js/admin.js', array( 'wp-color-picker' ), INJIL_HELP_VERSION, true );
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
		'seo'      => __( 'SEO', 'injil-help' ),
		'content'  => __( 'Content', 'injil-help' ),
	);
	?>
	<div class="wrap ih-options">
		<h1><?php esc_html_e( 'Theme Options', 'injil-help' ); ?></h1>
		<?php settings_errors(); ?>
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
						<th scope="row"><label for="ih-ornament"><?php esc_html_e( 'Ornament', 'injil-help' ); ?></label></th>
						<td>
							<select id="ih-ornament" name="<?php echo esc_attr( $name ); ?>[ornament]">
								<option value="rich" <?php selected( $o['ornament'], 'rich' ); ?>><?php esc_html_e( 'Rich: pattern, icons, banners and frames', 'injil-help' ); ?></option>
								<option value="subtle" <?php selected( $o['ornament'], 'subtle' ); ?>><?php esc_html_e( 'Subtle: the same, with a fainter pattern', 'injil-help' ); ?></option>
								<option value="off" <?php selected( $o['ornament'], 'off' ); ?>><?php esc_html_e( 'Off: plain pages', 'injil-help' ); ?></option>
							</select>
							<p class="description"><?php esc_html_e( 'The star pattern, section icons, tinted banners, badges and image frames. Everything takes its colour from the Colours tab.', 'injil-help' ); ?></p>
							<label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[animate]" value="1" <?php checked( $o['animate'] ); ?>> <?php esc_html_e( 'Let cards and answers rise into view as the page scrolls', 'injil-help' ); ?></label>
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

			<section class="ih-panel" id="panel-seo" role="tabpanel" aria-labelledby="tab-seo">
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="ih-schema"><?php esc_html_e( 'Theme SEO output', 'injil-help' ); ?></label></th>
						<td>
							<select id="ih-schema" name="<?php echo esc_attr( $name ); ?>[schema]">
								<option value="auto" <?php selected( $o['schema'], 'auto' ); ?>><?php esc_html_e( 'Automatic (off when Yoast, Rank Math or SEOPress is active)', 'injil-help' ); ?></option>
								<option value="on" <?php selected( $o['schema'], 'on' ); ?>><?php esc_html_e( 'Always on', 'injil-help' ); ?></option>
								<option value="off" <?php selected( $o['schema'], 'off' ); ?>><?php esc_html_e( 'Always off', 'injil-help' ); ?></option>
							</select>
							<p class="description"><?php esc_html_e( 'Titles, meta description, Open Graph and Article, FAQPage, WebSite and BreadcrumbList schema.', 'injil-help' ); ?></p>
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
						<td><input type="url" class="large-text" id="ih-og" name="<?php echo esc_attr( $name ); ?>[og_image]" value="<?php echo esc_attr( $o['og_image'] ); ?>" placeholder="https://"><p class="description"><?php esc_html_e( 'Used when a page has no featured image. Empty uses the Birmingham Qur’an manuscript from Wikimedia Commons.', 'injil-help' ); ?></p></td>
					</tr>
				</table>
			</section>

			<section class="ih-panel" id="panel-content" role="tabpanel" aria-labelledby="tab-content">
				<?php
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				if ( isset( $_GET['ih_created'] ) ) {
					printf( '<div class="notice notice-success inline"><p>%s</p></div>', esc_html( sprintf( /* translators: %d: number of pages */ _n( '%d page created.', '%d pages created.', (int) $_GET['ih_created'], 'injil-help' ), (int) $_GET['ih_created'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				}
				$missing = 0;
				?>
				<p class="description"><?php esc_html_e( 'The theme creates these pages when it is activated. Use the button to create any that are missing. Existing pages are never changed or overwritten.', 'injil-help' ); ?></p>
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
					<a class="button button-primary" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=injil_help_populate' ), 'injil_help_populate' ) ); ?>"><?php echo esc_html( $missing ? sprintf( /* translators: %d: missing pages */ __( 'Create %d missing pages', 'injil-help' ), $missing ) : __( 'Check again', 'injil-help' ) ); ?></a>
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
		$orn = injil_help_get_option( 'ornament' );
		if ( 'off' !== $orn ) {
			$classes[] = 'ih-orn';
			$classes[] = 'ih-orn-' . ( 'subtle' === $orn ? 'subtle' : 'rich' );
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
