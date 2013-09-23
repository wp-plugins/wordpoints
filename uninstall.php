<?php

/**
 * Uninstall the plugin.
 *
 * Uninstallation, as apposed to deactivation, will remove all of the plugin's data.
 *
 * @package WordPoints
 * @since 1.0.0
 */

// Exit if we aren't being uninstalled.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

if ( ! defined( 'WORDPOINTS_DIR' ) ) {

	// This likely won't be set, since the plugin isn't active (main file isn't loaded).
	define( 'WORDPOINTS_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'WORDPOINTS_VERSION' ) ) {

	define( 'WORDPOINTS_VERSION', '1.0.1' );
}

// Dependencies for the uninstall routine.
require_once WORDPOINTS_DIR . 'includes/functions.php';
require_once WORDPOINTS_DIR . 'includes/class-wordpoints-modules.php';
require_once WORDPOINTS_DIR . 'includes/class-wordpoints-components.php';

/*
 * Bulk 'deactivate' components and modules. No other filters should be applied later
 * than these (e.g., after 99) for this hook - doing so could have unexpected results.
 *
 * We do this so that we can load them to call the uninstall hooks, without them
 * being active. That means modules *must* load any dependencies for their uninstall
 * process.
 */
add_filter( 'wordpoints_module_active', '__return_false', 100 );
add_filter( 'wordpoints_component_active', '__return_false', 100 );

$modules    = WordPoints_Modules::instance();
$components = WordPoints_Components::instance();

// Load modules, run uninstall hook.
$modules->load();

foreach ( $modules->get() as $module => $data ) {

	/**
	 * Uninstall $module.
	 *
	 * You should hook into this to uninstall. If you have an install process, you
	 * *should* have an uninstall process too.
	 *
	 * @since 1.0.0
	 */
	do_action( "wordpoints_uninstall_module-{$module}" );
}

// Now for the components.
$components->load();

foreach ( $components->get() as $component => $data ) {

	/**
	 * Uninstall $component.
	 *
	 * @since 1.0.0
	 */
	do_action( "wordpoints_uninstall_component-{$component}" );
}

// Delete settings.
delete_option( 'wordpoints_data' );
delete_option( 'wordpoints_active_modules' );
delete_option( 'wordpoints_active_components' );
delete_option( 'wordpoints_excluded_users' );

// end of file /uninstall.php
