<?php

/**
 * WordPoints administration screen: Configure > Modules.
 *
 * @package WordPoints\Administration
 * @since 1.0
 */

$wordpoints_modules = WordPoints_Modules::instance();

$modules = $wordpoints_modules->get();

//
// Show messages and errors.
//

if ( isset( $_GET['wordpoints_module'], $_GET['_wpnonce'] ) && $wordpoints_modules->is_registered( $_GET['wordpoints_module'] ) ) {

	if ( isset( $_GET['message'] ) && wp_verify_nonce( $_GET['_wpnonce'], "wordpoints_module_message-{$_GET['wordpoints_module']}" ) ) {

		switch ( $_GET['message'] ) {

			case '1':
				if ( $wordpoints_modules->is_active( $_GET['wordpoints_module'] ) )
					$message = __( 'Module "%s" activated!', 'wordpoints' );
			break;

			case '2':
				if ( !  $wordpoints_modules->is_active( $_GET['wordpoints_module'] ) )
					$message = __( 'Module "%s" deactivated!', 'wordpoints' );
			break;
		}

		if ( $message ) {

			wordpoints_show_admin_message( esc_html( sprintf( $message, $modules[ $_GET['wordpoints_module'] ]['name'] ) ) );
		}

	} elseif ( isset( $_GET['error'] ) && wp_verify_nonce( $_GET['_wpnonce'], "wordpoints_module_error-{$_GET['wordpoints_module']}" ) ) {

		switch ( $_GET['error'] ) {

			case '1':
				if ( ! $wordpoints_modules->is_active( $_GET['wordpoints_module'] ) )
					$message = __( 'The module "%s" could not be activated.', 'wordpoints' );
			break;

			case '2':
				if ( $wordpoints_modules->is_active( $_GET['wordpoints_module'] ) )
					$message = __( 'The module "%s" could not be deactivated.', 'wordpoints' );
			break;
		}

		if ( $error ) {

			wordpoints_show_admin_error( esc_html( sprintf( $message, $modules[ $_GET['wordpoints_module'] ]['name'] ) ) );
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
		'oLanguage' => array(
			'sEmptyTable' => __( 'You have not installed any modules.', 'wordpoints' ),
		),
	)
);

?>

<p><?php _e( 'View installed WordPoints modules.', 'wordpoints' ); ?></p>
<p><?php _e( 'Currently WordPoints does not come with any modules installed.', 'wordpoints' ); ?> <?php echo sprintf( __( 'For information on extending the functionality of WordPoints by building your own custom modules, see <a href="%s">this link</a>.', 'wordpoints' ), 'http://wordpoints.org/developer-guide/modules/' ); ?></p>

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

		foreach( $modules as $module ) {

			if ( $module['module_uri'] != '' )
				$module_name = '<a href="' . esc_url( $module['module_uri'] ) . '">' . esc_html( $module['name'] ) . '</a>';
			else
				$module_name = esc_html( $module['name'] );

			if ( $module['author'] != '' ) {

				if ( $module['author_uri'] != '' )
					$author_name = '<a href="' . esc_url( $module['author_uri'] ) . '">' . esc_html( $module['author'] ) . '</a>';
				else
					$author_name = esc_html( $module['author'] );

				/* translators: %s is the module author's name. */
				$author = ' | ' . sprintf( __( 'By %s', 'wordpoints' ), $author_name );
			}

			if ( $wordpoints_modules->is_active( $module['slug'] ) ) {

				$action = 'deactivate';
				$button = __( 'Deactivate', 'wordpoints' );

			} else {

				$action = 'activate';
				$button = __( 'Activate', 'wordpoints' );
			}

			?>

			<tr>
				<td><?php echo $module_name; ?></td>
				<td><?php echo $module['description'] . $author; ?></td>
				<td><?php echo $module['version']; ?></td>
				<td>
					<form method="post" name="wordpoints_modules_form_<?php echo esc_attr( $module['slug'] ); ?>">
						<input type="hidden" name="wordpoints_module_action" value="<?php echo $action; ?>" />
						<input type="hidden" name="wordpoints_module" value="<?php echo esc_attr( $module['slug'] ); ?>" />
						<?php wp_nonce_field( "wordpoints_{$action}_module-{$module['slug']}" ); ?>
						<?php submit_button( $button, "secondary wordpoints-module-{$action}", "wordpoints-component-{$action}_{$module['slug']}", false ); ?>
					</form>
				</td>
			</tr>

			<?php

		} // foreach $module

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
