<?php

namespace  Timon\SimpleAPI\admin;

use Timon\SimpleAPI\admin\pages\Settings;
use Timon\SimpleAPI\inc\Config;
use Timon\SimpleAPI\inc\entity\Access;
use Timon\SimpleAPI\inc\entity\Method;
use Timon\SimpleAPI\lib\Helpers;
use Timon\SimpleAPI\lib\PostTypeCreator;
use Timon\SimpleAPI\lib\TaxonomyCreator;

class Admin {

	/**
	 * Holds class single instance
	 * @var null
	 */
	public static $_instance = null;

	public $settingPage;

	/**
	 * Get instance
	 * @return Admin|null
	 */
	public static function getInstance() {

		if ( null == static::$_instance ) {
			static::$_instance = new self();
		}

		return static::$_instance;
	}


	/**
	 * Initialize the class and set its properties.
	 *
	 */
	private function __construct()
	{
		Access::init();
		Method::init();
		$this->settingPage = new Settings();
	}


	public function init()
	{
		PostTypeCreator::registerPostTypes();
		TaxonomyCreator::registerTaxonomy();
	}


	/**
	 * Register the stylesheets for the admin area, etc.
	 *
	 */
	public function adminInit()
	{
		wp_register_style(SAPI_PLUGIN_BASENAME, SAPI_PLUGIN_ADMIN_URL . 'css/simple-api-admin.css', array(), SAPI_VERSION, 'all' );

		wp_register_script(SAPI_PLUGIN_BASENAME, SAPI_PLUGIN_ADMIN_URL . 'js/simple-api-admin.js', array( 'jquery' ), SAPI_VERSION, false );
	}

	/**
	 * Enqueue the stylesheets for the admin area.
	 *
	 */
	public function enqueueStyles()
	{
		wp_enqueue_style(SAPI_PLUGIN_BASENAME);
	}

	/**
	 * Enqueue the JavaScript for the admin area.
	 *
	 */
	public function enqueueScripts()
	{
		if( !wp_script_is( 'jquery' ) )
		{
			wp_enqueue_script('jquery');
		}

		wp_enqueue_script( SAPI_PLUGIN_BASENAME);

		wp_localize_script(SAPI_PLUGIN_BASENAME, 'ajaxSettings', array(
			'url' => admin_url('admin-ajax.php'),
		));
	}

	/**
	 * Added new points to admin menu
	 */
	public function initMenu()
	{
		global $submenu;
		add_menu_page( __('Simple API', SAPI_TEXT_DOMAIN), __('Simple API', SAPI_TEXT_DOMAIN), 'manage_options', 'edit.php?post_type=' . Access::$MACHINE_NAME, null, 'dashicons-admin-network' );
		add_submenu_page('edit.php?post_type=' . Access::$MACHINE_NAME, __('Methods', SAPI_TEXT_DOMAIN), __('Methods', SAPI_TEXT_DOMAIN), 'manage_options','edit.php?post_type=' . Method::$MACHINE_NAME);

		$this->settingPage->addToMenu('edit.php?post_type=' . Access::$MACHINE_NAME);

		$submenu['edit.php?post_type=' . Access::$MACHINE_NAME][0][0] = 'Accesses';
		$submenu['edit.php?post_type=' . Access::$MACHINE_NAME][0][3] = 'Accesses';
	}

	/**
	 * Change parent file for some active menu item
	 * @param $parent_file
	 * @return string
	 */
	public function recipeTaxMenuCorrection($parent_file)
	{
		global $submenu_file;
		global $current_screen;

		if ($current_screen->id == Access::$MACHINE_NAME ) {

			$parent_file = 'edit.php?post_type=' . Access::$MACHINE_NAME;
			$submenu_file = 'edit.php?post_type=' . Access::$MACHINE_NAME;

		} elseif ($current_screen->id == Method::$MACHINE_NAME ) {

			$parent_file = 'edit.php?post_type=' . Access::$MACHINE_NAME;
			$submenu_file = 'edit.php?post_type=' . Method::$MACHINE_NAME;

		}

		return $parent_file;
	}



}