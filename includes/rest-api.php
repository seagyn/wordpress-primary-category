<?php
/**
 * All admin related functions to handle primary categories.
 *
 * @package WPC
 */

namespace WordpressPrimaryCategory\RestAPI;

use \WP_REST_Request as WP_REST_Request;

/**
 * Hook into rest_api_init action
 *
 * @since 0.1.0
 *
 * @uses add_action()
 *
 * @return void
 */
function setup() {
	$n = function ( $function ) {
		return __NAMESPACE__ . "\\$function";
	};
	add_action( 'rest_api_init', $n( 'define_endpoint_for_setting_primary_category' ) );
}


/**
 * Define the endpoint to handle setting of primary category.
 *
 * @return void
 */
function define_endpoint_for_setting_primary_category() {
	register_rest_route(
		'wpc/v1',
		'set',
		array(
			'methods'             => \WP_REST_Server::CREATABLE,
			'callback'            => __NAMESPACE__ . '\handle_request',
			'permission_callback' => __NAMESPACE__ . '\can_user_request',
			'args'                => array(
				'category_id'     => array(
					'required'          => true,
					'sanitize_callback' => 'sanitize_text_field',
				),
				'post_id'         => array(
					'required'          => true,
					'sanitize_callback' => 'sanitize_text_field',
				),
				'old_category_id' => array(
					'required'          => true,
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		)
	);
}

/**
 * Checks to see if the user can edit the post.
 *
 * @return boolean
 */
function can_user_request() {
	if ( ! \current_user_can( 'edit_posts' ) ) {
		return false;
	}

	return true;
}

/**
 * Handles the admin ajax request.
 *
 * @param WP_REST_Request $request The request being sent via the api.
 *
 * @return \WP_REST_Response
 */
function handle_request( WP_REST_Request $request ) {
	$category_id     = $request->get_param( 'category' );
	$post_id         = $request->get_param( 'post_id' );
	$old_category_id = $request->get_param( 'old_category_id' );

	if (
		absint( $category_id ) === $category_id &&
		absint( $post_id ) === $post_id &&
		absint( $old_category_id ) === $old_category_id
	) {
		\update_post_meta( $post_id, '_primary_category_id', $category_id, $old_category_id );
		$response = [
			'success' => true,
		];
	} else {
		$response = [
			'success' => false,
			'message' => 'Invalid parameters passed.',
		];
	}

	return rest_ensure_response( $response );
}
