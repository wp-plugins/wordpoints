<?php

/**
 * Administration-side functions.
 *
 * This and the included files are run on the admin side only. They create all of
 * the main administration screens, enqueue scripts and styles where needed, etc.
 *
 * Note that each component has its own administration package also.
 *
 * @package WordPoints\Administration
 * @since 1.0.0
 */

/**
 * Screen: Configuration.
 *
 * @since 1.0.0
 */
include_once WORDPOINTS_DIR . 'admin/screens/configure.php';

/**
 * Add admin screens to the administration menu.
 *
 * @since 1.0.0
 *
 * @action admin_menu
 */
function wordpoints_admin_menu() {

	// Main page.
	add_menu_page(
		'WordPoints'
		,'WordPoints'
		,'manage_options'
		,'wordpoints_configure'
		,'wordpoints_admin_screen_configure'
	);

	$configure =  __( 'Configure', 'wordpoints' );

	// Settings page.
	add_submenu_page(
		'wordpoints_configure'
		,'WordPoints - ' . $configure
		,$configure
		,'manage_options'
		,'wordpoints_configure'
		,'wordpoints_admin_screen_configure'
	);
}
add_action( 'admin_menu', 'wordpoints_admin_menu' );

/**
 * Display an error message.
 *
 * @since 1.0.0
 *
 * @uses wordpoints_show_admin_message()
 *
 * @param string $message The text for the error message.
 */
function wordpoints_show_admin_error( $message ) {

	wordpoints_show_admin_message( $message, 'error' );
}

/**
 * Display an update message.
 *
 * Note that $type is expected to be properly sanitized as needed (e.g., esc_attr()).
 * But you should use {@see wordpoints_show_admin_error()} instead for showing error
 * messages. Currently there aren't wrappers for the other types, as they aren't used
 * in WordPoints core.
 *
 * @since 1.0.0
 *
 * @param string $message The text for the message. Must be pre-validated if needed.
 * @param string $type    The type of message to display. Default is 'updated'.
 */
function wordpoints_show_admin_message( $message, $type = 'updated' ) {

	?>

	<div id="message" class="<?php echo $type; ?>">
		<p>
			<?php echo $message; ?>
		</p>
	</div>

	<?php
}

/**
 * Get the current tab.
 *
 * @since 1.0.0
 *
 * @param array $tabs The tabs. If passed, the first key will be returned if
 *        $_GET['tab'] is not set.
 *
 * @return string
 */
function wordpoints_admin_get_current_tab( $tabs = null ) {

	$tab = '';

	if ( isset( $_GET['tab'] ) ) {

		$tab = $_GET['tab'];

	} elseif ( isset( $tabs ) && is_array( $tabs ) ) {

		reset( $tabs );
		$tab = key( $tabs );
	}

	return $tab;
}

/**
 * Display a set of tabs.
 *
 * @since 1.0.0
 *
 * @uses wordpoints_admin_get_current_tab()
 *
 * @param string[] $tabs         The tabs. Keys are slugs, values displayed text.
 * @param bool     $show_heading Whether to show an <h2> element using the current
 *        tab. Default is true.
 */
function wordpoints_admin_show_tabs( $tabs, $show_heading = true ) {

	$current = wordpoints_admin_get_current_tab( $tabs );

	if ( $show_heading ) {

		echo '<h2>WordPoints - ', esc_html( $tabs[ $current ] ), '</h2>';
	}

    echo '<h2 class="nav-tab-wrapper">';

	$page = rawurlencode( $_GET['page'] );

    foreach ( $tabs as $tab => $name ) {

        $class = ( $tab == $current ) ? ' nav-tab-active' : '';

        echo '<a class="nav-tab', $class, '" href="?page=', $page, '&amp;tab=', rawurlencode( $tab ), '">', esc_html( $name ), '</a>';
    }

    echo '</h2>';
}

// end of file /admin/admin.php
