<?php
/**
 * Basic class for create and registered pages for admin part.
 *
 * @since      1.0.0
 * @package    SimpleAPI
 * @subpackage SimpleAPI/admin
 * @author     Timon
 */

namespace Timon\SimpleAPI\admin;


use Timon\SimpleAPI\lib\Helpers;

class Page {

	protected $_name;

	public $slug;
	public $title;

	public $menu_title;
	public $menu_icon;

	/**
	 * Create the name, title and slug for page. Also create action for POST request to page
	 *
	 * @param $name - Title of page
	 */
	public function __construct($name)
	{
		$this->_name = preg_replace('/[^0-9a-z_\-]/', '', strtolower($name));
		$this->slug = 'sapi_' . sanitize_title($name);
		$this->title = $name;
		$this->menu_title = $name;

		$this->menu_icon = 'dashicons-admin-network';

		add_action( 'admin_post_' . $this->_name .'_post' , array($this,'post_callback') );
	}

	/**
	 * Global setters for all public fields of class
	 *
	 * @param $name  - name of field
	 * @param $value - value of field
	 * @return $this - return object of class
	 */
	function set($name, $value)
	{
		if (isset($this->{$name})) {
			if (method_exists($this,'set' . Helpers::camelcase($name))) {
				$this->{'set' . Helpers::camelcase($name)}($value);
			} else {
				$this->{$name} = $value;
			}
		}
		return $this;
	}


	/**
	 * Function where should be all business logic of the page
	 *
	 * @return array - return array of variables that should be in the view.
	 */
	public function controller()
	{
		return [];
	}

	/**
	 * Fired when come request to the admin-post.php with action $this->_name .'_post'
	 */
	public function post_callback()
	{
		global $_parent_pages;
		$parent_slug = Helpers::issetor($_parent_pages[$this->slug],'');

		wp_safe_redirect( admin_url( add_query_arg( 'page', $this->slug, $parent_slug ) ));
	}

	/**
	 * Show the view of page
	 * @return bool
	 */
	public function template()
	{
		$params = $this->controller();
		if (!empty($params))  extract($params);

		require_once $this->getHeader();

		require_once $this->getTemplate();

		require_once $this->getFooter();

		return true;
	}

	/**
	 * return the template path of page by the name if is set or path of error template in other case.
	 *
	 * @return string
	 */
	protected function getTemplate()
	{
		$file = implode(DIRECTORY_SEPARATOR,array(SAPI_PLUGIN_ADMIN_DIRECTORY,'templates',$this->_name)) . '.php';
		if (file_exists($file)) {
			return $file;
		} else {
			return implode(DIRECTORY_SEPARATOR,array(SAPI_PLUGIN_ADMIN_DIRECTORY,'templates','error')) . '.php';
		}
	}

	/**
	 * Return the path of header template.
	 *
	 * @param string $header
	 * @return string
	 */
	protected function getHeader($header='')
	{
		$header_temp = 'header';
		if (!empty($header)) $header_temp .= '-' . $header;
		return implode(DIRECTORY_SEPARATOR,array(SAPI_PLUGIN_ADMIN_DIRECTORY,'templates',$header_temp)) . '.php';
	}

	/**
	 * Return the path of footer template.
	 *
	 * @param string $footer
	 * @return string
	 */
	protected function getFooter($footer='')
	{
		$footer_temp = 'footer';
		if (!empty($footer)) $footer_temp .= '-' . $footer;
		return implode(DIRECTORY_SEPARATOR,array(SAPI_PLUGIN_ADMIN_DIRECTORY,'templates',$footer_temp)) . '.php';
	}


	/**
	 * Function added page to admin menu-bar and register the page.
	 * @param string $parent - parent menu url, if exist page will be added as subpage.
	 * @param string $capability
	 */
	public function addToMenu($parent='',$capability='manage_options')
	{
		if (empty($parent)) {
			add_menu_page( $this->title, $this->menu_title, $capability, $this->slug, array($this,'template'), $this->menu_icon );
		} else {
			add_submenu_page($parent, $this->title, $this->menu_title, $capability, $this->slug, array($this,'template'));
		}
	}

}