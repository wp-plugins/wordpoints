<?php

/**
 * Test uninstallation.
 *
 * @package WordPoints\Tests
 * @since 1.0.0
 *
 * @group uninstall
 */

class WordPoints_Uninstall_Test extends WordPoints_Uninstall_UnitTestCase {

	/**
	 * Run the tests.
	 *
	 * @since 1.0.0
	 */
	public function test_uninstall() {

		/** do stuff here **/

		/**
		 * Set up for uninstall tests.
		 *
		 * @since 1.0.0
		 */
		do_action( 'pre_wordpoints_uninstall_tests' );

		// We're going to do real table dropping, not temporary tables.
		remove_filter( 'query', array( $this, '_drop_temporary_tables' ) );

		$this->uninstall();

		/**
		 * Run uninstall tests.
		 *
		 * @since 1.0.0
		 *
		 * @param WordPoints_Uninstall_UnitTestCase The current instance.
		 */
		do_action( 'wordpoints_uninstall_tests', $this );

		$this->assertTableNotExists( WORDPOINTS_POINTS_LOGS_DB );
		$this->assertTableNotExists( WORDPOINTS_POINTS_LOG_META_DB );

		$this->assertNoOptionsWithPrefix( 'wordpoints' );
		$this->assertNoUserMetaWithPrefix( 'wordpoints' );
		$this->assertNoCommentMetaWithPrefix( 'wordpoints' );

		$this->assertNoOptionsWithPrefix( 'widget_wordpoints' );
	}
}

// end of file.
