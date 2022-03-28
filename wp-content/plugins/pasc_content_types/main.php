<?php
/*
Plugin Name: ECA PASC Team Example Content Types
Plugin URI: https://18f.gsa.gov
Description: Plugin to register types needed for ECA's websites
Version: 1.0
Author: Sarah Withee/18F
Author URI: https://github.com/geekygirlsarah
Textdomain: pasc_content_types
License: Public domain
*/


/**
 * This will register the custom "Program" type within WP
 * It will also create the "Programs" menu item in the 
 * admin panel.
 */
function register_custom_program_types() {

	/**
	 * Post Type: Programs.
	 */

	$labels = [
		"name" => __( "Programs" ),
		"singular_name" => __( "Program" ),
	];

	$args = [
		"label" => __( "Programs" ),
		"labels" => $labels,
		"description" => "For storing program-specific information on a page",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "programs",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"has_archive" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"can_export" => false,
		"rewrite" => [ "slug" => "programs", "with_front" => true ],
		"query_var" => true,
		"supports" => [ "title", "editor", "thumbnail", "revisions", "custom-fields" ],
		"show_in_graphql" => false,
	];

	register_post_type( "program", $args );
}
add_action( 'init', 'register_custom_program_types' );

/**
 * This will register the custom taxonomy types. Those 
 * are Countries and Program Types, both which can be 
 * added onto programs. (With a small tweak they can 
 * be also added to blog posts and pages too.)
 */
function register_custom_taxonomies() {

	/**
	 * Taxonomy: Countries.
	 */

	$labels = [
		"name" => __( "Countries" ),
		"singular_name" => __( "Country" ),
	];

	
	$args = [
		"label" => __( "Countries" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => false,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'country', 'with_front' => true, ],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "country",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"show_in_quick_edit" => false,
		"sort" => false,
		"show_in_graphql" => false,
	];
	register_taxonomy( "country", [ "program" ], $args );

	/**
	 * Taxonomy: Program Types.
	 */

	$labels = [
		"name" => __( "Program Types" ),
		"singular_name" => __( "Program Type" ),
	];

	
	$args = [
		"label" => __( "Program Types" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => false,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'program_type', 'with_front' => true, ],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "program_type",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"show_in_quick_edit" => false,
		"sort" => false,
		"show_in_graphql" => false,
	];
	register_taxonomy( "program_type", [ "program" ], $args );
}
add_action( 'init', 'register_custom_taxonomies' );

