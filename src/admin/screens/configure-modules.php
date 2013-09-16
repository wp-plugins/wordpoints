<?php

/**
 * WordPoints administration screen: Configure > Modules.
 *
 * @package WordPoints\Administration
 * @since 1.0
 */

$wordpoints_modules = WordPoints_Modules::instance();

//
// Process form submit.
//

if ( isset( $_POST['wordpoints_module_name'], $_POST['_wpnonce'] ) ) {

	$module_name = esc_html( $_POST['wordpoints_module_name'] );

	if ( isset( $_POST['wordpoints_module_activate'] ) ) {

		if ( 1 == wp_verify_nonce( $_POST['_wpnonce'], "wordpoints_activate_module-{$_POST['wordpoints_module_activate']}" ) && $wordpoints_modules->activate( $_POST['wordpoints_module_activate'] ) ) {

			wordpoints_show_admin_message( sprintf( __( 'Module "%s" activated!', 'wordpoints' ), $module_name ) );

		} else {

			wordpoints_show_admin_error( sprintf( __( 'The module "%s" could not be activated.', 'wordpoints' ), $module_name ) );
		}

	} elseif ( isset( $_POST['wordpoints_module_deactivate'] ) ) {

		if (  1 == wp_verify_nonce( $_POST['_wpnonce'], "wordpoints_deactivate_module-{$_POST['wordpoints_module_deactivate']}" ) && $wordpoints_modules->deactivate( $_POST['wordpoints_module_deactivate'] ) ) {

			wordpoints_show_admin_message( sprintf( __( 'Module "%s" deactivated!', 'wordpoints' ), $module_name ) );

		} else {

			wordpoints_show_admin_error( sprintf( __( 'The module "%s" could not be deactivated.', 'wordpoints' ), $module_name ) );
		}
	}
}

//
// Now display the page.
//

// Enqueue datatables.
wordpoints_enqueue_datatables(
	'#wordpoints_modules_table'
	,array(
		'bPaginate' => false,
		'aoColumns' => array(
			array( 'bSortable' => false ),
			array( 'bSortable' => false ),
			array( 'bSortable' => false ),
			array( 'bSortable' => false, 'bSearchable' => false ),
		),
	)
);

?>

<p><?php _e( 'View installed WordPoints modules.', 'wordpoints' ); ?></p>
<p><?php _e( 'Currently WordPoints does not come with any modules installed.', 'wordpoints' ); ?> <?php echo sprintf( __( 'For information on extending the functionality of WordPoints by building your own custom modules, see <a href="%s">this link</a>.', 'wordpoints' ), 'http://wordpress.org/plugins/wordpoints/' ); ?></p>

<table id="wordpoints_modules_table" class="widefat datatables">
	<thead>
		<tr>
			<th scope="col" width="150"><?php _ex( 'Module', 'modules table heading', 'wordpoints' ); ?></th>
			<th scope="col"><?php _ex( 'Description', 'modules table heading', 'wordpoints' ); ?></th>
			<th scope="col" width="80"><?php _ex( 'Version', 'modules table heading', 'wordpoints' ); ?></th>
			<th scope="col" width="70"><?php _ex( 'Action', 'modules table heading', 'wordpoints' ); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th scope="col"><?php _ex( 'Module', 'modules table heading', 'wordpoints' ); ?></th>
			<th scope="col"><?php _ex( 'Description', 'modules table heading', 'wordpoints' ); ?></th>
			<th scope="col"><?php _ex( 'Version', 'modules table heading', 'wordpoints' ); ?></th>
			<th scope="col"><?php _ex( 'Action', 'modules table heading', 'wordpoints' ); ?></th>
		</tr>
	</tfoot>
	<tbody>

		<?php

		$modules = $wordpoints_modules->get();

		if ( ! $modules ) {

			?><tr><td colspan="4"><?php _e( 'You have not installed any modules.', 'wordpoints' ); ?></td></tr><?php

		} else {

			foreach( $modules as $module ) {

				if ( $module['module_uri'] != '' )
					$module_name = '<a href="' . esc_url( $module['module_uri'] ) . '">' . esc_html( $module['name'] ) . '</a>';
				else
					$module_name = esc_html( $module['name'] );

				if ( $module['author'] != '' ) {

					if ( $module['author_uri'] != '' )
						$author_name = '<a href="' . esc_url( $module['author_url'] ) . '">' . esc_html( $module['author'] ) . '</a>';
					else
						$author_name = esc_html( $module['author'] );

					/* translators: %s is the module author's name. */
					$author = ' | ' . sprintf( __( 'By %s', 'wordpoints' ), $author_name );
				}

				?>

				<tr>
					<td><?php echo $module_name; ?></td>
					<td><?php echo $module['description'] . $author; ?></td>
					<td><?php echo $module['version']; ?></td>
					<td>

						<?php if ( $wordpoints_modules->is_active( $module['slug'] ) ) : ?>

							<form method="post" name="wordpoints_modules_form_<?php echo esc_attr( $module['slug'] ); ?>">
								<input type="hidden" name="wordpoints_module_deactivate" value="<?php echo esc_attr( $module['slug'] ); ?>" />
								<input type="hidden" name="wordpoints_module_name" value="<?php echo esc_attr( $module['name'] ); ?>" />
								<?php wp_nonce_field( "wordpoints_deactivate_module-{$module['slug']}" ); ?>
								<button type="submit" class="button-secondary wordpoints-module-deactivate"><?php _e( 'Deactivate', 'wordpoints' ); ?></button>
							</form>

						<?php else : ?>

							<form method="post" name="wordpoints_modules_form_<?php echo esc_attr( $module['slug'] ); ?>">
								<input type="hidden" name="wordpoints_module_activate" value="<?php echo esc_attr( $module['slug'] ); ?>" />
								<input type="hidden" name="wordpoints_module_name" value="<?php echo esc_attr( $module['name'] ); ?>" />
								<?php wp_nonce_field( "wordpoints_activate_module-{$module['slug']}" ); ?>
								<button type="submit" class="button-secondary wordpoints-module-activate"><?php _e( 'Activate', 'wordpoints' ); ?></button>
							</form>

						<?php endif; ?>

					</td>
				</tr>

				<?php

			} // foreach $module
		}

		?>

	</tbody>
</table>

<?php

/**
 * Bottom of modules administration panel.
 *
 * @since 1.0.0
 */
do_action( 'wordpoints_admin_modules' );

// end of file.
