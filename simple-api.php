<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @wordpress-plugin
 * Plugin Name:       Simple API
 * Description:       Generate KEYs for users and URLS for API methods.
 * Version:           1.0.0
 * Author:            Timon
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       simple-api
 * Domain Path:       /languages
 */

namespace Timon\SimpleAPI;

use Timon\SimpleAPI\inc\SimpleAPI;
use Timon\SimpleAPI\inc\Activator;
use Timon\SimpleAPI\inc\Deactivator;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


if(!defined('SAPI_VERSION')) {
	define('SAPI_VERSION', '1.0.0');
}

if(!defined('SAPI_TEXT_DOMAIN')) {
	define('SAPI_TEXT_DOMAIN', 'simple-api');
}

if(!defined('SAPI_PLUGIN_BASENAME')) {
	define('SAPI_PLUGIN_BASENAME', plugin_basename(__FILE__));
}

if(!defined('SAPI_PLUGIN_DIRECTORY')) {
	define('SAPI_PLUGIN_DIRECTORY', dirname(__FILE__));
}

if(!defined('SAPI_PLUGIN_FILE')) {
	define('SAPI_PLUGIN_FILE', __FILE__);
}

if(!defined('SAPI_PLUGIN_INC_URL')) {
	define('SAPI_PLUGIN_INC_URL', plugins_url('inc',__FILE__) . '/');
}

if(!defined('SAPI_PLUGIN_INC_DIRECTORY')) {
	define('SAPI_PLUGIN_INC_DIRECTORY', SAPI_PLUGIN_DIRECTORY . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR );
}

if(!defined('SAPI_PLUGIN_ADMIN_URL')) {
	define('SAPI_PLUGIN_ADMIN_URL', plugins_url('admin',__FILE__) . '/');
}

if(!defined('SAPI_PLUGIN_ADMIN_DIRECTORY')) {
	define('SAPI_PLUGIN_ADMIN_DIRECTORY', SAPI_PLUGIN_DIRECTORY . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR );
}

require_once SAPI_PLUGIN_DIRECTORY . '/vendor/autoload.php';


/**
 * The code that runs during plugin activation.
 */
function activate_simple_api() {
	Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_simple_api() {
	Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'Timon\SimpleAPI\activate_simple_api' );
register_deactivation_hook( __FILE__, 'Timon\SimpleAPI\deactivate_simple_api' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 */
function run_simple_api() {

	$plugin = new SimpleAPI();
	$plugin->run();

}
run_simple_api();