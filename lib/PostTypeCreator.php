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


class PostTypeCreator {
	private static $labels;
	private static $args;
	private static $machineNames;

	/**
	 * Added post type args to array for registering
	 *
	 * @param string $machineName - name of PostType
	 * @param string $text_domain - plugin textdomain
	 * @param string $singularName - singular name for labels
	 * @param string $pluralName - plural name for labels
	 * @param string $custom_base_link - custom slug for rewrite
	 */
	public static function addPostType($machineName, $text_domain, $singularName, $pluralName, $custom_base_link = '')
	{
		self::$labels[ $machineName ] = array(
			'name'                  => _x( $pluralName, 'Post Type General Name', $text_domain ),
			'singular_name'         => _x( $singularName, 'Post Type Singular Name', $text_domain ),
			'menu_name'             => __( $singularName, $text_domain),
			'name_admin_bar'        => __( $singularName, $text_domain ),
			'archives'              => __( $singularName . ' Archives', $text_domain),
			'parent_item_colon'     => __( 'Parent ' . $singularName . ':', $text_domain ),
			'all_items'             => __( 'All ' . $pluralName, $text_domain ),
			'add_new_item'          => __( 'Add New ' . $singularName . '', $text_domain ),
			'add_new'               => __( 'Add New', $text_domain ),
			'new_item'              => __( 'New ' . $singularName, $text_domain ),
			'edit_item'             => __( 'Edit ' . $singularName, $text_domain ),
			'update_item'           => __( 'Update ' . $singularName, $text_domain ),
			'view_item'             => __( 'View ' . $singularName, $text_domain ),
			'search_items'          => __( 'Search ' . $singularName, $text_domain ),
			'not_found'             => __( 'Not found', $text_domain ),
			'not_found_in_trash'    => __( 'Not found in Trash', $text_domain ),
			'featured_image'        => __( 'Featured Image', $text_domain ),
			'set_featured_image'    => __( 'Set featured image', $text_domain ),
			'remove_featured_image' => __( 'Remove featured image', $text_domain ),
			'use_featured_image'    => __( 'Use as featured image', $text_domain ),
			'insert_into_item'      => __( 'Insert into item', $text_domain ),
			'uploaded_to_this_item' => __( 'Uploaded to this ' . $singularName, $text_domain ),
			'items_list'            => __( $singularName . ' list', $text_domain ),
			'items_list_navigation' => __( $singularName . ' list navigation', $text_domain ),
			'filter_items_list'     => __( 'Filter ' . $singularName . ' list', $text_domain ),
		);

		$args = array(
			'label'               => __( $singularName, $text_domain ),
			'description'         => __( $singularName, $text_domain ),
			'labels'              => self::$labels[ $machineName ],
			'supports'            => array('title'),
			'taxonomies'          => array(),
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
		);

		if ($custom_base_link !== '') {
			$args['rewrite'] = [
				'slug' => $custom_base_link
			];
		}

		self::$args[ $machineName ] = $args;

		self::$machineNames[] = $machineName;
	}

	/**
	 * Change args for some PostType
	 *
	 * @param array  $args - args that should be changed
	 * @param string $machineName - name of PostType
	 */
	public static function setArgs(array $args, $machineName )
	{
		self::$args[ $machineName ] = array_merge( self::$args[ $machineName ], $args );
	}

	/**
	 * Change capability_type of some PostType
	 *
	 * @param string $type - new capability_type
	 * @param string $machineName - name of PostType
	 */
	public static function setType( $type, $machineName )
	{
		$args = [
			'capability_type' => $type
		];
		self::$args[ $machineName ] =  array_merge( self::$args[ $machineName ], $args );
	}

	/**
	 * Added Taxonomy for PostType
	 *
	 * @param string $taxonomy - name of Taxonomy
	 * @param string $machineName - name of PostType
	 */
	public static function addTaxonomy( $taxonomy, $machineName )
	{
		$curtax = self::$args[ $machineName ]['taxonomies'];
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
		self::$args[ $machineName ] =  array_merge( self::$args[ $machineName ], $args );
	}

	/**
	 * Changes labels for PostType
	 *
	 * @param array $labels - new labels
	 * @param string $machineName - name of PostType
	 */
	public static function setLabels(array $labels, $machineName )
	{
		self::$labels[ $machineName ] = array_merge( self::$labels[ $machineName ], $labels );
	}

	/**
	 * Registered all PostTypes
	 */
	public static function registerPostTypes()
	{
		if (!empty( self::$machineNames)) {
			foreach ( self::$machineNames as $machineName ) {
				register_post_type( $machineName, self::$args[ $machineName ] );
			}
		}
	}
}