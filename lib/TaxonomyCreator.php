<?php
/**
 * Create and register taxonomies for the plugin.
 *
 * @since      1.0.0
 * @package    SimpleAPI
 * @subpackage SimpleAPI/lib
 * @author     Timon
 */

namespace Timon\SimpleAPI\lib;


class TaxonomyCreator
{
	private static $postTypes;
	private static $labels;
	private static $args;
	private static $machineNames;

	/**
	 * Added taxonomy args to array for registering
	 *
	 * @param $machineName         - name of taxonomy
	 * @param $singularName        - singular name for labels
	 * @param $pluralName          - plural name for labels
	 * @param $postTypeMachineName - name of PostType where should be taxonomy
	 */
	public static function addTaxonomy($machineName, $singularName, $pluralName, $postTypeMachineName)
	{

		self::$labels[$machineName] = [
			'name'                       => $pluralName,
			'singular_name'              => $singularName,
			'menu_name'                  => $singularName,
			'all_items'                  => __('All Items'),
			'parent_item'                => __('Parent Item'),
			'parent_item_colon'          => __('Parent Item:'),
			'new_item_name'              => __('New Item Name'),
			'add_new_item'               => __('Add New Item'),
			'edit_item'                  => __('Edit Item'),
			'update_item'                => __('Update Item'),
			'view_item'                  => __('View Item'),
			'separate_items_with_commas' => __('Separate items with commas'),
			'add_or_remove_items'        => __('Add or remove items'),
			'choose_from_most_used'      => __('Choose from the most used'),
			'popular_items'              => __('Popular Items'),
			'search_items'               => __('Search Items'),
			'not_found'                  => __('Not Found'),
			'no_terms'                   => __('No items'),
			'items_list'                 => __('Items list'),
			'items_list_navigation'      => __('Items list navigation'),
		];


		$args = [
			'labels'            => self::$labels[$machineName],
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => false,
		];


		self::$args[$machineName] = $args;
		self::$postTypes[$machineName] = $postTypeMachineName;

		self::$machineNames[] = $machineName;
	}

	/**
	 * Change args for some Taxonomy
	 *
	 * @param array  $args        - args that should be changed
	 * @param string $machineName - name of Taxonomy
	 */
	public static function setArgs(array $args, $machineName)
	{
		self::$args[$machineName] = array_merge(self::$args[$machineName], $args);
	}

	/**
	 * Changes labels for Taxonomy
	 *
	 * @param array  $labels      - new labels
	 * @param string $machineName - name of Taxonomy
	 */
	public static function setLabels($labels, $machineName)
	{
		self::$labels[$machineName] = array_merge(self::$labels[$machineName], $labels);
		$args = [
			'labels' => self::$labels[$machineName],
		];
		self::setArgs($args, $machineName);
	}

	/**
	 * Changed PostTypes for Taxonomy
	 *
	 * @param array  $postTypes   - array of new PostTypes
	 * @param string $machineName - name of Taxonomy
	 */
	public static function setPostTypes(array $postTypes, $machineName)
	{
		self::$postTypes[$machineName] = $postTypes;
	}

	/**
	 * Added new PostType where should be Taxonomy
	 *
	 * @param string $postTypeMachineName - name of PostType
	 * @param string $machineName         - name of Taxonomy
	 */
	public static function addPostType($postTypeMachineName, $machineName)
	{
		$curPostTypes = self::$postTypes[$machineName];

		$postTypes = array();
		if (!empty($curPostTypes)) {
			if (is_array($curPostTypes)) {
				$postTypes = $curPostTypes;
			} else {
				$postTypes = [$curPostTypes];
			}
		}

		$postTypes[] = $postTypeMachineName;

		self::$postTypes[ $machineName ] =  array_unique($postTypes);
	}

	/**
	 * Registered all taxonomies
	 */
	public static function registerTaxonomy()
	{
		if (!empty(self::$machineNames)) {
			foreach (self::$machineNames as $machineName) {
				register_taxonomy($machineName, self::$postTypes[$machineName], self::$args[$machineName]);
			}
		}

	}

}