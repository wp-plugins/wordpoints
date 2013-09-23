<?php

/**
 * Miscellaneous functions used in the tests.
 *
 * @package WordPoints\Tests
 * @since 1.0.0
 */

/**
 * Manually load the plugin main file.
 *
 * The plugin won't be activated within the test WP environment, that's why we need
 * to load it manually. We also mock activate all components so they will be fully
 * loaded too.
 *
 * @since 1.0.0
 *
 * @filter muplugins_loaded
 */
function wordpointstests_manually_load_plugin() {

	add_filter( 'wordpoints_component_active', '__return_true', 100 );
	add_filter( 'wordpoints_module_active', '__return_true', 100 );

	add_action( 'wordpoints_components_loaded', 'wordpointstests_manually_activate_components', 0 );
	add_action( 'wordpoints_modules_loaded', 'wordpointstests_manually_activate_modules', 0 );

	require dirname( __FILE__ ) . '/../../../src/wordpoints.php';

	wordpoints_activate();
}

/**
 * Manually activate all components.
 *
 * @since 1.0.0
 *
 * @action wordpoints_components_loaded 0 Added by wordpointstests_manually_load_plugin().
 */
function wordpointstests_manually_activate_components() {

	$components = WordPoints_Components::instance();

	foreach ( $components->get() as $component => $data ) {

		do_action( "wordpoints_component_activate-{$component}" );
	}
}

/**
 * Manually activate all modules.
 *
 * @since 1.0.0
 *
 * @action wordpoints_modules_loaded 0 Added by wordpointstests_manually_load_plugin().
 */
function wordpointstests_manually_activate_modules() {

	$modules = WordPoints_Modules::instance();

	foreach ( $modules->get() as $module => $data ) {

		$modules->activate( $module );
	}
}

/**
 * Load the modules included with the tests.
 *
 * @since $ver$
 *
 * @return bool Whether the modules were loaded successfully.
 */
function wordpointstests_load_test_modules() {

	static $loaded = false;

	if ( ! $loaded ) {

		wordpoints_dir_include( dirname( dirname( __FILE__ ) ) . '/data/modules/' );

		$loaded = true;
	}

	do_action( 'wordpoints_modules_register' );
}

/**
 * Call a shortcode function by tag name.
 *
 * We can now avoid evil calls to do_shortcode( '[shortcode]' ).
 *
 * @since 1.0.0
 *
 * @param string $tag     The shortcode whose function to call.
 * @param array  $atts    The attributes to pass to the shortcode function. Optional.
 * @param array  $content The shortcodes content. Default is null (none).
 *
 * @return void|bool Returns false on failure. No return on success.
 */
function wordpointstests_do_shortcode_func( $tag, array $atts = array(), $content = null ) {

	global $shortcode_tags;

	if ( ! isset( $shortcode_tags[ $tag ] ) )
		return false;

	return call_user_func( $shortcode_tags[ $tag ], $atts, $content, $tag );
}

/**
 * Programatically create a new instance of a points hook.
 *
 * @since $ver$
 *
 * @param string $hook_type The type of hook to create.
 * @param array  $instance  The arguments for the instance. Optional.
 */
function wordpointstests_add_points_hook( $hook_type, $instance = array() ) {

	update_option( 'wordpoints_points_types_hooks', array( 'points' => array( $hook_type . '-1' ) ) );

	$hook = WordPoints_Points_Hooks::get_handler_by_id_base( $hook_type );
	$hook->update_callback( $instance, 1 );
}

/**
 * Programmatically save a new widget instance.
 *
 * Based on wp_ajax_save_widget().
 *
 * @since $ver$
 *
 * @param string $id_base    The base ID for instances of this widget.
 * @param array  $settings   The settings for this widget instance. Optional.
 * @param string $sidebar_id The ID of the sidebar to add the widget to. Optional.
 *
 * @return bool Whether the widget was saved successfully.
 */
function wordpointstests_add_widget( $id_base, array $settings = array(), $sidebar_id = null ) {

	global $wp_registered_widget_updates;
	static $multi_number = 0;
	$multi_number++;

	$sidebars = wp_get_sidebars_widgets();

	if ( isset( $sidebar_id ) ) {

		$sidebar = ( isset( $sidebars[ $sidebar_id ] ) ) ? $sidebars[ $sidebar_id ] : array();

	} else {

		$sidebar_id = key( $sidebars );
		$sidebar = array_shift( $sidebars );
	}

	$sidebar[] = $id_base . '-' . $multi_number;

	$_POST['sidebar'] = $sidebar_id;
	$_POST[ "widget-{$id_base}" ] = array( $multi_number => $settings );
	$_POST['widget-id'] = $sidebar;

	if (
		! isset( $wp_registered_widget_updates[ $id_base ] )
		|| ! is_callable( $wp_registered_widget_updates[ $id_base ]['callback'] )
	) {

		return false;
	}

	$control = $wp_registered_widget_updates[ $id_base ];

	return call_user_func_array( $control['callback'], $control['params'] );
}

/**
 * Check if selenium server is running.
 *
 * Selenium is required for the UI tests.
 *
 * @since $ver$
 *
 * @return bool
 */
function wordpointstests_selenium_is_running() {

	$selenium_running = false;
	$fp = @fsockopen( 'localhost', 4444 );

	if ( $fp !== false ) {

		$selenium_running = true;
		fclose( $fp );
	}

	return $selenium_running;
}

/**
 * Attempt to start Selenium.
 *
 * To make this work, add the following to wp-tests-config.php:
 * define( 'WORDPOINTS_TESTS_SELENIUM', '/path/to/selenium.jar' );
 *
 * @since $ver$
 */
function wordpointstests_start_selenium() {

	if ( ! defined( 'WORDPOINTS_TESTS_SELENIUM' ) )
		return false;

	$result = shell_exec( 'java -jar ' . escapeshellarg( WORDPOINTS_TESTS_SELENIUM ) );

	return ( $result && wordpointstests_selenium_is_running() );
}

/**
 * Get the user that is used in the UI tests.
 *
 * @since $ver$
 *
 * @return WP_User The user object.
 */
function wordpointstests_ui_user() {

	$user = get_user_by( 'login', 'wordpoints_ui_tester' );

	if ( ! $user ) {

		$user_factory = new WP_UnitTest_Factory_For_User();

		$user_id = $user_factory->create(
			array(
				'user_login' => 'wordpoints_ui_tester',
				'user_email' => 'wordpoints.ui.tester@example.com'
			)
		);

		wp_set_password( 'wordpoints_ui_tester', $user_id );

		$user = get_userdata( $user_id );
	}

	return $user;
}

/**
 * Create a symlink of a plugin in the WordPress tests suite and activate it.
 *
 * @since $ver$
 *
 * @return bool Whether this was successful.
 */
function wordpointstests_symlink_plugin( $plugin, $plugin_dir, $link_name = null ) {

	$link_name = dirname( WP_PLUGIN_DIR . '/' . $plugin );

	// Check if the symlink exists.
	if ( ! is_link( $link_name ) ) {

		shell_exec( 'ln -s ' . escapeshellarg( $plugin_dir ) . ' ' . escapeshellarg( $link_name ) );

		if ( ! is_link( $link_name ) )
			return false;
	}

    return true;
}

// end of file /tests/phpunit/includes/functions.php
