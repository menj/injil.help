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
		'block_xmlrpc'     => 1,
		'block_rest_writes' => 1,
		'spam_gate'        => 1,
		'spam_gone'        => 1,
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
		'same_as'          => "https://menj.bio\nhttps://apostleofdoom.org",
		'org_logo'         => '',
		'ctx_own'          => 1,
		'ctx_sister'       => 1,
		'ctx_own_max'      => 4,
		'ctx_sister_max'   => 3,
		'ctx_extra'        => '',
		'ctx_exclude'      => '',
		'remote_images'    => 0,
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
	// The Security switches change only for someone who may manage options; anyone else keeps the stored values.
	$stored = injil_help_get_option();
	foreach ( array( 'block_app_passwords', 'block_xmlrpc', 'block_rest_writes', 'spam_gate', 'spam_gone' ) as $key ) {
		$out[ $key ] = current_user_can( 'manage_options' ) ? ( empty( $in[ $key ] ) ? 0 : 1 ) : ( empty( $stored[ $key ] ) ? 0 : 1 );
	}
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
	// Split without the u flag, which fails (returns false) on invalid UTF-8. Addresses and
	// slugs keep their %XX octets: sanitize_text_field() deletes them, which turns an Arabic
	// slug such as /%d8%a7/ into //.
	$out['ctx_extra']        = implode( "\n", array_filter( array_map( 'injil_help_sanitize_ctx_line', preg_split( '/\r\n|\r|\n/', (string) ( $in['ctx_extra'] ?? '' ) ) ) ) );
	$out['ctx_exclude']      = implode( "\n", array_filter( array_map( 'injil_help_sanitize_ctx_slug', preg_split( '/\r\n|\r|\n/', (string) ( $in['ctx_exclude'] ?? '' ) ) ) ) );
	$out['remote_images']    = empty( $in['remote_images'] ) ? 0 : 1;
	return $out;
}

/**
 * One "phrase | address" line: the phrase as plain text, the address as a URL, so its %XX
 * octets survive. A line without an address, or whose address is refused, is dropped.
 *
 * @param string $line Raw line.
 * @return string
 */
function injil_help_sanitize_ctx_line( $line ) {
	$parts = array_map( 'trim', explode( '|', (string) $line, 2 ) );
	if ( count( $parts ) < 2 ) {
		return '';
	}
	$phrase  = sanitize_text_field( $parts[0] );
	$address = esc_url_raw( $parts[1] );
	return ( '' === $phrase || '' === $address ) ? '' : $phrase . ' | ' . $address;
}

/**
 * One excluded slug or path: tags and invalid bytes removed, %XX octets kept, since
 * WordPress stores a non-ASCII slug percent-encoded.
 *
 * @param string $line Raw line.
 * @return string
 */
function injil_help_sanitize_ctx_slug( $line ) {
	return trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( wp_check_invalid_utf8( (string) $line ) ) ) );
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
						'pagesNonce' => wp_create_nonce( 'injil_help_starter_pages' ),
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

/* ---------- Theme Options screen: building blocks of the card layout (3.20.0) ---------- */

/**
 * The name attribute of one option field.
 *
 * @param string $key    Option key.
 * @param string $suffix Optional suffix, such as "[]" or "[base]".
 * @return string
 */
function injil_help_ui_name( $key, $suffix = '' ) {
	return INJIL_HELP_OPTION . '[' . $key . ']' . $suffix;
}

/**
 * One field row: label and help on the left, the control on the right; stacked below 782px.
 *
 * @param string $id      The control's id, for label[for].
 * @param string $label   Label text.
 * @param string $control The control's markup, already escaped.
 * @param string $help    Help text (plain).
 * @param string $class   Extra class for the row.
 */
function injil_help_ui_row( $id, $label, $control, $help = '', $class = '' ) {
	?>
	<div class="ih-field<?php echo $class ? ' ' . esc_attr( $class ) : ''; ?>">
		<div class="ih-field__text">
			<label class="ih-field__label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
			<?php if ( '' !== $help ) : ?>
				<p class="description" id="<?php echo esc_attr( $id ); ?>-help"><?php echo esc_html( $help ); ?></p>
			<?php endif; ?>
		</div>
		<div class="ih-field__control"><?php echo $control; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts by the caller. ?></div>
	</div>
	<?php
}

/**
 * The markup of a toggle switch: a real checkbox with role="switch", styled.
 *
 * @param string $id      Control id.
 * @param string $name    Field name.
 * @param bool   $checked Whether it is on.
 * @param string $value   Submitted value.
 * @param string $extra   Extra attributes, already escaped.
 * @return string
 */
function injil_help_ui_switch( $id, $name, $checked, $value = '1', $extra = '' ) {
	return sprintf(
		'<span class="ih-switch"><input type="checkbox" role="switch" class="ih-switch__input" id="%1$s" name="%2$s" value="%3$s"%4$s%5$s><span class="ih-switch__track" aria-hidden="true"></span></span>',
		esc_attr( $id ),
		esc_attr( $name ),
		esc_attr( $value ),
		$checked ? ' checked' : '',
		$extra ? ' ' . $extra : ''
	);
}

/**
 * A toggle row for one boolean option.
 *
 * @param array  $o     Current options.
 * @param string $key   Option key.
 * @param string $label Label.
 * @param string $help  Help text.
 */
function injil_help_ui_toggle( $o, $key, $label, $help = '' ) {
	$id = 'ih-' . str_replace( '_', '-', $key );
	injil_help_ui_row( $id, $label, injil_help_ui_switch( $id, injil_help_ui_name( $key ), ! empty( $o[ $key ] ), '1', '' !== $help ? 'aria-describedby="' . esc_attr( $id ) . '-help"' : '' ), $help, 'ih-field--toggle' );
}

/**
 * Visual selector cards: a radio group where each choice is a card with an icon, a title and a line of text.
 *
 * @param array  $o       Current options.
 * @param string $key     Option key.
 * @param string $legend  Group label.
 * @param array  $choices value => array( title, description, dashicon ).
 * @param string $help    Help text.
 */
function injil_help_ui_cards( $o, $key, $legend, $choices, $help = '' ) {
	$base = 'ih-' . str_replace( '_', '-', $key );
	?>
	<fieldset class="ih-field ih-field--cards"<?php echo '' !== $help ? ' aria-describedby="' . esc_attr( $base ) . '-help"' : ''; ?>>
		<div class="ih-field__text">
			<legend class="ih-field__label"><?php echo esc_html( $legend ); ?></legend>
			<?php if ( '' !== $help ) : ?>
				<p class="description" id="<?php echo esc_attr( $base ); ?>-help"><?php echo esc_html( $help ); ?></p>
			<?php endif; ?>
		</div>
		<div class="ih-field__control">
			<div class="ih-choices">
				<?php foreach ( $choices as $value => $c ) : ?>
					<label class="ih-choice" for="<?php echo esc_attr( $base . '-' . $value ); ?>">
						<input type="radio" class="ih-choice__input" id="<?php echo esc_attr( $base . '-' . $value ); ?>" name="<?php echo esc_attr( injil_help_ui_name( $key ) ); ?>" value="<?php echo esc_attr( $value ); ?>" <?php checked( (string) $o[ $key ], (string) $value ); ?>>
						<span class="ih-choice__body">
							<span class="dashicons <?php echo esc_attr( $c[2] ?? 'dashicons-admin-appearance' ); ?>" aria-hidden="true"></span>
							<span class="ih-choice__title"><?php echo esc_html( $c[0] ); ?></span>
							<span class="ih-choice__desc"><?php echo esc_html( $c[1] ); ?></span>
						</span>
					</label>
				<?php endforeach; ?>
			</div>
		</div>
	</fieldset>
	<?php
}

/**
 * A range slider whose value is shown beside it as it moves.
 *
 * @param array  $o     Current options.
 * @param string $key   Option key.
 * @param string $label Label.
 * @param int    $min   Lowest value.
 * @param int    $max   Highest value.
 * @param string $help  Help text.
 */
function injil_help_ui_range( $o, $key, $label, $min, $max, $help = '' ) {
	$id      = 'ih-' . str_replace( '_', '-', $key );
	$control = sprintf(
		'<span class="ih-range"><input type="range" class="ih-range__input" id="%1$s" name="%2$s" min="%3$d" max="%4$d" step="1" value="%5$d"%6$s><output class="ih-range__value" for="%1$s" id="%1$s-value">%5$d</output></span>',
		esc_attr( $id ),
		esc_attr( injil_help_ui_name( $key ) ),
		(int) $min,
		(int) $max,
		(int) $o[ $key ],
		'' !== $help ? ' aria-describedby="' . esc_attr( $id ) . '-help"' : ''
	);
	injil_help_ui_row( $id, $label, $control, $help, 'ih-field--range' );
}

/**
 * A text, URL or textarea row.
 *
 * @param array  $o     Current options.
 * @param string $key   Option key.
 * @param string $label Label.
 * @param string $type  text, url or textarea.
 * @param string $help  Help text.
 * @param array  $attrs Extra attributes (placeholder, rows, maxlength).
 */
function injil_help_ui_input( $o, $key, $label, $type = 'text', $help = '', $attrs = array() ) {
	$id   = 'ih-' . str_replace( '_', '-', $key );
	$more  = '' !== $help ? ' aria-describedby="' . esc_attr( $id ) . '-help"' : '';
	$class = 'ih-input' . ( 'textarea' === $type ? ' ih-input--area' : '' ) . ( isset( $attrs['class'] ) ? ' ' . $attrs['class'] : '' );
	unset( $attrs['class'] );
	foreach ( $attrs as $attr => $value ) {
		$more .= ' ' . esc_attr( $attr ) . '="' . esc_attr( $value ) . '"';
	}
	if ( 'textarea' === $type ) {
		$control = sprintf( '<textarea class="%5$s" id="%1$s" name="%2$s"%3$s>%4$s</textarea>', esc_attr( $id ), esc_attr( injil_help_ui_name( $key ) ), $more, esc_textarea( (string) $o[ $key ] ), esc_attr( $class ) );
	} else {
		$control = sprintf( '<input type="%1$s" class="%6$s" id="%2$s" name="%3$s" value="%4$s"%5$s>', 'url' === $type ? 'url' : 'text', esc_attr( $id ), esc_attr( injil_help_ui_name( $key ) ), esc_attr( (string) $o[ $key ] ), $more, esc_attr( $class ) );
	}
	injil_help_ui_row( $id, $label, $control, $help );
}

/**
 * Open a card.
 *
 * @param string $title Card heading.
 * @param string $desc  Optional line under it.
 */
function injil_help_ui_card_open( $title, $desc = '' ) {
	echo '<div class="ih-card"><h2 class="ih-card__title">' . esc_html( $title ) . '</h2>';
	if ( '' !== $desc ) {
		echo '<p class="ih-card__desc">' . esc_html( $desc ) . '</p>';
	}
}

/** Close a card. */
function injil_help_ui_card_close() {
	echo '</div>';
}

/**
 * The usage note at the top of a tab.
 *
 * @param string $text Usage text.
 */
function injil_help_ui_usage( $text ) {
	echo '<div class="ih-usage"><span class="dashicons dashicons-info-outline" aria-hidden="true"></span><p><strong>' . esc_html__( 'How to use this tab.', 'injil-help' ) . '</strong> ' . esc_html( $text ) . '</p></div>';
}

/** The tabs of the screen: id => array( label, dashicon ). */
function injil_help_options_tabs() {
	return array(
		'colours'  => array( __( 'Colours', 'injil-help' ), 'dashicons-art' ),
		'branding' => array( __( 'Branding', 'injil-help' ), 'dashicons-admin-appearance' ),
		'sharing'  => array( __( 'Sharing', 'injil-help' ), 'dashicons-share' ),
		'links'    => array( __( 'Contextual links', 'injil-help' ), 'dashicons-admin-links' ),
		'seo'      => array( __( 'SEO', 'injil-help' ), 'dashicons-search' ),
		'security' => array( __( 'Security', 'injil-help' ), 'dashicons-shield' ),
		'content'  => array( __( 'Content', 'injil-help' ), 'dashicons-media-document' ),
	);
}

function injil_help_render_options() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to change the Theme Options.', 'injil-help' ), 403 );
	}
	$o    = injil_help_get_option();
	$tabs = injil_help_options_tabs();
	?>
	<div class="wrap ih-options">
		<header class="ih-header">
			<span class="ih-header__icon dashicons dashicons-book-alt" aria-hidden="true"></span>
			<div class="ih-header__text">
				<h1><?php esc_html_e( 'Theme Options', 'injil-help' ); ?> <span class="ih-badge"><?php echo esc_html( sprintf( /* translators: %s: theme version */ __( 'Version %s', 'injil-help' ), INJIL_HELP_VERSION ) ); ?></span></h1>
				<p class="ih-header__desc"><?php esc_html_e( 'Colours, layout, sharing, links, search output, security and the starter pages of The Injil. Choose a tab, change what you need and press Save Changes.', 'injil-help' ); ?></p>
			</div>
		</header>
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
		<form method="post" action="options.php" class="ih-form">
			<?php settings_fields( 'injil_help' ); ?>
			<div class="ih-tabs-wrap">
				<div class="ih-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Settings sections', 'injil-help' ); ?>">
					<?php foreach ( $tabs as $id => $tab ) : ?>
						<a href="#<?php echo esc_attr( $id ); ?>" class="ih-tab" role="tab" id="tab-<?php echo esc_attr( $id ); ?>" aria-controls="panel-<?php echo esc_attr( $id ); ?>" aria-selected="false"><span class="dashicons <?php echo esc_attr( $tab[1] ); ?>" aria-hidden="true"></span><span class="ih-tab__label"><?php echo esc_html( $tab[0] ); ?></span></a>
					<?php endforeach; ?>
				</div>
			</div>

			<section class="ih-panel" id="panel-colours" role="tabpanel" aria-labelledby="tab-colours" tabindex="0">
				<?php
				injil_help_ui_usage( __( 'Each swatch overrides one colour of the theme palette, on the site and in the editor. Pick a colour or type a hex value. The Default button under a picker empties the field, and an empty field keeps the colour of theme.json.', 'injil-help' ) );
				injil_help_ui_card_open( __( 'Palette', 'injil-help' ), __( 'Nine colours. Every border, icon, banner and button on the site reads them.', 'injil-help' ) );
				?>
				<div class="ih-colors">
					<?php foreach ( injil_help_color_fields() as $slug => $f ) : ?>
						<div class="ih-color-card">
							<label class="ih-field__label" for="ih-c-<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $f[0] ); ?></label>
							<input type="text" class="ih-color" id="ih-c-<?php echo esc_attr( $slug ); ?>" name="<?php echo esc_attr( injil_help_ui_name( 'colors', '[' . $slug . ']' ) ); ?>" value="<?php echo esc_attr( $o['colors'][ $slug ] ?? '' ); ?>" data-default-color="<?php echo esc_attr( $f[1] ); ?>">
							<code class="ih-color-card__slug"><?php echo esc_html( $slug . ' ' . $f[1] ); ?></code>
						</div>
					<?php endforeach; ?>
				</div>
				<?php injil_help_ui_card_close(); ?>
			</section>

			<section class="ih-panel" id="panel-branding" role="tabpanel" aria-labelledby="tab-branding" tabindex="0">
				<?php
				injil_help_ui_usage( __( 'Set how wide the text runs, how much ornament the pages carry and how the favicon is chosen. Click a card to choose it; the switches turn a feature on or off. Nothing changes on the site until you save.', 'injil-help' ) );
				injil_help_ui_card_open( __( 'Layout', 'injil-help' ) );
				injil_help_ui_cards(
					$o,
					'content_width',
					__( 'Content width', 'injil-help' ),
					array(
						'full'    => array( __( 'Full width', 'injil-help' ), __( 'The content fills the page between the side gutters.', 'injil-help' ), 'dashicons-editor-expand' ),
						'wide'    => array( __( 'Wide', 'injil-help' ), __( '1240px, the width of the header and footer.', 'injil-help' ), 'dashicons-align-wide' ),
						'reading' => array( __( 'Reading column', 'injil-help' ), __( 'A 720px column of text.', 'injil-help' ), 'dashicons-align-center' ),
					),
					__( 'How wide the text and tables of every page may run. Full width is the default, and the header, footer and front page widen with it. Reading column restores the former layout: a 720px column of text under the 1240px header, footer and front page. Tables and plates marked wide fill the width in every case.', 'injil-help' )
				);
				injil_help_ui_card_close();

				injil_help_ui_card_open( __( 'Ornament and motion', 'injil-help' ) );
				injil_help_ui_cards(
					$o,
					'ornament',
					__( 'Ornament', 'injil-help' ),
					array(
						'rich'   => array( __( 'Rich', 'injil-help' ), __( 'Pattern, icons, banners and frames.', 'injil-help' ), 'dashicons-star-filled' ),
						'subtle' => array( __( 'Subtle', 'injil-help' ), __( 'The same, with a fainter pattern.', 'injil-help' ), 'dashicons-star-half' ),
						'off'    => array( __( 'Off', 'injil-help' ), __( 'Plain pages.', 'injil-help' ), 'dashicons-star-empty' ),
					),
					__( 'The star pattern, section icons, tinted banners, badges and image frames. Everything takes its colour from the Colours tab.', 'injil-help' )
				);
				injil_help_ui_toggle( $o, 'animate', __( 'Let cards and answers rise into view', 'injil-help' ), __( 'They rise as the page scrolls.', 'injil-help' ) );
				injil_help_ui_cards(
					$o,
					'parallax',
					__( 'Parallax depth', 'injil-help' ),
					array(
						'strong' => array( __( 'Strong', 'injil-help' ), __( 'The full depth.', 'injil-help' ), 'dashicons-images-alt2' ),
						'gentle' => array( __( 'Gentle', 'injil-help' ), __( 'Half the movement.', 'injil-help' ), 'dashicons-images-alt' ),
						'off'    => array( __( 'Off', 'injil-help' ), __( 'Layers stay still.', 'injil-help' ), 'dashicons-format-image' ),
					),
					__( 'Layers move at different speeds as the page scrolls: the star lattice behind the page, the hero image and text, the section banners and their watermark icons, the manuscript plates, the card grids and the footer pattern. Switched off for visitors who ask for reduced motion.', 'injil-help' )
				);
				injil_help_ui_card_close();

				injil_help_ui_card_open( __( 'Brandmark and favicon', 'injil-help' ) );
				injil_help_ui_toggle( $o, 'footer_mark', __( 'Show the footer brandmark', 'injil-help' ), __( 'The brandmark at the top of the footer.', 'injil-help' ) );
				injil_help_ui_toggle( $o, 'favicon_force', __( 'Always use the brandmark as favicon', 'injil-help' ), __( 'By default the brandmark is used only when no Site Icon is set (Settings > General > Site Icon). Switch this on to use it even when one is set.', 'injil-help' ) );
				injil_help_ui_input( $o, 'favicon_url', __( 'Custom favicon URL', 'injil-help' ), 'url', __( 'Optional. Replaces the brandmark favicon. SVG, PNG or ICO.', 'injil-help' ), array( 'placeholder' => 'https://' ) );
				injil_help_ui_card_close();
				?>
			</section>

			<section class="ih-panel" id="panel-sharing" role="tabpanel" aria-labelledby="tab-sharing" tabindex="0">
				<?php
				injil_help_ui_usage( __( 'The share row sits under the content of posts and pages. Switch it on, choose where it shows and which networks it lists. Where the browser allows it, a Copy link button and a More button (the device share sheet) are added. The front page never shows the row.', 'injil-help' ) );
				injil_help_ui_card_open( __( 'Share row', 'injil-help' ) );
				injil_help_ui_toggle( $o, 'share', __( 'Show share links', 'injil-help' ), __( 'Icons take their colour from the Colours tab.', 'injil-help' ) );
				$types = (array) $o['share_types'];
				injil_help_ui_row( 'ih-share-types-post', __( 'Show share links on posts', 'injil-help' ), injil_help_ui_switch( 'ih-share-types-post', injil_help_ui_name( 'share_types', '[]' ), in_array( 'post', $types, true ), 'post' ), '', 'ih-field--toggle' );
				injil_help_ui_row( 'ih-share-types-page', __( 'Show share links on pages', 'injil-help' ), injil_help_ui_switch( 'ih-share-types-page', injil_help_ui_name( 'share_types', '[]' ), in_array( 'page', $types, true ), 'page' ), '', 'ih-field--toggle' );
				injil_help_ui_input( $o, 'share_label', __( 'Label', 'injil-help' ), 'text', __( 'The word before the icons. Empty shows "Share".', 'injil-help' ), array( 'placeholder' => __( 'Share', 'injil-help' ) ) );
				injil_help_ui_card_close();

				injil_help_ui_card_open( __( 'Networks', 'injil-help' ), __( 'Shown in this order. Pinterest uses the featured image of the page, or the default social image from the SEO tab.', 'injil-help' ) );
				?>
				<div class="ih-networks">
					<?php foreach ( injil_help_share_networks() as $key => $net ) : ?>
						<?php $nid = 'ih-net-' . $key; ?>
						<div class="ih-network">
							<label class="ih-network__label" for="<?php echo esc_attr( $nid ); ?>"><span class="ih-net-icon ih-share-<?php echo esc_attr( $key ); ?>" aria-hidden="true"></span><?php echo esc_html( $net[0] ); ?></label>
							<?php echo injil_help_ui_switch( $nid, injil_help_ui_name( 'share_networks', '[]' ), in_array( $key, (array) $o['share_networks'], true ), $key, 'aria-label="' . esc_attr( sprintf( /* translators: %s: network name */ __( 'Show %s in the share row', 'injil-help' ), $net[0] ) ) . '"' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside. ?>
						</div>
					<?php endforeach; ?>
				</div>
				<?php injil_help_ui_card_close(); ?>
			</section>

			<section class="ih-panel" id="panel-links" role="tabpanel" aria-labelledby="tab-links" tabindex="0">
				<?php
				injil_help_ui_usage( __( 'In the running text of a page, the first mention of a phrase the network map knows becomes a link to the inner page that treats it, here or on one of the six sister sites. Links are made as the page is shown, and the stored text is never changed. Set the two limits with the sliders, add your own phrases, and list the pages that should carry no links.', 'injil-help' ) );
				injil_help_ui_card_open( __( 'Where links go', 'injil-help' ), __( 'Never in a heading, a quotation, a verse, the gospel text, Arabic, a note, a table or an existing link. At most one new link in a paragraph, and each page is linked once.', 'injil-help' ) );
				injil_help_ui_toggle( $o, 'ctx_own', __( 'Link to pages on this site', 'injil-help' ), __( 'Pages on injil.help.', 'injil-help' ) );
				injil_help_ui_range( $o, 'ctx_own_max', __( 'Links to this site on one page', 'injil-help' ), 0, 15, __( 'At most this many, from 0 to 15.', 'injil-help' ) );
				injil_help_ui_toggle( $o, 'ctx_sister', __( 'Link to pages on the sister sites', 'injil-help' ), __( 'Pages on bestofislam.org, apostleofdoom.org, abrahamic-religions.com, compelling-evidence.com, murtadd.org and lailatulqadar.guide.', 'injil-help' ) );
				injil_help_ui_range( $o, 'ctx_sister_max', __( 'Links to sister sites on one page', 'injil-help' ), 0, 10, __( 'At most this many, from 0 to 10.', 'injil-help' ) );
				injil_help_ui_card_close();

				injil_help_ui_card_open( __( 'Your own phrases', 'injil-help' ) );
				injil_help_ui_input( $o, 'ctx_extra', __( 'More phrases', 'injil-help' ), 'textarea', __( 'One per line: phrase | address. A path such as /paul/james/ is a page on this site; a full address must be on one of the sister sites. These come before the shared map, so a phrase here wins.', 'injil-help' ), array( 'rows' => '6', 'placeholder' => 'Gospel of Peter | /gospels/peter/', 'class' => 'code' ) );
				injil_help_ui_input( $o, 'ctx_exclude', __( 'Pages without links', 'injil-help' ), 'textarea', __( 'One per line: a page slug (terms) or its path (paul/james).', 'injil-help' ), array( 'rows' => '3', 'placeholder' => 'terms' ) );
				injil_help_ui_card_close();
				?>
			</section>

			<section class="ih-panel" id="panel-seo" role="tabpanel" aria-labelledby="tab-seo" tabindex="0">
				<?php
				$room = sprintf( /* translators: %d: number of characters */ __( 'The title tag adds " | " and the site name. %d characters fit; a longer title is cut.', 'injil-help' ), injil_help_title_room() );
				injil_help_ui_usage( __( 'Choose who prints the titles, meta descriptions and JSON-LD graph, then fill in only the fields you want to change. An empty field keeps the default shown in grey. With Rank Math active, Automatic lets the theme work inside it.', 'injil-help' ) );
				injil_help_ui_card_open( __( 'Search output', 'injil-help' ) );
				injil_help_ui_cards(
					$o,
					'schema',
					__( 'Theme SEO output', 'injil-help' ),
					array(
						'auto' => array( __( 'Automatic', 'injil-help' ), __( 'With Rank Math the theme works inside it; off with Yoast or SEOPress.', 'injil-help' ), 'dashicons-update' ),
						'on'   => array( __( 'Always on', 'injil-help' ), __( 'The theme prints its head output whatever plugin is active.', 'injil-help' ), 'dashicons-yes-alt' ),
						'off'  => array( __( 'Always off', 'injil-help' ), __( 'The theme prints nothing.', 'injil-help' ), 'dashicons-dismiss' ),
					),
					__( 'Titles, meta description (trimmed to 160 characters), the breadcrumb row, Open Graph, and the JSON-LD graph with every schema.org type the content supports: Organization, Person, WebSite with search, site navigation, the page node (WebPage, CollectionPage, ProfilePage and AboutPage, ContactPage or SearchResultsPage), BreadcrumbList, Article with citations, Book and Chapter, the commentaries and their translations, DefinedTermSet, Table, Dataset, Event, Quotation, FAQPage and ImageObject licence metadata.', 'injil-help' )
				);
				injil_help_ui_card_close();

				injil_help_ui_card_open( __( 'Titles and descriptions', 'injil-help' ) );
				injil_help_ui_input( $o, 'home_title', __( 'Home title', 'injil-help' ), 'text', $room, array( 'placeholder' => injil_help_seo_defaults()['home_title'] ) );
				injil_help_ui_input( $o, 'home_description', __( 'Home description', 'injil-help' ), 'textarea', __( 'Keep under 160 characters for search results.', 'injil-help' ), array( 'rows' => '3', 'maxlength' => '320', 'placeholder' => injil_help_seo_defaults()['home_description'] ) );
				injil_help_ui_input( $o, 'pillar_title', __( 'Pillar page title', 'injil-help' ), 'text', $room, array( 'placeholder' => injil_help_seo_defaults()['pillar_title'] ) );
				injil_help_ui_input( $o, 'og_image', __( 'Default social image URL', 'injil-help' ), 'url', __( 'Used when a page has no featured image. Empty uses the Birmingham Qur’an manuscript bundled with the theme (1280 × 833).', 'injil-help' ), array( 'placeholder' => 'https://' ) );
				injil_help_ui_card_close();

				injil_help_ui_card_open( __( 'Structured data', 'injil-help' ), __( 'The Organization and Person nodes in the JSON-LD graph on every page. Empty name and URL fall back to the author of each page.', 'injil-help' ) );
				injil_help_ui_input( $o, 'author_name', __( 'Author name', 'injil-help' ) );
				injil_help_ui_input( $o, 'author_url', __( 'Author URL', 'injil-help' ), 'url', __( 'Empty uses the About page.', 'injil-help' ), array( 'placeholder' => 'https://' ) );
				injil_help_ui_input( $o, 'same_as', __( 'Profile links (sameAs)', 'injil-help' ), 'textarea', __( 'One address per line.', 'injil-help' ), array( 'rows' => '3', 'class' => 'code' ) );
				injil_help_ui_input( $o, 'org_logo', __( 'Organization logo URL', 'injil-help' ), 'url', __( 'Empty uses the Site Icon, or the brandmark when none is set. Google asks for at least 112 × 112 pixels.', 'injil-help' ), array( 'placeholder' => 'https://' ) );
				injil_help_ui_card_close();
				?>
			</section>

			<section class="ih-panel" id="panel-security" role="tabpanel" aria-labelledby="tab-security" tabindex="0">
				<?php
				injil_help_ui_usage( __( 'The switches close the doors spam tools use to publish from outside the site; all are on by default and writing in the editor is not affected. Below the Save button are the spam scan, the clean-up buttons and the reports. Only an administrator sees this tab in full.', 'injil-help' ) );
				if ( current_user_can( 'manage_options' ) ) {
					injil_help_sec_render_settings( $o );
				} else {
					echo '<div class="ih-card"><p class="description">' . esc_html__( 'Only an administrator can change the Security settings.', 'injil-help' ) . '</p></div>';
				}
				?>
			</section>

			<section class="ih-panel" id="panel-content" role="tabpanel" aria-labelledby="tab-content" tabindex="0">
				<?php
				injil_help_ui_usage( __( 'Choose how the gospel text is set and whether plates may load from Wikimedia Commons and Pexels. The list shows every starter page: green has been created, gold is missing. The first button creates the missing pages; the second rewrites every page from the theme, your edits included.', 'injil-help' ) );
				injil_help_ui_card_open( __( 'Gospel text', 'injil-help' ) );
				injil_help_ui_cards(
					$o,
					'gospel_layout',
					__( 'Gospel text layout', 'injil-help' ),
					array(
						'columns' => array( __( 'Bible', 'injil-help' ), __( 'Two columns, verse numbers, references at the foot.', 'injil-help' ), 'dashicons-book' ),
						'single'  => array( __( 'Single column', 'injil-help' ), __( 'One column with verse numbers.', 'injil-help' ), 'dashicons-editor-ol' ),
						'plain'   => array( __( 'Plain', 'injil-help' ), __( 'Paragraphs, no verse numbers.', 'injil-help' ), 'dashicons-editor-paragraph' ),
					),
					__( 'How the 27 sections of The Gospel According to Islam are set. The verse numbers and the Qur’anic references are in the page markup whichever layout is chosen; the layout only shows or hides them. Below 900px the text is always one column.', 'injil-help' )
				);
				injil_help_ui_card_close();

				injil_help_ui_card_open( __( 'Images and privacy', 'injil-help' ) );
				injil_help_ui_toggle( $o, 'remote_images', __( 'Load plates from Wikimedia Commons and Pexels', 'injil-help' ), __( 'Off by default. The theme copies each plate into the Media Library a few at a time on admin screen loads, and visitors are served that copy. A plate with no copy yet is left out of the page. Switch this on to show those plates from their source in the meantime; the browsers of your readers then contact commons.wikimedia.org, upload.wikimedia.org and images.pexels.com.', 'injil-help' ) );
				injil_help_ui_card_close();

				injil_help_ui_card_open( __( 'Starter pages', 'injil-help' ), __( 'The theme creates these pages when it is activated. When the theme is updated, the pages whose text changed are rewritten automatically, a few at a time as you move between admin screens, and pages you have edited are kept. Both buttons work in small batches, show their progress here and report what they did at the top of the screen.', 'injil-help' ) );
				// The list looks up every starter page; build it only on ?tab=content, or
				// fetch it over admin-ajax when the tab is first shown.
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- tab selection only.
				$ih_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
				if ( 'content' === $ih_tab ) {
					echo '<div id="ih-starter-pages">';
					injil_help_render_starter_pages();
					echo '</div>';
				} else {
					printf(
						'<div id="ih-starter-pages" data-ih-lazy="injil_help_starter_pages" aria-busy="true"><p><a class="button" href="%1$s">%2$s</a></p></div>',
						esc_url( add_query_arg( array( 'page' => 'injil-help-options', 'tab' => 'content' ), admin_url( 'themes.php' ) ) . '#content' ),
						esc_html__( 'Open the Content tab', 'injil-help' )
					);
				}
				?>
				<?php injil_help_ui_card_close(); ?>
			</section>

			<div class="ih-savebar"><?php submit_button( null, 'primary', 'submit', false ); ?></div>
		</form>
		<?php
		// The Security tools post their own forms, so they sit outside the options form; security.js shows them with the tab.
		injil_help_sec_render_tools();
		?>
		<footer class="ih-footer">
			<a href="<?php echo esc_url( admin_url( 'themes.php?theme=' . get_stylesheet() ) ); ?>"><span class="dashicons dashicons-media-text" aria-hidden="true"></span><?php esc_html_e( 'Theme details and readme', 'injil-help' ); ?></a>
			<a href="<?php echo esc_url( admin_url( 'site-health.php' ) ); ?>"><span class="dashicons dashicons-heart" aria-hidden="true"></span><?php esc_html_e( 'Site Health', 'injil-help' ); ?></a>
			<span class="ih-footer__version"><?php echo esc_html( 'The Injil ' . INJIL_HELP_VERSION ); ?></span>
		</footer>
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

/**
 * The starter page list and its two buttons, on the Content tab. It looks up
 * every starter page, so it runs only on ?tab=content or over admin-ajax.
 */
function injil_help_render_starter_pages() {
	$missing = 0;
	?>
	<ul class="ih-pages">
		<?php foreach ( injil_help_starter_pages() as $def ) : ?>
			<?php
			$page     = injil_help_find_page( $def['path'] );
			$missing += $page ? 0 : 1;
			?>
			<li class="<?php echo $page ? 'is-ok' : 'is-missing'; ?>">
				<span class="ih-dot" aria-hidden="true"></span>
				<?php if ( $page ) : ?>
					<a href="<?php echo esc_url( get_permalink( $page ) ); ?>"><?php echo esc_html( $def['title'] ); ?></a>
				<?php else : ?>
					<?php echo esc_html( $def['title'] ); ?> <span class="screen-reader-text"><?php esc_html_e( '(missing)', 'injil-help' ); ?></span>
				<?php endif; ?>
				<code>/<?php echo esc_html( $def['path'] ); ?>/</code>
			</li>
		<?php endforeach; ?>
	</ul>
	<p class="ih-actions">
		<a class="button button-primary" data-ih-populate="create" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=injil_help_populate' ), 'injil_help_populate' ) ); ?>"><?php echo esc_html( $missing ? sprintf( /* translators: %d: missing pages */ __( 'Create %d missing pages', 'injil-help' ), $missing ) : __( 'Check again', 'injil-help' ) ); ?></a>
		<a class="button" data-ih-populate="update" data-confirm="<?php echo esc_attr__( 'Rewrite every listed page from the theme? Edits you made to those pages will be lost.', 'injil-help' ); ?>" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=injil_help_populate&overwrite=1' ), 'injil_help_populate' ) ); ?>"><?php esc_html_e( 'Update all pages from the theme', 'injil-help' ); ?></a>
	</p>
	<?php
}

add_action(
	'wp_ajax_injil_help_starter_pages',
	function () {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to do this.', 'injil-help' ) ), 403 );
		}
		check_ajax_referer( 'injil_help_starter_pages', 'nonce' );
		ob_start();
		injil_help_render_starter_pages();
		wp_send_json_success( array( 'html' => ob_get_clean() ) );
	}
);
