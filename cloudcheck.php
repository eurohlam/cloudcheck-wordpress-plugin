<?php
/**
 * Plugin Name: Cloudcheck Integration
 * Plugin URI: https://wordpress.org/plugins/cloudcheck_integration/
 * Description: Integration with cloudcheck service for electronic identification verification. Only for New Zealand and Australia
 * Version: 1.0.0
 * Author: Roundkick.Studio, eurohlam
 * Author URI: https://roundkick.studio
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * License: GPLv2 or later
 * Copyright (C) 2012-2017 by Teplitsa of Social Technologies (http://te-st.ru).
 *
 * GNU General Public License, Free Software Foundation <http://www.gnu.org/licenses/gpl-2.0.html>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 */

if (!defined('ABSPATH')) exit;
include_once 'class-cloudcheck-integration.php';

define('CLOUDCHECK_INT_VERSION', '1.0.0');

if (!class_exists('WP_Cloudcheck_Int')) {
	class WP_Cloudcheck_Int {
		/**
		* Plugin's options
		*/
	 	private $options_group = 'cloudcheck_int';
	 	private $url_option = 'cloudcheck_url';
		private $accessKey_option = 'cloudcheck_access_key';
		private $secret_option = 'cloudcheck_secret';


		function __construct() {
			add_action('admin_menu', array( $this, 'cloudcheck_menu'));
			add_action('wp_ajax_cloudcheck_send_request', array( $this,'cloudcheck_send_request'));
		}

		/**
		* Send request to cloudcheck
		*/
		function cloudcheck_send_request() {

			$accessKey = get_option($this->accessKey_option);
			$secret = get_option($this->secret_option);
			$url = get_option($this->url_option);
			$request = stripcslashes($_POST['request']);
			$path = $_POST['path'];
			error_log('Cloudcheck got path from AJAX:' . $path);
			error_log('Cloudcheck got request from AJAX: ' . $request);

			if (!empty($accessKey) && !empty($secret) && !empty($url) && !empty($path)) {
				$cloudcheckInt = new Cloudcheck_Integration();
				$cloudcheckRequest = $cloudcheckInt->prepare_cloudcheck_parameters($accessKey, $secret, $path, $request);
				error_log('Cloudcheck request: ' . json_encode($cloudcheckRequest));
				$result = $cloudcheckInt->send_request($url . $path, $cloudcheckRequest);
				error_log('Cloudcheck response: ' . $result);
				echo $result;
			} else {
				error_log('Cloudchek error: empty one or several required parameters - accessKey, secret, url or path. Please check settings of Cloudcheck Integration plugin');
				echo '{"Cloudchek error": "empty one or several required parameters - accessKey, secret, url or path"}';
			}
			wp_die();
		}



		function cloudcheck_settings() {
			register_setting( $this->options_group, $this->url_option );
			register_setting( $this->options_group, $this->accessKey_option );
			register_setting( $this->options_group, $this->secret_option );
		}

		function cloudcheck_menu() {
		  	add_action('admin_init', array( $this,'cloudcheck_settings'));
			add_options_page('Cloudcheck Integration', 'Cloudcheck Integration', 'manage_options', 'cloudcheck-int', array( $this,'cloudcheck_options_page'));
		}


		/**
		* Admin options page
		*/
		function cloudcheck_options_page() {
			?>
		    <div class="wrap">
		        <h2>Cloudcheck Integration</h2>
		        <p>Cloudcheck is an electronic identification verification (EV) tool that allows you to verify the identity of your customer using biometric checks, Australian and New Zealand data sources and global watchlists in one easy step. More details about
		            <a href="https://www.verifidentity.com/cloudcheck/">Cloudcheck</a></p>
		        <p>Version: <?php echo CLOUDCHECK_INT_VERSION ?></p>
		        <div>
		            <form method="post" action="options.php">
		            <?php
						settings_fields($this->options_group);
						do_settings_sections($this->options_group);
					?>
						<table class="form-table">
			            	<tr valign="top">
								<th scope="row">Cloudcheck URL</th>
								<td>
									<input type="url" class="regular-text" name="cloudcheck_url" value="<?php echo get_option($this->url_option) ?>" />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">Cloudcheck Access Key</th>
								<td>
									<input type="text" class="regular-text" name="cloudcheck_access_key" value="<?php echo get_option($this->accessKey_option) ?>" />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">Cloudcheck Secret Key</th>
								<td>
									<input type="text" class="regular-text" name="cloudcheck_secret" value="<?php echo get_option($this->secret_option) ?>" />
								</td>
							</tr>
						</table>
						<input type="hidden" name="page_options" value="cloudcheck_url,cloudcheck_access_key,cloudcheck_secret" />
						<p class="submit">
							<input class="button-primary" type="submit" value="Save Changes" />
						</p>
					</form>
				</div>
			</div>
			<?php
		}

	} //end class WP_Cloudcheck_Int
}


if (class_exists('WP_Cloudcheck_Int')) {
	// Installation and uninstallation hooks
	//register_activation_hook(__FILE__, array('WP_Cloudcheck_Int', 'activate'));
	//register_deactivation_hook(__FILE__, array('WP_Cloudcheck_Int', 'deactivate'));
	// instantiate the plugin class
	$wp_plugin = new WP_Cloudcheck_Int();
}
?>
