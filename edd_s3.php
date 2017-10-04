<?php
/**
 * Plugin Name: Easy Digital Downloads - Amazon S3
 * Plugin URI: http://easydigitaldownloads.com/downloads/amazon-s3/
 * Description: Amazon S3 integration with EDD.  Allows you to upload or download directly from your S3 bucket. Configure on Settings > Extensions tab.
 * Version: 2.3.6
 * Author: Easy Digital Downloads
 * Author URI: https://easydigitaldownloads.com
 * Text Domain: edd_s3
 * Domain Path: languages
 *
 * @package  EDD_Amazon_S3
 * @category Core
 * @author   Easy Digital Downloads
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EDD_AS3_VERSION', '2.3.6' );
define( 'EDD_AS3_FILE_PATH', dirname( __FILE__ ) );
define( 'EDD_AS3_DIR_NAME', basename( EDD_AS3_FILE_PATH ) );
define( 'EDD_AS3_FOLDER', dirname( plugin_basename( __FILE__ ) ) );
define( 'EDD_AS3_URL', plugins_url( '', __FILE__ ) );
define( 'EDD_AS3_DIR', plugin_dir_path( __FILE__ ) );
define( 'EDD_AS3_SL_PRODUCT_NAME', 'Amazon S3' );

/**
 * The main function responsible for returning the one true EDD_Amazon_s3
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $edd_amazon_s3 = edd_amazon_s3(); ?>
 *
 * @since  2.3
 * @return object|null The one true EDD_Amazon_S3 Instance.
 */
function edd_amazon_s3() {
	if ( ! class_exists( 'Easy_Digital_Downloads' ) ) {
		return null;
	}

	if ( version_compare( '5.5.0', PHP_VERSION, '>' ) && current_user_can( 'activate_plugins' ) ) {
		add_action( 'admin_notices', 'edd_amazon_s3_php_version_notice' );
		return;
	}

	include( EDD_AS3_DIR . 'class-edd-amazon-s3.php' );

	// FES Integration
	if ( class_exists( 'EDD_Front_End_Submissions' ) && version_compare( fes_plugin_version, '2.3', '>=' ) ) {
		add_action( 'fes_load_fields_require', 'EDD_Amazon_S3::add_fes_functionality' );
	}

	return EDD_Amazon_S3::get_instance();
}
add_action( 'plugins_loaded', 'edd_amazon_s3', 10 );

/**
 * Display an error notice if the PHP version is lower than 5.3.
 *
 * @return void
 */
function edd_amazon_s3_php_version_notice() {
	printf( '<div class="error"><p>' . __( 'Easy Digital Downloads - Amazon S3 requires PHP version 5.5.0 or higher. Your server is running PHP version %s. Please contact your hosting company to upgrade your site to 5.5.0 or later.', 'edd_s3' ) . '</p></div>', PHP_VERSION );
}