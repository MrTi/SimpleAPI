<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    SimpleAPI
 * @subpackage SimpleAPI/inc
 * @author     Timon
 */


namespace Timon\SimpleAPI\inc;

use Timon\SimpleAPI\admin\Admin;
use Timon\SimpleAPI\frontend\Frontend;
use Timon\SimpleAPI\lib\Loader;

class SimpleAPI {

	protected $loader;

	public function __construct()
	{
		$this->loadDependencies();
		$this->initAdminHooks();
		$this->initFrontendHooks();
	}

	/**
	 * Method loads all dependencies and libs that need for class
	 */
	protected function loadDependencies()
	{
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		$this->loader = new Loader();
	}

	/**
	 * Added needed hooks for admin part of plugin
	 */
	protected function initAdminHooks()
	{
		$plugin_admin = new Admin();

		$this->getLoader()->add_action( 'init', $plugin_admin, 'init' );

		$this->getLoader()->add_action( 'admin_init', $plugin_admin, 'admin_init' );

		$this->getLoader()->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->getLoader()->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->getLoader()->add_action( 'admin_menu', $plugin_admin, 'init_menu' );
		$this->getLoader()->add_filter( 'parent_file', $plugin_admin, 'recipe_tax_menu_correction' );
	}

	/**
	 * Added needed hooks for frontend part of plugin
	 */
	protected function initFrontendHooks()
	{
		$plugin_frontend = new Frontend();

		$this->getLoader()->add_action( 'init', $plugin_frontend, 'init' );

		$this->getLoader()->add_filter( 'query_vars', $plugin_frontend, 'add_query_vars' );
		$this->getLoader()->add_action( 'parse_request', $plugin_frontend, 'parse_request_callback' );

	}

	/**
	 * Get the object of Loader
	 *
	 * @return Loader
	 */
	public function getLoader()
	{
		return $this->loader;
	}

	/**
	 *  Register the filters and actions with WordPress.
	 */
	public function run()
	{
		$this->getLoader()->run();
	}

}