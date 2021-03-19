<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
/** custom Post type Price Groups */
function CSTD_custom_post_type()
{
    // Set UI labels for Custom Post Type
    $labels = array(
        'name'                => _x('Price Groups', 'Post Type General Name', 'cstd'),
        'singular_name'       => _x('Price Group', 'Post Type Singular Name', 'cstd'),
        'menu_name'           => __('Price Groups', 'cstd'),
        'parent_item_colon'   => __('Parent Price Group', 'cstd'),
        'all_items'           => __('All Price Groups', 'cstd'),
        'view_item'           => __('View Price Group', 'cstd'),
        'add_new_item'        => __('Add New Price Group', 'cstd'),
        'add_new'             => __('Add New', 'cstd'),
        'edit_item'           => __('Edit Price Group', 'cstd'),
        'update_item'         => __('Update Price Group', 'cstd'),
        'search_items'        => __('Search Price Group', 'cstd'),
        'not_found'           => __('Not Found', 'cstd'),
        'not_found_in_trash'  => __('Not found in Trash', 'cstd'),
    );

    // Set other options for Custom Post Type
    $args = array(
        'label'               => __('Price Groups', 'cstd'),
        'description'         => __('Price Group for products', 'cstd'),
        'labels'              => $labels,
        // Features this CPT supports in Post Editor
        'supports'            => array('title', 'editor', 'thumbnail'),
        // You can associate this CPT with a taxonomy or custom taxonomy. 
        'taxonomies'          => array(),
        /* A hierarchical CPT is like Pages and can have
            * Parent and child items. A non-hierarchical CPT
            * is like Posts.
            */
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 5,
        'can_export'          => true,
        'has_archive'         => true,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'post',
        'show_in_rest' => true,
    );
    // Registering your Custom Post Type
    register_post_type('price-groups', $args);
}
add_action('init', 'CSTD_custom_post_type', 0);


function CSTD_add_post_meta_boxes()
{
    add_meta_box(
        "main_matrix",
        "Price Matrix",
        "CSTD_post_meta_box_upload_matrix",
        "price-groups",
        "advanced",
        "low"
    );
}
add_action("admin_init", "CSTD_add_post_meta_boxes");

function CSTD_post_meta_box_upload_matrix()
{
    global $post;

    $post_id = $post->ID;
    if (!empty($post_id)) {
        $cst_matrix_options = get_post_meta($post_id, 'cst_matrix_options', true);
    }
    $cst_matrix_options_html = CSTD_generate_matrix_options_html($cst_matrix_options);
    echo "<div>
            <input type='file' id='matrix_file' accept='.csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel'/>
            <input type='button' value='upload' id='upload_matrix_btn'/>
          </div>
          <div id='maxtix_options'>
          " . $cst_matrix_options_html . "
          </div>";
}

function CSTD_generate_matrix_options_html($cst_matrix_options)
{
    $cst_matrix_options_html = "";
    if (CSTD_check_empty_length($cst_matrix_options)) {
        $cst_matrix_options_html = '<div class="main_matrix_label"><label>Height (in cm)</label><label>Width (in cm)</label><label>Price</label></div>';
        foreach ($cst_matrix_options as $key => $cst_matrix_option) {
            $height = $cst_matrix_option["height"];
            $width = $cst_matrix_option["width"];
            $price = $cst_matrix_option["price"];
            $delete_btn_id = 'cst_delete_matrix_btn_' . $key;
            $cst_matrix_options_html .= "<div class='matrix_row'>
                                            <input type='number' step='0.01' name='cst_height[" . $key . "]' class='cst_height' value= '" . $height . "'/>
                                            <input type='number' step='0.01' name='cst_width[" . $key . "]' class='cst_width' value= '" . $width . "'/>
                                            <input type='number' step='0.01' name='cst_price[" . $key . "]' class='cst_price' value= '" . $price . "'/>
                                            <input type='button' value='Delete' class='cst_delete_matrix_btn' id='" . $delete_btn_id . "' />
                                        </div>";
        }
    }
    return $cst_matrix_options_html;
}

function CSTD_save_post_meta_boxes()
{
    global $post; // $post->ID
	
    CSTD_save_post_groups_meta($_POST, $post);
}
add_action('save_post', 'CSTD_save_post_meta_boxes');

function CSTD_save_post_groups_meta($cst_POST, $post)
{
    if (!empty($post) && $post->post_type == "price-groups") {
        $cst_matrix_options = array();

        $post_id = $post->ID;

        $cst_height = $cst_POST['cst_height'];
        $cst_width = $cst_POST['cst_width'];
        $cst_price = $cst_POST['cst_price'];

        if (CSTD_check_empty_length($cst_height) && CSTD_check_empty_length($cst_width) && CSTD_check_empty_length($cst_price)) {
            foreach ($cst_height as $key => $value) {
                $cst_matrix_option = array();
                $cst_matrix_option["height"] = sanitize_text_field($value);
                $cst_matrix_option["width"] = floatval(sanitize_text_field($cst_width[$key]));
                $cst_matrix_option["price"] = floatval(sanitize_text_field($cst_price[$key]));
                $cst_matrix_options[] = $cst_matrix_option;
            }
        }
        update_post_meta($post_id, 'cst_matrix_options', $cst_matrix_options);
    }
}
