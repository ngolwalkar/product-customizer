<?php
/**
 * Plugin Name:       Dynamic Pricing Calculation by Width and Height
 * Description:       This plugin gives the ability to calculates the Woocommerce product price by entering width and height of the product.
 * Version:           1.0.0
 * Author:            Prozoned Technologies
 * Author URI:        https://prozoned.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class  CST_Dynamic_Pricing_Calc
{
    public function __construct()
    {
		define('CSTD_PLUGIN_URL', plugin_dir_url(__FILE__));
		define('CSTD_PLUGIN_PATH', plugin_dir_path( __FILE__ ));
		require_once CSTD_PLUGIN_PATH . '/components/enqueue.php';
		require_once CSTD_PLUGIN_PATH . '/components/components_prod_customizer/custom_prod_customizer.php';
		require_once CSTD_PLUGIN_PATH . '/components/components_wordpress/custom_wordpress.php';
    }
}

add_action( 'plugins_loaded', 'cstd_plugin_initiate' );

function cstd_plugin_initiate() {

	if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		add_action( 'admin_notices', 'cstd_plugin_init_missing_wc_notice' );
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		 deactivate_plugins(plugin_basename(__FILE__));
		return;
	}
	else{
		$CST_Dynamic_Pricing_Calc = new CST_Dynamic_Pricing_Calc();
	}
}

function cstd_plugin_init_missing_wc_notice() {
	/* translators: %s WC download URL link. */
	echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'Dynamic Pricing Calculation by Width and Height Plugin requires WooCommerce to be installed and active. You can download %s here.', 'cst_plugin' ), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</strong></p></div>';
}
