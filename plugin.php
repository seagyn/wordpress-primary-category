<?php
/**
 * Plugin Name: WordPress Primary Category
 * Plugin URI:
 * Description:
 * Version:     0.1.0
 * Author:      Seagyn Davis
 * Author URI:  https://www.seagyndavis.com
 * Text Domain: wordpress-primary-category
 * Domain Path: /languages
 *
 * @package WordPressPrimaryCategory
 */

// Useful global constants.
define( 'WORDPRESS_PRIMARY_CATEGORY_VERSION', '0.1.0' );
define( 'WORDPRESS_PRIMARY_CATEGORY_URL', plugin_dir_url( __FILE__ ) );
define( 'WORDPRESS_PRIMARY_CATEGORY_PATH', plugin_dir_path( __FILE__ ) );
define( 'WORDPRESS_PRIMARY_CATEGORY_INC', WORDPRESS_PRIMARY_CATEGORY_PATH . 'includes/' );

// Include files.
require_once WORDPRESS_PRIMARY_CATEGORY_INC . 'core.php';
require_once WORDPRESS_PRIMARY_CATEGORY_INC . 'rest-api.php';
require_once WORDPRESS_PRIMARY_CATEGORY_INC . 'helpers.php';

// Activation/Deactivation.
register_activation_hook( __FILE__, '\SeagynDavis\WordPressPrimaryCategory\Core\activate' );
register_deactivation_hook( __FILE__, '\SeagynDavis\WordPressPrimaryCategory\Core\deactivate' );

// Bootstrap.
\SeagynDavis\WordPressPrimaryCategory\Core\setup();

// Require Composer autoloader if it exists.
if ( file_exists( WORDPRESS_PRIMARY_CATEGORY_PATH . 'vendor/autoload.php' ) ) {
	require_once WORDPRESS_PRIMARY_CATEGORY_PATH . 'vendor/autoload.php';
}
