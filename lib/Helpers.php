<?php
/**
 * Additional functions for the plugin.
 *
 * @since      1.0.0
 * @package    SimpleAPI
 * @subpackage SimpleAPI/lib
 * @author     Timon
 */

namespace Timon\SimpleAPI\lib;


class Helpers {

	/**
	 * Function as_array return all value as array: null => array(), 5=> array(5), {field:int} => array({field:int})
	 *
	 * @param       mixed       $var
	 * @return      array
	 */
	public static function as_array($var) {
		if (empty($var)) return array();

		if (is_array($var)) {
			return $var;
		} else {
			return array($var);
		}

	}

	/**
	 * Function camelcase convert text to camelcase word: 'some-teXt_for funcTionName' => 'SomeTextForFunctionname'
	 *
	 * @param       string      $text
	 * @return      string
	 */
	public static function camelcase($text)
	{
		return str_replace(' ','',ucwords(preg_replace('/[\-\_]/',' ', strtolower($text))));
	}

	/**
	 * Function issetor return $var if is set or $default in other case
	 *
	 * @param   mixed $var
	 * @param   mixed $default (false for default)
	 * @return  mixed
	 */
	public static function issetor(&$var, $default = false)
	{
		return isset($var) ? $var : $default;
	}

	/**
	 * Function get_previous_url return the REFERER url for some page
	 *
	 * @param       string      $default_url
	 * @return      string
	 */
	public static function get_previous_url($default_url = '')
	{
		if ( isset( $_SERVER["HTTP_REFERER"] ) ) {
			return $_SERVER["HTTP_REFERER"];
		}
		return $default_url;
	}

}