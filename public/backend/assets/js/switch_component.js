/**
 *
 * You can write your JS code here, DO NOT touch the default style file
 * because it will make it harder for you to update.
 *
 * Register your switch elements here
 * this will make it easier for you tu find components
 */

"use strict";

$(document).ready(function () {

    $('input[type=checkbox][name=status]').change(function () {
        if ($(this).is(':checked')) {
            $("#para").text("Approved");
        } else {
            $("#para").text("Dis-Approved");
        }
    });
    // for category edit 
    $('input[type=checkbox][name=changer_1]').change(function () {
        if ($(this).is(':checked')) {
            $("#category_para_edit").text("Enable");
        } else {
            $("#category_para_edit").text("Disable");
        }
    });

    // for category
    $('input[type=checkbox][name=changer]').change(function () {
        if ($(this).is(':checked')) {
            $("#category_para").text("Enable");
        } else {
            $("#category_para").text("Disable");
        }
    });

    // for slider
    $('input[type=checkbox][name=slider_switch]').change(function () {
        if ($(this).is(':checked')) {
            $("#slider_text").text("Enable");
        } else {
            $("#slider_text").text("Disable");
            $("#slider_text").html("Disable");
        }
    });

    // for edit slider
    $('input[type=checkbox][name=edit_slider_switch]').change(function () {
        if ($(this).is(':checked')) {
            $("#edit_slider_text").text("Enable");
        } else {
            $("#edit_slider_text").text("Disable");
            $("#edit_slider_text").html("Disable");
        }
    });

    // for cancelable events
    $('#cancel_order').hide();
    $('input[type=checkbox][name=is_cancelable]').change(function () {
        if ($(this).is(':checked')) {
            // console.log('bhai');
            $("#cancel_order").show();
        } else {
            $('#cancel_order').hide();
        }
    });

    // for tax events
    $('input[type=checkbox][name=tax_status]').change(function () {
        if ($(this).is(':checked')) {
            // console.log('bhai');
            $("#tax_status").text("Enable");
        } else {
            $("#tax_status").html("Disable");
        }
    });

    $('input[type=checkbox][name=tax_status_edit]').change(function () {
        if ($(this).is(':checked')) {
            // console.log('bhai');
            $("#tax_status_edit").text("Enable");
        } else {
            $("#tax_status_edit").html("Disable");
        }
    });
    // for system tax settings
    $('input[type=checkbox][name=type]').change(function () {
        if ($(this).is(':checked')) {
            $("#type_text").text("Included");
        } else {
            $("#type_text").text("Excluded");
        }
    });

    // for maintenance mode
    $('input[type=checkbox][name=maintenance_mode]').change(function () {
        if ($(this).is(':checked')) {
            $("#mode_text").text("Active");
        } else {
            $("#mode_text").text("De active");
        }
    });
});


// for tags

$(document).ready(function () {
    var input = document.querySelector('input[id=tags]');
    var edit_input = document.querySelector('input[id=edit_service_tags]');

    // initialize Tagify on the above input node reference
    if (input != null) {
        new Tagify(input);
    }

    if (edit_input != null) {
        new Tagify(edit_input);
    }
});