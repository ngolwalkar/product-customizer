<?php
add_action('wp_enqueue_scripts', 'cst_enqueue_custom_script_style');
function cst_enqueue_custom_script_style()
{
    wp_register_style('cst_custom_css', CST_PLUGIN_URL . 'assets/css/custom.css', array(), cst_get_version('/assets/css/custom.css'));
    wp_enqueue_style('cst_custom_css');

    wp_enqueue_script('jquery');
    wp_register_script('cst_custom_js', CST_PLUGIN_URL . 'assets/js/custom.js', array("jquery"), cst_get_version('/assets/js/custom.js'));
    wp_enqueue_script('cst_custom_js');

    $cart_url = get_permalink(wc_get_page_id('cart'));
    $localize_vars  = array(
        'ajaxurl'      => admin_url('admin-ajax.php'),
        'cstsiteurl'      => get_site_url(),
        'cart_url' => $cart_url,
        'currency' => get_woocommerce_currency_symbol()
    );
    wp_localize_script('cst_custom_js', 'ajax_vars', $localize_vars);
    load_common_script_style();
}

add_action('admin_enqueue_scripts', 'cst_admin_enqueue_custom_script_style');
function cst_admin_enqueue_custom_script_style()
{
    wp_register_script('cst_admin_custom_js', CST_PLUGIN_URL . 'assets/js/admin/custom.js', array("jquery"), cst_get_version('/assets/js/admin/custom.js'));
    wp_enqueue_script('cst_admin_custom_js');

    $localize_vars  = array(
        'ajaxurl'      => admin_url('admin-ajax.php'),
        'cstsiteurl'      => get_site_url(),
    );
    wp_localize_script('cst_admin_custom_js', 'ajax_vars', $localize_vars);

    load_external_script_style();
    load_common_script_style();
}

function load_external_script_style()
{
    wp_register_style('cst_admin_custom_css', CST_PLUGIN_URL . 'assets/css/admin/custom.css', array(), cst_get_version('/assets/css/admin/custom.css'));
    wp_enqueue_style('cst_admin_custom_css');

    wp_register_script('cst_admin_xlsx_js', CST_PLUGIN_URL . 'assets/js/admin/external/xlsx_full.min.js', array("jquery"), cst_get_version('/assets/js/admin/external/xlsx_full.min.js'));
    wp_enqueue_script('cst_admin_xlsx_js');
    wp_register_script('cst_admin_jszip_js', CST_PLUGIN_URL . 'assets/js/admin/external/jszip.js', array("jquery"), cst_get_version('/assets/js/admin/external/jszip.js'));
    wp_enqueue_script('cst_admin_jszip_js');
}

function load_common_script_style() {
    wp_register_script('cst_common_custom_js', CST_PLUGIN_URL . 'assets/js/common/custom.js', array("jquery"), cst_get_version('/assets/js/common/custom.js'));
    wp_enqueue_script('cst_common_custom_js');
}

function cst_get_version($path)
{
    return filemtime(CST_PLUGIN_PATH . $path);
}

function check_empty_length($current_var)
{
    return !!(!empty($current_var) && count($current_var) > 0);
}
