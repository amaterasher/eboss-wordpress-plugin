<?php

/**
 *  Get Configuration from database
 */
global $wpdb, $wp_version;

$sqlQuery = $wpdb->prepare("SELECT *  FROM `" . EBOSS_API_V3_TABLE . "` WHERE `id` = %d", array(1));
$dataConfig = array();
$dataConfig = $wpdb->get_row($sqlQuery, ARRAY_A);

if ($dataConfig) {
		if (isset($dataConfig['consumer_key'])) {
				define("API_KEY",$dataConfig['consumer_key']);
		}

		if (isset($dataConfig['consumer_secret'])) {
				define("API_SECRET",$dataConfig['consumer_secret']);
		}

		if (isset($dataConfig['myoffice_username'])) {
				define("E_BOSS_USERNAME",$dataConfig['myoffice_username']);
		}

		if (isset($dataConfig['myoffice_password'])) {
				define("E_BOSS_PASSWORD",$dataConfig['myoffice_password']);
		}

		if (isset($dataConfig['myoffice_url'])) {

				//fix API URL
				if (substr($dataConfig['myoffice_url'], -1) != '/') {
						$dataConfig['myoffice_url'] = $dataConfig['myoffice_url'] . '/';
				}

				if (strpos($dataConfig['myoffice_url'] , 'v3') === false) {
						$dataConfig['myoffice_url'] = $dataConfig['myoffice_url'] . 'v3/';
				}

				define("E_BOSS_API_URL",$dataConfig['myoffice_url']);
		}
}
