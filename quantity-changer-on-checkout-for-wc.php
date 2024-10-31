<?php
/*
Plugin Name: Quantity Changer On CheckOut For WC 
Plugin URI: https://wordpress.org/
Description: This plugin adds functionality to change product quantity and also allows your users to delete product from their cart directly from  checkout page.
Version: 1.0
Author: Kshirod Patel
Author URI: https://profiles.wordpress.org/kshirod-patel/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: quantity_changer_on_checkout_for_wc
*/

// No direct file access
! defined( 'ABSPATH' ) AND exit;

define('QCFWC_FILE', __FILE__);
define('QCFWC_PATH', plugin_dir_path(__FILE__));
define('QCFWC_BASE', plugin_basename(__FILE__));
define('QCFWC_PLUGIN_NAME', 'Quantity Changer On CheckOut For WC');


add_action('plugins_loaded', 'qcfwc_load_textdomain');

function qcfwc_load_textdomain() {
  load_plugin_textdomain( 'quantity_changer_on_checkout_for_wc', false, dirname( plugin_basename( __FILE__ ) ). '/languages/' );
}

require_once dirname( __FILE__ ) . '/inc/quantity_changer_for_wc.php';
new Quantity_Changer_On_Checkout_For_WC();