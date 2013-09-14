<?php

/**
 * WordPoints_Modules class and wrapper functions.
 *
 * This class loads, registers, activates and deactivates modules.
 *
 * @package WordPoints\Modules
 * @since 1.0.0
 */

/**
 * Register a module.
 *
 * @since 1.0.0
 *
 * @uses WordPoints_Modules::register()
 *
 * @param array $args The module arguments.
 *
 * @return bool Whether the module was registered.
 */
function wordpoints_register_module( $args ) {

	return WordPoints_Modules::instance()->register( $args );
}

/**
 * Component activation check wrapper.
 *
 * @since 1.0.0
 *
 * @uses WordPoints_Modules::is_active()
 *
 * @param string $slug The component slug.
 *
 * @return bool Whether the module is active.
 */
function wordpoints_module_is_active( $slug ) {

	return WordPoints_Components::instance()->is_active( $slug );
}

// Instantiate the class.
WordPoints_Modules::set_up();

/**
 * Module handler class.
 *
 * This class handles module related actions, including activating and deactivating.
 * It is a singleton, with only one instance which must be accessed through the
 * instance() method.
 *
 * @since 1.0.0
 */
final class WordPoints_Modules {

	//
	// Private Vars.
	//

	/**
	 * The single instance.
	 *
	 * @since 1.0.0
	 *
	 * @type WordPoints_Modules $instance
	 */
	private static $instance;

	/**
	 * The directory where the module files are strored.
	 *
	 * @since 1.0.0
	 *
	 * @type string $dir
	 */
	private $dir;

	/**
	 * The registered modules.
	 *
	 * @since 1.0.0
	 *
	 * @type array $registered
	 */
	private $registered;

	/**
	 * The active modules.
	 *
	 * @since 1.0.0
	 *
	 * @type array $active
	 */
	private $active;

	//
	// Private Methods.
	//

	/**
	 * Inconstructable.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {}

	/**
	 * After they made me, they broke the mold.
	 *
	 * @since 1.0.0
	 */
	private function __clone() {}

	/**
	 * Reset the $active member var from the database.
	 *
	 * @since 1.0.0
	 */
	private function _reset_active() {

		$this->active = wordpoints_get_array_option( 'wordpoints_active_modules' );
	}

	//
	// Public Methods.
	//

	/**
	 * Set up the class.
	 *
	 * This function is called at the top of this file to insure the hook is set up.
	 *
	 * You should not call this method directly.
	 *
	 * @since 1.0.0
	 *
	 * @uses add_action() To hook up the load function.
	 */
	public static function set_up() {

		if ( isset( self::$instnace ) )
			return;

		self::$instance = new WordPoints_Modules();

		self::$instance->_reset_active();

		add_action( 'plugins_loaded', array( self::$instance, 'load' ), 15 );

		if ( defined( 'WORDPOINTS_MODULES_DIR' ) && WORDPOINTS_MODULES_DIR == true ) {

			self::$instance->dir = trailingslashit( WORDPOINTS_MODULES_DIR );

		} else {

			self::$instance->dir = dirname( dirname( WORDPOINTS_DIR ) ) . '/wordpoints-modules/';
		}
	}

	/**
	 * Return an instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return WordPoints_Modules
	 */
	public static function instance() {

		return self::$instance;
	}

	/**
	 * Get all registered modules.
	 *
	 * @since 1.0.0
	 *
	 * @return array If the modules haven't been registered yet, it will be empty.
	 */
	public function get() {

		return (array) $this->registered;
	}

	/**
	 * Get all active modules.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_active() {

		return $this->active;
	}

	/**
	 * Get the path to the modules directory.
	 *
	 * The default is /wp-content/wordpoints-modules/. To override this, define the
	 * WORDPOINTS_MODULES_DIR constant in wp-config.php like this:
	 *
	 * define( 'WORDPOINTS_MODULES_DIR', '/my/custom/path/' );
	 *
	 * @since 1.0.0
	 *
	 * @return string The path to the modules directory (with trailing slash).
	 */
	public function get_dir() {

		return $this->dir;
	}

	/**
	 * Include all modules in the modules directory.
	 *
	 * This function includes all '.php' files in the module directory. It also
	 * includes '.php' files in subdirectories, where the file name matches the sub-
	 * directory name. For example '/modules/test/test.php' would be included, but
	 * '/modules/test/example.php' would not be included automatically. This is very
	 * similar to the way it works with WordPress plugins. If you have more than one
	 * file in a module, you will need to conditionally include it in the main file.
	 *
	 * @since 1.0.0
	 *
	 * @action plugins_loaded 15 After components. Added in the init() method.
	 *
	 * @uses WordPoints_Modules::get_dir() To get the modules directory.
	 * @uses wordpoints_dir_include() To include the modules' main files.
	 * @uses do_action() To call 'wordpoints_modules_register'.
	 * @uses do_action() To call 'wordpoints_modules_loaded'.
	 */
	public function load() {

		wordpoints_dir_include( $this->dir );

		/**
		 * Register modules.
		 *
		 * You should hook into this function to register your module.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wordpoints_modules_register' );

		/**
		 * Modules loaded.
		 *
		 * All modules loaded and registered.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wordpoints_modules_loaded' );
	}

	/**
	 * Check if a module is registered.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The module's slug.
	 *
	 * @return bool True if the module is registered.
	 */
	public function is_registered( $slug ) {

		return isset( $this->registered[ $slug ] );
	}

	/**
	 * Register a module.
	 *
	 * This function's single parameter is an associative array of module data. Only
	 * the first three parameters are required.
	 *
	 * Example:
	 * <code>
	 * array(
	 *      'name'           => 'Module Name',
	 *      'slug'           => 'module_name',
	 *      'version'        => '1.0.0',
	 *      'description'    => 'This cool module does all sorts of nice stuff.',
	 *      'author'         => 'J.D. Grimes',
	 *      'author_uri'     => 'http://www.example.com/me',
	 *      'module_uri'     => 'http://www.example.com/module-name',
	 * );
	 * </code>
	 *
	 * It will return false if name, slug, or version are not set, or if the slug is
	 * already registered.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args The module data. {
	 *        @type string $slug        The slug for the modules. Must be unique.
	 *        @type string $name        The module's name.
	 *        @type string $version     The version number.
	 *        @type string $author      The name of the module author.
	 *        @type string $author_uri  The author's web page.
	 *        @type string $module_uri  The modules' web page.
	 *        @type string $description A description of what the module does.
	 * }
	 *
	 * @return bool Whether the module was registered successfully.
	 */
	public function register( $args ) {

		$defaults = array(
			'slug'        => '',
			'name'        => '',
			'version'     => '1.0.0',
			'author'      => '',
			'author_uri'  => '',
			'module_uri'  => '',
			'description' => '',
		);

		$module = wp_parse_args( $args, $defaults );

		if ( $this->is_registered( $module['slug'] ) || empty( $module['name'] ) || empty( $module['slug'] ) || empty( $module['version'] ) )
			return false;

		$this->registered[ $module['slug'] ] = array_intersect_key( $module, $defaults );

		return true;
	}

	/**
	 * Deregister a module.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The module's slug.
	 *
	 * @return bool True if the module isn't registered.
	 */
	public function deregister( $slug ) {

		if ( $this->is_registered( $slug ) ) {

			unset( $this->registered[ $slug ] );

			/**
			 * Module deregistered.
			 *
			 * @since 1.0.0
			 */
			do_action( "wordpoints_module_deregiseter-{$slug}" );
		}

		return true;
	}

	/**
	 * Check if a module is activated.
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'wordpoints_module_active'.
	 *
	 * @param string $slug The module's slug.
	 *
	 * @return bool True if the module is activated, otherwise, false.
	 */
	public function is_active( $slug ) {

		$is_active = isset( $this->active[ $slug ] );

		/**
		 * Is a module active?
		 *
		 * Be careful what you do with this, as switching a module's activation on
		 * and off without calling the expected actions could cause unexpected
		 * results (to state the obvious). Its main purpose from within the plugin is
		 * to turn all modules off during the plugin uninstall process.
		 *
		 * @since 1.0.0
		 *
		 * @param bool   $is_active Whether the module is active.
		 * @param string $slug      The module's slug.
		 */
		return apply_filters( 'wordpoints_module_active', $is_active, $slug );
	}

	/**
	 * Activate a module.
	 *
	 * @since 1.0.0
	 *
	 * @uses do_action() To call the module's activation hook..
	 *
	 * @param string $slug The module's slug.
	 *
	 * @return bool
	 */
	public function activate( $slug ) {

		if ( ! $this->is_registered( $slug ) )
			return false;

		if ( ! $this->is_active( $slug ) ) {

			$this->active[ $slug ] = 1;

			if ( update_option( 'wordpoints_active_modules', $this->active ) ) {

				/**
				 * Module activated.
				 *
				 * You need to hook into this to run your install process if you have
				 * one.
				 *
				 * @since 1.0.0
				 */
				do_action( "wordpoints_module_activate-{$slug}" );

			} else {

				$this->_reset_active();
				return false;
			}
		}

		return true;
	}

	/**
	 * Deactivate a module.
	 *
	 * The returned value indicates whether the module is deactivated. It does not
	 * necessarily mean that it was just deactivated. Note that if the module isn't
	 * registered, true will be returned.
	 *
	 * @since 1.0.0
	 *
	 * @uses do_action() To call the module's deactivation hook.
	 *
	 * @param string $slug The comonent's slug.
	 *
	 * @return bool
	 */
	public function deactivate( $slug ) {

		if ( $this->is_active( $slug ) ) {

			unset( $this->active[ $slug ] );

			if ( update_option( 'wordpoints_active_modules', $this->active) ) {

				/**
				 * Module deactivated.
				 *
				 * @since 1.0.0
				 */
				do_action( "wordpoints_modules_deactivate-{$slug}" );

			} else {

				$this->_reset_active();
				return false;
			}
		}

		return true;
	}
}

// end of file /includes/class-wordpoints-modules.php
