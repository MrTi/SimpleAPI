<?php
/**
 * Created PostType for methods
 *
 * This class create and register PostType sapi_method and have the functions for work with entity Method.
 *
 * @since      1.0.0
 * @package    SimpleAPI
 * @subpackage SimpleAPI/inc/entity
 * @author     Timon
 */

namespace Timon\SimpleAPI\inc\entity;

use Timon\SimpleAPI\lib\Helpers;
use Timon\SimpleAPI\lib\PostTypeCreator;

class Method {

	public $id;
	public $title;
	public $create_date;

	public $method;
	public $desc;
	public $description;

	public $ips;

	public static $MACHINE_NAME = 'sapi_method';

	/**
	 * Create object of Method class
	 *
	 * @param mixed $matches - Can be empty or id of wp_post or WP_POST object or array with values
	 */
	public function __construct($matches = null)
	{
		if (!empty($matches)) {

			if (is_numeric($matches)) {
				$wp_post = get_post($matches);
				$this->createFromPost($wp_post);
			} elseif ($matches instanceof \WP_Post) {
				$this->createFromPost($matches);
			} elseif (is_array($matches)) {
				$this->updateFromArray($matches);
			}
		}
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
	 * loads values of fields from WP_Post
	 *
	 * @param \WP_Post $post  - The wp_post from which the data will be taken
	 * @param bool     $force - if false than data will taken if post_type == $MACHINE_NAME of class only, if true data will taken from any post
	 * @return $this - return object of class
	 */
	public function createFromPost(\WP_Post $post, $force = false)
	{
		if (!$force && $post->post_type != Method::$MACHINE_NAME) return $this;

		$this->id = $post->ID;
		$this->title = $post->post_title;
		$this->create_date = $post->post_date;

		$this->method = get_post_meta($post->ID, 'method', true);
		$this->desc = get_post_meta($post->ID, 'desc', true);
		$this->description = get_post_meta($post->ID, 'description', true);

		return $this;
	}

	/**
	 * Set values for fields from array
	 *
	 * @param array $array  - array where keys it is names of fields and values it is values of fields
	 * @return $this
	 */
	public function updateFromArray($array)
	{
		if (!empty($array)) {
			foreach ($array as $key=>$item) {
				$this->set($key,$item);
			}
		}

		return $this;
	}

	/**
	 * Insert or Update data of object in the database
	 *
	 * @return $this
	 */
	public function save()
	{
		$args = [
			'post_title' => $this->title,
			'post_date' => $this->create_date,
		];

		if (empty($this->id)) {
			$args['post_type'] = Method::$MACHINE_NAME;
			$args['post_status'] = 'public';
		} else {
			$args['ID'] = $this->id;
		}

		remove_action('save_post', array(get_class($this), 'method_save_meta_box'));
		$this->id = wp_update_post($args);
		add_action('save_post', array(get_class($this), 'method_save_meta_box'));

		update_post_meta($this->id,'method',$this->method);
		update_post_meta($this->id,'desc',$this->desc);
		update_post_meta($this->id,'description',$this->description);

		Method::generateApiFile();

		return $this;
	}

	/**
	 * Get all existing methods for API
	 * @return array - return array of objects Method
	 */
	public static function getAll()
	{
		$methods = [];

		$args = array(
			'post_type' => Method::$MACHINE_NAME,
			'posts_per_page' => -1
		);

		$loop = new \WP_Query($args);

		$posts = $loop->get_posts();
		if (!empty($posts) && !is_wp_error($posts)) {
			foreach ($posts as $postitem) {
				$methods[] = new Method($postitem);
			}
		}
		return $methods;
	}

	/**
	 * Find wp_post by method field and create the object of class or return false if method not exist in DB
	 *
	 * @param string $method
	 * @return bool|Access
	 */
	public static function findByMethod($method)
	{
		if (empty($method)) return false;

		$args = array(
			'post_type' => Method::$MACHINE_NAME,
			'posts_per_page' => 1,
			'meta_query'     => array(
				array(
					'key'     => 'method',
					'value'   => $method,
					'compare' => '=',
				),
			)
		);

		$loop = new \WP_Query($args);

		$posts = $loop->get_posts();
		if (!empty($posts) && !is_wp_error($posts)) {
			return new Method(reset($posts));
		}

		return false;
	}

	/**
	 * Function get all methods and generate the file with filters for this methods
	 */
	public static function generateApiFile()
	{
		$methods = Method::getAll();

		$apifilepath = get_template_directory() . '/sapi_methods.php';

		$description = "<?php\n\n/**\n * Callbacks for API methods\n *\n * This generated by Simple API plugin. Here are written callback functions for all methods that was added in admin panel\n */\n\n";
		$filecontent = '';

		if (file_exists($apifilepath)) {
			$filecontent = file_get_contents($apifilepath);
			$f = fopen($apifilepath,'a');
		} else {
			$f = fopen($apifilepath,'w');
			fwrite($f,$description);
		}

		if (!empty($methods)) {
			foreach ($methods as $method) {

				if( strpos($filecontent, "'sapi_" . $method->method . "'") === false) {
					$methodtext = "/**\n * Method name: " . self::_s($method->title) . "\n *\n * " . self::_s($method->desc) . "\n *\n * " . self::_s($method->description) . "\n *\n * @return array like ['success'=>true|false, error=>'',data=>'']\n*/\nadd_filter('sapi_$method->method', function(" . '$response' . ") {\n\n\treturn " . '$response' . ";\n},10,1);\n\n";

					fwrite($f,$methodtext);
				}
			}
		}

		fclose($f);
	}

	/**
	 * Function parse the file with methods and add/update them in the DB
	 */
	public static function parseApiFile()
	{

		$apifilepath = realpath(get_template_directory() . '/sapi_methods.php');

		if (file_exists($apifilepath)) {
//			$filecontent = file_get_contents($apifilepath,true);
			show($apifilepath);

			ob_start();

			print htmlspecialchars(file_get_contents($apifilepath));

			$filecontent = ob_get_clean();

			$filecontent = preg_replace("/\r|\n/","\$1",$filecontent);
			show($filecontent);

			show(preg_match("/(\/\*\*.*\*\/)((\r\n)|(\n\r)|\r|\n)\s*(add_filter.*}\s*\,?\s*(\d*)\s*\,?\s*(\d*)\s*\))\;/gisU",$filecontent,$mached));

			if (!empty($mached)) {

				showx($mached);
			}
		}


		$methods = Method::getAll();



		$description = "<?php\n\n/**\n * Callbacks for API methods\n *\n * This generated by Simple API plugin. Here are written callback functions for all methods that was added in admin panel\n */\n\n";
		$filecontent = '';

		if (file_exists($apifilepath)) {
			$filecontent = file_get_contents($apifilepath);
			$f = fopen($apifilepath,'a');
		} else {
			$f = fopen($apifilepath,'w');
			fwrite($f,$description);
		}

		if (!empty($methods)) {
			foreach ($methods as $method) {

				if( strpos($filecontent, "'sapi_" . $method->method . "'") === false) {
					$methodtext = "/**\n * Method name: " . self::_s($method->title) . "\n *\n * " . self::_s($method->desc) . "\n *\n * " . self::_s($method->description) . "\n *\n * @return array like ['success'=>true|false, error=>'',data=>'']\n*/\nadd_filter('sapi_$method->method', function(" . '$response' . ") {\n\n\treturn " . '$response' . ";\n},10,1);\n\n";

					fwrite($f,$methodtext);
				}
			}
		}

		fclose($f);
	}


	public static function _s($var ='') {
		return preg_replace('/(\r\n|\n\r|\r|\n)/','$1 * ',$var);
	}

	/**
	 *  Function for create and settings post_type sapi_method in the WP
	 */
	public static function init()
	{
		PostTypeCreator::addPostType(Method::$MACHINE_NAME, 'Method', 'Methods');

		PostTypeCreator::setArgs(array(
			'show_in_menu'  =>false,
			'menu_position' =>null,
		),Method::$MACHINE_NAME);

		$self = new Method();

		add_action('add_meta_boxes', array(get_class($self), 'method_register_meta_boxes'));
		add_action('save_post', array(get_class($self), 'method_save_meta_box'));

		add_filter("manage_edit-" . Method::$MACHINE_NAME . "_columns", function($columns) {
			global $typenow;
			$new_columns = $columns;
			if (in_array($typenow, array(Method::$MACHINE_NAME) )) {

				$new_columns = array();
				$after = false;
				foreach ($columns as $key=>$value) {
					if ($after) {
						$new_columns['method']=  __("Method",SAPI_TEXT_DOMAIN);
						$new_columns['desc']=    __("Description",SAPI_TEXT_DOMAIN);
						$after = false;
					}
					$new_columns[$key] = $value;
					if ($key == 'title') $after = true;
				}
			}
			return $new_columns;
		});

		add_action( "manage_" . Method::$MACHINE_NAME . "_posts_custom_column", function($column) {
			global $post;
			global $typenow;
			if (in_array($typenow, array(Method::$MACHINE_NAME) )) {

				$post_id = get_the_ID();
				switch ( $column ) {
					case 'method' :
						$user = get_post_meta($post_id,'method',true );
						print $user;
						break;
					case 'desc' :
						$user = get_post_meta($post_id,'desc',true );
						print $user;
						break;

				}
			}
		}, 11, 1 );

	}

	/**
	 * Function for register the meta boxes for sapi_method
	 */
	public static function method_register_meta_boxes()
	{
		$self = new Method();
		add_meta_box('method_data', __('Method data', SAPI_TEXT_DOMAIN), array(get_class($self), 'method_metabox_callback'), Method::$MACHINE_NAME);
	}

	/**
	 * Function for create meta box Method data for sapi_method
	 */
	public static function method_metabox_callback()
	{

		$access = new Method(get_post());

		wp_nonce_field('meta_box', 'meta_box_nonce');
		?>
		<div class="__sapi">

			<div class="row">
				<div class="col-md-12">
					<label for="sapi-method-method"><?php _e('Method',SAPI_TEXT_DOMAIN); ?>: </label>
					<input id="sapi-method-method" name="sapi_method_field[method]" type="text" class="form-control" value="<?php print $access->method; ?>"/>
				</div>
			</div>

			<div class="row">
				<div class="col-md-12">
					<label for="sapi-method-desc"><?php _e('Desc',SAPI_TEXT_DOMAIN); ?>: </label>
					<input id="sapi-method-desc" name="sapi_method_field[desc]" type="text" class="form-control" value="<?php print $access->desc; ?>"/>
				</div>
			</div>

			<div class="row">
				<div class="col-md-12">
					<label for="sapi-method-description"><?php _e('Description',SAPI_TEXT_DOMAIN); ?>: </label>
					<textarea name="sapi_method_field[description]" id="sapi-method-description" class="form-control"  cols="30" rows="10"><?php print $access->description ?></textarea>
				</div>
			</div>

		</div>

	<?php
	}

	/**
	 * Function for filter save_post. Save meta_data for post_type sapi_method
	 * @param $post_id
	 */
	public static function method_save_meta_box($post_id)
	{
		// Check if our nonce is set.
		if (!isset($_POST['meta_box_nonce'])) {
			return;
		}

		// Verify that the nonce is valid.
		if (!wp_verify_nonce($_POST['meta_box_nonce'], 'meta_box')) {
			return;
		}
		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		// Check the user's permissions.
		if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) {
			if (!current_user_can('edit_page', $post_id)) {
				return;
			}
		} else {
			if (!current_user_can('edit_post', $post_id)) {
				return;
			}
		}

		if (isset($_POST['post_type']) &&  Method::$MACHINE_NAME != $_POST['post_type']) {
			return;
		}

		if( ! empty($_POST['sapi_method_field']) ) {

			$access = new Method($post_id);

			$access->updateFromArray($_POST['sapi_method_field']);

			$access->save();


		}
	}

}