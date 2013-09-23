<?php

/**
 * Test component activation.
 *
 * @package WordPoints\Tests
 * @since $ver$
 *
 * @group uic
 */

class WordPoints_Module_UI_Test extends WordPoints_Selenium2TestCase {

	/**
	 * The user requires the manage_options capability.
	 *
	 * @since $ver$
	 *
	 * @type array $user_capabilities
	 */
	protected $user_capabilities = array( 'manage_options' => true );

	/**
	 * Set up before the tests.
	 *
	 * @since $ver$
	 */
	public function setUp() {

		parent::setUp();

		delete_option( 'wordpoints_active_modules' );

		remove_filter( 'wordpoints_module_active', '__return_true', 100 );

		wordpointstests_load_test_modules();
	}

	/**
	 * Test activation/deactivation of a component.
	 *
	 * @since $ver$
	 */
	public function test_activation_deactivation() {

		$this->url(
			add_query_arg(
				array(
					'page' => 'wordpoints_configure',
					'tab'  => 'modules',
				)
				,admin_url()
			)
		);

		try {

			// Activate the points component.
			$this->clickOnElement( 'wordpoints-module-activate_test_1' );
			$this->flush_cache();
			$this->assertTrue( wordpoints_module_is_active( 'test_1' ) );

			// Deactivate it.
			$this->clickOnElement( 'wordpoints-module-deactivate_test_1' );
			$this->flush_cache();
			$this->assertFalse( wordpoints_module_is_active( 'test_1' ) );

		} catch ( PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e ) {

			$this->markTestSkipped( 'You must define WORDPOINTS_MODULES_DIR to point to /data/modules/ in wp-config.php' );
		}
	}

	/**
	 * Clean up after the test.
	 *
	 * @since $ver$
	 */
	public function tearDown() {

		delete_option( 'wordpoints_active_modules' );

		add_filter( 'wordpoints_module_active', '__return_true', 100 );

		parent::tearDown();
	}
}

// end of file /tests/phpunit/tests/ui/components.php