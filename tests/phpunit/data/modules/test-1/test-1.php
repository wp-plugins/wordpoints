<?php

/**
 * Test module 1.
 *
 * @package WordPoints\Tests
 * @since $ver$
 */

/**
 * Register the first test module.
 *
 * @since $ver$
 */
function wordpoints_module_test_1_register() {

	wordpoints_register_module(
		array(
			'slug'        => 'test_1',
			'name'        => 'Test 1',
			'version'     => '1.0.0',
			'author'      => 'Me',
			'description' => 'A test module',
		)
	);
}
add_action( 'wordpoints_modules_register', 'wordpoints_module_test_1_register' );
