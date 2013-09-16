<?php

/**
 * WordPoints Administration Screen: Configure > Components.
 *
 * This template displays the Components tab on the Configure panel.
 *
 * @package WordPoints\Administration
 * @since 1.0.0
 */

$wordpoints_components = WordPoints_Components::instance();

//
// Process form submit.
//

if ( isset( $_POST['wordpoints_component_name'], $_POST['_wpnonce'] ) ) {

	// A component is being activated/deactivated.

	$component_name = esc_html( $_POST['wordpoints_component_name'] );

	if ( isset( $_POST['wordpoints_component_activate'] ) ) {

		// Component is being activated.

		$component_slug = $_POST['wordpoints_component_activate'];

		if ( 1 == wp_verify_nonce( $_POST['_wpnonce'], "wordpoints_activate_component-{$component_slug}" ) && $wordpoints_components->activate( $component_slug ) ) {

			wordpoints_show_admin_message( sprintf( __( 'Component "%s" activated!', 'wordpoints' ), $component_name ) );

		} else {

			wordpoints_show_admin_error( sprintf( __( 'The component "%s" could not be activated. Please try again.', 'wordpoints' ), $component_name ) );
		}

	} elseif ( isset( $_POST['wordpoints_component_deactivate'] ) ) {

		// Component is being deactivated.

		$component_slug = $_POST['wordpoints_component_deactivate'];

		if ( 1 == wp_verify_nonce( $_POST['_wpnonce'], "wordpoints_deactivate_component-{$component_slug}" ) && $wordpoints_components->deactivate( $component_slug ) ) {

			wordpoints_show_admin_message( sprintf( __( 'Component "%s" deactivated!', 'wordpoints' ), $component_name ) );

		} else {

			wordpoints_show_admin_error( sprintf( __( 'The component "%s" could not be deactivated. Please try again.', 'wordpoints' ), $component_name ) );
		}
	}
}

//
// Display the page.
//

?>

<p><?php _e( 'View installed WordPoints components.', 'wordpoints' ); ?></p>
<p><?php _e( 'Currently WordPoints only has one component.', 'wordpoints' ); ?> <?php _e( 'More components are planned for future versions of the plugin.', 'wordpoints' ); ?></p>

<?php

/**
 * Top of the components administration page.
 *
 * @since 1.0.0
 */
do_action( 'wordpoints_admin_components_top' );

?>

<table id="wordpoints_components_table" class="widefat datatables">
	<thead>
		<tr>
			<th scope="col" width="150"><?php _ex( 'Component', 'components table heading', 'wordpoints' ); ?></th>
			<th scope="col"><?php _e( 'Description', 'components table heading', 'wordpoints' ); ?></th>
			<th scope="col" width="80"><?php _ex( 'Version', 'components table heading', 'wordpoints' ); ?></th>
			<th scope="col" width="70"><?php _ex( 'Action', 'components table heading', 'wordpoints' ); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th scope="col"><?php _ex( 'Component', 'components table heading', 'wordpoints' ); ?></th>
			<th scope="col"><?php _ex( 'Description', 'components table heading', 'wordpoints' ); ?></th>
			<th scope="col"><?php _ex( 'Version', 'components table heading', 'wordpoints' ); ?></th>
			<th scope="col"><?php _ex( 'Action', 'components table heading', 'wordpoints' ); ?></th>
		</tr>
	</tfoot>

	<?php

	$components = $wordpoints_components->get();

	foreach( $components as $component ) {

		if ( $component['component_uri'] != '' )
			$component_name = '<a href="' . esc_url( $component['component_uri'] ) . '">' . esc_html( $component['name'] ) . '</a>';
		else
			$component_name = esc_html( $component['name'] );

		if ( $component['author'] != '' ) {

			if ( $component['author_uri'] != '' )
				$author_name = '<a href="' . esc_url( $component['author_uri'] ) . '">' . esc_html( $component['author'] ) . '</a>';
			else
				$author_name = esc_html( $component['author'] );

			/* translators: %s is the component author's name. */
			$author = ' | ' . sprintf( __( 'By %s', 'wordpoints' ), $author_name );
		}

		?>

		<tr>
			<td><?php echo $component_name; ?></td>
			<td><?php echo $component['description'] . $author; ?></td>
			<td><?php echo $component['version']; ?></td>
			<td>

				<?php

				if ( $wordpoints_components->is_active( $component['slug'] ) ) {

					?>

					<form method="post" name="wordpoints_components_form_<?php echo esc_attr( $component['slug'] ); ?>">
						<input type="hidden" name="wordpoints_component_deactivate" value="<?php echo esc_attr( $component['slug'] ); ?>" />
						<input type="hidden" name="wordpoints_component_name" value="<?php echo esc_attr( $component['name'] ); ?>" />
						<?php wp_nonce_field( "wordpoints_deactivate_component-{$component['slug']}" ); ?>
						<button type="submit" class="button-secondary wordpoints-component-deactivate"><?php _e( 'Deactivate', 'wordpoints' ); ?></button>
					</form>

					<?php

				} else {

					?>

					<form method="post" name="wordpoints_components_form_<?php echo esc_attr( $component['slug'] ); ?>">
						<input type="hidden" name="wordpoints_component_activate" value="<?php echo esc_attr( $component['slug'] ); ?>" />
						<input type="hidden" name="wordpoints_component_name" value="<?php echo esc_attr( $component['name'] ); ?>" />
						<?php wp_nonce_field( "wordpoints_activate_component-{$component['slug']}" ); ?>
						<button type="submit" class="button-secondary wordpoints-component-activate"><?php _e( 'Activate', 'wordpoints' ); ?></button>
					</form>

					<?php
				}

				?>

			</td>
		</tr>

	<?php

	} // foreach ( $components as $component )

	?>

</table>

<?php

/**
 * Bottom of components administration panel.
 *
 * @since 1.0.0
 */
do_action( 'wordpoints_admin_components_bottom' );

// end of file.
