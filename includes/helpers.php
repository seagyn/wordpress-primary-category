<?php
/**
 * A couple of helper functions for us to use.
 *
 * @package WPC
 */

namespace SeagynDavis\WordPressPrimaryCategory\Helpers;

/**
 * Get posts which use the built-in categories taxonomy for a certain primary category.
 *
 * @param int   $category_id The category ID you are wanting to get posts for.
 * @param int   $posts_per_page The number of posts that you would like displayed. Defaults to the main posts per page setting.
 * @param mixed $post_type The post type or types you want to search for. Use an array for multiple post types.
 *
 * @return \WP_Query
 */
function get_posts_from_primary_category( $category_id, $posts_per_page = null, $post_type = 'post' ) {
	$args = [
		'post_type'      => $post_type,
		'meta_key'       => '_primary_category_id', // WPCS: @codingStandardsIgnoreLine - no way around using a meta "query" when querying post meta.
		'meta_value_num' => $category_id,
	];
	if ( $posts_per_page ) {
		$args['posts_per_page'] = intval( $posts_per_page );
	}

	return new \WP_Query( $args );
}

/**
 * Gets the primary category for a post. Must be used within the loop.
 *
 * @return array|object|\WP_Error|null
 */
function get_primary_category() {
	$post        = get_post();
	$category_id = get_post_meta( $post->ID, '_primary_category_id', true );
	if ( ! $category_id ) {
		$categories = get_categories();

		return $categories[0];
	}

	return get_category( $category_id );
}
