<?php

/**
 * Install the plugin.
 *
 * @package WordPoints
 * @since 1.0.0
 */

// Add plugin data.
add_option(
	'wordpoints_data',
	array(
		'version'    => WORDPOINTS_VERSION,
		'components' => array( 'points' ), // Components use this to store data.
		'modules'    => array(), // Modules can use this to store data.
	)
);

// end of file /install.php
