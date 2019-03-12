<?php
/**
 * Create and register post types for the plugin.
 *
 * @since      1.0.0
 * @package    SimpleAPI
 * @subpackage SimpleAPI/lib
 * @author     Timon
 */

namespace Timon\SimpleAPI\lib;


class PostTypeCreator
{
	private static $labels;
	private static $args;
	private static $machineNames;

	/**
	 * Added post type args to array for registering
	 *
	 * @param string $machineName      - name of PostType
	 * @param string $singularName     - singular name for labels
	 * @param string $pluralName       - plural name for labels
	 * @param string $custom_base_link - custom slug for rewrite
	 */
	public static function addPostType($machineName, $singularName, $pluralName, $custom_base_link = '')
	{
		self::$labels[$machineName] = [
			'name'                  => $pluralName,
			'singular_name'         => $singularName,
			'menu_name'             => $singularName,
			'name_admin_bar'        => $singularName,
			'archives'              => sprintf(_x('%s Archives', 'Post Type Creator'), $singularName),
			'parent_item_colon'     => sprintf(_x('Parent %s:', 'Post Type Creator'), $singularName),
			'all_items'             => sprintf(_x('All %s', 'Post Type Creator'), $pluralName),
			'add_new_item'          => sprintf(_x('Add New %s', 'Post Type Creator'), $singularName),
			'add_new'               => sprintf(_x('Add New %s', 'Post Type Creator'), $singularName),
			'new_item'              => sprintf(_x('New %s', 'Post Type Creator'), $singularName),
			'edit_item'             => sprintf(_x('Edit %s', 'Post Type Creator'), $singularName),
			'update_item'           => sprintf(_x('Update %s', 'Post Type Creator'), $singularName),
			'view_item'             => sprintf(_x('View %s', 'Post Type Creator'), $singularName),
			'search_items'          => sprintf(_x('Search %s', 'Post Type Creator'), $singularName),
			'not_found'             => _x('Not found', 'Post Type Creator'),
			'not_found_in_trash'    => _x('Not found in Trash', 'Post Type Creator'),
			'featured_image'        => _x('Featured Image', 'Post Type Creator'),
			'set_featured_image'    => _x('Set featured image', 'Post Type Creator'),
			'remove_featured_image' => _x('Remove featured image', 'Post Type Creator'),
			'use_featured_image'    => _x('Use as featured image', 'Post Type Creator'),
			'insert_into_item'      => _x('Insert into item', 'Post Type Creator'),
			'uploaded_to_this_item' => sprintf(_x('Uploaded to this %s', 'Post Type Creator'), $singularName),
			'items_list'            => sprintf(_x('%s list', 'Post Type Creator'), $singularName),
			'items_list_navigation' => sprintf(_x('%s list navigation', 'Post Type Creator'), $singularName),
			'filter_items_list'     => sprintf(_x('Filter %s list', 'Post Type Creator'), $singularName),
		];

		$args = [
			'label'               => $singularName,
			'description'         => $singularName,
			'labels'              => self::$labels[$machineName],
			'supports'            => ['title'],
			'taxonomies'          => [],
			'hierarchical'        => TRUE,
			'public'              => TRUE,
			'show_ui'             => TRUE,
			'show_in_menu'        => TRUE,
			'menu_position'       => 5,
			'show_in_admin_bar'   => TRUE,
			'show_in_nav_menus'   => TRUE,
			'can_export'          => TRUE,
			'has_archive'         => TRUE,
			'exclude_from_search' => FALSE,
			'publicly_queryable'  => TRUE,
			'capability_type'     => 'post',
		];

		if ($custom_base_link !== '') {
			$args['rewrite'] = [
				'slug' => $custom_base_link
			];
		}

		self::$args[$machineName] = $args;

		self::$machineNames[] = $machineName;
	}

	/**
	 * Change args for some PostType
	 *
	 * @param array  $args        - args that should be changed
	 * @param string $machineName - name of PostType
	 */
	public static function setArgs(array $args, $machineName)
	{
		self::$args[$machineName] = array_merge(self::$args[$machineName], $args);
	}

	/**
	 * Change capability_type of some PostType
	 *
	 * @param string $type        - new capability_type
	 * @param string $machineName - name of PostType
	 */
	public static function setType($type, $machineName)
	{
		$args = [
			'capability_type' => $type
		];
		self::setArgs($args, $machineName);
	}

	/**
	 * Added Taxonomy for PostType
	 *
	 * @param string $taxonomy    - name of Taxonomy
	 * @param string $machineName - name of PostType
	 */
	public static function addTaxonomy($taxonomy, $machineName)
	{
		$curtax = self::$args[$machineName]['taxonomies'];
		$taxonomies = array();
		if (!empty($curtax)) {
			if (is_array($curtax)) {
				$taxonomies = $curtax;
			} else {
				$taxonomies = array($curtax);
			}
		}

		if (!in_array($taxonomy, $taxonomies)) {
			$taxonomies[] = $taxonomy;
		}

		$args = [
			'taxonomies' => $taxonomies
		];
		self::setArgs($args, $machineName);
	}

	/**
	 * Changes labels for PostType
	 *
	 * @param array  $labels      - new labels
	 * @param string $machineName - name of PostType
	 */
	public static function setLabels(array $labels, $machineName)
	{
		self::$labels[$machineName] = array_merge(self::$labels[$machineName], $labels);

		$args = [
			'label' => self::$labels[$machineName]
		];
		self::setArgs($args, $machineName);
	}

	/**
	 * Registered all PostTypes
	 */
	public static function registerPostTypes()
	{
		if (!empty(self::$machineNames)) {
			foreach (self::$machineNames as $machineName) {
				register_post_type($machineName, self::$args[$machineName]);
			}
		}
	}

	/**
	 * Added function for registered post types to init action
	 */
	public static function addToInit()
	{
		add_action('init', [self::class, 'registerPostTypes']);
	}
}