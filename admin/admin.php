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
 *        $_GET['tab'] is not set, or not one of the values in $tabs.
 *
 * @return string
 */
function wordpoints_admin_get_current_tab( array $tabs = null ) {

	$tab = '';

	if ( isset( $_GET['tab'] ) ) {

		$tab = $_GET['tab'];
	}

	if ( isset( $tabs ) && ! isset( $tabs[ $tab ] ) ) {

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

/**
 * Activate/deactivate components.
 *
 * This function handles activation and deactivation of components from the
 * WordPoints > Configure > Modules administration screen.
 *
 * @since $ver$
 *
 * @action load-toplevel_page_wordpoints_configure
 */
function wordpoints_admin_activate_components() {

	if ( wordpoints_admin_get_current_tab() != 'components' || ! isset( $_POST['wordpoints_component'], $_POST['wordpoints_component_action'], $_POST['_wpnonce'] ) )
		return;

	$components = WordPoints_Components::instance();

	switch ( $_POST['wordpoints_component_action'] ) {

		case 'activate':
			if ( 1 == wp_verify_nonce( $_POST['_wpnonce'], "wordpoints_activate_component-{$_POST['wordpoints_component']}" ) && $components->activate( $_POST['wordpoints_component'] ) ) {

				$message = array( 'message' => 1 );

			} else {

				$message = array( 'error' => 1 );
			}
		break;

		case 'deactivate':
			if ( 1 == wp_verify_nonce( $_POST['_wpnonce'], "wordpoints_deactivate_component-{$_POST['wordpoints_component']}" ) && $components->deactivate( $_POST['wordpoints_component'] ) ) {

				$message = array( 'message' => 2 );

			} else {

				$message = array( 'error' => 2 );
			}
		break;

		default: return;
	}

	wp_redirect(
		add_query_arg(
			$message + array(
				'page'                 => 'wordpoints_configure',
				'tab'                  => 'components',
				'wordpoints_component' => $_POST['wordpoints_component'],
				'_wpnonce'             => wp_create_nonce( "wordpoints_component_" . key( $message ) . "-{$_POST['wordpoints_component']}" )
			)
			, admin_url( 'admin.php' )
		)
	);

	exit;
}
add_action( 'load-toplevel_page_wordpoints_configure', 'wordpoints_admin_activate_components' );

/**
 * Activate/deactivate modules.
 *
 * This function handles activation and deactivation of modules from the WordPoints
 * > Configure > Modules administration screen.
 *
 * @since $ver$
 *
 * @action load-toplevel_page_wordpoints_configure
 */
function wordpoints_admin_activate_modules() {

	if ( wordpoints_admin_get_current_tab() != 'modules' || ! isset( $_POST['wordpoints_module'], $_POST['wordpoints_module_action'], $_POST['_wpnonce'] ) )
		return;

	$modules = WordPoints_Modules::instance();

	switch ( $_POST['wordpoints_module_action'] ) {

		case 'activate':
			if ( 1 == wp_verify_nonce( $_POST['_wpnonce'], "wordpoints_activate_module-{$_POST['wordpoints_module']}" ) && $modules->activate( $_POST['wordpoints_module'] ) ) {

				$message = array( 'message' => 1 );

			} else {

				$message = array( 'error' => 1 );
			}
		break;

		case 'deactivate':
			if ( 1 == wp_verify_nonce( $_POST['_wpnonce'], "wordpoints_deactivate_module-{$_POST['wordpoints_module']}" ) && $modules->deactivate( $_POST['wordpoints_module'] ) ) {

				$message = array( 'message' => 2 );

			} else {

				$message = array( 'error' => 2 );
			}
		break;

		default: return;
	}

	wp_redirect(
		add_query_arg(
			$message + array(
				'page'              => 'wordpoints_configure',
				'tab'               => 'modules',
				'wordpoints_module' => $_POST['wordpoints_module'],
				'_wpnonce'          => wp_create_nonce( "wordpoints_module_" . key( $message ) . "-{$_POST['wordpoints_module']}" )
			)
			, admin_url( 'admin.php' )
		)
	);

	exit;
}
add_action( 'load-toplevel_page_wordpoints_configure', 'wordpoints_admin_activate_modules' );

// end of file /admin/admin.php
