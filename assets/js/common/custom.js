/** Ajax */
function CSTD_postRequest(dataToSend, action, callBack) {
    //applyLoader();
    jQuery.ajax({
        url: ajax_vars.ajaxurl,
        method: 'post',
        data: {
            action: action,
            val: dataToSend,
        },
        success: function(resp, aa) {
            CSTD_ajaxRequestScucess(resp, callBack)
        },
        error: function(xhr) {
            CSTD_ajaxRequestError(xhr, callBack)
        }
    });
}

function CSTD_ajaxRequestScucess(resp, callBack) {
    // removeLoader();
    callBack(true, resp);
}

function CSTD_ajaxRequestError(xhr, callBack) {
    //  removeLoader();
    callBack(false, xhr);
}

function CSTD_delay(callback, ms) {
    var timer = 0;
    return function() {
        var context = this,
            args = arguments;
        clearTimeout(timer);
        timer = setTimeout(function() {
            callback.apply(context, args);
        }, ms || 0);
    };
}