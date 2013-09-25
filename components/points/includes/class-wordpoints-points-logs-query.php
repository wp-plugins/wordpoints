<?php

/**
 * WordPoints_Points_Logs_Query class.
 *
 * @package WordPoints\Points
 * @since 1.0.0
 */

/**
 * Query the points logs database table.
 *
 * This class lets you query the points logs database. The arguments are similar to
 * those available in {@link http://codex.wordpress.org/Class_Reference/WP_Query
 * WP_Query}.
 *
 * @since 1.0.0
 */
class WordPoints_Points_Logs_Query {

	//
	// Private Vars.
	//

	/**
	 * The query arguments.
	 *
	 * @since 1.0.0
	 *
	 * @type array $_args
	 */
	private $_args = array();

	/**
	 * The database table columns.
	 *
	 * @since 1.0.0
	 *
	 * @type array $_fields
	 */
	private $_fields = array( 'id', 'user_id', 'log_type', 'text', 'points', 'points_type', 'blog_id', 'site_id', 'date' );

	/**
	 * Whether the query is ready for execution.
	 *
	 * @since 1.0.0
	 *
	 * @type bool $_query_ready
	 */
	private $_query_ready = false;

	/**
	 * The type of select being performed.
	 *
	 * @since 1.0.0
	 *
	 * @type string $_select_type
	 */
	private $_select_type = 'SELECT';

	/**
	 * The SELECT statement for the query.
	 *
	 * @since 1.0.0
	 *
	 * @type string $_select
	 */
	private $_select;

	/**
	 * The SELECT COUNT statement for a count query.
	 *
	 * @since 1.0.0
	 *
	 * @type string $_select_count
	 */
	private $_select_count = 'SELECT COUNT(*)';

	/**
	 * The JOIN query with the log meta table.
	 *
	 * @since 1.0.0
	 *
	 * @type string $_meta_join
	 */
	private $_meta_join;

	/**
	 * The WHERE clause for the query.
	 *
	 * @since 1.0.0
	 *
	 * @type string $_where
	 */
	private $_where;

	/**
	 * The array of conditions for the WHERE clause.
	 *
	 * @since 1.0.0
	 *
	 * @type array $_wheres
	 */
	private $_wheres = array();

	/**
	 * The LIMIT clause for the query.
	 *
	 * @since 1.0.0
	 *
	 * @type string $_limit
	 */
	private $_limit;

	/**
	 * The ORDER clause for the query.
	 *
	 * @since 1.0.0
	 *
	 * @type string $_order
	 */
	private $_order;

	/**
	 * Holds the results for the query and count.
	 *
	 * @since 1.0.0
	 *
	 * @type array $_cache
	 */
	private $_cache = array();

	//
	// Public Methods.
	//

	/**
	 * Construct the class.
	 *
	 * All of the arguments are expected *not* to be SQL escaped.
	 *
	 * Date searching is currently not provided by this class. We're waiting on
	 * WordPoints 3.7 and its WP_Date_Query class. Won't be long!
	 *
	 * @since 1.0.0
	 *
	 * @param array $args The arguments for the query {
	 *        @type string|array $fields              Fields to include in the results.
	 *        @type int          $limit               The maximum number of results to return. Default is null (no limit).
	 *        @type int          $start               The start for the LIMIT clause. Default: 0.
	 *        @type string       $orderby             The field to use to order the results. Default: 'date'.
	 *        @type string       $order               The order for the query: ASC or DESC (default).
	 *        @type int          $user_id             Limit results to logs for this user.
	 *        @type int[]        $user__in            Limit results to logs for these users.
	 *        @type int[]        $user__not_in        Exclude all logs for these users from the results.
	 *        @type string       $points_type         Include only results for this type.
	 *        @type string[]     $points_type__in     Limit results to these types.
	 *        @type string[]     $points_type__not_in Exclude logs for these points types from the results.
	 *        @type string       $log_type            Return only logs of this type.
	 *        @type string[]     $log_type__in        Return only logs of these types.
	 *        @type string[]     $log_type__not_in    Exclude these log types from the results.
	 *        @type int          $points              Limit results to transactions of this amount. More uses when used with $points_compare.
	 *        @type string       $points_compare      Comparison operator for logs comparison with $points. May be any of these: '=', '<', '>', '<>', '!=', '<=', '>='. Default is '='.
	 *        @type int          $blog_id             Limit results to those from this blog within the network (mulitsite). Default is $wpdb->blogid (current blog).
	 *        @type int[]        $blog__in            Limit results to these blogs.
	 *        @type int[]        $blog__not_in        Exclude these blogs.
	 *        @type int          $site_id             Limit results to this network.
	 *              Default is $wpdb->siteid (current network). There isn't currently
	 *              a use for this one, but its possible in future that WordPress
	 *              will allow multi-network installs.
	 *        @type array $meta_query Arguments for INNER JOIN on meta table {
	 *              @type int    $id            Query only the log for this meta entry.
	 *              @type int[]  $id__in        Limit results to logs matching these meta IDs.
	 *              @type int[]  $id__not_in    Exclude results for these meta entries.
	 *              @type string $key           Return logs which have meta for this key.
	 *              @type mixed  $value         Return logs which have metadata matching this value.
	 *              @type array  $value__in     Limit results to entries with metadata matching these meta values.
	 *              @type array  $value__not_in Exclude entries with metadata matching these meta values.
	 *        }
	 * }
	 */
	public function __construct( $args ) {

		global $wpdb;

		$defaults = array(
			'fields'              => 'all',
			'limit'               => null,
			'start'               => 0,
			'orderby'             => 'date',
			'order'               => 'DESC',
			'user_id'             => 0,
			'user__in'            => array(),
			'user__not_in'        => array(),
			'points_type'         => '',
			'points_type__in'     => array(),
			'points_type__not_in' => array(),
			'log_type'            => '',
			'log_type__in'        => array(),
			'log_type__not_in'    => array(),
			'points'              => null,
			'points__compare'     => '=',
			'blog_id'             => $wpdb->blogid,
			'blog__in'            => array(),
			'blog__not_in'        => array(),
			'site_id'             => $wpdb->siteid,
			'meta_query'          => array(),
		);

		$this->_args = wp_parse_args( $args, $defaults );

		$meta_defaults = array(
			'id'            => 0,
			'id__in'        => array(),
			'id__not_in'    => array(),
			'key'           => '',
			'value'         => '',
			'value__in'     => array(),
			'value__not_in' => array(),
		);

		$this->_args['meta_query'] = wp_parse_args( $this->_args['meta_query'], $meta_defaults );
	}

	/**
	 * Count the number of results.
	 *
	 * When used with a query that contains a LIMIT clause, this method currently
	 * returns the count of the query ignoring the LIMIT, as would be the case with
	 * any similar query. However, this behaviour is not hardened and should not be
	 * relied upon. Make inquiry before assuming the constancy of this behaviour.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $use_cache Whether to return a cached result if one is available.
	 *        The cache is not persistent and covers only the instance.
	 *
	 * @return int The number of results.
	 */
	public function count( $use_cache = true ) {

		// Return the cached value if available.
		if ( $use_cache && isset( $this->_cache['count'] ) )
			return $this->_cache['count'];

		$this->_select_type = 'SELECT COUNT';
		$this->_prepare_query();

		$count = (int) $this->_get( 'var' );

		// Cache the result.
		$this->_cache['count'] = $count;

		return $count;
	}

	/**
	 * Get the results for the query.
	 *
	 * @since 1.0.0
	 *
	 * @param string $method The method to use. Options are 'results', 'row', and
	 *        'col', and 'var'.
	 *
	 * @return mixed The results fo the query, or false on failure.
	 */
	public function get( $method = 'results', $use_cache = true ) {

		$methods = array( 'results', 'row', 'col', 'var' );

		if ( ! in_array( $method, $methods ) ) {

			wordpoints_debug_message( "invalid get method {$method}, possible values are " . implode( ', ', $methods ), __METHOD__, __FILE__, __LINE__ );

			return false;
		}

		if ( $use_cache && isset( $this->_cache[ "get_{$method}" ] ) )
			return $this->_cache[ "get_{$method}" ];

		$this->_select_type = 'SELECT';
		$this->_prepare_query();

		return $this->_get( $method );
	}

	/**
	 * Get the SQL for the query.
	 *
	 * This function can return the SQL for a SELECT or SELECT COUNT query. To
	 * specify which one to return, set the $select_type parameter. If it is not set,
	 * the type will be that of the last query. If no queries have been made yet,
	 * this defaults to SELECT.
	 *
	 * Useful for debugging.
	 *
	 * @since 1.0.0
	 *
	 * @param string $select_type The type of query, SELECT, or SELECT COUNT.
	 *
	 * @return string The SQL for the query.
	 */
	function get_sql( $select_type = null ) {

		if ( isset( $select_type ) )
			$this->_select_type = $select_type;

		$this->_prepare_query();

		return $this->_get_sql();
	}

	//
	// Private Methods.
	//

	/**
	 * Get the SQL for a query.
	 *
	 * @since 1.0.0
	 *
	 * @return string The SQL for the query.
	 */
	private function _get_sql() {

		$select = ( 'SELECT COUNT' == $this->_select_type ) ? $this->_select_count : $this->_select;

		return "
			{$select}
			FROM `" . WORDPOINTS_POINTS_LOGS_DB . "`
			{$this->_meta_join}
			{$this->_where}
			{$this->_order}
			{$this->_limit}
			";
	}

	/**
	 * Perform a get query.
	 *
	 * This function is essentially a wrapper for the wpdb::get_* methods.
	 *
	 * @since 1.0.0
	 *
	 * @param string $method The method to use. See get() for possilbe values.
	 *
	 * @return mixed The query results.
	 */
	private function _get( $method ) {

		global $wpdb;

		$get = "get_{$method}";

		return $wpdb->$get( $this->_get_sql() );;
	}

	/**
	 * Prepare the query.
	 *
	 * @since 1.0.0
	 */
	private function _prepare_query() {

		if ( ! $this->_query_ready ) {

			$this->_prepare_select();
			$this->_prepare_meta_join();
			$this->_prepare_where();
			$this->_prepare_orderby();
			$this->_prepare_limit();

			$this->_query_ready = true;
		}
	}

	/**
	 * Prepare the select statement.
	 *
	 * @since 1.0.0
	 */
	private function _prepare_select() {

		$_fields = $this->_args['fields'];
		$fields  = '';

		$var_type = gettype( $_fields );

		if ( 'string' == $var_type ) {

			if ( 'all' == $_fields )
				$fields = '`' . implode( '` ,`', $this->_fields ) . '`';
			elseif ( in_array( $_fields, $this->_fields ) )
				$fields = $_fields;
			else
				wordpoints_debug_message( "invalid field {$_fields}, possible values are " . implode( ', ', $this->_fields ), __METHOD__, __FILE__, __LINE__ );

		} elseif ( 'array' == $var_type ) {

			$diff = array_diff( $_fields, $this->_fields );
			$_fields = array_intersect( $this->_fields, $_fields );

			if ( ! empty( $diff ) )
				wordpoints_debug_message( 'invalid field(s) "' . implode( '", "', $diff ) . '" given', __METHOD__, __FILE__, __LINE__ );

			if ( ! empty( $_fields ) )
				$fields = '`' . implode( '`, `', $_fields ) . '`';
		}

		if ( empty( $fields ) )
			$fields = '`' . implode( '` ,`', $this->_fields ) . '`';

		$this->_select = "SELECT {$fields}";
	}

	/**
	 * Prepare the where condition.
	 *
	 * @since 1.0.0
	 */
	private function _prepare_where() {

		global $wpdb;

		$this->_wheres = array();

		// User
		if ( wordpoints_posint( $this->_args['user_id'] ) ) {

			$this->_wheres[] = $wpdb->prepare( '`user_id` = %d', $this->_args['user_id'] );

		} else {

			$this->_prepare_posint__in( $this->_args['user__in'], 'user_id' );
			$this->_prepare_posint__in( $this->_args['user__not_in'], 'user_id', 'NOT IN' );
		}

		// Points type.
		if ( wordpoints_is_points_type( $this->_args['points_type'] ) ) {

			$this->_wheres[] = $wpdb->prepare( '`points_type` = %s', $this->_args['points_type'] );

		} else {

			$points_types = array_keys( wordpoints_get_points_types() );

			if ( is_array( $this->_args['points_type__in'] ) ) {

				$this->_prepare__in( array_intersect( $this->_args['points_type__in'], $points_types ), 'points_type' );
			}

			if ( is_array( $this->_args['points_type__not_in'] ) ) {

				$this->_prepare__in( array_intersect( $this->_args['points_type__not_in'], $points_types ), 'points_type', 'NOT IN' );
			}
		}

		// Log type.
		if ( ! empty( $this->_args['log_type'] ) ) {

			$this->_wheres[] = $wpdb->prepare( '`log_type` = %s', $this->_args['log_type'] );

		} else {

			$this->_prepare__in( $this->_args['log_type__in'], 'log_type' );
			$this->_prepare__in( $this->_args['log_type__not_in'], 'log_type', 'NOT IN' );
		}

		// Points.
		if ( isset( $this->_args['points'] ) ) {

			$_points = $this->_args['points'];

			if ( isset( $_points ) && ! wordpoints_int( $this->_args['points'] ) ) {

				wordpoints_debug_message( "'points' must be an integer, " . gettype( $_points ) . " given",  __METHOD__, __FILE__, __LINE__ );

			} else {

				$comparisons = array( '=', '<', '>', '<>', '!=', '<=', '>=' );

				if ( ! in_array( $this->_args['points__compare'], $comparisons ) ) {

					wordpoints_debug_message( "invalid 'points__compare' {$this->_args['points__compare']}, possible values are " . implode( ', ', $comparisions ), __METHOD__, __FILE__, __LINE__ );
				}

				$this->_wheres[] = $wpdb->prepare( "`points` {$this->_args['points__compare']} %d", $this->_args['points'] );
			}
		}

		// Multisite isn't really supported. This is just theoretical... :)
		if ( is_multisite() ) {

			if ( wordpoints_posint( $this->_args['site_id'] ) )
				$this->_wheres[] = $wpdb->prepare( '`site_id` = %d', $this->_args['site_id'] );

			if ( wordpoints_posint( $this->_args['blog_id'] ) )
				$this->_wheres[] = $wpdb->prepare( '`blog_id` = %d', $this->_args['blog_id'] );

			$this->_prepare_posint__in( $this->_args['blog__in'], 'blog_id' );
			$this->_prepare_posint__in( $this->_args['blog__not_in'], 'blog_id', 'NOT IN' );
		}

		if ( ! empty( $this->_wheres ) ) {

			$where = implode( ' AND ', $this->_wheres );

			$this->_where = "WHERE {$where}";
		}

	} // function _prepare_where()

	/**
	 * Prepare the JOIN clause for the meta query.
	 *
	 * @since 1.0.0
	 */
	private function _prepare_meta_join() {

		global $wpdb;

		$meta_query = $this->_args['meta_query'];

		$join  = array();
		$this->_wheres = array();

		if ( wordpoints_posint( $meta_query['id'] ) ) {

			$this->_wheres[] = "meta.id = {$meta_query['id']}";

		} else {

			$this->_prepare_posint__in( $meta_query['id__in'], 'meta.id' );
			$this->_prepare_posint__in( $meta_query['id__not_in'], 'meta.id', 'NOT IN' );
		}

		if ( ! empty( $meta_query['key'] ) ) {

			$this->_wheres[] = $wpdb->prepare( 'meta.meta_key = %s', $meta_query['key'] );
		}

		if ( ! empty( $meta_query['value'] ) ) {

			$this->_wheres[] = $wpdb->prepare( 'meta.meta_value = %s', $meta_query['value'] );

		} else {

			$this->_prepare_posint__in( $meta_query['value__in'], 'meta.meta_value' );
			$this->_prepare_posint__in( $meta_query['value__not_in'], 'meta.meta_value', 'NOT IN' );
		}

		if ( ! empty( $this->_wheres ) ) {

			$this->_meta_join = 'INNER JOIN `' . WORDPOINTS_POINTS_LOG_META_DB . '` AS meta
				ON ' . WORDPOINTS_POINTS_LOGS_DB . '.id = meta.log_id
					AND ' . implode( ' AND ', $this->_wheres );
		}
	}

	/**
	 * Prepare the LIMIT clause for the query.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function _prepare_limit() {

		if ( ! isset( $this->_args['limit'] ) )
			return;

		$_var = $this->_args['limit'];

		if ( wordpoints_int( $this->_args['limit'] ) === false ) {

			wordpoints_debug_message( "'limit' must be a positive integer, " . ( strval( $_var ) ? $_var : gettype( $_var ) ) . ' given', __METHOD__, __FILE__, __LINE__ );

			$this->_args['limit'] = 0;
		}

		$_var = $this->_args['start'];

		if ( wordpoints_int( $this->_args['start'] ) === false ) {

			wordpoints_debug_message( "'start' must be a positive integer, " . ( strval( $_var ) ? $_var : gettype( $_var ) ) . ' given', __METHOD__, __FILE__, __LINE__ );

			$this->_args['start'] = 0;
		}

		if ( $this->_args['limit'] > 0 && $this->_args['start'] >= 0 )
			$this->_limit = "LIMIT {$this->_args['start']}, {$this->_args['limit']}";
	}

	/**
	 * Prepare the ORDER clause for the query.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function _prepare_orderby() {

		$order = $this->_args['order'];
		$order_by = $this->_args['orderby'];

		if ( 'none' == $order_by )
			return;

		if ( ! in_array( $order, array( 'DESC', 'ASC' ) ) ) {

			wordpoints_debug_message( "invalid 'order' \"{$order}\", possible values are DESC and ASC", __METHOD__, __FILE__, __LINE__ );
			$order = 'DESC';
		}

		if ( ! in_array( $order_by, $this->_fields ) ) {

			wordpoints_debug_message( "invalid 'orderby' \"{$orderby}\", possible values are " . implode( ', ', $this->_fields ), __METHOD__, __FILE__, __LINE__ );

		} else {

			$this->_order = "ORDER BY `{$order_by}` {$order}";
		}
	}

	/**
	 * Prepare an IN or NOT IN condition.
	 *
	 * @since 1.0.0
	 *
	 * @uses wordpoints_prepare__in() To prepare the IN condition.
	 *
	 * @param array $_in The array of values for the IN condition.
	 * @param string $column The column to search.
	 * @param string $type The type of IN condition: 'IN' or 'NOT IN'.
	 * @param string $format The format for the values in $_in ('%s', '%d', '%f').
	 */
	private function _prepare__in( $_in, $column, $type = 'IN', $format = '%s' ) {

		if ( ! empty( $_in ) ) {

			$in = wordpoints_prepare__in( $_in, $format );

			if ( $in )
				$this->_wheres[] = "{$column} {$type} ({$in})";
		}
	}

	/**
	 * Prepare and IN or NOT IN condition for integer arrays.
	 *
	 * @since 1.0.0
	 *
	 * @uses wordpoints_prepare__in() To prepare the IN condition.
	 *
	 * @param array $in The arg that is the array of values for the IN condition.
	 * @param string $column The column to search.
	 * @param string $type The type of IN condition: 'IN' or 'NOT IN'.
	 */
	private function _prepare_posint__in( $in, $column, $type = 'IN' ) {

		if ( ! empty( $in ) ) {

			$var_type = gettype( $in );

			if ( 'array' == $var_type ) {

				$in = array_filter( array_map( 'wordpoints_posint', $in ) );

				if ( ! empty( $in ) ) {

					$in = wordpoints_prepare__in( $in, '%d' );

					if ( $in )
						$this->_wheres[] = "{$column} {$type} ({$in})";
				}

			} else {

				wordpoints_debug_message( "\$in must be an array, {$var_type} given", __METHOD__, __FILE__, __LINE__ );
			}
		}
	}
}

// end of file /components/points/includes/class-WordPoints_Points_Logs_Query.php
