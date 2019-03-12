<?php
/**
 * The core plugin class for Frontend part.
 *
 * This is used to implementation public-facing site hooks.
 *
 *
 * @package    SimpleAPI
 * @subpackage SimpleAPI/inc/frontend
 * @author     Timon
 */
namespace  Timon\SimpleAPI\frontend;

use Timon\SimpleAPI\inc\entity\Access;
use Timon\SimpleAPI\inc\entity\Method;

class Frontend {

	/**
	 * Holds class single instance
	 * @var null
	 */
	public static $_instance = null;

	/**
	 * Get instance
	 * @return Frontend|null
	 */
	public static function getInstance() {

		if ( null == static::$_instance ) {
			static::$_instance = new self();
		}

		return static::$_instance;
	}

	private function __construct(){}

	/**
	 * Fired with action init.
	 * Added route for API.
	 */
	public function init()
	{
		add_rewrite_rule('^sapi/api/(.+)/?','index.php?__sapi=1&method=$matches[1]','top');
		flush_rewrite_rules();
	}

	/**
	 * Implementation of filter query_vars.
	 * Added new query tha needed for API requests
	 *
	 * @param $vars  - array of existing query vars
	 * @return array - new query vars array
	 */
	public function addQueryVars($vars)
	{
		$vars[] = '__sapi';
		$vars[] = 'method';
		return $vars;
	}

	/**
	 * Implementation of action parse_request.
	 * Parse query vars take the method from request and run the filter.
	 * Print json response.
	 */
	public function parseRequestCallback()
	{
		global $wp;

		if(isset($wp->query_vars['__sapi'])) {

			$response = array(
				'success' => false,
				'error'   => '',
				'data'    => ''
			);

			$method = Method::findByMethod($wp->query_vars['method']);

			if (!empty($method)) {
				if (Access::apiauthenticate()) {

					$apifilepath = get_template_directory() . '/sapi_methods.php';

					if (file_exists($apifilepath)) {
						require_once $apifilepath;
					}

					$response['error'] = __('The method has not been processed',SAPI_TEXT_DOMAIN);
					$response = apply_filters('sapi_' . $method->method, $response);
				} else {
					$response['error'] = __('Wrong key or your IP is blocked',SAPI_TEXT_DOMAIN);
				}
			} else {
				$response['error'] = __('Method not found',SAPI_TEXT_DOMAIN);
			}

			$this->_response($response);
		}

	}

	/**
	 * Print json responce
	 *
	 * @param $response - result of method
	 */
	protected function _response($response) {
		header('content-type: application/json; charset=utf-8');
		echo json_encode($response)."\n";
		exit;
	}

}