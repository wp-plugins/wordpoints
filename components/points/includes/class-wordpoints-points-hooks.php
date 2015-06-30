<?php

/**
 * WordPoints_Points_Hooks class.
 *
 * This is a static class to help with points hooks.
 *
 * @package WordPoints\Points\Hooks
 * @since 1.0.0
 */

// Initialise the class.
WordPoints_Points_Hooks::init();

/**
 * Points hooks class.
 *
 * This is a static helper class to handle the storing of points hooks.
 *
 * The points hook API is based on WordPress's Widget API. There are different
 * types of points hooks (their widget API counterpart would be the widgets), and
 * each type is represented by a class. There may be  multiple instances of each type
 * of hook, and the class or "handler" for that hook type is used to save, update,
 * and trigger instances of that type of hook. Each instance of a hook is associated
 * with one of the points types (kind of like sidebars).
 *
 * This class provides an API to get the handler for a type of hook. It also provides
 * methods to get a list of hooks by what points type they are attached to, as well
 * as to determine to which points type a particular instance of a hook is attached.
 *
 * The first of the two (the handler API) works like this:
 *
 * Each points hook type's class is registered with self::register(). Later, after
 * any modules are loaded, self::initialize_hooks() instantiates each of the classes.
 * A list of the hook types and the handler (class object) for each is saved in
 * self::$hook_types when this occurs. This allows for the handler for a type of hook
 * to be easily retrieved, using the provided methods. This is necessary for saving,
 * updating, and deleting instances of a hook, as these actions must be performed by
 * the handler for that hook type.
 *
 * The latter (the points types API) works like this:
 *
 * Each hook instance is assigned a unique ID (this is handled by the handlers, i.e.
 * the WordPoints_Points_Hook class). A multidemensional array of hook instances,
 * indexed by points type, is maintained in the database. Checking this list is
 * currently the only means of determining what type of points a hook instance is
 * supposed to award. Several methods are provided for working with this list, and
 * these should always be used rather than accessing or altering it in the database
 * directly.
 *
 * For more information on how the handler for a type of hook actually awards the
 * points for each of its instances, see the docs for the WordPoints_Points_Hook
 * class.
 *
 * @since 1.0.0
 */
final class WordPoints_Points_Hooks {

	//
	// Private Vars.
	//

	/**
	 * A list of registered hook type class names.
	 *
	 * Holds the list of classes registered with the self::register() method,
	 * accessed only by self::initialize_hooks().
	 *
	 * @since 1.5.0
	 *
	 * @param string[] $classes
	 */
	private static $classes;

	/**
	 * The instances of the handlers for the registered types of points hooks.
	 *
	 * @since 1.5.0
	 *
	 * @type WordPoints_Points_Hook[] $hook_types
	 */
	private static $hook_types = array();

	/**
	 * Whether to display network hooks.
	 *
	 * @since 1.2.0
	 *
	 * @type bool $network_mode
	 */
	private static $network_mode = false;

	//
	// Public Methods.
	//

	/**
	 * Initialize the class.
	 *
	 * @since 1.0.0
	 *
	 * @uses add_action() To hook the initialize_hooks() method to the {@see
	 *       'wordpoints_modules_loaded'} action.
	 */
	public static function init() {

		add_action( 'wordpoints_modules_loaded', array( __CLASS__, 'initialize_hooks' ) );
	}

	/**
	 * Register a points hook type's handler class.
	 *
	 * @since 1.0.0
	 *
	 * @param string $class_name A 'WordPoints_Points_Hook' class name.
	 */
	public static function register( $class_name ) {

		self::$classes[] = $class_name;
	}

	/**
	 * Register all points hooks.
	 *
	 * @since 1.0.0
	 *
	 * @action wordpoints_modules_loaded Added by the init() method.
	 */
	public static function initialize_hooks() {

		/**
		 * Points hooks may be registered on this action.
		 *
		 * @since 1.4.0
		 */
		do_action( 'wordpoints_points_hooks_register' );

		$classes = array_unique( self::$classes );

		foreach ( $classes as $class_name ) {

			$hook_type = new $class_name();
			self::$hook_types[ $hook_type->get_id_base() ] = $hook_type;
		}

		/**
		 * All points hooks registered and initialized.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wordpoints_points_hooks_registered' );
	}

	/**
	 * Get a list of registered points hook handlers.
	 *
	 * @since 1.5.0
	 *
	 * @return WordPoints_Points_Hook[] The registered points hook types.
	 */
	public static function get_handlers() {

		return self::$hook_types;
	}

	/**
	 * Get the object representing the hook type of a hook by its ID.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook_id The unique ID of the hook to get the handler for.
	 *
	 * @return WordPoints_Points_Hook|false The hook object, or false for invalid ID.
	 */
	public static function get_handler( $hook_id ) {

		list( $hook_type, $id_number ) = explode( '-', $hook_id );

		$hook_type = self::get_handler_by_id_base( $hook_type );

		if ( false === $hook_type ) {
			return false;
		}

		$type = ( self::$network_mode ) ? 'network' : 'standard';

		$instances = $hook_type->get_instances( $type );

		if ( ! isset( $instances[ $id_number ] ) ) {
			return false;
		}

		$hook_type->set_number( $id_number );

		return $hook_type;
	}

	/**
	 * Get the handler object for a hook by id_base (hook type).
	 *
	 * This is used to get the handler for a new hook instance so that it can be
	 * created.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id_base The basic identifier the the type of hook.
	 *
	 * @return WordPoints_Points_Hook|false False if no handler found.
	 */
	public static function get_handler_by_id_base( $id_base ) {

		if ( ! isset( self::$hook_types[ $id_base ] ) ) {
			return false;
		}

		return self::$hook_types[ $id_base ];
	}

	/**
	 * Delete the database data for a list of hook types.
	 *
	 * @since 1.7.0
	 * @deprecated 2.0.0 Use an un/installer class instead.
	 *
	 * @param array|string $hook_types The hook type(s) to uninstall.
	 * @param int[]        $site_ids   List of site IDs where this hook type is
	 *                                 installed. Only needed if on multisite. If
	 *                                 omitted, the current site ID is used.
	 */
	public static function uninstall_hook_types( $hook_types, array $site_ids = null ) {

		_deprecated_function( __METHOD__, '2.0.0', 'WordPoints_Un_Installer_Base::$uninstall' );

		$hook_types = (array) $hook_types;

		if ( is_multisite() ) {

			foreach ( $hook_types as $hook_type ) {
				delete_site_option( "wordpoints_hook-{$hook_type}" );
			}

			if ( ! isset( $site_ids ) ) {
				$site_ids = array( get_current_blog_id() );
			}

			foreach ( $site_ids as $site_id ) {

				switch_to_blog( $site_id );
				foreach ( $hook_types as $hook_type ) {
					delete_option( "wordpoints_hook-{$hook_type}" );
				}
				restore_current_blog( $site_id );
			}

		} else {

			foreach ( $hook_types as $hook_type ) {
				delete_option( "wordpoints_hook-{$hook_type}" );
			}
		}
	}

	/**
	 * Displays a list of available hooks for the Points Hooks administration panel.
	 *
	 * @since 1.0.0
	 *
	 * @uses WordPoints_Points_Hooks::_sort_name_callback()
	 * @uses WordPoints_Points_Hooks::_list_hook()
	 */
	public static function list_hooks() {

		// Sort the hooks by name.
		$hook_types = self::$hook_types;
		uasort( $hook_types, array( __CLASS__, '_sort_name_callback' ) );

		$i = 0;

		// Display a representative for each hook type.
		foreach ( $hook_types as $id_base => $hook_type ) {

			$i++;

			$args = $hook_type->get_options();

			$args['_add']       = 'multi';
			$args['_display']   = 'template';
			$args['_multi_num'] = $hook_type->next_hook_id_number();
			$args['_id_slug']   = $i;

			$hook_type->set_options( $args );

			self::_list_hook( $hook_type->get_id( 0 ), $hook_type );
		}

		// If there were none, give the user a message.
		if ( empty( $hook_types ) ) {

			echo '<div class="wordpoints-no-hooks">'
				. esc_html__( 'There are no points hooks currently available.', 'wordpoints' )
				. '</div>';
		}
	}

	/**
	 * Display hooks by points type.
	 *
	 * @since 1.0.0
	 * @since 1.2.0 Now displays only the forms for the hooks, not the points type.
	 *
	 * @uses wordpoints_is_points_type()   To check if $slug is valid.
	 * @uses self::get_points_type_hooks() To get all hooks for this points type.
	 *
	 * @param string $slug The slug of the points type to display the hooks for.
	 *
	 * @return void
	 */
	public static function list_by_points_type( $slug ) {

		if ( '_inactive_hooks' !== $slug && ! wordpoints_is_points_type( $slug ) ) {
			return;
		}

		$points_type_hooks = self::get_points_type_hooks( $slug );

		foreach ( $points_type_hooks as $hook_id ) {

			list( $hook_type ) = explode( '-', $hook_id );

			$hook_type = self::get_handler_by_id_base( $hook_type );

			if ( false === $hook_type ) {
				continue;
			}

			$options = $hook_type->get_options();

			$options['_display'] = 'instance';

			unset( $options['_add'] );

			$options['_id_slug'] = $slug;

			$hook_type->set_options( $options );

			self::_list_hook( $hook_id, $hook_type, $slug );
		}
	}

	/**
	 * Set network mode.
	 *
	 * When network mode is on, the network-wide hooks will be displayed. This is
	 * only relevant on multisite installs.
	 *
	 * Network mode is off by default.
	 *
	 * @since 1.2.0
	 *
	 * @param bool $on Whether to turn network mode on or off.
	 */
	public static function set_network_mode( $on ) {

		if ( $on !== self::$network_mode ) {
			self::$network_mode = (bool) $on;
		}
	}

	/**
	 * Get the network mode.
	 *
	 * @see WordPoints_Points_Hooks::set_network_mode()
	 *
	 * @since 1.2.0
	 *
	 * @return bool Whether network mode is on.
	 */
	public static function get_network_mode() {

		return self::$network_mode;
	}

	/**
	 * Retrieve full list of points types and their hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public static function get_points_types_hooks() {

		if ( self::$network_mode ) {
			$type = 'site';
		} else {
			$type = 'default';
		}

		return wordpoints_get_array_option( 'wordpoints_points_types_hooks', $type );
	}

	/**
	 * Retrieve the hooks for a points type.
	 *
	 * @since 1.0.0
	 *
	 * @uses WordPoints_Points_Hooks::get_points_types_hooks()
	 * @param string $slug The slug for the points type.
	 *
	 * @return array
	 */
	public static function get_points_type_hooks( $slug ) {

		$points_types_hooks = self::get_points_types_hooks();

		if ( isset( $points_types_hooks[ $slug ] ) && is_array( $points_types_hooks[ $slug ] ) ) {
			$points_type_hooks = $points_types_hooks[ $slug ];
		} else {
			$points_type_hooks = array();
		}

		return $points_type_hooks;
	}

	/**
	 * Save a full list of points types and their hooks.
	 *
	 * @since 1.0.0
	 *
	 * @param array $points_types_hooks The list of points types and their hooks.
	 */
	public static function save_points_types_hooks( array $points_types_hooks ) {

		if ( self::$network_mode ) {
			update_site_option( 'wordpoints_points_types_hooks', $points_types_hooks );
		} else {
			update_option( 'wordpoints_points_types_hooks', $points_types_hooks );
		}
	}

	/**
	 * Retrieve points type by hook ID.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook_id The ID of the hook.
	 *
	 * @return string|false The points type for the hook. False if not found.
	 */
	public static function get_points_type( $hook_id ) {

		foreach ( self::get_points_types_hooks() as $points_type => $hooks ) {

			if ( in_array( $hook_id, $hooks ) ) {
				return $points_type;
			}
		}

		return false;
	}

	/**
	 * Retrieve empty settings for hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return array[] An array of empty arrays indexed by points type slugs.
	 */
	public static function get_defaults() {

		$defaults = array();

		foreach ( wordpoints_get_points_types() as $slug => $settings ) {

			$defaults[ $slug ] = array();
		}

		return $defaults;
	}

	/**
	 * Display a settings form for a type of points.
	 *
	 * By default, this function wraps the form in a widget like container. To over-
	 * ride this, the seccond parameter may be set to 'none'. If $slug is not set,
	 * $wrap will always be 'none'. If the inputs should be wrapped only in a form
	 * and the .hook-content div, then $wrap may be set to 'hook-content'.
	 *
	 * @since 1.0.0
	 *
	 * @uses do_action() Calls 'wordpoints_points_type_form_top' at the top of the
	 *       settings form with $slug and $settings. A null slug indicated a new
	 *       points type is being added. Calls 'wordpoints_points_type_form_bottom'
	 *       at the bottom of the form, with the same values.
	 *
	 * @param string $slug The slug for this type of points.
	 * @param string $wrap Whether to wrap the form inputs in a "widget" or not.
	 */
	public static function points_type_form( $slug = null, $wrap = 'hook' ) {

		$add_new = 0;

		$points_type = wordpoints_get_points_type( $slug );

		if ( ! $points_type ) {

			$points_type = array();
			$add_new     = wp_create_nonce( 'wordpoints_add_new_points_type' );
		}

		$points_type = array_merge(
			array(
				'name'   => '',
				'prefix' => '',
				'suffix' => '',
			)
			,$points_type
		);

		if ( ! isset( $slug ) && 'hook' === $wrap ) {
			$wrap = 'hook-content';
		}

		switch ( $wrap ) {

			case 'hook':
				$hook_wrap = $hook_content_wrap = true;
			break;

			case 'hook-content':
				$hook_wrap = ! $hook_content_wrap = true;
			break;

			default:
				$hook_wrap = $hook_content_wrap = false;
		}

		?>

		<?php if ( $hook_wrap ) : ?>
			<div class="hook points-settings">
				<div class="hook-top">
					<div class="hook-title-action">
						<a class="hook-action hide-if-no-js" href="#available-hooks"></a>
					</div>
					<div class="hook-title"><h4><?php esc_html_e( 'Settings', 'wordpoints' ); ?><span class="in-hook-title"></span></h4></div>
				</div>

				<div class="hook-inside">
		<?php endif; ?>

			<?php if ( $hook_content_wrap ) : ?>
				<form method="post">
					<div class="hook-content">
			<?php endif; ?>

						<?php if ( $slug ) : ?>
						<p><span class="wordpoints-points-slug"><em><?php esc_html_e( 'Slug', 'wordpoints' ); ?>: <?php echo esc_html( $slug ); ?></em></span></p>
						<?php endif; ?>

						<?php

						/**
						 * At the top of the points type settings form.
						 *
						 * Called before the default inputs are displayed.
						 *
						 * @since 1.0.0
						 *
						 * @param string $points_type The slug of the points type.
						 */
						do_action( 'wordpoints_points_type_form_top', $slug );

						if ( 'hook-content' === $wrap ) {

							// Mark the prefix and suffix optional on the add new form.
							$prefix = _x( 'Prefix (optional):', 'points type', 'wordpoints' );
							$suffix = _x( 'Suffix (optional):', 'points type', 'wordpoints' );

						} else {

							$prefix = _x( 'Prefix:', 'points type', 'wordpoints' );
							$suffix = _x( 'Suffix:', 'points type', 'wordpoints' );
						}

						?>

						<p>
							<label for="points-name-<?php echo esc_attr( $slug ); ?>"><?php echo esc_html_x( 'Name:', 'points type', 'wordpoints' ); ?></label>
							<input class="widefat" type="text" id="points-name-<?php echo esc_attr( $slug ); ?>" name="points-name" value="<?php echo esc_attr( $points_type['name'] ); ?>" />
						</p>
						<p>
							<label for="points-prefix-<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $prefix ); ?></label>
							<input class="widefat" type="text" id="points-prefix-<?php echo esc_attr( $slug ); ?>" name="points-prefix" value="<?php echo esc_attr( $points_type['prefix'] ); ?>" />
						</p>
						<p>
							<label for="points-suffix-<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $suffix ); ?></label>
							<input class="widefat" type="text" id="points-suffix-<?php echo esc_attr( $slug ); ?>" name="points-suffix" value="<?php echo esc_attr( $points_type['suffix'] ); ?>" />
						</p>

						<?php

						/**
						 * At the bottom of the points type settings form.
						 *
						 * Called below the default inputs, but abouve the submit buttons.
						 *
						 * @since 1.0.0
						 *
						 * @param string $points_type The slug of the points type.
						 */
						do_action( 'wordpoints_points_type_form_bottom', $slug );

						?>

			<?php if ( $hook_content_wrap ) : ?>
					</div>

					<input type="hidden" name="points-slug" value="<?php echo esc_attr( $slug ); ?>" />
					<input type="hidden" name="add_new" class="add_new" value="<?php echo esc_attr( $add_new ); ?>" />

					<div class="hook-control-actions">
						<div class="alignleft">
							<?php

							if ( ! $add_new ) {
								wp_nonce_field( "wordpoints_delete_points_type-{$slug}", 'delete-points-type-nonce' );
								submit_button( _x( 'Delete', 'points type', 'wordpoints' ), 'delete', 'delete-points-type', false, array( 'id' => "delete_points_type-{$slug}" ) );
							}

							?>
							<a class="hook-control-close" href="#close"><?php esc_html_e( 'Close', 'wordpoints' ); ?></a>
						</div>
						<div class="alignright">
							<?php submit_button( _x( 'Save', 'points type', 'wordpoints' ), 'button-primary hook-control-save right', 'save-points-type', false, array( 'id' => "points-{$slug}-save" ) ); ?>
							<span class="spinner"></span>
						</div>
						<br class="clear" />
					</div>
				</form>
			<?php endif; ?>

		<?php if ( $hook_wrap ) : ?>
				</div>
			</div>

			<hr class="points-hooks-settings-separator" />
		<?php endif;
	}

	/**
	 * Display the administration form for a hook.
	 *
	 * The $points_type parameter is only needed if the hook is hooked to a points
	 * type.
	 *
	 * @since 1.0.0
	 *
	 * @param string                 $hook_id     The ID of a hook.
	 * @param WordPoints_Points_Hook $hook        A points hook object.
	 * @param string                 $points_type The slug for a points type.
	 */
	private static function _list_hook( $hook_id, $hook, $points_type = null ) {

		$number  = $hook->get_number_by_id( $hook_id );
		$id_base = $hook->get_id_base();
		$options = $hook->get_options();

		$id_format = $hook_id;

		$multi_number = ( isset( $options['_multi_num'] ) ) ? $options['_multi_num'] : '';
		$add_new      = ( isset( $options['_add'] ) )       ? $options['_add']       : '';

		// Prepare the URL query string.
		$query_arg = array( 'edithook' => $id_format );

		if ( $add_new ) {

			$query_arg['addnew'] = 1;

			if ( $multi_number ) {

				$query_arg['num']  = $multi_number;
				$query_arg['base'] = $id_base;
			}

		} else {

			$query_arg['points_type'] = $points_type;
		}

		if ( isset( $options['_display'] ) && 'template' === $options['_display'] ) {

			/*
			 * We aren't outputting the form for a hook, but a template form for this
			 * hook type. (In other words, we are in the "Available Hooks" section.)
			 */

			// number === 0 implies a template where id numbers are replaced by a generic '__i__'.
			$number = 0;

			// With id_base hook id's are constructed like {$id_base}-{$id_number}.
			$id_format = "{$id_base}-__i__";
		}

		?>

		<div id="hook-<?php echo esc_html( $options['_id_slug'] ); ?>_<?php echo esc_attr( $id_format ); ?>" class="hook <?php echo esc_attr( $options['_classname'] ); ?>">
		<div class="hook-top">
			<div class="hook-title-action">
				<a class="hook-action hide-if-no-js" href="#available-hooks"></a>
				<a class="hook-control-edit hide-if-js" href="<?php echo esc_attr( esc_url( add_query_arg( $query_arg ) ) ); ?>">
					<span class="edit"><?php echo esc_html_x( 'Edit', 'hook', 'wordpoints' ); ?></span>
					<span class="add"><?php echo esc_html_x( 'Add', 'hook', 'wordpoints' ); ?></span>
					<span class="screen-reader-text"><?php echo esc_html( strip_tags( $hook->get_name() ) ); ?></span>
				</a>
			</div>
			<div class="hook-title"><h4><?php echo esc_html( strip_tags( $hook->get_name() ) ) ?><span class="in-hook-title"></span></h4></div>
		</div>

		<div class="hook-inside">
			<form method="post">
				<div class="hook-content">
					<?php $has_form = $hook->form_callback( $number ); ?>
				</div>

				<input type="hidden" name="hook-id" class="hook-id" value="<?php echo esc_attr( $id_format ); ?>" />
				<input type="hidden" name="id_base" class="id_base" value="<?php echo esc_attr( $id_base ); ?>" />
				<input type="hidden" name="hook-width" class="hook-width" value="<?php echo isset( $options['width'] ) ? esc_attr( $options['width'] ) : ''; ?>" />
				<input type="hidden" name="hook-height" class="hook-height" value="<?php echo isset( $options['height'] ) ? esc_attr( $options['height'] ) : ''; ?>" />
				<input type="hidden" name="hook_number" class="hook_number" value="<?php echo esc_attr( $number ); ?>" />
				<input type="hidden" name="multi_number" class="multi_number" value="<?php echo esc_attr( $multi_number ); ?>" />
				<input type="hidden" name="add_new" class="add_new" value="<?php echo esc_attr( $add_new ); ?>" />

				<div class="hook-control-actions">
					<div class="alignleft">
						<a class="hook-control-remove" href="#remove"><?php esc_html_e( 'Delete', 'wordpoints' ); ?></a> |
						<a class="hook-control-close" href="#close"><?php esc_html_e( 'Close', 'wordpoints' ); ?></a>
					</div>
					<div class="alignright<?php echo ( false === $has_form ) ? ' hook-control-noform' : ''; ?>">
						<?php submit_button( __( 'Save', 'wordpoints' ), 'button-primary hook-control-save right', 'savehook', false, array( 'id' => "hook-{$id_format}-savehook" ) ); ?>
						<span class="spinner"></span>
					</div>
					<br class="clear" />
				</div>
			</form>
		</div>

		<div class="hook-description">
			<?php if ( ! empty( $options['description'] ) ) : ?>
				<?php echo esc_html( $options['description'] ); ?>
			<?php endif; ?>
		</div>
		</div>

		<?php
	}

	/**
	 * Callback to sort hooks by name.
	 *
	 * @see http://www.php.net/strnatcasecmp strnatcasecmp()
	 *
	 * @since 1.0.0
	 *
	 * @param WordPoints_Points_Hook $a The first hook object.
	 * @param WordPoints_Points_Hook $b The second hook object.
	 *
	 * @return int
	 */
	private static function _sort_name_callback( $a, $b ) {

		return strnatcasecmp( $a->get_name(), $b->get_name() );
	}

} // class WordPoints_Points_Hooks

// EOF
