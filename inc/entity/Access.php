<?php
/**
 * Created PostType for accesses
 *
 * This class create and register PostType sapi_access and have the functions for work with entity Access.
 *
 * @since      1.0.0
 * @package    SimpleAPI
 * @subpackage SimpleAPI/inc/entity
 * @author     Timon
 */

namespace Timon\SimpleAPI\inc\entity;

use Timon\SimpleAPI\lib\Helpers;
use Timon\SimpleAPI\lib\PostTypeCreator;

class Access {

	public $id;
	public $title;
	public $desc;
	public $create_date;

	public $key;
	public $user;

	public $ips;

	public static $MACHINE_NAME = 'sapi_access';

	/**
	 * Create object of Access class
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
		if (!$force && $post->post_type != Access::$MACHINE_NAME) return $this;

		$this->id = $post->ID;
		$this->title = $post->post_title;
		$this->desc = $post->post_content;
		$this->create_date = $post->post_date;

		$this->key = get_post_meta($post->ID, 'key', true);
		$user = get_post_meta($post->ID, 'user', true);

		if (is_numeric($user)) {
			$wpuser = get_user_by('id',$user);
			if (!empty($wpuser) && !is_wp_error($wpuser)) {
				$this->user = $wpuser->user_login;
			}
		} else {
			$this->user = $user;
		}

		$this->ips = get_post_meta($post->ID, 'ips', true);

		return $this;
	}

	/**
	 * Set values for fields from array
	 *
	 * @param array $array  - array where keys it is names of fields and values it is values of fields
	 * @return $this
	 */
	public function updateFromArray(array $array)
	{
		if (!empty($array)) {
			foreach ($array as $key=>$item) {
				$this->set($key,$item);
			}
		}

		return $this;
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
	 * Insert or Update data of object in the database
	 *
	 * @return $this
	 */
	public function save()
	{
		$args = [
			'post_title'   => $this->title,
			'post_content' => $this->desc,
			'post_date'    => $this->create_date,
		];

		if (empty($this->id)) {
			$args['post_type'] = Access::$MACHINE_NAME;
			$args['post_status'] = 'publish';
		} else {
			$args['ID'] = $this->id;
		}

		remove_action('save_post', array(get_class($this), 'access_save_meta_box'));
		$this->id = wp_update_post($args);
		add_action('save_post', array(get_class($this), 'access_save_meta_box'));

		update_post_meta($this->id,'user',$this->user);
		update_post_meta($this->id,'key',$this->key);
		update_post_meta($this->id,'ips',$this->ips);

		return $this;
	}

	/**
	 * Check if IP address of client are in White list for request
	 *
	 * @param $addr - IP address of client
	 * @return bool
	 */
	public function isAvailableIP($addr) {

		if (empty($this->ips)) return true;

		$available_addrs = array_filter(array_map(function($item) use ($addr) {
			$regitem = str_replace('*','.*',$item);

			return preg_match('/' . $regitem . '/',$addr) ? $item : '';
		},$this->ips));

		return !empty($available_addrs);
	}

	/**
	 * Find wp_post by key field and create the object of class or return false if key not exist in DB
	 *
	 * @param string $key
	 * @return bool|Access
	 */
	public static function findByKey($key)
	{
		if (empty($key)) return false;

		$args = array(
			'post_type' => Access::$MACHINE_NAME,
			'meta_query'     => array(
				array(
					'key'     => 'key',
					'value'   => $key,
					'compare' => '=',
				),
			)
		);

		$loop = new \WP_Query($args);

		$posts = $loop->get_posts();
		if (!empty($posts) && !is_wp_error($posts)) {
			return new Access(reset($posts));
		}

		return false;
	}

	/**
	 * Check if valid key and REMOTE_ADDR of clients to make API request
	 *
	 * @return bool
	 */
	public static function apiauthenticate() {

		$key = filter_input( INPUT_GET, 'k' );
		$remote_addr = filter_input( INPUT_SERVER, 'REMOTE_ADDR' );

		$access = Access::findByKey($key);

		if ($access->key == $key && $access->isAvailableIP($remote_addr)) {
			return true;
		}

		return false;
	}

	/**
	 *  Function for create and settings post_type sapi_access in the WP
	 */
	public static function init()
	{
		PostTypeCreator::addPostType(Access::$MACHINE_NAME, 'Access', 'Accesses');

		PostTypeCreator::setArgs(array(
			'supports'      => ['title','editor'],
			'menu_icon'     => 'dashicons-admin-network',
			'show_in_menu'  => false,
			'menu_position' => null,
		),Access::$MACHINE_NAME);

		$self = new Access();

		add_action('add_meta_boxes', array(get_class($self), 'access_register_meta_boxes'));
		add_action('save_post', array(get_class($self), 'access_save_meta_box'));

		add_filter("manage_edit-" . Access::$MACHINE_NAME . "_columns", function($columns) {
			global $typenow;
			$new_columns = $columns;
			if (in_array($typenow, array(Access::$MACHINE_NAME) )) {

				$new_columns = array();
				$after = false;
				foreach ($columns as $key=>$value) {
					if ($after) {
						$new_columns['user']=  __("User",SAPI_TEXT_DOMAIN);
						$new_columns['key'] =  __("Key",SAPI_TEXT_DOMAIN);
						$after = false;
					}
					$new_columns[$key] = $value;
					if ($key == 'title') $after = true;
				}
			}
			return $new_columns;
		});

		add_action( "manage_" . Access::$MACHINE_NAME . "_posts_custom_column", function($column) {
			global $post;
			global $typenow;
			if (in_array($typenow, array(Access::$MACHINE_NAME) )) {

				$post_id = get_the_ID();
				switch ( $column ) {
					case 'user' :
						$user = get_post_meta($post_id,'user',true );
						print $user;
						break;
					case 'key' :
						$key = get_post_meta($post_id,'key',true );
						print $key;
						break;
				}
			}
		}, 11, 1 );

	}

	/**
	 * Function for register the meta boxes for sapi_access
	 */
	public static function access_register_meta_boxes()
	{
		$self = new Access();
		add_meta_box('access_data', __('Access data', SAPI_TEXT_DOMAIN), array(get_class($self), 'access_metabox_callback'), Access::$MACHINE_NAME);

		add_action( 'edit_form_after_title', function($post) {
			if ($post->post_type == Access::$MACHINE_NAME) {
				print '<div style="position: relative;top: 20px;z-index: 9999;padding: 5px 0;font-size: 20px;font-weight: 400">Description:</div>';
			}
		} );
	}

	/**
	 * Function for create meta box Access data for sapi_access
	 */
	public static function access_metabox_callback()
	{

		$access = new Access(get_post());

		wp_nonce_field('meta_box', 'meta_box_nonce');
		?>
		<div class="__sapi">
			<div class="row">
				<div class="col-md-12">
					<label for="sapi-access-user"><?php _e('User',SAPI_TEXT_DOMAIN); ?>: </label>
					<input id="sapi-access-user" name="sapi_access_field[user]" type="text" class="form-control" value="<?php print $access->user; ?>"/>
				</div>
			</div>

			<div class="row">
				<div class="col-md-8">
					<label for="sapi-access-key"><?php _e('Key',SAPI_TEXT_DOMAIN); ?>: </label>
					<input id="sapi-access-key" name="sapi_access_field[key]" type="text" class="form-control" value="<?php print $access->key; ?>"/>
				</div>
				<div class="col-md-4">
					<label>&nbsp; </label>
					<a href="#" class=" btn btn-primary form-control" id="sapi-access-generate-key-btn">Generate</a>
				</div>
			</div>

			<div class="row">
				<div class="col-md-12">
					<label for="sapi-access-ips"><?php _e('White list of IPs',SAPI_TEXT_DOMAIN); ?>: </label><br>
					<span> <?php _e('Set the list of IPs  that will have access to API divided by comma. If the list is empty then it means there is no IP restriction', SAPI_TEXT_DOMAIN) ?></span>
					<textarea name="sapi_access_field[ips]" id="sapi-access-ips" class="form-control" cols="30" rows="10"><?php print empty($access->ips) ? '' : implode(",\n",$access->ips) ?></textarea>
				</div>
			</div>

		</div>

	<?php
	}

	/**
	 * Function for filter save_post. Save meta_data for post_type sapi_access
	 * @param $post_id
	 */
	public static function access_save_meta_box($post_id)
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

		if (isset($_POST['post_type']) &&  Access::$MACHINE_NAME != $_POST['post_type']) {
			return;
		}

		if( ! empty($_POST['sapi_access_field']) ) {

			$access = new Access($post_id);

			$access->updateFromArray($_POST['sapi_access_field']);

			$access->save();
		}
	}

}