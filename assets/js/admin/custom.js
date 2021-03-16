jQuery(document).ready(function() {
    product_customizer_init();
});

function product_customizer_init() {
    price_groups_init();
    product_page_init();
}

/** Price Group Page changes */
function price_groups_init() {
    upload_file();
    subscribe_delete_matrix_rows();
}

function subscribe_delete_matrix_rows() {
    jQuery(".cst_delete_matrix_btn").click(function(e) {
        e.preventDefault();
        delete_matrix_row(this);
    })
}

function upload_file() {
    jQuery('#upload_matrix_btn').click(function(e) {
        e.preventDefault();
        generate_matrix();
    })
}

function generate_matrix() {
    const files = jQuery('#matrix_file').prop('files');
    if (files && files.length > 0) {
        const maxtix_options_control = jQuery("#maxtix_options");
        maxtix_options_control.html('<div class="main_matrix_label"><label>Height (in cm)</label><label>Width (in cm)</label><label>Price</label></div>');
        const fileName = files[0].name;
        const fileNameExt = fileName.substr(fileName.lastIndexOf('.') + 1).toLocaleLowerCase();
        if (fileNameExt == 'csv') {
            // csv
            generateHeightWidthFromCsv(files[0]);
        } else if (fileNameExt == 'xlsx') {
            // xlsx or xls
            generateHeightWidthFromXlsx(files[0]);
        }
        clear_matrix_file_control();
    }
}

function clear_matrix_file_control() {
    jQuery("#matrix_file").val('');
}

function generateHeightWidthFromCsv(file) {
    if (typeof(FileReader) != "undefined") {
        const reader = new FileReader();
        reader.onload = function(e) {
            const rows = e.target.result.split("\n");
            getHeightWidthFromCSV(rows);
        }
        reader.readAsText(file);
    } else {
        //alert("This browser does not support HTML5.");
    }
}

function getHeightWidthFromCSV(rows) {
    if (!!rows && rows.length > 0) {
        const width_row = rows[0].split(",");
        let index = 0;
        for (let i = 2; i < rows.length; i++) {
            const cells = rows[i].split(",");
            if (!!cells && cells.length > 0) {
                const height = cells[0];
                if (!!height) {
                    cells.forEach((x, row_index) => {
                        if (row_index > 1) {
                            const price = x;
                            const width = width_row[row_index];
                            if (!!width && !!price) {
                                const height_width_price_obj = { height, width, price, index };
                                generateHeightWidthRow(height_width_price_obj);
                                index++;
                            }
                        }
                    })
                }
            }
        }
    }

}

function generateHeightWidthFromXlsx(file) {
    const reader = new FileReader();

    reader.onload = function(e) {
        const data = e.target.result;
        const workbook = XLSX.read(data, {
            type: 'binary'
        });
        workbook.SheetNames.forEach(function(sheetName) {
            // Here is your object
            const XL_row_object = XLSX.utils.sheet_to_row_object_array(workbook.Sheets[sheetName]);
            getHeightWidthFromXlsx(XL_row_object);
        })

    };

    reader.onerror = function(ex) {
        console.log(ex);
    };

    reader.readAsBinaryString(file);
}

function getHeightWidthFromXlsx(file_data) {
    let index = 0;
    if (!!file_data && file_data.length > 0) {
        file_data.splice(0, 1);
        file_data.forEach(x => {
            const obj = JSON.parse(JSON.stringify(x))
            const height = x['Sizes'];
            delete obj['Sizes'];
            delete obj['mm'];
            if (Object.keys(obj).length > 0) {
                for (const property in obj) {
                    const width = property;
                    const price = obj[property];
                    const height_width_price_obj = { height, width, price, index };
                    generateHeightWidthRow(height_width_price_obj);
                    index++;
                }
            }
        })
    }
}

function generateHeightWidthRow(height_width_price_obj) {
    const { height, width, price, index } = height_width_price_obj;
    const maxtix_options_control = jQuery("#maxtix_options");
    const delete_btn_id = 'cst_delete_matrix_btn_' + index;
    const html = `<div class='matrix_row'>
                        <input type='text' name='cst_height[` + index + `]' class='cst_height' value= '` + height + `'/>
                        <input type='text' name='cst_width[` + index + `]' class='cst_width' value= '` + width + `'/>
                        <input type='text' name='cst_price[` + index + `]' class='cst_price' value= '` + price + `'/>
                        <input type='button' value='Delete' class='cst_delete_matrix_btn' id='` + delete_btn_id + `' />
                    </div>`;
    maxtix_options_control.append(html);
    subscribe_delete_matrix_row(delete_btn_id)
}

function subscribe_delete_matrix_row(btn_id) {
    jQuery("#" + btn_id).click(function(e) {
        e.preventDefault();
        delete_matrix_row(this);
    });
}

function delete_matrix_row(that) {
    const control = jQuery(that);
    const matrix_row_control = control.parent('.matrix_row');
    matrix_row_control.remove();
}

/** product page change */
function product_page_init() {
    blindChange();
}

function blindChange() {
    jQuery("input[name='cst_prod_blinds']").change(function(e) {
        const dataToSend = { blind: this.value };
        get_price_groups_option_html(dataToSend);
    });
    // postRequest(dataToSend, 'cst_get_variation_price_id', function(status, res) {
    //     if (status && res && Object.keys(res).length > 0) {}
    // })
}

function get_price_groups_option_html(dataToSend) {
    postRequest(dataToSend, 'cst_get_price_groups_option_html', function(status, res) {
        jQuery("#cst_price_group_dd").html(res);
    });
}