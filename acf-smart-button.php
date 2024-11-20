<?php
/*
	Plugin Name: Advanced Custom Fields: Smart Button
	Plugin URI: https://github.com/gillesgoetsch/acf-smart-button
	Description: A button field that lets you choose between internal and external and gives you either a post_object or a url field
	Version: 1.0.5
	Author: Gilles Goetsch
	Author URI: https://gillesgoetsch.ch
	License: GPLv2 or later
	License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/


/**
 * Load plugin textdomain.
 *
 * @see https://codex.wordpress.org/Function_Reference/load_plugin_textdomain
 *
 *  @type	action
 *  @since	1.0.0
 */
function acf_smart_button_load_textdomain() {
	load_plugin_textdomain( 'acf-smart-button', false, dirname( plugin_basename(__FILE__) ) . '/lang/' );
}
add_action( 'init', 'acf_smart_button_load_textdomain' );


/**
 * Include field.
 *
 * @type	action
 * @since 1.0.0
 */
function include_field_types_smart_button( $version ) {
	include_once('acf-smart-button-v5.php');
}
add_action('acf/include_field_types', 'include_field_types_smart_button');


/*
* Register a new REST route for fetching posts of multiple post types in one go
*
* @type	action
*/
function register_multi_post_type_rest_route() {
	register_rest_route('acf-smart-button/v1', '/posts', array(
		'methods' => 'GET',
		'callback' => 'get_multi_post_type_posts',
    'permission_callback' => 'rest_route_permission_callback',
		'args' => array(
			'post_types' => array(
				'description' => 'Array of post types to query.',
				'type' => 'array',
				'items' => array(
					'type' => 'string',
				),
			),
      'post_status' => array(
        'description' => 'Array of post statuses to query.',
        'type' => 'array',
        'items' => array(
          'type' => 'string',
        ),
      ),
			'posts_per_page' => array(
				'description' => 'Number of posts to return per page.',
				'type' => 'integer',
				'default' => 10,
			),
			'orderby' => array(
				'description' => 'Field to order posts by.',
				'type' => 'string',
				'default' => 'date',
			),
			'order' => array(
				'description' => 'Order of results (ASC or DESC).',
				'type' => 'string',
				'default' => 'DESC',
			),
      'q' => array(
				'description' => 'Search Query',
				'type' => 'string',
				'default' => '',
			),
		),
	));
}

/*
* Permission callback for the REST route
* Only allow access to users who have permission to edit posts
*
* @type	filter
*/
function rest_route_permission_callback() {
  return current_user_can('edit_posts');
}

/*
* Callback function for the REST route
* Fetches posts of multiple post types using the get_posts function, and runs them through
* the ACF post_object field filters to ensure compatibility with ACF fields that use post_object
*
* @type	function
*/
function get_multi_post_type_posts($request) {

	// Set default or requested post types
	$post_types = $request->get_param('post_types') ?: array('post', 'page');

	// Build arguments array for get_posts
	$args = array(
		'post_type'      => $post_types,
    'post_status'    => $request->get_param('post_status') ?: array('publish'),
		'posts_per_page' => $request->get_param('posts_per_page') ?: 10,
		'orderby'        => $request->get_param('orderby') ?: 'date',
		'order'          => $request->get_param('order') ?: 'DESC',
    's'              => $request->get_param('q') ?: '',
		'suppress_filters' => false, // Allow filters to run
	);

  $args = apply_filters('acf/fields/post_object/query', $args, [], "");

  $posts = get_posts($args);

  $posts_filtered = array_map(function($post) {
    return array(
      'id' => $post->ID,
      'title' => apply_filters('acf/fields/post_object/result', $post->post_title, $post, [], ""),
    );
  }, $posts);

	return rest_ensure_response($posts_filtered);
}
add_action('rest_api_init', 'register_multi_post_type_rest_route');


?>
