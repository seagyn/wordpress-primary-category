<?php
/**
 * Core plugin functionality.
 *
 * @package WordpressPrimaryCategory
 */

namespace WordpressPrimaryCategory\Core;

use \WP_Error as WP_Error;

/**
 * Default setup routine
 *
 * @return void
 */
function setup() {
	$n = function ( $function ) {
		return __NAMESPACE__ . "\\$function";
	};

	add_action( 'init', $n( 'i18n' ) );
	add_action( 'init', $n( 'init' ) );
	add_action( 'admin_enqueue_scripts', $n( 'admin_scripts' ) );
	add_action( 'admin_enqueue_scripts', $n( 'admin_styles' ) );

	// Hook to remove a primary category if it is not a selected category anymore.
	add_action( 'save_post', $n( 'check_primary_category' ), 10, 3 );

	// Hook to allow async or defer on asset loading.
	add_filter( 'script_loader_tag', $n( 'script_loader_tag' ), 10, 2 );

	\WordpressPrimaryCategory\RestAPI\setup();

	do_action( 'wordpress_primary_category_loaded' );
}

/**
 * Registers the default textdomain.
 *
 * @return void
 */
function i18n() {
	$locale = apply_filters( 'plugin_locale', get_locale(), 'wordpress-primary-category' );
	load_textdomain( 'wordpress-primary-category', WP_LANG_DIR . '/wordpress-primary-category/wordpress-primary-category-' . $locale . '.mo' );
	load_plugin_textdomain( 'wordpress-primary-category', false, plugin_basename( WORDPRESS_PRIMARY_CATEGORY_PATH ) . '/languages/' );
}

/**
 * Initializes the plugin and fires an action other plugins can hook into.
 *
 * @return void
 */
function init() {
	do_action( 'wordpress_primary_category_init' );
}

/**
 * Activate the plugin
 *
 * @return void
 */
function activate() {
	// First load the init scripts in case any rewrite functionality is being loaded.
	init();
	flush_rewrite_rules();
}

/**
 * Deactivate the plugin
 *
 * Uninstall routines should be in uninstall.php
 *
 * @return void
 */
function deactivate() {
	// We could potentially delete the primary category references from post_meta but that could suck if plugin is deactivated by mistake.
}

/**
 * The list of knows contexts for enqueuing scripts/styles.
 *
 * @return array
 */
function get_enqueue_contexts() {
	return [ 'admin', 'frontend', 'shared' ];
}

/**
 * Generate an URL to a script, taking into account whether SCRIPT_DEBUG is enabled.
 *
 * @param string $script Script file name (no .js extension).
 * @param string $context Context for the script ('admin', 'frontend', or 'shared').
 *
 * @return string|WP_Error URL
 */
function script_url( $script, $context ) {

	if ( ! in_array( $context, get_enqueue_contexts(), true ) ) {
		return new WP_Error( 'invalid_enqueue_context', 'Invalid $context specified in WordpressPrimaryCategory script loader.' );
	}

	return ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ?
		WORDPRESS_PRIMARY_CATEGORY_URL . "assets/js/${context}/{$script}.js" :
		WORDPRESS_PRIMARY_CATEGORY_URL . "dist/js/${script}.min.js";

}

/**
 * Generate an URL to a stylesheet, taking into account whether SCRIPT_DEBUG is enabled.
 *
 * @param string $stylesheet Stylesheet file name (no .css extension).
 * @param string $context Context for the script ('admin', 'frontend', or 'shared').
 *
 * @return string URL
 */
function style_url( $stylesheet, $context ) {

	if ( ! in_array( $context, get_enqueue_contexts(), true ) ) {
		return new WP_Error( 'invalid_enqueue_context', 'Invalid $context specified in WordpressPrimaryCategory stylesheet loader.' );
	}

	return ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ?
		WORDPRESS_PRIMARY_CATEGORY_URL . "assets/css/${context}/{$stylesheet}.css" :
		WORDPRESS_PRIMARY_CATEGORY_URL . "dist/css/${stylesheet}.min.css";

}

/**
 * Enqueue scripts for admin.
 *
 * @param string $hook Which file is the enqueue do_action being called on.
 *
 * @return void
 */
function admin_scripts( $hook ) {
	if ( ! in_array( $hook, [ 'edit.php', 'post.php' ], true ) ) {
		return;
	}

	global $post;

	wp_enqueue_script(
		'wordpress_primary_category_shared',
		script_url( 'shared', 'shared' ),
		[],
		WORDPRESS_PRIMARY_CATEGORY_VERSION,
		true
	);

	wp_enqueue_script(
		'wordpress_primary_category_admin',
		script_url( 'admin', 'admin' ),
		[],
		WORDPRESS_PRIMARY_CATEGORY_VERSION,
		true
	);

	$primary_category_id = \get_post_meta( $post->ID, '_primary_category_id', true );

	$localized_data = [
		'label'               => __( 'Make Primary', 'wordpress-primary-categories' ),
		'link_title'          => __( 'Set as the primary category.', 'wordpress-primary-categories' ),
		'nonce'               => \wp_create_nonce( 'wpc-nonce' ),
		'primary_category_id' => $primary_category_id,
	];
	\wp_localize_script(
		'wordpress-primary-categories',
		'wpc_data',
		$localized_data
	);

}

/**
 * Enqueue styles for admin.
 *
 * @param string $hook Which file is the enqueue do_action being called on.
 *
 * @return void
 */
function admin_styles( $hook ) {
	if ( ! in_array( $hook, [ 'edit.php', 'post.php' ], true ) ) {
		return;
	}

	wp_enqueue_style(
		'wordpress_primary_category_shared',
		style_url( 'shared-style', 'shared' ),
		[],
		WORDPRESS_PRIMARY_CATEGORY_VERSION
	);

	wp_enqueue_style(
		'wordpress_primary_category_admin',
		style_url( 'admin-style', 'admin' ),
		[],
		WORDPRESS_PRIMARY_CATEGORY_VERSION
	);

}

/**
 * Add async/defer attributes to enqueued scripts that have the specified script_execution flag.
 *
 * @link https://core.trac.wordpress.org/ticket/12009
 *
 * @param string $tag The script tag.
 * @param string $handle The script handle.
 *
 * @return string
 */
function script_loader_tag( $tag, $handle ) {
	$script_execution = wp_scripts()->get_data( $handle, 'script_execution' );

	if ( ! $script_execution ) {
		return $tag;
	}

	if ( 'async' !== $script_execution && 'defer' !== $script_execution ) {
		return $tag;
	}

	// Abort adding async/defer for scripts that have this script as a dependency. _doing_it_wrong()?
	foreach ( wp_scripts()->registered as $script ) {
		if ( in_array( $handle, $script->deps, true ) ) {
			return $tag;
		}
	}

	// Add the attribute if it hasn't already been added.
	if ( ! preg_match( ":\s$script_execution(=|>|\s):", $tag ) ) {
		$tag = preg_replace( ':(?=></script>):', " $script_execution", $tag, 1 );
	}

	return $tag;
}

/**
 * Check to see if the current primary category is still selected.
 *
 * @param int      $post_ID ID of post being saved.
 * @param \WP_Post $post Post object of post being saved.
 * @param bool     $update If this was an update or create.
 *
 * @return void
 */
function check_primary_category( $post_ID, $post, $update ) {
	if ( $update && is_object_in_taxonomy( $post->post_type, 'category' ) ) {
		$primary_category_id = get_post_meta( $post_ID, '_primary_category_id', true );
		if ( $primary_category_id ) {
			$categories = wp_list_pluck( get_the_terms( $post, 'category' ), 'name', 'term_id' );

			if ( ! isset( $categories[ $primary_category_id ] ) ) {
				delete_post_meta( $post_ID, '_primary_category_id', $primary_category_id );
			}
		}
	}
}