<?php

/**
 * Uninstall the points component.
 *
 * @package WordPoints\Points
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'WORDPOINTS_POINTS_LOGS_DB' ) ) exit;

global $wpdb;

$wpdb->query( 'DROP TABLE IF EXISTS `' . WORDPOINTS_POINTS_LOGS_DB . '`' );
$wpdb->query( 'DROP TABLE IF EXISTS `' . WORDPOINTS_POINTS_LOG_META_DB . '`' );

foreach ( wordpoints_get_points_types() as $slug => $settings ) {

	delete_metadata( 'user', 0, "wordpoints_points-{$slug}", '', true );
	delete_metadata( 'comment', 0, "wordpoints_last_status-{$slug}", '', true );
}

delete_metadata( 'user', 0, 'wordpoints_points_period_start', '', true );

delete_option( 'wordpoints_points_hooks' );
delete_option( 'wordpoints_points_types' );
delete_option( 'wordpoints_points_types_hooks' );
delete_option( 'wordpoints_default_points_type' );
delete_option( 'wordpoints_hook-wordpoints_registration_points_hook' );
delete_option( 'wordpoints_hook-wordpoints_post_points_hook' );
delete_option( 'wordpoints_hook-wordpoints_comment_points_hook' );
delete_option( 'wordpoints_hook-wordpoints_periodic_points_hook' );

// end of file /components/points/uninstall.php