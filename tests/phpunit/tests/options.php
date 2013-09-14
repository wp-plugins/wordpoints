<?php

/**
 * Test the WordPoints option wrappers.
 *
 * @package WordPoints\Tests\Options
 * @since 1.0.0
 */
class WordPoints_Get_Array_Option_Test extends WP_UnitTestCase {

	/**
	 * Test that wordpoints_get_option() handles incorrect types properly.
	 *
	 * @since 1.0.0
	 */
	public function test_get_typechecks() {

		add_option( 'wordpoints_not_array', 'blah' );
		$array_option = wordpoints_get_array_option( 'wordpoints_not_array' );
		$this->assertEquals( array(), $array_option );
	}
}
