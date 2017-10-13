<?php
/**
 * Class for create and registered page Settings for admin part.
 *
 * @since      1.0.0
 * @package    SimpleAPI
 * @subpackage SimpleAPI/admin/pages
 * @author     Timon
 */
namespace Timon\SimpleAPI\admin\pages;

use Timon\SimpleAPI\admin\Page;
use Timon\SimpleAPI\inc\Config;
use Timon\SimpleAPI\lib\Helpers;

class Settings extends Page {

	public $ips;

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct()
	{
		parent::__construct('Settings');
		$config = new Config();

		$this->ips = $config->ips;
	}

	/**
	 * Setter for field ips
	 *
	 * @param $ips - values of field ips
	 * @return $this
	 */
	public function setIps($ips)
	{
		if (empty($ips)) {
			$this->ips = [];
		}elseif (is_array($ips)) {
			$this->ips = $ips;
		} elseif (is_string($ips)) {
			$trimIps = trim(preg_replace('/\s{2,}/',' ',preg_replace('/\n|\r|,/',' ',$ips)));
			if (empty($trimIps)) {
				$this->ips = [];
			} else {
				$this->ips = explode(' ',$trimIps);
			}
		}
		return $this;
	}

	/**
	 * Function where is all business logic of the page
	 *
	 * @return array - return array of variables that should be in the view.
	 */
	public function controller()
	{
		return [
			'ips' => $this->ips
		];
	}

	/**
	 * Save data that come from POST request from form on the page
	 */
	public function post_callback()
	{
		if (!empty($_POST) && !empty($_POST['sapi_settings_fields']) && wp_verify_nonce( $_POST['sapi_' . $this->_name . '_nonce'], 'sapi_' . $this->_name . '_post')) {
			foreach ($_POST['sapi_settings_fields'] as $key=>$value) {
				$this->set($key,$value);
			}

			$this->updateConfig();
		}

		wp_safe_redirect(Helpers::get_previous_url(admin_url()));
		exit;
	}

	/**
	 * Update config for the new values of the page
	 */
	public function updateConfig() {
		$config = new Config();

		$config->set('ips',$this->ips)->save();
	}
}