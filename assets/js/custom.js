jQuery(document).ready(function() {
    product_customizer_init();
});

function product_customizer_init() {
    product_single_page_init();
}

function product_single_page_init() {
    add_to_cart_btn_click();
    ht_wt_change();
}

function add_to_cart_btn_click() {
    jQuery("#cst_add_to_cart").click(function(e) {
        const cst_product_id = jQuery("#cst_product_id").val();
        const dataToSend = { cst_product_id };
        add_to_cart(dataToSend);
    });
}

function add_to_cart(dataToSend) {
    postRequest(dataToSend, 'cst_addtocart', function(status, res) {
        if (status) {
            window.location.href = ajax_vars.cart_url;
        }
    });
}

function ht_wt_change() {
    const cst_height_control = jQuery("#cst_height");
    txt_change(cst_height_control);
    const cst_width_control = jQuery("#cst_width");
    txt_change(cst_width_control);
}

function txt_change(control) {
    control.keyup(delay(function() {
        get_price_by_ht_wt();
    })).focusout(delay(function() {
        get_price_by_ht_wt();
    }));
}

function get_price_by_ht_wt() {
    const cst_height = jQuery("#cst_height").val();
    const cst_width = jQuery("#cst_width").val();
    const cst_product_id = jQuery("#cst_product_id").val();
    if (!!cst_height && !!cst_width) {
        const dataToSend = { cst_height, cst_width, cst_product_id };
        postRequest(dataToSend, 'cst_get_ht_wt_price', function(status, res) {
            if (status && !!res && Object.keys(res).length > 0) {
                jQuery(".price").html(res.formatted_price);
            }
        });
    }
}