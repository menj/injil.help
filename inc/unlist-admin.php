<?php
/**
 * Unlisted posts in the admin (3.21.0): the box in the editor sidebar, the Unlisted column with
 * Quick Edit and Bulk Edit, the "Unlisted" post state and the Unlisted view of each list table.
 * Loaded by inc/unlist.php only in the admin and only while the plugin is inactive.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

/** The box, in the sidebar of the block editor and the classic editor alike. */
add_action(
	'add_meta_boxes',
	function () {
		add_meta_box( 'injil-help-unlist', __( 'Unlist', 'injil-help' ), 'injil_help_unlist_box', array_values( injil_help_unlist_post_types() ), 'side', 'high' );
	}
);

/**
 * The box's checkbox.
 *
 * @param WP_Post $post Post.
 */
function injil_help_unlist_box( $post ) {
	wp_nonce_field( 'injil_help_unlist_box', 'injil_help_unlist_nonce' );
	?>
	<p>
		<label for="injil-help-unlisted">
			<input type="checkbox" id="injil-help-unlisted" name="injil_help_unlisted" value="1" aria-describedby="injil-help-unlisted-help" <?php checked( injil_help_is_unlisted( $post->ID ) ); ?>>
			<?php esc_html_e( 'Unlist this post', 'injil-help' ); ?>
		</label>
	</p>
	<p class="description" id="injil-help-unlisted-help"><?php esc_html_e( 'The post opens for anyone with its address. It is left out of the home page, archives, feeds, search, the sitemaps and the previous and next links, and search engines are asked not to index it.', 'injil-help' ); ?></p>
	<?php
}

/**
 * Save from the box, Quick Edit or Bulk Edit, each checked by its own nonce.
 *
 * @param int $post_id Post ID.
 */
function injil_help_unlist_save( $post_id ) {
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || false !== wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	// phpcs:disable WordPress.Security.NonceVerification -- each branch verifies its nonce.
	if ( isset( $_REQUEST['bulk_edit'], $_REQUEST['injil_help_unlist_bulk'] ) ) {
		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'bulk-posts' ) ) {
			return;
		}
		$value = sanitize_key( wp_unslash( $_REQUEST['injil_help_unlist_bulk'] ) );
		if ( 'unlist' === $value || 'list' === $value ) {
			injil_help_set_unlisted( $post_id, 'unlist' === $value );
		}
		return;
	}
	if ( isset( $_POST['injil_help_unlist_qe_nonce'] ) ) {
		if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['injil_help_unlist_qe_nonce'] ) ), 'injil_help_unlist_quick' ) ) {
			injil_help_set_unlisted( $post_id, ! empty( $_POST['injil_help_unlist_quick'] ) );
		}
		return;
	}
	if ( isset( $_POST['injil_help_unlist_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['injil_help_unlist_nonce'] ) ), 'injil_help_unlist_box' ) ) {
		injil_help_set_unlisted( $post_id, ! empty( $_POST['injil_help_unlisted'] ) );
	}
	// phpcs:enable WordPress.Security.NonceVerification
}
add_action( 'save_post', 'injil_help_unlist_save' );

/** Whether the list table is showing the Unlisted view. */
function injil_help_unlist_view_on() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- a view switch only.
	return isset( $_GET['post_status'] ) && 'unlisted' === $_GET['post_status'];
}

add_filter(
	'display_post_states',
	function ( $states, $post ) {
		if ( ! injil_help_unlist_view_on() && $post instanceof WP_Post && injil_help_is_unlisted( $post->ID ) ) {
			$states['injil_help_unlisted'] = __( 'Unlisted', 'injil-help' );
		}
		return $states;
	},
	10,
	2
);

/* The Unlisted view, the column, Quick Edit and Bulk Edit on every public post type. */
add_action(
	'admin_init',
	function () {
		foreach ( injil_help_unlist_post_types() as $type ) {
			add_filter( "views_edit-{$type}", 'injil_help_unlist_view_link' );
			add_filter(
				"manage_{$type}_posts_columns",
				function ( $columns ) {
					$columns['injil_help_unlisted'] = __( 'Unlisted', 'injil-help' );
					return $columns;
				}
			);
			add_action(
				"manage_{$type}_posts_custom_column",
				function ( $column, $post_id ) {
					if ( 'injil_help_unlisted' === $column ) {
						$on = injil_help_is_unlisted( $post_id );
						printf( '<span class="injil-help-unlisted" data-unlisted="%s">%s</span>', $on ? '1' : '0', $on ? esc_html__( 'Yes', 'injil-help' ) : '&mdash;' );
					}
				},
				10,
				2
			);
		}
	}
);

/**
 * The "Unlisted (n)" link above the list.
 *
 * @param array $views Views.
 * @return array
 */
function injil_help_unlist_view_link( $views ) {
	$ids = injil_help_unlisted_ids();
	if ( ! $ids ) {
		return $views;
	}
	$screen = get_current_screen();
	$query  = new WP_Query(
		array(
			'post_type'      => $screen && $screen->post_type ? $screen->post_type : 'any',
			'post__in'       => $ids,
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);
	if ( $query->found_posts ) {
		$views['injil_help_unlisted'] = sprintf(
			'<a href="%1$s"%2$s>%3$s <span class="count">(%4$d)</span></a>',
			esc_url( add_query_arg( 'post_status', 'unlisted' ) ),
			injil_help_unlist_view_on() ? ' class="current" aria-current="page"' : '',
			esc_html__( 'Unlisted', 'injil-help' ),
			(int) $query->found_posts
		);
	}
	return $views;
}

add_action(
	'parse_query',
	function ( $query ) {
		global $pagenow;
		if ( is_admin() && 'edit.php' === $pagenow && $query->is_main_query() && injil_help_unlist_view_on() ) {
			$ids = injil_help_unlisted_ids();
			$query->query_vars['post__in']    = $ids ? $ids : array( 0 );
			$query->query_vars['post_status'] = 'any';
		}
	}
);

add_action(
	'quick_edit_custom_box',
	function ( $column, $type ) {
		if ( 'injil_help_unlisted' !== $column || ! in_array( $type, injil_help_unlist_post_types(), true ) ) {
			return;
		}
		wp_nonce_field( 'injil_help_unlist_quick', 'injil_help_unlist_qe_nonce' );
		?>
		<fieldset class="inline-edit-col-right"><div class="inline-edit-col">
			<label class="alignleft">
				<input type="checkbox" name="injil_help_unlist_quick" value="1">
				<span class="checkbox-title"><?php esc_html_e( 'Unlist this post', 'injil-help' ); ?></span>
			</label>
		</div></fieldset>
		<?php
	},
	10,
	2
);

add_action(
	'bulk_edit_custom_box',
	function ( $column, $type ) {
		if ( 'injil_help_unlisted' !== $column || ! in_array( $type, injil_help_unlist_post_types(), true ) ) {
			return;
		}
		?>
		<fieldset class="inline-edit-col-right"><div class="inline-edit-col">
			<label class="inline-edit-group">
				<span class="title"><?php esc_html_e( 'Unlisted', 'injil-help' ); ?></span>
				<select name="injil_help_unlist_bulk">
					<option value="-1"><?php esc_html_e( 'No change', 'injil-help' ); ?></option>
					<option value="unlist"><?php esc_html_e( 'Unlist', 'injil-help' ); ?></option>
					<option value="list"><?php esc_html_e( 'List', 'injil-help' ); ?></option>
				</select>
			</label>
		</div></fieldset>
		<?php
	},
	10,
	2
);

/* Quick Edit opens with the box ticked when the row is unlisted; the script is a file of its own. */
add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		$screen = get_current_screen();
		if ( 'edit.php' !== $hook || ! $screen || ! in_array( $screen->post_type, injil_help_unlist_post_types(), true ) ) {
			return;
		}
		wp_enqueue_script( 'injil-help-unlist', get_stylesheet_directory_uri() . '/assets/js/unlist-admin.js', array( 'jquery', 'inline-edit-post' ), INJIL_HELP_VERSION, true );
	}
);
