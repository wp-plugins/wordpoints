<?php

/**
 * Test case parent for the points tests.
 *
 * @package WordPoints\Tests\Points
 * @since 1.0.0
 */
class WordPoints_Points_UnitTestCase extends WP_UnitTestCase {

	/**
	 * The default points data set up for each test.
	 *
	 * @since 1.0.0
	 *
	 * @type array $points_data
	 */
	protected $points_data;

	/**
	 * The hooks that have been created (if any).
	 *
	 * @since 1.0.0
	 *
	 * @type array $hooks
	 */
	private $hooks = array();

	/**
	 * Set up the points type.
	 *
	 * @since 1.0.0
	 */
	public function setUp() {

		parent::setUp();

		$this->points_data = array(
			'name'   => 'Points',
			'prefix' => '$',
			'suffix' => 'pts.',
		);

		add_option( 'wordpoints_points_types', array( 'points' => $this->points_data ) );

		WordPoints_Points_Types::_reset();
	}

	/**
	 * Tear down.
	 *
	 * @since 1.0.0
	 */
	public function tearDown() {

		if ( $this->hooks ) {

			foreach ( $this->hooks as $hook ) {

				$hook->delete_callback( $hook->get_id( 1 ) );
			}

			$this->hooks = array();
		}

		parent::tearDown();
	}

	/**
	 * Programatically create a new instance of a points hook.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook_type The type of hook to create.
	 * @param array  $instance  The arguments for the instance.
	 */
	protected function new_points_hook_instance( $hook_type, $instance ) {

		update_option( 'wordpoints_points_types_hooks', array( 'points' => array( $hook_type . '-1' ) ) );

		$hook = WordPoints_Points_Hooks::get_handler_by_id_base( $hook_type );
		$hook->update_callback( $instance, 1 );

		$this->hooks[] = $hook;
	}
}

// end of file /tests/class-wordpoints-points-unittestcase.php
