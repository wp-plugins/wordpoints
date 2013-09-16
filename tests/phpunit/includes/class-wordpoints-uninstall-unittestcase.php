<?php

/**
 * Test uninstallation.
 *
 * @package WordPoints\Tests
 * @since 1.0.0
 */

abstract class WordPoints_Uninstall_UnitTestCase extends WP_UnitTestCase {

	/**
	 * Run the WordPoints uninstall script.
	 *
	 * Call it and then run your assertions.
	 *
	 * @since 1.0.0
	 */
	public static function uninstall() {

		define( 'WP_UNINSTALL_PLUGIN', WORDPOINTS_DIR . '/wordpoints.php' );

		include WORDPOINTS_DIR . '/uninstall.php';
	}

	/**
	 * Asserts that a database table does not exist.
	 *
	 * @since 1.0.0
	 *
	 * @param string $table	  The table name.
	 * @param string $message An optional message.
	 *
	 * @throws PHPUnit_Framework_AssertionFailedError
	 */
	public static function assertTableNotExists( $table, $message = '' ) {

		self::assertThat( $table, self::isNotInDatabase(), $message );
	}

	/**
	 * Asserts that no options with a given prefix exist.
	 *
	 * @since 1.0.0
	 *
	 * @param string $prefix  The prefix to check for.
	 * @param string $message An optional message.
	 *
	 * @throws PHPUnit_Framework_AssertionFailedError
	 */
	public static function assertNoOptionsWithPrefix( $prefix, $message = '' ) {

		global $wpdb;

		self::assertThat( $prefix, self::tableColumnHasNoRowsWithPrefix( $wpdb->options, 'option_name', $prefix ), $message );
	}

	/**
	 * Asserts that no usermeta with a given prefix exists.
	 *
	 * @since 1.0.0
	 *
	 * @param string $prefix  The prefix to check for.
	 * @param string $message An optional message.
	 *
	 * @throws PHPUnit_Framework_AssertionFailedError
	 */
	public static function assertNoUserMetaWithPrefix( $prefix, $message = '' ) {

		global $wpdb;

		self::assertThat( $prefix, self::tableColumnHasNoRowsWithPrefix( $wpdb->usermeta, 'meta_key', $prefix ), $message );
	}

	/**
	 * Asserts that no postmeta with a given prefix exists.
	 *
	 * @since 1.0.0
	 *
	 * @param string $prefix  The prefix to check for.
	 * @param string $message An optional message.
	 *
	 * @throws PHPUnit_Framework_AssertionFailedError
	 */
	public static function assertNoPostMetaWithPrefix( $prefix, $message = '' ) {

		global $wpdb;

		self::assertThat( $prefix, self::tableColumnHasNoRowsWithPrefix( $wpdb->postmeta, 'meta_key', $prefix ), $message );
	}

	/**
	 * Asserts that no commentmeta with a given prefix exist.
	 *
	 * @since 1.0.0
	 *
	 * @param string $prefix  The prefix to check for.
	 * @param string $message An optional message.
	 *
	 * @throws PHPUnit_Framework_AssertionFailedError
	 */
	public static function assertNoCommentMetaWithPrefix( $prefix, $message = '' ) {

		global $wpdb;

		self::assertThat( $prefix, self::tableColumnHasNoRowsWithPrefix( $wpdb->commentmeta, 'meta_key', $prefix ), $message );
	}

	/**
	 * Database table not existant constraint.
	 *
	 * @since 1.0.0
	 *
	 * @return WordPoints_PHPUnit_Constraint_IsTableExistant
	 */
	public static function isNotInDatabase() {

		return new WordPoints_PHPUnit_Constraint_IsTableExistant;
	}

	/**
	 * No row values with prefix in DB table constraint.
	 *
	 * @since 1.0.0
	 *
	 * @param string $table  The name of the table.
	 * @param string $column The name of the row in the table to check.
	 *
	 * @return WordPoints_PHPUnit_Constraint_NoRowsWithPrefix
	 */
	public static function tableColumnHasNoRowsWithPrefix( $table, $column, $prefix ) {

		return new WordPoints_PHPUnit_Constraint_NoRowsWithPrefix( $table, $column, $prefix );
	}
}

/**
 * Database table not existant constraint matcher.
 *
 * @since 1.0.0
 */
class WordPoints_PHPUnit_Constraint_IsTableExistant extends PHPUnit_Framework_Constraint {

	/**
	 * Checks if $table exists in the database.
	 *
	 * @since 1.0.0
	 *
	 * @param string $table The name of the table that shouldn't exist.
	 *
	 * @return bool Whether the table is non existant.
	 */
	public function matches( $table ) {

		return ! wordpoints_db_table_exists( $table );
	}

	/**
	 * Returns a string representation of the constraint.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function toString() {

		return 'is not a table in the database';
	}
}

/**
 * Database table column has no rows with prefix constraint matcher.
 *
 * @since 1.0.0
 */
class WordPoints_PHPUnit_Constraint_NoRowsWithPrefix extends PHPUnit_Framework_Constraint {

	/**
	 * The table to check in.
	 *
	 * @since 1.0.0
	 *
	 * @type string $table
	 */
	private $table;

	/**
	 * The column to check in.
	 *
	 * @since 1.0.0
	 *
	 * @type string $column
	 */
	private $column;

	/**
	 * The prefix that should not be present.
	 *
	 * @since 1.0.0
	 *
	 * @type string $prefix
	 */
	private $prefix;

	/**
	 * The rows in the table that have the prefix.
	 *
	 * @since 1.0.0
	 *
	 * @type array $prefixed_rows
	 */
	private $prefixed_rows = array();

	/**
	 * Construct the class.
	 *
	 * @since 1.0.0
	 *
	 * @param string $table
	 * @param string $column
	 * @param string $prefix
	 */
	public function __construct( $table, $column, $prefix ) {

		$this->table  = esc_sql( $table );
		$this->column = esc_sql( $column );
		$this->prefix = $prefix;
	}

	/**
	 * Checks that no rows in the specified table column have the $prefix.
	 *
	 * @since 1.0.0
	 *
	 * @param string $prefix The prefix that should not be present.
	 *
	 * @return bool Whether the prefix is absent.
	 */
	public function matches( $prefix ) {

		global $wpdb;

		$prefix = esc_sql( $prefix );

		$rows = $wpdb->get_var(
			"
				SELECT COUNT(`{$this->column}`)
				FROM `{$this->table}`
				WHERE `{$this->column}` LIKE '{$prefix}%'
			"
		);

		if ( 0 == $rows )
			return true;

		$prefixed_rows = $wpdb->get_col(
			"
				SELECT `{$this->column}`
				FROM `{$this->table}`
				WHERE `{$this->column}` LIKE '{$prefix}%'
			"
		);

		if ( is_array( $prefixed_rows ) )
			$this->prefixed_rows = array_unique( $prefixed_rows );

		return false;
	}

	/**
	 * Returns a string representation of the constraint.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function toString() {

		return "prefix does not exist in `{$this->table}`.`{$this->column}`.\n"
			. "The following rows were found:\n\t" . implode( "\t\n", $this->prefixed_rows ) . "\n";
	}
}

// end of file.