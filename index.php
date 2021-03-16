<?php

/**
 * Plugin Name:       Product Customizer
 * Plugin URI:        https://prozoned.com/
 * Description:       Product Customizer
 * Version:           1.0.0
 * Author:            Prozoned Technologies
 * Author URI:        https://prozoned.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */
class  CST_WPCustomDatatables
{
    public function __construct()
    {
        $cst_plugin_basename = plugin_basename(__FILE__);
        $cst_plugin_root_name = "";
        if (!empty($cst_plugin_basename)) {
            $cst_plugin_basename_data = explode("/", $cst_plugin_basename);
            if (!empty($cst_plugin_basename_data) && count($cst_plugin_basename_data) > 0) {
                $cst_plugin_root_name = $cst_plugin_basename_data[0];
            }
        }
        define('CST_PLUGIN_URL', plugin_dir_url(__FILE__));
        define('CST_PLUGIN_PATH', ABSPATH . 'wp-content/plugins/' . $cst_plugin_root_name);
        require_once CST_PLUGIN_PATH . '/components/enqueue.php';
        require_once CST_PLUGIN_PATH . '/components/custom_shortcode/custom_shortcode.php';
        require_once CST_PLUGIN_PATH . '/components/components_prod_customizer/custom_prod_customizer.php';
        require_once CST_PLUGIN_PATH . '/components/components_wordpress/custom_wordpress.php';
        // require_once CST_PLUGIN_PATH . '/components/ajax/ajax.php';
    }
}

$cst_wpCustomDatatables = new CST_WPCustomDatatables();
