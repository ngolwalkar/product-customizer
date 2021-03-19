jQuery(document).ready(function() {
    CSTD_product_customizer_init();
});

function CSTD_product_customizer_init() {
    CSTD_product_single_page_init();
}

function CSTD_product_single_page_init() {
    CSTD_add_to_cart_btn_click();
    CSTD_ht_wt_change();
}

function CSTD_add_to_cart_btn_click() {
    jQuery("#cst_add_to_cart").click(function(e) {
        const cst_product_id = jQuery("#cst_product_id").val();
        const dataToSend = { cst_product_id };
        CSTD_add_to_cart(dataToSend);
    });
}

function CSTD_add_to_cart(dataToSend) {
    CSTD_postRequest(dataToSend, 'cst_addtocart', function(status, res) {
        if (status) {
            window.location.href = ajax_vars.cart_url;
        }
    });
}

function CSTD_ht_wt_change() {
    const cst_height_control = jQuery("#cst_height");
    CSTD_txt_change(cst_height_control);
    const cst_width_control = jQuery("#cst_width");
    CSTD_txt_change(cst_width_control);
}

function CSTD_txt_change(control) {
    control.keyup(CSTD_delay(function() {
        CSTD_get_price_by_ht_wt();
    })).focusout(CSTD_delay(function() {
        CSTD_get_price_by_ht_wt();
    }));
}

function CSTD_get_price_by_ht_wt() {
    const cst_height = jQuery("#cst_height").val();
    const cst_width = jQuery("#cst_width").val();
    const cst_product_id = jQuery("#cst_product_id").val();
    if (!!cst_height && !!cst_width) {
        const dataToSend = { cst_height, cst_width, cst_product_id };
        CSTD_postRequest(dataToSend, 'cst_get_ht_wt_price', function(status, res) {
            if (status && !!res && Object.keys(res).length > 0) {
				if(res.status == "failed"){
					jQuery('.cstd_error').find('label').html('Values must be a number');
					jQuery('.cstd_error').show();
					return false;
				}
				jQuery('.cstd_error').hide();
                jQuery(".price").html(res.formatted_price);
            }
        });
    }
}