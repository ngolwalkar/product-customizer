<?php

/** custom Post type Price Groups */
function custom_post_type()
{
    // Set UI labels for Custom Post Type
    $labels = array(
        'name'                => _x('Price Groups', 'Post Type General Name', 'twentytwenty'),
        'singular_name'       => _x('Price Group', 'Post Type Singular Name', 'twentytwenty'),
        'menu_name'           => __('Price Groups', 'twentytwenty'),
        'parent_item_colon'   => __('Parent Price Group', 'twentytwenty'),
        'all_items'           => __('All Price Groups', 'twentytwenty'),
        'view_item'           => __('View Price Group', 'twentytwenty'),
        'add_new_item'        => __('Add New Price Group', 'twentytwenty'),
        'add_new'             => __('Add New', 'twentytwenty'),
        'edit_item'           => __('Edit Price Group', 'twentytwenty'),
        'update_item'         => __('Update Price Group', 'twentytwenty'),
        'search_items'        => __('Search Price Group', 'twentytwenty'),
        'not_found'           => __('Not Found', 'twentytwenty'),
        'not_found_in_trash'  => __('Not found in Trash', 'twentytwenty'),
    );

    // Set other options for Custom Post Type
    $args = array(
        'label'               => __('Price Groups', 'twentytwenty'),
        'description'         => __('Price Group news and reviews', 'twentytwenty'),
        'labels'              => $labels,
        // Features this CPT supports in Post Editor
        'supports'            => array('title', 'editor', 'thumbnail'),
        // You can associate this CPT with a taxonomy or custom taxonomy. 
        'taxonomies'          => array('genres'),
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
add_action('init', 'custom_post_type', 0);


function add_post_meta_boxes()
{
    add_meta_box(
        "main_matrix",
        "Additional Information",
        "post_meta_box_upload_matrix",
        "price-groups",
        "advanced",
        "low"
    );
}
add_action("admin_init", "add_post_meta_boxes");

function post_meta_box_upload_matrix()
{
    global $post;

    $post_id = $post->ID;
    if (!empty($post_id)) {
        $cst_blinds = get_post_meta($post_id, 'cst_blinds', true);
        $cst_matrix_options = get_post_meta($post_id, 'cst_matrix_options', true);
    }
    $cst_matrix_options_html = generate_matrix_options_html($cst_matrix_options);
    $cst_blinds = !empty($cst_blinds) ? $cst_blinds : 'roller';
    echo "<div>
            <div class='cst_blinds'>
                <b>Is Roller Blinds</b> <input type='radio' name='cst_blinds' value='roller' " . ($cst_blinds == "roller" ? "checked" : "") . "/>
            </div>
            <div class='cst_blinds'>
                <b>Is Vertical Blinds</b> <input type='radio' name='cst_blinds' value='vertical' " . ($cst_blinds == "vertical" ? "checked" : "") . "/>
            </div>
          </div>
          <div>
            <input type='file' id='matrix_file' accept='.csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel'/>
            <input type='button' value='upload' id='upload_matrix_btn'/>
          </div>
          <div id='maxtix_options'>
          " . $cst_matrix_options_html . "
          </div>";
}

function generate_matrix_options_html($cst_matrix_options)
{
    $cst_matrix_options_html = "";
    if (check_empty_length($cst_matrix_options)) {
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

function save_post_meta_boxes()
{
    global $post; // $post->ID
    save_post_groups_meta($_POST, $post);
}
add_action('save_post', 'save_post_meta_boxes');

function save_post_groups_meta($cst_POST, $post)
{
    if (!empty($post) && $post->post_type == "price-groups") {
        $cst_matrix_options = array();

        $post_id = $post->ID;
        $cst_blinds = $cst_POST['cst_blinds'];
        update_post_meta($post_id, 'cst_blinds', $cst_blinds);

        $cst_height = $cst_POST['cst_height'];
        $cst_width = $cst_POST['cst_width'];
        $cst_price = $cst_POST['cst_price'];

        if (check_empty_length($cst_height) && check_empty_length($cst_width) && check_empty_length($cst_price)) {
            foreach ($cst_height as $key => $value) {
                $cst_matrix_option = array();
                $cst_matrix_option["height"] = $value;
                $cst_matrix_option["width"] = floatval($cst_width[$key]);
                $cst_matrix_option["price"] = floatval($cst_price[$key]);
                $cst_matrix_options[] = $cst_matrix_option;
            }
        }
        update_post_meta($post_id, 'cst_matrix_options', $cst_matrix_options);
    }
}
