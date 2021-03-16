/** Ajax */
function postRequest(dataToSend, action, callBack) {
    //applyLoader();
    jQuery.ajax({
        url: ajax_vars.ajaxurl,
        method: 'post',
        data: {
            action: action,
            val: dataToSend,
        },
        success: function(resp, aa) {
            ajaxRequestScucess(resp, callBack)
        },
        error: function(xhr) {
            ajaxRequestError(xhr, callBack)
        }
    });
}

function ajaxRequestScucess(resp, callBack) {
    // removeLoader();
    callBack(true, resp);
}

function ajaxRequestError(xhr, callBack) {
    //  removeLoader();
    callBack(false, xhr);
}

function delay(callback, ms) {
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