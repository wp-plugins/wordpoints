<?php

/**
 * WordPoints administration screen: Configure.
 *
 * @package WordPoints\Administration
 * @since 1.0.0
 *
 * @uses do_action() To call 'wordpoints_admin_screen_head'.
 * @uses wordpoints_admin_show_tabs() To display the tabs.
 * @uses wordpoints_admin_get_current_tab() To get the current tab.
 * @uses do_action() To call 'wordpoints_admin_screen_foot'.
 */
function wordpoints_admin_screen_configure() {

	?>

	<div class="wrap">

		<?php

			/**
			 * At the top of the configure screens.
			 *
			 * @since 1.0.0
			 */
			do_action( 'wordpoints_admin_configure_head' );

			wordpoints_admin_show_tabs(
				array(
					'general'    => __( 'General Settings', 'wordpoints' ),
					'components' => __( 'Components', 'wordpoints' ),
					'modules'    => __( 'Modules', 'wordpoints' ),
				)
			);

			switch ( wordpoints_admin_get_current_tab() ) {

				case 'components':
					$template = '/configure-components.php';
				break;

				case 'modules':
					$template = '/configure-modules.php';
				break;

				default:
					$template = '/configure-settings.php';
			}

			include dirname( __FILE__ ) . $template;

			/**
			 * At the bottom of the configure screens.
			 *
			 * @since 1.0.0
			 */
			do_action( 'wordpoints_admin_configure_foot' );
		?>

	</div>

	<?php
}

// end of file /admin/screens/configure.php