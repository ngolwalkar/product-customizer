<?php
add_action('add_meta_boxes', 'create_meta_box_custom_product');
if (!function_exists('create_meta_box_custom_product')) {
    function create_meta_box_custom_product()
    {
        add_meta_box(
            'custom_product_meta_box',
            __('Product Type <em>(optional)</em>', 'cmb'),
            'add_content_custom_product',
            'product',
            'normal',
            'default'
        );
    }
}

function add_content_custom_product($post)
{
    $product_id = $post->ID;
    if (!empty($product_id)) {
        $cst_prod_blinds = get_post_meta($product_id, 'cst_prod_blinds', true);
        $cst_price_group_dd = get_post_meta($product_id, 'cst_price_group_dd', true);
    }
    $cst_prod_blinds = !empty($cst_prod_blinds) ? $cst_prod_blinds : 'roller';
    $cst_price_group_dd = !empty($cst_price_group_dd) ? $cst_price_group_dd : '';
    $price_groups_posts = get_price_groups($cst_prod_blinds);
    $price_groups_dropdown = get_price_group_dropdown($price_groups_posts, $cst_price_group_dd);
    echo "
        <input type='hidden' id='cst_prod_id' value='" . $product_id . "'/>
        <div>
			<div class='cst_blinds'>
				<b>Is Roller Blinds</b> <input type='radio' name='cst_prod_blinds' value='roller' " . ($cst_prod_blinds == "roller" ? "checked" : "") . "/>
			</div>
			<div class='cst_blinds'>
				<b>Is Vertical Blinds</b> <input type='radio' name='cst_prod_blinds' value='vertical' " . ($cst_prod_blinds == "vertical" ? "checked" : "") . "/>
			</div>
		</div>
        <div>" . $price_groups_dropdown . "</div>";
}

function get_price_groups($cst_blinds)
{
    $args = array(
        'post_type'  => 'price-groups',
        'meta_query' => array(
            array(
                'key'     => 'cst_blinds',
                'value'   => $cst_blinds,
                'compare' => 'LIKE',
            )
        )
    );
    $posts = get_posts($args);
    $price_groups_posts = check_empty_length($posts) ? $posts : array();
    return $price_groups_posts;
}

function get_price_group_dropdown($price_groups_posts, $cst_price_group_dd = "")
{
    return '<select id="cst_price_group_dd" name="cst_price_group_dd">' . get_price_group_options($price_groups_posts, $cst_price_group_dd) . '</select>';
}

function get_price_group_options($price_groups_posts, $cst_price_group_dd = "")
{
    $html = '<option value="">Select Price Group</option>';
    if (check_empty_length($price_groups_posts)) {
        foreach ($price_groups_posts as $key => $price_groups_post) {
            $ID = $price_groups_post->ID;
            $post_title = $price_groups_post->post_title;
            $isSelected = $cst_price_group_dd == $ID ? 'selected' : '';
            $html .= '<option value="' . $ID . '" ' . $isSelected . '>' . $post_title . '</option>';
        }
    }
    return $html;
}

add_action('wp_ajax_cst_get_price_groups_option_html', 'cst_get_price_groups_option_html');
function cst_get_price_groups_option_html()
{
    $cst_price_group_options = "";
    $values = $_POST['val'];
    $blind = $values['blind'];
    if (!empty($blind)) {
        $price_groups_posts = get_price_groups($blind);
        $cst_price_group_options = get_price_group_options($price_groups_posts);
    }
    wp_send_json($cst_price_group_options, 200);
}

add_action('woocommerce_process_product_meta', 'cst_section_save', 10, 2);
function cst_section_save($post_id, $post)
{
    update_post_meta($post_id, 'cst_price_group_dd', $_POST['cst_price_group_dd']);
    update_post_meta($post_id, 'cst_prod_blinds', $_POST['cst_prod_blinds']);
}

add_action('woocommerce_before_add_to_cart_form', 'cst_product_custom');
function cst_product_custom()
{
    global $post;
    $product_id = $post->ID;
    echo '<div>
            <input type="hidden" id="cst_product_id" value="' . $product_id . '" />
            <div>
                <b>Height</b><input type="text" id="cst_height" />
            </div>
            <div>
                <b>Width</b><input type="text" id="cst_width" />
            </div>
            <div>
                <b>Price</b><label id="cst_price"></label>
            </div>
            <div>
                <input type="button" id="cst_add_to_cart" value="Add to cart" />
            </div>
          </div>';
}

function get_price_by_ht_wt($product_id, $ht, $wt)
{
    $current_ht_wt_pr = array("ht" => $ht, "wt" => $wt, "product_id" => $product_id);
    $cst_matrix_options = get_product_blinds($product_id);
    if (check_empty_length($cst_matrix_options)) {
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

function get_product_blinds($product_id)
{
    $cst_matrix_options = array();
    $cst_price_group_dd = get_post_meta($product_id, 'cst_price_group_dd', true);
    if (!empty($cst_price_group_dd)) {
        $cst_matrix_options = get_post_meta($cst_price_group_dd, 'cst_matrix_options', true);
    }
    return $cst_matrix_options;
}

add_action('wp_ajax_cst_addtocart', 'cst_addtocart');
add_action('wp_ajax_nopriv_cst_addtocart', 'cst_addtocart');
function cst_addtocart()
{
    global $woocommerce;
    $values = $_POST['val'];
    $pstid = $values['cst_product_id'];
    $woocommerce->cart->add_to_cart(intval($pstid), 1);
}

add_action('wp_ajax_cst_get_ht_wt_price', 'cst_get_ht_wt_price');
add_action('wp_ajax_nopriv_cst_get_ht_wt_price', 'cst_get_ht_wt_price');
function cst_get_ht_wt_price()
{
    if (!session_id()) {
        session_start();
    }
    $values = $_POST['val'];
    $cst_height = $values['cst_height'];
    $cst_width = $values['cst_width'];
    $cst_product_id = $values['cst_product_id'];
    $htwt_price = get_price_by_ht_wt($cst_product_id, $cst_height, $cst_width);
    $_SESSION['all_custom_values_product_' . $cst_product_id] = $htwt_price;
    wp_send_json($htwt_price, 200);
}

add_filter('woocommerce_add_cart_item_data', 'add_custom_fields_cart_item_data', 10, 2);
function add_custom_fields_cart_item_data($cart_item_data, $product_id)
{
    if (!session_id()) {
        session_start();
    }

    update_option("cart_item_datacart_item_data", $cart_item_data);
    $product = wc_get_product($product_id);
    $product_name = $product->get_title();
    $all_custom_values_product = $_SESSION['all_custom_values_product_' . $product_id];
    $cart_item_data['cst_custom_data_' . $product_id]['height'] = $_SESSION['all_custom_values_product']['ht'];
    $cart_item_data['cst_custom_data_' . $product_id]['width'] = $all_custom_values_product['wt'];
    $cart_item_data['cst_custom_data_' . $product_id]['price'] = $all_custom_values_product['price'];
    $cart_item_data['cst_custom_data_' . $product_id]['product_name'] = $product_name;
    WC()->session->set('cst_custom_data_sess' . $product_id, $cart_item_data['cst_custom_data_' . $product_id]);
    return $cart_item_data;
}

add_filter('woocommerce_get_item_data', 'customizing_cart_item_data', 10, 2);
function customizing_cart_item_data($cart_data, $cart_item)
{
    $proid = $cart_item['product_id'];
    if (!isset($_SESSION['save_in_ordermeta'])) {
        $_SESSION['save_in_ordermeta'] = array();
        $_SESSION['save_in_ordermeta'][$proid] = array();
    }
    $custom_items[] = array(
        'name'      => 'Total Width',
        'value'     => $cart_item['cst_custom_data_' . $proid]['width'],
    );
    $_SESSION['save_in_ordermeta'][$proid]['Total_Width'] = $cart_item['cst_custom_data_' . $proid]["width"] . " cm";
    $custom_items[] = array(
        'name'      => 'Total Height',
        'value'     => $cart_item['cst_custom_data_' . $proid]['height'],
    );
    $_SESSION['save_in_ordermeta'][$proid]['Total_Height'] = $cart_item['cst_custom_data_' . $proid]['height'] . " cm";
    $custom_items[] = array(
        'name'      => 'Product Price',
        'value'     => get_woocommerce_currency_symbol() . $cart_item['cst_custom_data_' . $proid]['price'],
    );
    $itemky = $cart_item['key'];
    $_SESSION['save_in_ordermeta'][$proid]['addon_product_prices'] = $cart_item['cst_custom_data_' . $proid]['add_ons_price'];
    $_SESSION['temp_wc_sess_' . $itemky] = $cart_item['cst_custom_data_' . $proid];
    WC()->session->set('save_in_ordermeta_woo', $_SESSION['save_in_ordermeta']);
    return $custom_items;
}

add_action('woocommerce_before_calculate_totals', 'add_custom_price', 9999, 1);
function add_custom_price($cart)
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
        $cst_custom_data = WC()->session->get('cst_custom_data_sess' . $product_id);
        $price = $cst_custom_data['price'];
        $item['data']->set_price((int)$price);
    }
}

add_action('woocommerce_checkout_update_order_meta', 'cst_save_ordermeta', 10, 2);
function cst_save_ordermeta($order_id, $posted)
{
    if (!session_id()) {
        session_start();
    }
    $order = wc_get_order($order_id);
    $order_meta = array();
    $save_in_ordermeta_woo = WC()->session->get('save_in_ordermeta_woo');
    foreach ($save_in_ordermeta_woo as $prokey => $ordrmta) {
        foreach ($ordrmta as $key => $ordrmtaval) {
            $order_meta[$key] = $ordrmtaval;
        }
    }
    $order->update_meta_data('cst_order_meta', $order_meta);
    $order->save();
}

function cst_cloudways_display_order_data_in_admin( $order ){  ?>
    <div class="order_data_column">
<?php
$in_ordermeta = get_post_meta($order->get_id(), 'cst_order_meta');
if($in_ordermeta){
?>
        <h4><?php _e( 'Additional Information', 'woocommerce' ); ?></h4>
        <div class="address">
        <?php
		//var_dump($in_ordermeta);
		foreach($in_ordermeta as $ordrmta){
			foreach($ordrmta as $key => $ordrmtaval){
				 echo '<div class="add_info"><strong>' . ucFirst(str_replace("_", ' ', $key )) . ':</strong>' . $ordrmtaval . '</div>';
			}
		}
?>
        </div>
    </div>
<?php }
}
add_action( 'woocommerce_admin_order_data_after_order_details', 'cst_cloudways_display_order_data_in_admin' );