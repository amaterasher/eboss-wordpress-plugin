<?php
/**
 *
 * Plugin Name: eBoss API v3 Client
 * Plugin URI: http://ebossrecruitment.com
 * Description: eBoss API v3 integration
 * Version: 0.1.1 alpha
 * Author: ebossrecruitment
 * Author URI: http://ebossrecruitment.com
 * License: eBoss License
 *
 */

global $wpdb, $wp_version;

define("EBOSS_API_V3_TABLE", $wpdb->prefix . "eboss_api");
define('EBOSS_API_V3_FAV', get_option('siteurl') . 'favicon.ico');

if ( ! defined( 'EBOSS_API_V3_BASENAME' ) )
		define( 'EBOSS_API_V3_BASENAME', plugin_basename( __FILE__ ) );

if ( ! defined( 'EBOSS_API_V3_PLUGIN_NAME' ) )
		define( 'EBOSS_API_V3_PLUGIN_NAME', trim( dirname( EBOSS_API_V3_BASENAME ), '/' ) );

if ( ! defined( 'EBOSS_API_V3_PLUGIN_URL' ) )
		define( 'EBOSS_API_V3_PLUGIN_URL', WP_PLUGIN_URL . '/' . EBOSS_API_V3_PLUGIN_NAME );

if ( ! defined( 'EBOSS_API_V3_ADMIN_URL' ) )
		define( 'EBOSS_API_V3_ADMIN_URL', get_option('siteurl') . '/wp-admin/options-general.php?page=eboss-api-v3' );

if (!session_id())
{
		session_start();
}

require 'shortcodes.php';


add_action('wp_print_scripts', 'eboss_v3_script_enqueuer');

function eboss_v3_script_enqueuer()
{
		wp_register_script("jquery_validate", plugins_url(null, __FILE__ ) . '/inc/jquery.validate.min.js', array('jquery'));

		wp_register_script("eboss_v3_custom", plugins_url(null, __FILE__ )  . '/inc/main.js', array('jquery'));
		wp_localize_script('eboss_v3_custom', 'myAjax', array('ajaxurl' => admin_url('admin-ajax.php')));


		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery_validate');
		wp_enqueue_script('eboss_v3_custom');

}


function eboss_api_v3_install()
{
		global $wpdb, $wp_version;

		if ($wpdb->get_var("SHOW tables LIKE '" . EBOSS_API_V3_TABLE . "'") != EBOSS_API_V3_TABLE) {
				$createTableQuery = "CREATE TABLE IF NOT EXISTS " . EBOSS_API_V3_TABLE . " (";
				$createTableQuery .= "id INT NOT NULL AUTO_INCREMENT ,";
				$createTableQuery .= "consumer_key VARCHAR( 1024 ) NOT NULL default '' ,";
				$createTableQuery .= "consumer_secret VARCHAR( 1024 ) NOT NULL default '' ,";
				$createTableQuery .= "myoffice_username VARCHAR( 1024 ) NOT NULL default '' ,";
				$createTableQuery .= "myoffice_password VARCHAR( 1024 ) NOT NULL default '' ,";
				$createTableQuery .= "myoffice_url VARCHAR( 1024 ) NOT NULL default '' ,";
				$createTableQuery .= "PRIMARY KEY ( `id` )";
				$createTableQuery .= ") ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
				$wpdb->query($createTableQuery);

		}

		add_option('eboss_api_v3_option', '');
		add_option('eboss_api_v3_version', '0.1.1');
}

function efancybox_deactivation()
{
		delete_option( 'eboss_api_v3_option' );
		delete_option( 'eboss_api_v3_version' );
}

function eboss_api_v3_add_to_menu() {
		add_options_page('eboss_api_v3', 'eBoss API V3 Integration', 'manage_options', 'eboss-api-v3', 'eboss_api_v3_admin' );

}

function eboss_api_v3_admin() {
		global $wpdb;
		include('admin/content-management-show.php');
}


/**
 * ========================
 * Start configuration
 */
register_activation_hook(__FILE__, 'eboss_api_v3_install');
register_deactivation_hook(__FILE__, 'eboss_api_v3_deactivation');


if (is_admin()) {
		add_action('admin_menu', 'eboss_api_v3_add_to_menu');
}



function eboss_v3_callback($buffer)
{
		return $buffer;
}

function add_eboss_v3_ob_start()
{
		ob_start("eboss_v3_callback");
}

function flush_eboss_v3_end()
{
		ob_end_flush();
}

add_action('init', 'add_eboss_v3_ob_start');
add_action('wp_footer', 'flush_eboss_v3_end');


/**
 *
 * WP Actions Hook
 *
 */

add_action("wp_ajax_nopriv_v3_regions", "wp_v3_regions");
add_action("wp_ajax_v3_regions", "wp_v3_regions");

function wp_v3_regions()
{
		$apiClient = new eBossApiClient();
		$regions = $apiClient->getRegions([
			"country_id" => $_GET['country_id'],
			"page_size" => 100
		], true);

		echo json_encode($regions);
		exit(0);
}
