<?php

/**
 * Test module 2.
 *
 * @package WordPoints\Tests
 * @since $ver$
 */

/**
 * Register the second test module.
 *
 * @since $ver$
 */
function wordpoints_module_test_2_register() {

	wordpoints_register_module(
		array(
			'slug'        => 'test_2',
			'name'        => 'Test 2',
			'version'     => '1.0.0',
			'author'      => 'Me',
			'description' => 'Another test module',
		)
	);
}
add_action( 'wordpoints_modules_register', 'wordpoints_module_test_2_register' );
