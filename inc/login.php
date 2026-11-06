<?php
/**
 * The log-in screen in the site's own dress: its palette, typefaces, brandmark, star lattice and the
 * Qur'anic gloss of the header, in place of the WordPress defaults. The words are in
 * inc/data/text.json (group "login"), the styles in assets/css/login.css, which reads only the
 * palette and font tokens of theme.json.
 *
 * Theme Options > Branding > Log-in screen turns the design off (`login_design`) and sets an
 * optional note above the form (`login_message`). While a plugin that dresses the log-in screen
 * is active (LoginPress, Custom Login, Login Logo, Branda, Admin Custom Login and the like), the
 * theme leaves the screen to it. WPS Hide Login only moves the address, so the design follows it.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether a plugin that styles the log-in screen is active. Filter: injil_help_login_plugin_active.
 *
 * @return bool
 */
function injil_help_login_plugin_active() {
	$active = defined( 'LOGINPRESS_VERSION' ) || class_exists( 'LoginPress' )
		|| defined( 'CUSTOM_LOGIN_VERSION' ) || class_exists( 'CWS_Login_Logo_Plugin' )
		|| defined( 'BRANDA_VERSION' ) || class_exists( 'Branda' )
		|| function_exists( 'ulp_admin_custom_login' ) || defined( 'ACL_PLUGIN_URL' )
		|| class_exists( 'Login_Designer' ) || defined( 'ERIDENT_CUSTOM_LOGIN_VERSION' ) || function_exists( 'er_login_logo' );
	return (bool) apply_filters( 'injil_help_login_plugin_active', $active );
}

/**
 * Whether the theme dresses the log-in screen: the switch is on and no log-in plugin does it.
 *
 * @return bool
 */
function injil_help_login_design_on() {
	$on = function_exists( 'injil_help_get_option' ) ? injil_help_get_option( 'login_design' ) : 1;
	return ! empty( $on ) && ! injil_help_login_plugin_active();
}

/** Whether this request is the pop-up log-in shown when a session expires. */
function injil_help_login_is_interim() {
	return isset( $_REQUEST['interim-login'] ); // phpcs:ignore WordPress.Security.NonceVerification -- read only
}

/** The screen wp-login.php is serving, as its action. */
function injil_help_login_action() {
	return isset( $_REQUEST['action'] ) && is_string( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : 'login'; // phpcs:ignore WordPress.Security.NonceVerification -- read only, to name the screen
}

/** The stylesheet, with the theme.json tokens and any colours set under Theme Options. */
function injil_help_login_assets() {
	if ( ! injil_help_login_design_on() ) {
		return;
	}
	wp_enqueue_style( 'injil-help-login', get_stylesheet_directory_uri() . '/assets/css/login.css', array( 'login' ), INJIL_HELP_VERSION );
	// The log-in screen loads no global styles: give it the theme.json tokens, and any colours set under Theme Options.
	wp_add_inline_style( 'injil-help-login', wp_get_global_stylesheet( array( 'variables' ) ) . ( function_exists( 'injil_help_color_css' ) ? injil_help_color_css() : '' ) );
}
add_action( 'login_enqueue_scripts', 'injil_help_login_assets' );

/** The typefaces declared in theme.json (KFGQPC Hafs for the gloss, Kagnue, Garamond, Sabon, Evangelion). */
function injil_help_login_fonts() {
	if ( injil_help_login_design_on() ) {
		wp_print_font_faces();
	}
}
add_action( 'login_head', 'injil_help_login_fonts' );

/**
 * The brandmark links home.
 *
 * @param string $url Core's address.
 * @return string
 */
function injil_help_login_header_url( $url = '' ) {
	return injil_help_login_design_on() ? home_url( '/' ) : $url;
}
add_filter( 'login_headerurl', 'injil_help_login_header_url' );

/**
 * The link's text is the site name, set under the brandmark.
 *
 * @param string $text Core's text.
 * @return string
 */
function injil_help_login_header_text( $text = '' ) {
	return injil_help_login_design_on() ? wp_strip_all_tags( get_bloginfo( 'name' ) ) : $text;
}
add_filter( 'login_headertext', 'injil_help_login_header_text' );

/**
 * Body classes that scope the design: ih-login, and ih-login--interim in the pop-up.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function injil_help_login_body_class( $classes ) {
	if ( ! injil_help_login_design_on() ) {
		return $classes;
	}
	$classes[] = 'ih-login';
	if ( injil_help_login_is_interim() ) {
		$classes[] = 'ih-login--interim';
	}
	return $classes;
}
add_filter( 'login_body_class', 'injil_help_login_body_class' );

/**
 * The gloss above the form (Q 5:47 in KFGQPC Hafs, with its English and reference), then the
 * note from Theme Options on the log-in screen itself (never in the pop-up, which has little room).
 *
 * @param string $message Messages core and plugins have added.
 * @return string
 */
function injil_help_login_message( $message ) {
	if ( ! injil_help_login_design_on() ) {
		return $message;
	}
	$note = function_exists( 'injil_help_get_option' ) ? trim( (string) injil_help_get_option( 'login_message' ) ) : '';
	if ( '' !== $note && 'login' === injil_help_login_action() && ! injil_help_login_is_interim() ) {
		$message .= '<p class="message ih-login-note">' . esc_html( $note ) . '</p>';
	}
	$gloss = injil_help_text( 'login', 'gloss' );
	if ( '' === $gloss ) {
		return $message;
	}
	return '<p class="ih-login-gloss"><span class="ih-quran" dir="rtl" lang="ar">' . esc_html( $gloss ) . '</span><span class="ih-login-gloss-en">' . esc_html( injil_help_text( 'login', 'gloss_en' ) ) . '</span></p>' . $message;
}
add_filter( 'login_message', 'injil_help_login_message' );

/**
 * The title tag keeps the site's rule: "<page part> | <site name>", under 60 characters. It holds
 * whether or not the design is on, since the rule covers every title the site prints.
 *
 * @param string $title Core's title.
 * @return string
 */
function injil_help_login_title( $title ) {
	if ( ! function_exists( 'injil_help_title_fit' ) ) {
		return $title;
	}
	$action = injil_help_login_action();
	$key    = in_array( $action, array( 'lostpassword', 'retrievepassword', 'rp', 'resetpass', 'register' ), true ) ? $action : 'login';
	$part   = injil_help_text( 'login', 'title_' . $key );
	$page = injil_help_title_fit( '' !== $part ? $part : injil_help_text( 'login', 'title_login' ), injil_help_title_room() );
	return '' !== $page ? $page . ' | ' . injil_help_title_site() : injil_help_title_site();
}
add_filter( 'login_title', 'injil_help_login_title' );

// The last line: functions.php checks for it, so a file an upload cut short is caught.
$GLOBALS['injil_help_loaded'][ basename( __FILE__, '.php' ) ] = true;
