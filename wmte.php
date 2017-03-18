<?php
/*
Plugin Name: wooMoodleTokenEnrolment
Plugin URI: https://github.com/frumbert/wooMoodleTokenEnrolment
Description: A plugin designed to allow a wooCommerce digital download to generate enrolment tokens using the Moodle Token Enrolment plugins.
Requires: Moodle 2.7+ site with the https://github.com/frumbert/moodle-token-enrolment plugins
Version: 0.1
Author: Tim St.Clair
Author URI: http://frumbert.org
License: MIT
*/
?><?php

// some definition we will use
define( 'WMTE_PUGIN_NAME', 'wooMoodleTokenEnrolment');
define( 'WMTE_PLUGIN_DIRECTORY', 'wooMoodleTokenEnrolment');
define( 'WMTE_CURRENT_VERSION', '0.1' );
define( 'WMTE_CURRENT_BUILD', '1' );
define( 'EMU2_I18N_DOMAIN', 'wmte' );

function wmte_set_lang_file() {
	$currentLocale = get_locale();
	if(!empty($currentLocale)) {
		$moFile = dirname(__FILE__) . "/lang/" . $currentLocale . ".mo";
		if (@file_exists($moFile) && is_readable($moFile)) {
			load_textdomain(EMU2_I18N_DOMAIN, $moFile);
		}

	}
}
wmte_set_lang_file();

// actions - register the plugin itself, it's settings pages and its wordpress hooks
add_action( 'admin_menu', 'wmte_create_menu' );
add_action( 'admin_init', 'wmte_register_settings' );
register_activation_hook(__FILE__, 'wmte_activate');
register_deactivation_hook(__FILE__, 'wmte_deactivate');
register_uninstall_hook(__FILE__, 'wmte_uninstall');

/**
 * activating the default values
*/
function wmte_activate() {
	add_option('wmte_moodle_url', 'http://your-moodle-sitemoodle');
	add_option('wmte_webservice_token', 'enter your webservice token here');
}

/**
 * deactivating requires deleting any options set
 */
function wmte_deactivate() {
	delete_option('wmte_moodle_url');
	delete_option('wmte_webservice_token');
}

/**
 * uninstall routine
 */
function wmte_uninstall() {
	delete_option( 'wmte_moodle_url' );
	delete_option( 'wmte_webservice_token' );
}

/**
 * Creates a sub menu in the settings menu for the Link2Moodle settings
 */
function wmte_create_menu() {
	add_menu_page(
		__('woo Moodle Token Enrolment', EMU2_I18N_DOMAIN),
		__('woo Moodle Token Enrolment', EMU2_I18N_DOMAIN),
		'administrator',
		WMTE_PLUGIN_DIRECTORY.'/wmte_settings_page.php',
		'',
		plugins_url('wooMoodleTokenEnrolment/icon.png', wmte_PLUGIN_DIRECTORY) //__FILE__));
	);
}

/**
 * Registers the settings that this plugin will read and write
 */
function wmte_register_settings() {
	//register settings against a grouping (how wp-admin/options.php works)
	register_setting( 'wmte-settings-group', 'wmte_moodle_url' );
	register_setting( 'wmte-settings-group', 'wmte_webservice_token' );
}

// over-ride the url for Marketpress *if* the download is a file named something-wmte.txt
add_filter('mp_download_url', 'wmte_download_url', 10, 3);

// over-ride the url for WooCommerce *if* the download is a file named something-wmte.txt
add_filter('woocommerce_download_file_redirect','woo_wmte_download_url', 5, 2);
add_filter('woocommerce_download_file_force','woo_wmte_download_url', 5, 2);

// woo shim to handle different arguments
function woo_wmte_download_url($filepath, $filename) {
	wmte_download_url($filepath, "", "");
}

// the download file is actually a text file containing the shortcode values
function wmte_download_url($url, $order, $download) {

	if (strpos($url, '-wmte.txt') !== false) {
		// mp url is full url = including http:// and so on... we want the file url
		$path = $_SERVER["DOCUMENT_ROOT"] . explode($_SERVER["SERVER_NAME"], $url)[1];
		$cohort = "";
		$course = "";
		$seats = 1;
		$places = 1;
		$expiry = 0;
		$prefix = "";

		$data = file($path); // now it's an array!
		foreach ($data as $row) {
			$pair = explode("=",$row);
			switch (strtolower(trim($pair[0]))) {
				case "course":
					$course = trim(str_replace(array('\'','"'), '', $pair[1]));
					break;
				case "cohort":
					$cohort = trim(str_replace(array('\'','"'), '', $pair[1]));
					break;
				case "seats":
					$seats = trim(str_replace(array('\'','"'), '', $pair[1]));
					break;
				case "places":
					$places = trim(str_replace(array('\'','"'), '', $pair[1]));
					break;
				case "expiry":
					$expiry = trim(str_replace(array('\'','"'), '', $pair[1]));
					break;
				case "prefix":
					$prefix = trim(str_replace(array('\'','"'), '', $pair[1]));
					break;
			}
		}

		$args = array(
		              "course" => $course,
		              "seats" => $seats,
		              "places" => $places,
		              "expiry" => $expiry,
		              "cohort" => $cohort,
		              "prefix" => $prefix,
		);

		$moodle_webservice_token = get_option('wmte_webservice_token');
		$moodle_url = get_option('wmte_moodle_url');
		$moodle_webservice_url = "$moodle_url/webservice/rest/server.php?wstoken=$moodle_webservice_token&wsfunction=local_token_generatetokens&moodlewsrestformat=json";

		$response = wp_remote_post( $moodle_webservice_url, array(
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(),
			'body' => $args,
			'cookies' => array()
		));

		if (ob_get_contents()) { ob_clean(); }

		// we're rendering a page in the middle of a process that is trying to download
		// so it's going to be a bit of hack. the page might look awful. sorry.
		get_header();
		_e('<div id="primary" class="content-area">');
		_e('<main id="main" class="site-main" role="main">');
		_e('<article ' ); post_class(); _e('>');
		_e('<header class="entry-header">');
		_e('<h1 class="entry-title">Here are your tokens</h1>');
		_e('</header><div class="entry-content">');
		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			_e("Something went wrong: $error_message");
		} else {
			$tokens = json_decode($response['body'])->token;
			_e("<p>Use them on your moodle site <a href='$moodle_url/auth/token/login.php' target='_blank'>$moodle_url/auth/token/login.php</a>.</p>");
			_e("<pre>");
			foreach ($tokens as $token) {
				_e($token . "\n");
			}
			_e("</pre>");
		}
		_e('</div></article>');
		_e('</main></div>');
		get_sidebar();
		get_footer();
		exit(); // don't go any further
	}
	return $url;
}

/*
MarketPress extras
If you are only using wmte downloads and not offering other marketpress products, you can hide the shipping info like this:
add_action('add_meta_boxes_product','remove_unwanted_mp_meta_boxes',999);
function remove_unwanted_mp_meta_boxes() {
	// shipping meta box
	remove_meta_box('mp-meta-shipping','product','normal');
	// download file box
    	// remove_meta_box('mp-meta-download','product','normal');
}
*/
?>
