<?php
/**
 * Created Config class
 *
 * This class take and save the config values for Simple API from/to db.
 *
 * @since      1.0.0
 * @package    SimpleAPI
 * @subpackage SimpleAPI/inc
 * @author     Timon
 */

namespace Timon\SimpleAPI\inc;


use Timon\SimpleAPI\lib\Helpers;

class Config {

	public $ips;

	/**
	 * Construct for Config class, takes config values from DB.
	 */
	public function __construct()
	{
		$sapi_config = Helpers::as_array(get_option('sapi_config'));

		$this->ips = Helpers::issetor($sapi_config['ips'],[]);
	}

	/**
	 * Global setters for all public fields of class
	 *
	 * @param $name  - name of field
	 * @param $value - value of field
	 * @return $this - return object of class
	 */
	public function set($name, $value)
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
	 * Save config values to DB
	 *
	 * @return $this
	 */
	public function save()
	{
		$sapi_config = Helpers::as_array(get_option('sapi_config'));
		$sapi_config['ips'] = $this->ips;

		update_option('sapi_config',$sapi_config);

		return $this;
	}

}