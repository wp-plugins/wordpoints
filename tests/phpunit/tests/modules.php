<?php

/**
 * Test the WordPoints_Components class.
 *
 * @package WordPoints\Tests
 * @since $ver$
 *
 * @group modules
 */

class WordPoints_Modules_Test extends WP_UnitTestCase {

	/**
	 * Set up for the tests.
	 *
	 * @sine $ver$
	 */
	public function setUp() {

		parent::setUp();

		remove_filter( 'wordpoints_module_active', '__return_true', 100 );
	}

	/**
	 * Clean up after the tests.
	 *
	 * @scince $ver$
	 */
	public function tearDown() {

		add_filter( 'wordpoints_module_active', '__return_true', 100 );

		parent::tearDown();
	}

	/**
	 * Test that the instance() method returns and instance of the class.
	 *
	 * @since $ver$
	 */
	public function test_instance_returns_instance() {

		$this->assertInstanceOf( 'WordPoints_Modules', WordPoints_Modules::instance() );
	}

	/**
	 * Test registration functions.
	 *
	 * @since $ver$
	 */
	public function test_registration() {

		$modules = WordPoints_Modules::instance();

		$modules->register(
			array(
				'slug'    => 'test_3',
				'name'    => 'Test 3',
				'version' => '0.3-beta-60986740293859',
			)
		);

		$this->assertArrayHasKey( 'test_3', $modules->get() );
		$this->assertTrue( $modules->is_registered( 'test_3' ) );

		$modules->deregister( 'test_3' );

		$this->assertArrayNotHasKey( 'test_3', $modules->get() );
		$this->assertFalse( $modules->is_registered( 'test_3' ) );
	}

	/**
	 * Test that register() returns false if already registered.
	 *
	 * @since $ver$
	 */
	public function test_register_fails_if_already_registered() {

		$this->assertFalse( WordPoints_Modules::instance()->register( 'points' ) );
	}

	/**
	 * Test activation.
	 *
	 * @since $ver$
	 */
	public function test_activation() {

		$modules = WordPoints_Modules::instance();

		$modules->register(
			array(
				'slug'    => 'test_4',
				'name'    => 'Test 4',
				'version' => '1.5.5',
			)
		);

		$modules->activate( 'test_4' );

		$this->assertTrue( $modules->is_active( 'test_4' ) );
		$this->assertArrayHasKey( 'test_4', $modules->get_active() );
		$this->assertEquals( 1, did_action( 'wordpoints_module_activate-test_4' ) );

		$modules->deactivate( 'test_4' );

		$this->assertFalse( $modules->is_active( 'test_4' ) );
		$this->assertArrayNotHasKey( 'test_4', $modules->get_active() );
		$this->assertEquals( 1, did_action( 'wordpoints_module_deactivate-test_4' ) );
	}

	/**
	 * Test that an unregistered component can't be activated.
	 *
	 * @since $ver$
	 */
	public function test_activation_fails_if_not_registered() {

		$this->assertFalse( WordPoints_Modules::instance()->activate( 'not_registered' ) );
	}

	/**
	 * Test module loading.
	 *
	 * @since $ver$
	 */
	public function test_module_loading() {

		wordpointstests_load_test_modules();

		$modules = WordPoints_Modules::instance();

		$this->assertTrue( $modules->is_registered( 'test_1' ) );
		$this->assertTrue( $modules->is_registered( 'test_2' ) );
	}
}

// end of file /tests/phpunit/tests/components.php
