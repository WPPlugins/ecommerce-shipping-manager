<?php
/*Plugin Name: Shipping Manager for WooCommerce
 Plugin URI: https://paymentplugins.com
 Description: Manage the tracking numbers for your WooCommerce orders. Automatically update all orders with tracking. 
 Version: 1.0.0
 Author: Clayton Rogers, mr.clayton@paymentplugins.com
 Author URI:
 Tested up to: 4.3.1
 */

define('SHIPPING_MANAGER', plugin_dir_path(__FILE__));
define('SHIPPING_MANAGER_ASSETS', plugin_dir_url(__FILE__).'admin/assets/');
define('SHIPPINGMANAGER_LICENSE_ACTIVATION_URL', 'http://paymentplugins.com/');
define('SHIPPINGMANAGER_LICENSE_VERIFICATION_KEY', 'gTys$hsjeScg63dDs35JlWqbx7h');

include_once(SHIPPING_MANAGER.'admin/classes/class-html-helper.php');
include_once(SHIPPING_MANAGER.'admin/classes/class-shipping-manager.php');
include_once(SHIPPING_MANAGER.'admin/classes/class-admin-screen.php');
include_once(SHIPPING_MANAGER.'admin/classes/class-shipping-manager-log.php');
?>