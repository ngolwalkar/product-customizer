<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
add_action('add_meta_boxes', 'CSTD_create_meta_box_custom_product');
if (!function_exists('CSTD_create_meta_box_custom_product')) {
    function CSTD_create_meta_box_custom_product()
    {
        add_meta_box(
            'custom_product_meta_box',
            'Price Group <em>(optional)</em>',
            'CSTD_add_content_custom_product',
            'product',
            'normal',
            'default'
        );
    }
}

function CSTD_add_content_custom_product($post)
{
    $product_id = $post->ID;
    if (!empty($product_id)) {
        $cst_price_group_dd = get_post_meta($product_id, 'cst_price_group_dd', true);
    }
    $cst_price_group_dd = !empty($cst_price_group_dd) ? $cst_price_group_dd : '';
    $price_groups_posts = CSTD_get_price_groups();
    $price_groups_dropdown = CSTD_get_price_group_dropdown($price_groups_posts, $cst_price_group_dd);
    echo " <input type='hidden' id='cst_prod_id' value='" . $product_id . "'/>
        <div>" . $price_groups_dropdown . "</div>";
}

function CSTD_get_price_groups()
{
    $args = array(
        'post_type'  => 'price-groups',
    );
    $posts = get_posts($args);
    $price_groups_posts = CSTD_check_empty_length($posts) ? $posts : array();
    return $price_groups_posts;
}

function CSTD_get_price_group_dropdown($price_groups_posts, $cst_price_group_dd = "")
{
    return '<select id="cst_price_group_dd" name="cst_price_group_dd">' . CSTD_get_price_group_options($price_groups_posts, $cst_price_group_dd) . '</select>';
}

function CSTD_get_price_group_options($price_groups_posts, $cst_price_group_dd = "")
{
    $html = '<option value="">Select Price Group</option>';
    if (CSTD_check_empty_length($price_groups_posts)) {
        foreach ($price_groups_posts as $key => $price_groups_post) {
            $ID = $price_groups_post->ID;
            $post_title = $price_groups_post->post_title;
            $isSelected = $cst_price_group_dd == $ID ? 'selected' : '';
            $html .= '<option value="' . $ID . '" ' . $isSelected . '>' . $post_title . '</option>';
        }
    }
    return $html;
}

/* add_action('wp_ajax_cst_get_price_groups_option_html', 'CSTD_get_price_groups_option_html');
function CSTD_get_price_groups_option_html()
{
    $cst_price_group_options = "";
    $values = sanitize_text_field($_POST['val']);

	$price_groups_posts = CSTD_get_price_groups();
	$cst_price_group_options = CSTD_get_price_group_options($price_groups_posts);

    wp_send_json($cst_price_group_options, 200);
} */

add_action('woocommerce_process_product_meta', 'CSTD_section_save', 10, 2);
function CSTD_section_save($post_id, $post)
{
    update_post_meta($post_id, 'cst_price_group_dd', sanitize_text_field($_POST['cst_price_group_dd']));
}

add_action('woocommerce_before_add_to_cart_form', 'CSTD_product_custom');
function CSTD_product_custom()
{
    global $post;
    $product_id = $post->ID;
    echo '<div>
            <input type="hidden" id="cst_product_id" value="' . $product_id . '" />
			<div class="cstd_error">
                <b>Error :</b><label></label>
            </div>
            <div>
                <b>Height</b><input type="text" id="cst_height" />
            </div>
            <div>
                <b>Width</b><input type="text" id="cst_width" />
            </div>
            <div>
                <input type="button" id="cst_add_to_cart" value="Add to cart" />
            </div>
          </div>';
}

function CSTD_get_price_by_ht_wt($product_id, $ht, $wt)
{
    $current_ht_wt_pr = array("ht" => $ht, "wt" => $wt, "product_id" => $product_id);
    $cst_matrix_options = CSTD_get_product_blinds($product_id);
    if (CSTD_check_empty_length($cst_matrix_options)) {
        foreach ($cst_matrix_options as $key => $cst_matrix_option) {
            $height = $cst_matrix_option["height"];
            $width = $cst_matrix_option["width"];
            $price = $cst_matrix_option["price"];
            if ($height >= $ht && $width >= $wt) {
                $current_ht_wt_pr["height"] = $height;
                $current_ht_wt_pr["width"] = $width;
               	$current_ht_wt_pr["formatted_price"] = wc_price($price);
                $current_ht_wt_pr["price"] = $price;
                break;
            }
        }
    }
    return $current_ht_wt_pr;
}

function CSTD_get_product_blinds($product_id)
{
    $cst_matrix_options = array();
    $cst_price_group_dd = get_post_meta($product_id, 'cst_price_group_dd', true);
    if (!empty($cst_price_group_dd)) {
        $cst_matrix_options = get_post_meta($cst_price_group_dd, 'cst_matrix_options', true);
    }
    return $cst_matrix_options;
}

add_action('wp_ajax_cst_addtocart', 'CSTD_addtocart');
add_action('wp_ajax_nopriv_cst_addtocart', 'CSTD_addtocart');
function CSTD_addtocart()
{
    global $woocommerce;
	$values = array_map( 'sanitize_text_field', $_POST['val'] );
    $pstid = $values['cst_product_id'];
    $woocommerce->cart->add_to_cart(intval($pstid), 1);
}

add_action('wp_ajax_cst_get_ht_wt_price', 'CSTD_get_ht_wt_price');
add_action('wp_ajax_nopriv_cst_get_ht_wt_price', 'CSTD_get_ht_wt_price');
function CSTD_get_ht_wt_price()
{
    if (!session_id()) {
        session_start();
    }
	$_POST = 
    $values = array_map( 'sanitize_text_field', $_POST['val'] );
    $cst_height = $values['cst_height'];
    $cst_width = $values['cst_width'];
	if(!is_numeric($cst_height) || !is_numeric($cst_width)){
		wp_send_json(array('status'=>'failed'), 200);
		exit;
	}
    $cst_product_id = $values['cst_product_id'];
    $htwt_price = CSTD_get_price_by_ht_wt($cst_product_id, $cst_height, $cst_width);
    $_SESSION['all_custom_values_product_' . $cst_product_id] = $htwt_price;
    wp_send_json($htwt_price, 200);
}

add_filter('woocommerce_add_cart_item_data', 'CSTD_add_custom_fields_cart_item_data', 10, 2);
function CSTD_add_custom_fields_cart_item_data($cart_item_data, $product_id)
{
    if (!session_id()) {
        session_start();
    }

    update_option("cart_item_datacart_item_data", $cart_item_data);
    $product = wc_get_product($product_id);
    $product_name = $product->get_title();
    $all_custom_values_product = $_SESSION['all_custom_values_product_' . $product_id];
    $cart_item_data['cst_custom_data_' . $product_id]['height'] = $all_custom_values_product['ht'];
    $cart_item_data['cst_custom_data_' . $product_id]['width'] = $all_custom_values_product['wt'];
    $cart_item_data['cst_custom_data_' . $product_id]['price'] = $all_custom_values_product['price'];
    $cart_item_data['cst_custom_data_' . $product_id]['product_name'] = $product_name;
    return $cart_item_data;
}

add_filter('woocommerce_get_item_data', 'CSTD_customizing_cart_item_data', 10, 2);
function CSTD_customizing_cart_item_data($cart_data, $cart_item)
{
    $proid = $cart_item['product_id'];
	    $itemky = $cart_item['key'];
    if (!isset($_SESSION['save_in_ordermeta'])) {
        $_SESSION['save_in_ordermeta'] = array();
        $_SESSION['save_in_ordermeta'][$itemky] = array();
    }
    $custom_items[] = array(
        'name'      => 'Total Width',
        'value'     => $cart_item['cst_custom_data_' . $proid]['width'],
    );
    $_SESSION['save_in_ordermeta'][$itemky]['Total_Width'] = $cart_item['cst_custom_data_' . $proid]["width"] . " cm";
    $custom_items[] = array(
        'name'      => 'Total Height',
        'value'     => $cart_item['cst_custom_data_' . $proid]['height'],
    );
    $_SESSION['save_in_ordermeta'][$itemky]['Total_Height'] = $cart_item['cst_custom_data_' . $proid]['height'] . " cm";


    $_SESSION['save_in_ordermeta'][$itemky]['addon_product_prices'] = $cart_item['cst_custom_data_' . $proid]['add_ons_price'];
    $_SESSION['temp_wc_sess_' . $itemky] = $cart_item['cst_custom_data_' . $proid];
	WC()->session->set('cst_custom_data_sess' . $itemky, $cart_item['cst_custom_data_' . $proid]);
    WC()->session->set('save_in_ordermeta_woo', $_SESSION['save_in_ordermeta']);
    return $custom_items;
}

add_action('woocommerce_before_calculate_totals', 'CSTD_add_custom_price', 9999, 1);
function CSTD_add_custom_price($cart)
{
    // This is necessary for WC 3.0+
    if (is_admin() && !defined('DOING_AJAX'))
        return;

    // Avoiding hook repetition 
    if (did_action('woocommerce_before_calculate_totals') >= 2)
        return;

    // Loop through cart items
    foreach ($cart->get_cart() as $item) {
        $product_id = $item['product_id'];
        $cst_custom_data = WC()->session->get('cst_custom_data_sess' . $item['key']);
        $price = $cst_custom_data['price'];
        $item['data']->set_price((int)$price);
    }
}

add_action('woocommerce_checkout_update_order_meta', 'CSTD_save_ordermeta', 10, 2);
function CSTD_save_ordermeta($order_id, $posted)
{
    if (!session_id()) {
        session_start();
    }
    $order = wc_get_order($order_id);
    $order_meta = array();
    $save_in_ordermeta_woo = WC()->session->get('save_in_ordermeta_woo');
    foreach ($save_in_ordermeta_woo as $prokey => $ordrmta) {
        foreach ($ordrmta as $key => $ordrmtaval) {
            $order_meta[$prokey][$key] = $ordrmtaval;
        }
    }
    $order->update_meta_data('cst_order_meta', $order_meta);
    $order->save();
}

function CSTD_display_order_data_in_admin( $order ){  ?>
    <div class="order_data_column">
<?php
$in_ordermeta = get_post_meta($order->get_id(), 'cst_order_meta', true);
if($in_ordermeta){
?>
        <h4><?php _e( 'Additional Information', 'woocommerce' ); ?></h4>
        <div class="address">
		</br>
        <?php
		//var_dump($in_ordermeta);
		$count = 1;
		foreach($in_ordermeta as $ordrmtakey => $ordmetaval){
			echo '<strong>For Item '.$count.':</strong>';
			foreach($ordmetaval as $key => $ordrmtaval){
				if(ucFirst(str_replace("_", ' ', $key )) == 'Addon product prices'){
					continue;
				}
				 echo '<div class="add_info"><strong>' . ucFirst(str_replace("_", ' ', $key )) . ':</strong>' . $ordrmtaval . '</div>';
			}
			echo '</br>';
				 $count += $count ;
		}
?>
        </div>
    </div>
<?php }
}
add_action( 'woocommerce_admin_order_data_after_order_details', 'CSTD_display_order_data_in_admin' );

function CSTD_add_order_item_meta($item_id, $values) {
	$order_meta = array();
	  $save_in_ordermeta_woo = WC()->session->get('save_in_ordermeta_woo');
    foreach ($save_in_ordermeta_woo as $prokey => $ordrmta) {
        foreach ($ordrmta as $key => $ordrmtaval) {
           woocommerce_add_order_item_meta($item_id, 'ikey', $prokey);
        }
    }
}
//add_action('woocommerce_add_order_item_meta', 'CSTD_add_order_item_meta', 10, 2);