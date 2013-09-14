<?php

/**
 * Set up environment for WordPoints tests suite.
 *
 * @package WordPoints\Tests
 * @since 1.0.0
 */

if ( ! getenv( 'WP_TESTS_DIR' ) ) {

	exit( 'WP_TESTS_DIR is not set.' );
}

/**
 * The WordPress tests functions.
 *
 * Clearly, WP_TESTS_DIR should be the path to the WordPress PHPUnit tests checkout.
 *
 * We are loading this so that we can add our tests filter to load the plugin, using
 * tests_add_filter().
 *
 * @since 1.0.0
 */
require_once getenv( 'WP_TESTS_DIR' ) . 'includes/functions.php';

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
function _manually_load_plugin() {

	add_filter( 'wordpoints_component_active', '__return_true' );
	add_action( 'wordpoints_components_loaded', '_manually_activate_components', 0 );
	add_action( 'wordpoints_modules_loaded', '_manually_activate_modules', 0 );

	require dirname( __FILE__ ) . '/../../src/wordpoints.php';

	wordpoints_activate();
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

/**
 * Manually activate all components.
 *
 * @since 1.0.0
 *
 * @action wordpoints_components_loaded 0 Added by _manually_load_plugin().
 */
function _manually_activate_components() {

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
 * @action wordpoints_modules_loaded 0 Added by _manually_load_plugin().
 */
function _manually_activate_modules() {

	$modules = WordPoints_Modules::instance();

	foreach ( $modules->get() as $module => $data ) {

		$modules->activate( $module );
	}
}

/**
 * Sets up the WordPress test environment.
 *
 * We've got our action set up, so we can load this now, and viola, the tests begin.
 * Again, WordPress' PHPUnit test suite needs to be installed under the given path.
 *
 * @since 1.0.0
 */
require getenv( 'WP_TESTS_DIR' ) . '/includes/bootstrap.php';

/**
 * The WordPoints_Points_UnitTestCase class.
 *
 * @since 1.0.0
 */
require_once dirname( __FILE__ ) . '/includes/class-wordpoints-points-unittestcase.php';

/**
 * The uninstall test class and helpers.
 *
 * @since 1.0.0
 */
require_once dirname( __FILE__ ) . '/includes/class-wordpoints-uninstall-unittestcase.php';

/**
 * Miscellaneous utility functions.
 *
 * @since $ver$
 */
require_once dirname( __FILE__ ) . '/includes/functions.php';

// end of file /tests/phpunit/bootstrap.php
