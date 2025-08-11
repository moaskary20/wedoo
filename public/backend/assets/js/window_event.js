"use strict";
$(document).on("submit", ".update-form-submit-event", function (e) {
  e.preventDefault();
  var formData = new FormData(this);
  var form_id = $(this).attr("id");
  var error_box = $("#error_box", this);
  var submit_btn = $(this).find(".submit_btn");
  var btn_html = $(this).find(".submit_btn").html();
  var btn_val = $(this).find(".submit_btn").val();
  var button_text =
    btn_html != "" || btn_html != "undefined" ? btn_html : btn_val;
  formData.append(csrfName, csrfHash);
  $.ajax({
    type: "POST",
    url: $(this).attr("action"),
    data: formData,
    cache: false,
    contentType: false,
    processData: false,
    dataType: "json",
    beforeSend: function () {
      submit_btn.html("Please Wait..");
      submit_btn.attr("disabled", true);
    },
    success: function (response) {
      csrfName = response["csrfName"];
      csrfHash = response["csrfHash"];
      if (response.error == false) {
        iziToast.success({
          title: "Success",
          message: response.message,
          position: "topRight",
        });
        submit_btn.attr("disabled", false);
        submit_btn.html(button_text);
        $(".close").click();
        $("#user_list").bootstrapTable("refresh");
        $("#slider_list").bootstrapTable("refresh");
        window.location.reload();
      } else {
        if (
          typeof response.message === "object" &&
          !Array.isArray(response.message) &&
          response.message !== null
        ) {
          for (var k in response.message) {
            if (response.message.hasOwnProperty(k)) {
              showToastMessage(response.message[k], "error");
            }
          }
        } else {
          showToastMessage(response.message, "error");
        }
        submit_btn.attr("disabled", false);
        submit_btn.html(button_text);
        $("#update_modal").bootstrapTable("refresh");
      }
    },
  });
});
window.user_events = {
  "click .deactivate_user": function (e, value, row, index) {
    var user_id = row.id;
    Swal.fire({
      title: are_your_sure,
      text: are_you_sure_you_want_to_deactivate_this_user,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      var input_body = {
        [csrfName]: csrfHash,
        user_id: user_id,
      };
      if (result.isConfirmed) {
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/users/deactivate",
          data: input_body,
          dataType: "json",
          success: function (response) {
            csrfName = response["csrfName"];
            csrfHash = response["csrfHash"];
            if (response.error == false) {
              showToastMessage(response.message, "success");
              setTimeout(() => {
                $("#user_list").bootstrapTable("refresh");
              }, 2000);
            } else {
              setTimeout(() => {
                $("#user_list").bootstrapTable("refresh");
              }, 2000);
              return showToastMessage(response.message, "error");
            }
          },
        });
      }
    });
  },
  "click .activate_user": function (e, value, row, index) {
    var user_id = row.id;
    Swal.fire({
      title: are_your_sure,
      text: are_you_sure_you_want_to_activate_this_user,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      var input_body = {
        [csrfName]: csrfHash,
        user_id: user_id,
      };
      if (result.isConfirmed) {
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/users/activate",
          data: input_body,
          dataType: "json",
          success: function (response) {
            csrfName = response["csrfName"];
            csrfHash = response["csrfHash"];
            if (response.error == false) {
              showToastMessage(response.message, "success");
              setTimeout(() => {
                $("#user_list").bootstrapTable("refresh");
              }, 2000);
            } else {
              setTimeout(() => {
                $("#user_list").bootstrapTable("refresh");
              }, 2000);
              return showToastMessage(response.message, "error");
            }
          },
        });
      }
    });
  },
  "click .delete-user": function (e, value, row, index) {
    var user_id = row.id;
    Swal.fire({
      title: are_your_sure,
      text: are_you_sure_you_want_to_delete_this_user,
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      var input_body = {
        [csrfName]: csrfHash,
        user_id: user_id,
      };
      if (result.isConfirmed) {
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/system_users/delete_user",
          data: input_body,
          dataType: "json",
          success: function (response) {
            if (response.error == false) {
              showToastMessage(response.message, "success");
              setTimeout(() => {
                $("#partner_list").bootstrapTable("refresh");
              }, 2000);
              window.location.reload();
              return;
            } else {
              return showToastMessage(response.message, "error");
            }
          },
        });
      }
    });
  },
  "click .edit-user": function (e, value, row, index) {
    $("#id").val(row.id);
    $(document).ready(function () {
      $("#edit_role").val(row.role_a).trigger("change");
      if ($("#edit_role").val() == 1) {
        $("#permissions").hide();
      } else {
        $("#permissions").show();
      }
    });
    var permissions = JSON.parse(row.permissions);
    let values;
    var data = permissions != null ? true : false;
    if (data) {
      Object.keys(permissions).forEach((key) => {
        let single_object = permissions[key];
        if (key == "create") {
          Object.keys(single_object).forEach((val) => {
            if (single_object.order == 0) {
              let order = $("#orders_create_edit")[0];
              $(order).attr("checked", true);
            }
            if (single_object.category == 1) {
              let category = $("#categories_create_edit")[0];
              $(category).attr("checked", true);
            }
            if (single_object.subscription == 1) {
              let subscription = $("#subscription_create_edit")[0];
              $(subscription).attr("checked", true);
            }
            if (single_object.sliders == 1) {
              let object = $("#sliders_create_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.tax == 1) {
              let object = $("#tax_create_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.sliders == 1) {
              let object = $("#sliders_create_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.services == 1) {
              let object = $("#services_create_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.promo_code == 1) {
              let object = $("#promo_code_create_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.featured_section == 1) {
              let object = $("#featured_section_create_edit")[0];
              $(object).attr("checked", true);
            } //
            if (
              single_object.partner == 1 ||
              single_object.partner != undefined
            ) {
              let object = $("#partner_create_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.notification == 1) {
              let object = $("#send_notification_create_edit")[0];
              $(object).attr("checked", true);
            } //
            if (single_object.faq == 1) {
              let object = $("#faq_create_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.settings == 1) {
              let object = $("#settings_create_edit")[0];
              $(object).attr("checked", true);
            }
          });
        } else if (key == "read") {
          Object.keys(single_object).forEach((val) => {
            if (single_object.order == 0) {
              let object = $("#orders_read_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.category == 1) {
              let object = $("#categories_read_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.subscription == 1) {
              let object = $("#subscription_read_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.sliders == 1) {
              let object = $("#sliders_read_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.tax == 1) {
              let object = $("#tax_read_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.services == 1) {
              let object = $("#services_read_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.promo_code == 1) {
              let object = $("#promo_code_read_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.featured_section == 1) {
              let object = $("#featured_section_read_edit")[0];
              $(object).attr("checked", true);
            } //
            if (
              single_object.partner == 1 ||
              single_object.partner != undefined
            ) {
              let object = $("#partner_read_edit")[0];
              $(object).attr("checked", true);
            }
            if (
              single_object.customer != undefined &&
              single_object.customer == 1
            ) {
              let object = $("#customers_read_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.notification == 1) {
              let object = $("#send_notification_read_edit")[0];
              $(object).attr("checked", true);
            } //
            if (single_object.faq == 1) {
              let object = $("#faq_read_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.settings == 1) {
              let object = $("#settings_read_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.system == 1) {
              let object = $("#system_user_read_edit")[0];
              $(object).attr("checked", true);
            }
          });
        } else if (key == "update") {
          Object.keys(single_object).forEach((val) => {
            if (single_object.order == 0) {
              let object = $("#orders_update_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.category == 1) {
              let object = $("#categories_update_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.subscription == 1) {
              let object = $("#subscription_update_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.sliders == 1) {
              let object = $("#sliders_update_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.tax == 1) {
              let object = $("#tax_update_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.services == 1) {
              let object = $("#services_update_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.promo_code == 1) {
              let object = $("#promo_code_update_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.featured_section == 1) {
              let object = $("#featured_section_update_edit")[0];
              $(object).attr("checked", true);
            } //
            if (
              single_object.partner == 1 ||
              single_object.partner != undefined
            ) {
              let object = $("#partner_update_edit")[0];
              $(object).attr("checked", true);
            }
            if (
              single_object.customer != undefined &&
              single_object.customer == 1
            ) {
              let object = $("#customers_update_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.notification == 1) {
              let object = $("#send_notification_update_edit")[0];
              $(object).attr("checked", true);
            } //
            if (single_object.faq == 1) {
              let object = $("#faq_update_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.system == 1) {
              let object = $("#system_update_update_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.settings == 1) {
              let object = $("#settings_update_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.system_user == 1) {
              let object = $("#system_user_update_edit")[0];
              $(object).attr("checked", true);
            }
          });
        } else if (key == "delete") {
          Object.keys(single_object).forEach((val) => {
            if (single_object.order == 0) {
              let object = $("#orders_delete_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.category == 1) {
              let object = $("#categories_delete_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.subscription == 1) {
              let object = $("#subscription_delete_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.sliders == 1) {
              let object = $("#sliders_delete_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.tax == 1) {
              let object = $("#tax_delete_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.services == 1) {
              let object = $("#services_delete_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.promo_code == 1) {
              let object = $("#promo_code_delete_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.featured_section == 1) {
              let object = $("#featured_section_delete_edit")[0];
              $(object).attr("checked", true);
            } //
            if (
              single_object.partner == 1 ||
              single_object.partner != undefined
            ) {
              let object = $("#partner_delete_edit")[0];
              $(object).attr("checked", true);
            }
            if (
              single_object.customer != undefined &&
              single_object.customer == 1
            ) {
              let object = $("#customers_delete_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.notification == 1) {
              let object = $("#send_notification_delete_edit")[0];
              $(object).attr("checked", true);
            } //
            if (single_object.faq == 1) {
              let object = $("#faq_delete_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.system == 1) {
              let object = $("#system_update_delete_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.settings == 1) {
              let object = $("#settings_delete_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.system_user == 1) {
              let object = $("#system_user_delete_edit")[0];
              $(object).attr("checked", true);
            }
          });
        }
      });
    }
  },
};
$("#type_1").change(function () {
  if ($("#type_1").val() == "provider") {
    $("#categories_select_1").hide();
    $("#services_select_1").show();
    $("#edit_url_section").hide();
  } else if ($("#type_1").val() == "Category") {
    $("#categories_select_1").show();
    $("#services_select_1").hide();
    $("#edit_url_section").hide();
  } else if ($("#type_1").val() == "url") {
    $("#categories_select_1").hide();
    $("#services_select_1").hide();
    $("#edit_url_section").show();
  } else {
    $("#categories_select_1").hide();
    $("#services_select_1").hide();
    $("#edit_url_section").hide();
  }
});
let source = "";
window.slider_events = {
  "click .edite-slider": function (e, value, row, index) {
  
    $("#id").val(row.id);
    $("#type_1").val(row.type);
    if (row.type == "provider") {
      $("#service_item_1").val(row.type_id).trigger("change");
    }
    if (row.type == "Category") {
      $("#Category_item_1").val(row.type_id).trigger("change");
    }
    if (row.type == "url") {
      $("#edit_slider_url").val(row.url);
    }
    var regex = /<img.*?src="(.*?)"/;
    var app_image_src = regex.exec(row.slider_app_image)[1];
    var web_image_src = regex.exec(row.slider_web_image)[1];
    source = app_image_src;
    $("#id").val(row.id);
    $("#offer_image").attr("src", app_image_src);
    $("#offer_web_image").attr("src", web_image_src);
    setTimeout(function () {
      if (row.og_status == "1") {
        $(".editInModel").prop("checked", false).trigger("click");
      } else {
        $(".editInModel").prop("checked", true).trigger("click");
      }
    }, 600);
    $("#categories_select_1").hide();
    $("#services_select_1").hide();
    $("#edit_url_section").hide();
    $("#type_1").val(row.type).trigger("change");
  },
  "click .delete-slider": function (e, value, row, index) {
    var users_id = row.id;
    Swal.fire({
      title: are_your_sure,
      text: you_wont_be_able_to_revert_this,
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      if (result.isConfirmed) {
        $.post(
          baseUrl + "/admin/sliders/delete_sliders",
          {
            [csrfName]: csrfHash,
            user_id: users_id,
          },
          function (data) {
            csrfName = data.csrfName;
            csrfHash = data.csrfHash;
            if (data.error == false) {
              showToastMessage(data.message, "success");
              setTimeout(() => {
                $("#slider_list").bootstrapTable("refresh");
              }, 2000);
              return;
            } else {
              return showToastMessage(data.message, "error");
            }
          }
        );
      }
    });
  },
};
$(document).ready(function () {
  $("#edit_section_type").change(function (e) {
    e.preventDefault();
    const sections = {
      partners: ".edit_partners_ids",
      categories: ".edit_category_item",
      top_rated_partner: ".edit_top_rated_providers",
      previous_order: ".edit_previous_order",
      ongoing_order: ".edit_ongoing_order",
      near_by_provider: ".edit_near_by_providers",
      banner: ".edit_banner_section",
    };
    // Get the selected value from the dropdown
    const selectedSection = $(this).val();
    // Hide all sections
    $(
      ".edit_category_item, .edit_partners_ids, .edit_top_rated_providers, .edit_previous_order, .edit_ongoing_order, .edit_near_by_providers,.edit_banner_section"
    ).addClass("d-none");
    if (sections[selectedSection]) {
      $(sections[selectedSection]).removeClass("d-none");
    }
  });
  $(
    "#edit_banner_providers_select,#edit_banner_categories_select,#edit_banner_url_section"
  ).hide();
  $("#edit_banner_type").on("change", function () {
    if ($("#edit_banner_type").val() == "banner_default") {
      $("#edit_banner_providers_select").hide();
      $("#edit_banner_categories_select").hide();
      $("#edit_banner_url_section").hide();
    }
    if ($("#edit_banner_type").val() == "banner_provider") {
      $("#edit_banner_providers_select").show();
      $("#edit_banner_categories_select").hide();
      $("#edit_banner_url_section").hide();
    } else if ($("#edit_banner_type").val() == "banner_category") {
      $("#edit_banner_providers_select").hide();
      $("#edit_banner_categories_select").show();
      $("#edit_banner_url_section").hide();
    } else if ($("#edit_banner_type").val() == "banner_url") {
      $("#edit_banner_providers_select").hide();
      $("#edit_banner_categories_select").hide();
      $("#edit_banner_url_section").show();
    } else {
      $("#edit_banner_providers_select").hide();
      $("#edit_banner_categories_select").hide();
      $("#edit_banner_url_section").hide();
    }
  });
});
window.featured_section_events = {
  "click .delete-featured_section": function (e, value, row, index) {
    var id = row.id;
    Swal.fire({
      title: are_your_sure,
      text: you_wont_be_able_to_revert_this,
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      if (result.isConfirmed) {
        $.post(
          baseUrl + "/admin/featured_sections/delete_featured_section",
          {
            [csrfName]: csrfHash,
            id: id,
          },
          function (data) {
            csrfName = data.csrfName;
            csrfHash = data.csrfHash;
            if (data.error == false) {
              showToastMessage(data.message, "success");
              setTimeout(() => {
                $("#user_list").bootstrapTable("refresh");
              }, 2000);
              return;
            } else {
              return showToastMessage(data.message, "error");
            }
          }
        );
      }
    });
  },
  "click .update_featured_section": function (e, value, row, index) {


    console.log(row.description);
    var regex = /<img.*?src="(.*?)"/;
     var app_image_src = row.app_banner_image && regex.exec(row.app_banner_image);
     if (app_image_src) {
         $("#preview_app_image").attr("src", app_image_src[1]);
     } else {
         $("#preview_app_image").attr("src", ""); // Or set to a default image
     }
 
     // Check if web_banner_image exists and matches the regex
     var web_image_src = row.web_banner_image && regex.exec(row.web_banner_image);
     if (web_image_src) {
         $("#preview_banner_image").attr("src", web_image_src[1]);
     } else {
         $("#preview_banner_image").attr("src", ""); // Or set to a default image
     }
 
    var category, partner, i, previous_order;
    $("#id").val(row.id);
    $("#edit_title").val(row.title);
    $("#edit_description").val(row.description);
    if (row.status == "1") {
      $("#edit_status_active").prop("checked", true);
    } else {
      $("#edit_status_deactive").prop("checked", true);
    }
    $("#edit_section_type").val(row.section_type).trigger("change");
    const sections = {
      partners: ".partners_ids",
      categories: ".Category_item",
      top_rated_partner: ".top_rated_providers",
      previous_order: ".previous_order",
      ongoing_order: ".ongoing_order",
      near_by_provider: ".near_by_providers",
      banner: ".edit_banner_section",
    };
    const selectedSection = $("#edit_section_type").val();
    $(
      ".Category_item, .partners_ids, .top_rated_providers, .previous_order, .ongoing_order, .near_by_providers,.edit_banner_section"
    ).addClass("d-none");
    if (sections[selectedSection]) {
      $(sections[selectedSection]).removeClass("d-none");
    }
    setTimeout(function () {
      if (row.status == "1") {
        $(".editInModel").prop("checked", false).trigger("click");
      } else {
        $(".editInModel").prop("checked", true).trigger("click");
      }
    }, 600);

    console.log(row.section_type);
    
    if (row.section_type == "categories") {
      category = row.category_ids.split(",");
      var value_given = row.category_ids.split(",");
      $(document).ready(function () {
        $("#edit_Category_item").val(row.category_ids.split(",")).select2({
          placeholder: "Select Categories",
        });
      });
    } else if (row.section_type == "previous_order") {
      $("#edit_previoud_order_limit").val(row.limit);
    } else if (row.section_type == "ongoing_order") {
      $("#edit_ongoing_order_limit").val(row.limit);
    } else if (row.section_type == "near_by_provider") {
      $("#edit_limit_for_near_by_providers").val(row.limit);
    }
    else if (row.section_type == "top_rated_partner") {
      $("#edit_top_rated_providers").val(row.limit);
    }  
    else if (row.section_type == "banner") {
      $("#edit_title").val();
      $(".edit_title").hide();
      $("#edit_banner_type").val(row.banner_type).trigger("change");
      if (row.banner_type == "banner_default") {
        $("#edit_banner_categories_select").hide();
        $("#edit_banner_providers_select").hide();
        $("#edit_banner_url_section").hide();
      } else if (row.banner_type == "banner_category") {
        $("#edit_banner_categories_select").show();
        $("#edit_category_item")
          .val(row.category_ids.split(","))
          .select2({ placeholder: "Select Categories" });
        $("#edit_banner_providers_select").hide();
        $("#edit_banner_url_section").hide();
      } else if (row.banner_type == "banner_provider") {
        $("#edit_banner_providers_select").show();
        $("#edit_banner_providers")
          .val(row.partners_ids.split(","))
          .select2({ placeholder: "Select Provider" });
        $("#edit_banner_categories_select").hide();
        $("#edit_banner_url_section").hide();
      } else if (row.banner_type == "banner_url") {
        $("#edit_banner_categories_select").hide();
        $("#edit_banner_providers_select").hide();
        $("#edit_banner_url_section").show();
        $("#edit_banner_url").val(row.banner_url);
      }
    } else {
      if (row.partners_ids != null) {
        partner = row.partners_ids.split(",");
        parseInt(row.partners_ids);
      }
      $(document).ready(function () {
        $("#edit_partners_ids").val(partner).select2({
          placeholder: "Select Providers",
        });
      });
    }
  },
};
// $(document).ready(function () {
//   $("#edit_section_type").on("change", function () {
//     if ($(this).val() == "categories") {
//       $(".edit_category_item").removeClass("d-none");
//       $(".edit_partners_ids").addClass("d-none");
//       $(".edit_previous_order").addClass("d-none");
//       $(".edit_ongoing_order").addClass("d-none");
//     } else if ($(this).val() == "partner" || $(this).val() == "partners") {
//       $(".edit_category_item").addClass("d-none");
//       $(".edit_partners_ids").removeClass("d-none");
//       $(".edit_previous_order").addClass("d-none");
//       $(".edit_ongoing_order").addClass("d-none");
//     } else if ($(this).val() == "top_rated_partner" || $(this).val() == "top_rated_service") {
//       $(".edit_partners_ids").addClass("d-none");
//       $(".edit_category_item").addClass("d-none");
//       $(".edit_previous_order").addClass("d-none");
//       $(".edit_ongoing_order").addClass("d-none");
//     } else if ($(this).val() == "previous_order") {
//       $(".edit_partners_ids").addClass("d-none");
//       $(".edit_category_item").addClass("d-none");
//       $(".edit_previous_order").removeClass("d-none");
//       $(".edit_ongoing_order").addClass("d-none");
//     } else if ($(this).val() == "ongoing_order") {
//       $(".edit_partners_ids").addClass("d-none");
//       $(".edit_category_item").addClass("d-none");
//       $(".edit_previous_order").addClass("d-none");
//       $(".edit_ongoing_order").removeClass("d-none");
//     } else {
//       $(".edit_partners_ids").addClass("d-none");
//       $(".edit_category_item").addClass("d-none");
//       $(".edit_previous_order").addClass("d-none");
//       $(".edit_ongoing_order").addClass("d-none");
//     }
//   });
// });
window.promo_codes_events = {
  "click .delete-promo_codes": function (e, value, row, index) {
    var id = row.id;
    Swal.fire({
      title: are_your_sure,
      text: you_wont_be_able_to_revert_this,
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      if (result.isConfirmed) {
        var input_body = {
          [csrfName]: csrfHash,
          id: id,
        };
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/promo_codes/delete",
          data: input_body,
          dataType: "json",
          success: function (response) {
            csrfName = response["csrfName"];
            csrfHash = response["csrfHash"];
            if (response.error == false) {
              showToastMessage(response.message, "success");
              setTimeout(() => {
                $("#promo_code_list").bootstrapTable("refresh");
              }, 2000);
            } else {
              setTimeout(() => {
                $("#promo_code_list").bootstrapTable("refresh");
              }, 2000);
              return showToastMessage(response.message, "error");
            }
          },
        });
      }
    });
  },
  "click .edit": function (e, value, row, index) {
    $("#image_edit").html("");
    e.preventDefault();
    var img = row.image;
    $('input[name="promo_id"]').val(row.id);
    $('input[name="promo_code"]').val(row.promo_code);
    $("#partner").val(row.partner_id);
    $('input[name="start_date"]').val(row.start_date);
    $('input[name="end_date"]').val(row.end_date);
    $('textarea[name="message"]').val(row.message);
    $('input[name="discount"]').val(row.discount);
    $('input[name="max_discount_amount"]').val(row.max_discount_amount);
    $('input[name="minimum_order_amount"]').val(row.minimum_order_amount);
    $("#discount_type").val(row.discount_type).trigger("change");
    setTimeout(function () {
      if (row.status == "Active") {
        $(".editInModel").prop("checked", false).trigger("click");
      } else {
        $(".editInModel").prop("checked", true).trigger("click");
      }
      if (row.repeat_usage == 1) {
        $("#repeat_usage").prop("checked", false).trigger("click");
        $(".repeat_usage").show();
        $('input[name="no_of_repeat_usage"]').val(row.no_of_repeat_usage);
      } else {
        $("#repeat_usage").prop("checked", true).trigger("click");
        $(".repeat_usage").hide();
      }
    }, 600);
    $('input[name="no_of_users"]').val(row.no_of_users);
    $("#image_edit").append(img);
  },
};
window.services_events_admin = {
  "click .delete": function (e, value, row, index) {
    var id = row.id;
    Swal.fire({
      title: are_your_sure,
      text: you_wont_be_able_to_revert_this,
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      if (result.isConfirmed) {
        var input_body = {
          [csrfName]: csrfHash,
          id: id,
        };
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/services/delete_service",
          data: input_body,
          dataType: "json",
          success: function (response) {
            csrfName = response["csrfName"];
            csrfHash = response["csrfHash"];
            if (response.error == false) {
              showToastMessage(response.message, "success");
              setTimeout(() => {
                $("#service_list").bootstrapTable("refresh");
              }, 2000);
            } else {
              showToastMessage(response.message, "error");
              setTimeout(() => {
                $("#service_list").bootstrapTable("refresh");
              }, 2000);
            }
          },
        });
      }
    });
  },
  "click .edit": function (e, value, row, index) {
    $("#service_id").val(row.id);
    $("#edit_partner").val(row.user_id);
    $("#edit_title").val(row.title);
    $("#edit_category_item").val(row.category_id).trigger("change");
    $("#edit_service_tags").val(row.tags);
    $("#edit_tax_type").val(row.tax_type.trim());
    $("#edit_tax").val(row.tax_id);
    if (row.status_number == "1") {
      $("#edit_status_active").prop("checked", true);
    } else {
      $("#edit_status_deactive").prop("checked", true);
    }
    var regex = /<img.*?src="(.*?)"/;
    if (
      row.image_of_the_service != null &&
      row.image_of_the_service != "nothing found"
    ) {
      var src = regex.exec(row.image_of_the_service)[1];
      source = src;
      $("#edit_service_image").attr("src", source);
    }
    $("#edit_price").val(row.price);
    $("#edit_discounted_price").val(row.discounted_price);
    if (row.on_site_allowed == "Allowed" || row.on_site_allowed == "allowed") {
      $("#edit_on_site").attr("checked", true);
    }
    if (
      row.is_pay_later_allowed == "Allowed" ||
      row.is_pay_later_allowed == "allowed" ||
      row.is_pay_later_allowed == "1"
    ) {
      $("#edit_pay_later").attr("checked", true);
    } else {
      $("#edit_pay_later").attr("checked", false);
    }
    if (row.cancelable == "1" || row.cancelable == "1") {
      $("#edit_is_cancelable").prop("checked", true);
      $("#edit_cancelable_till_value").val(row.cancelable_till);
    } else {
      $("#edit_is_cancelable").prop("checked", false);
      $("#edit_cancelable_till").hide();
      $("#edit_cancelable_till_value").val("empty");
    }
    if (row.cancelable == "1" || row.cancelable == "1") {
      $("#edit_is_cancelable").prop("checked", true);
      $("#edit_cancelable_till_value").val(row.cancelable_till);
    } else {
      $("#edit_is_cancelable").prop("checked", false);
      $("#edit_cancelable_till").hide();
      $("#edit_cancelable_till_value").val("empty");
    }
    $("#edit_members").val(row.number_of_members_required);
    $("#edit_duration").val(row.duration);
    $("#edit_max_qty").val(row.max_quantity_allowed);
    $("#edit_description").text(row.description);
    //
  },
  "click .disapprove_service": function (e, value, row, index) {
    var partner_id = row.user_id;
    var service_id = row.id;
    Swal.fire({
      title: are_your_sure,
      text: "Are you sure you want to disapprove this service",
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      var input_body = {
        [csrfName]: csrfHash,
        partner_id: partner_id,
        service_id: service_id,
      };
      if (result.isConfirmed) {
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/services/disapprove_service",
          data: input_body,
          dataType: "json",
          success: function (response) {
            if (response.error == false) {
              showToastMessage(response.message, "success");
              $("#service_list").bootstrapTable("refresh");
            } else {
              return showToastMessage(response.message, "error");
            }
          },
        });
      }
    });
  },
  "click .approve_service": function (e, value, row, index) {
    var partner_id = row.user_id;
    var service_id = row.id;
    Swal.fire({
      title: are_your_sure,
      text: "Are you sure you want to approve this service",
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      var input_body = {
        [csrfName]: csrfHash,
        partner_id: partner_id,
        service_id: service_id,
      };
      if (result.isConfirmed) {
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/services/approve_service",
          data: input_body,
          dataType: "json",
          success: function (response) {
            if (response.error == false) {
              showToastMessage(response.message, "success");
              $("#service_list").bootstrapTable("refresh");
            } else {
              return showToastMessage(response.message, "error");
            }
          },
        });
      }
    });
  },
  "click .clone_service": function (e, value, row, index) {
    var partner_id = row.user_id;
    var service_id = row.id;
    Swal.fire({
      title: are_your_sure,
      text: "Are you sure you want to clone this service",
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      var input_body = {
        [csrfName]: csrfHash,
        partner_id: partner_id,
        service_id: service_id,
      };
      if (result.isConfirmed) {
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/services/clone_service",
          data: input_body,
          dataType: "json",
          success: function (response) {
            if (response.error == false) {
              showToastMessage(response.message, "success");
              $("#service_list").bootstrapTable("refresh");
            } else {
              return showToastMessage(response.message, "error");
            }
          },
        });
      }
    });
  },
};
window.subscription_events_admin = {
  "click .delete": function (e, value, row, index) {
    var id = row.id;
    Swal.fire({
      title: are_your_sure,
      text: you_wont_be_able_to_revert_this,
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      if (result.isConfirmed) {
        var input_body = {
          [csrfName]: csrfHash,
          id: id,
        };
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/subscription/delete_subscription",
          data: input_body,
          dataType: "json",
          success: function (response) {
            csrfName = response["csrfName"];
            csrfHash = response["csrfHash"];
            if (response.error == false) {
              showToastMessage(response.message, "success");
              setTimeout(() => {
                $("#subscription_list").bootstrapTable("refresh");
              }, 2000);
            } else {
              showToastMessage(response.message, "error");
              setTimeout(() => {
                $("#subscription_list").bootstrapTable("refresh");
              }, 2000);
            }
          },
        });
      }
    });
  },
};
function loadFile(event) {
  var image = document.getElementById("edit_service_image");
  image.src = URL.createObjectURL(event.target.files[0]);
}
window.email_template_actions_events = {
  "click .delete-email-template": function (e, value, row, index) {
    var id = row.id;
    Swal.fire({
      title: are_your_sure,
      text: you_wont_be_able_to_revert_this,
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      if (result.isConfirmed) {
        var input_body = {
          [csrfName]: csrfHash,
          id: id,
        };
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/settings/delete_email_template",
          data: input_body,
          dataType: "json",
          success: function (response) {
            csrfName = response["csrfName"];
            csrfHash = response["csrfHash"];
            if (response.error == false) {
              showToastMessage(response.message, "success");
              setTimeout(() => {
                $("#category_list").bootstrapTable("refresh");
              }, 2000);
            } else {
              showToastMessage(response.message, "error");
              setTimeout(() => {
                $("#category_list").bootstrapTable("refresh");
              }, 2000);
            }
          },
        });
      }
    });
  },
};
window.system_user_events = {
  "click .deactivate-user": function (e, value, row, index) {
    var user_id = row.id;
    // return;
    Swal.fire({
      title: are_your_sure,
      text: are_you_sure_you_want_to_deactivate_this_user,
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      var input_body = {
        [csrfName]: csrfHash,
        user_id: user_id,
      };
      if (result.isConfirmed) {
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/system_users/deactivate_user",
          data: input_body,
          dataType: "json",
          timeout: 5000,
          success: function (response) {
            if (response.error == false) {
              showToastMessage(response.message, "success");
              setTimeout(() => {
                $("#partner_list").bootstrapTable("refresh");
              }, 5000);
              return;
            } else {
              return showToastMessage(response.message, "error");
            }
          },
        });
      }
    });
  },
  "click .activate-user": function (e, value, row, index) {
    var user_id = row.id;
    Swal.fire({
      title: are_your_sure,
      text: are_you_sure_you_want_to_activate_this_user,
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      var input_body = {
        [csrfName]: csrfHash,
        user_id: user_id,
      };
      if (result.isConfirmed) {
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/system_users/activate_user",
          data: input_body,
          dataType: "json",
          success: function (response) {
            if (response.error == false) {
              showToastMessage(response.message, "success");
              setTimeout(() => {
                $("#partner_list").bootstrapTable("refresh");
              }, 2000);
              window.location.reload();
              return;
            } else {
              window.location.reload();
              return showToastMessage(response.message, "error");
            }
          },
        });
      }
    });
  },
  "click .delete-user": function (e, value, row, index) {
    e.preventDefault();
    var user_id = row.id;
    Swal.fire({
      title: are_your_sure,
      text: are_you_sure_you_want_to_delete_this_user,
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      var input_body = {
        [csrfName]: csrfHash,
        user_id: user_id,
      };
      if (result.isConfirmed) {
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/system_users/delete_user",
          data: input_body,
          dataType: "json",
          success: function (response) {
            if (response.error == false) {
              showToastMessage(response.message, "success");
              setTimeout(() => {
                $("#partner_list").bootstrapTable("refresh");
              }, 2000);
              window.location.reload();
              return;
            } else {
              return showToastMessage(response.message, "error");
            }
          },
        });
      }
    });
  },
  "click .edit-user": function (e, value, row, index) {
    $("#id").val(row.id);
    if (row.role_a == "1") {
      $("#edit_role").val("1");
    } else if (row.role_a == "2") {
      $("#edit_role").val("2");
    } else if (row.role_a == "3") {
      $("#edit_role").val("3");
    }
    $(document).ready(function () {
      if ($("#edit_role").val() == 1) {
        $("#permissions").hide();
      } else {
        $("#permissions").show();
      }
    });
    var permissions = JSON.parse(row.permissions);
    var data = permissions != null ? true : false;
    if (data) {
      Object.keys(permissions).forEach((key) => {
        let single_object = permissions[key];
        if (key == "create") {
          Object.keys(single_object).forEach((key) => {
            if (single_object[key] == 1) {
              $("#" + key + "_create_edit").attr("checked", true);
            } else {
              $("#" + key + "_create_edit").attr("checked", false);
            }
          });
        } else if (key == "read") {
          Object.keys(single_object).forEach((key) => {
            if (single_object[key] == 1) {
              $("#" + key + "_read_edit").attr("checked", true);
            } else {
              $("#" + key + "_read_edit").attr("checked", false);
            }
          });
        } else if (key == "update") {
          Object.keys(single_object).forEach((key) => {
            if (single_object[key] == 1) {
              $("#" + key + "_update_edit").attr("checked", true);
            } else {
              $("#" + key + "_update_edit").attr("checked", false);
            }
          });
        } else if (key == "delete") {
          Object.keys(single_object).forEach((key) => {
            if (single_object[key] == 1) {
              $("#" + key + "_delete_edit").attr("checked", true);
            } else {
              $("#" + key + "_delete_edit").attr("checked", false);
            }
          });
        }
      });
    }
  },
};
function set_attribute_checked(ids) {
  for (let i = 0; i < Object.keys(ids).length; i++) {
    const element = ids[i];
    $(element[0]).attr("checked", true);
  }
}
$("#permissions").show();
$(document).ready(function () {
  $("#role").on("change", function (e) {
    let role = $(this).val();
    if (role == "1") {
      $("#permissions").hide();
    } else {
      $("#permissions").show();
    }
  });
});
$("#edit_role").on("change", function (e) {
  let role = $(this).val();
  if (role == "1") {
    $("#permissions").hide();
  } else {
    $("#permissions").show();
  }
});
window.commission_events = {
  "click .pay-out": function (e, value, row, index) {
    $("#partner_id").val(row.partner_id);
  },
};
window.notification_event = {
  "click .delete-notification": function (e, value, row, index) {
    var users_id = row.id;
    Swal.fire({
      title: are_your_sure,
      text: you_wont_be_able_to_revert_this,
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      if (result.isConfirmed) {
        $.post(
          baseUrl + "/admin/notification/delete_notification",
          {
            [csrfName]: csrfHash,
            user_id: users_id,
          },
          function (data) {
            csrfName = data.csrfName;
            csrfHash = data.csrfHash;
            if (data.error == false) {
              showToastMessage(data.message, "success");
              setTimeout(() => {
                $("#user_list").bootstrapTable("refresh");
              }, 2000);
              return;
            } else {
              return showToastMessage(data.message, "error");
            }
          }
        );
      }
    });
  },
};
window.partner_events = {
  "click .deactivate_partner": function (e, value, row, index) {
    var id = row.partner_id;
    Swal.fire({
      title: are_your_sure,
      text: "Are you sure you want to deactivate this provider",
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      var input_body = {
        [csrfName]: csrfHash,
        partner_id: id,
      };
      if (result.isConfirmed) {
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/partner/deactivate_partner",
          data: input_body,
          dataType: "json",
          success: function (response) {
            if (response.error == false) {
              showToastMessage(response.message, "success");
              setTimeout(() => {
                $("#partner_list").bootstrapTable("refresh");
              }, 2000);
              window.location.reload();
              return;
            } else {
              window.location.reload();
              return showToastMessage(response.message, "error");
            }
          },
        });
      }
    });
  },
  "click .activate_partner": function (e, value, row, index) {
    var id = row.partner_id;
    Swal.fire({
      title: are_your_sure,
      text: "Are you sure you want to activate this provider",
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      var input_body = {
        [csrfName]: csrfHash,
        partner_id: id,
      };
      if (result.isConfirmed) {
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/partner/activate_partner",
          data: input_body,
          dataType: "json",
          success: function (response) {
            if (response.error == false) {
              showToastMessage(response.message, "success");
              setTimeout(() => {
                $("#partner_list").bootstrapTable("refresh");
              }, 2000);
              window.location.reload();
              return;
            } else {
              window.location.reload();
              return showToastMessage(response.message, "error");
            }
          },
        });
      }
    });
  },
  "click .approve_partner": function (e, value, row, index) {
    ``;
    var id = row.partner_id;
    Swal.fire({
      title: are_your_sure,
      text: "Are you sure you want to approve this provider",
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      var input_body = {
        [csrfName]: csrfHash,
        partner_id: id,
      };
      if (result.isConfirmed) {
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/partner/approve_partner",
          data: input_body,
          dataType: "json",
          success: function (response) {
            if (response.error == false) {
              showToastMessage(response.message, "success");
              $("#partner_list").bootstrapTable("refresh");
            } else {
              showToastMessage(response.message, "error");
            }
          },
        });
      }
    });
  },
  "click .disapprove_partner": function (e, value, row, index) {
    var id = row.partner_id;
    Swal.fire({
      title: are_your_sure,
      text: "Are you sure you want to disapprove this provider",
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      var input_body = {
        [csrfName]: csrfHash,
        partner_id: id,
      };
      if (result.isConfirmed) {
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/partner/disapprove_partner",
          data: input_body,
          dataType: "json",
          success: function (response) {
            if (response.error == false) {
              showToastMessage(response.message, "success");
              $("#partner_list").bootstrapTable("refresh");
              return;
            } else {
              return showToastMessage(response.message, "error");
            }
          },
        });
      }
    });
  },
  "click .delete_partner": function (e, value, row, index) {
    var id = row.partner_id;
    Swal.fire({
      title: are_your_sure,
      text: "Are you sure you want to delete this provider",
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      var input_body = {
        [csrfName]: csrfHash,
        partner_id: id,
      };
      if (result.isConfirmed) {
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/partner/delete_partner",
          data: input_body,
          dataType: "json",
          success: function (response) {
            if (response.error == false) {
              showToastMessage(response.message, "success");
              setTimeout(() => {
                $("#partner_list").bootstrapTable("refresh");
              }, 2000);
              window.location.reload();
              return;
            } else {
              window.location.reload();
              return showToastMessage(response.message, "error");
            }
          },
        });
      }
    });
  },
  "click .view_rating": function (e, value, row, index) {
    $("#partner_id").val(row.partner_id);
    var id = row.partner_id;
    $("#rating_table").bootstrapTable("refresh", {
      url: baseUrl + "/admin/partners/view_ratings/" + id,
    });
  },
  "click .edit": function (e, value, row, index) {
    $("#company_name").val(row.company_name);
    if (row.type == "Individual") {
      $("#type").val(0);
    } else {
      $("#type").val(1);
    }
    $("#partner_id").val(row.partner_id);
    $("#about").val(row.about);
    $("#visiting_charges").val(row.visiting_charges);
    $("#advance_booking_days").val(row.advance_booking_days);
    $("#number_of_members").val(row.number_of_members);
    $("#city").val(row.city);
    $("#partner_latitude").val(row.latitude);
    $("#partner_longitude").val(row.longitude);
    $("#address").val(row.address);
    $("#username").val(row.partner_name);
    $("#email").val(row.email);
    $("#phone").val(row.mobile);
    $("#admin_commission").val(row.admin_commission);
    $("#tax_name").val(row.tax_name);
    $("#tax_number").val(row.tax_number);
    $("#account_number").val(row.account_number);
    $("#account_name").val(row.account_name);
    $("#bank_code").val(row.bank_code);
    $("#bank_name").val(row.bank_name);
    $("#swift_code").val(row.swift_code);
    $("#image_preview").attr("src", row.image);
    $("#banner_image_preview").attr("src", row.banner_edit);
    $("#national_id_preview").attr("src", row.national_id);
    $("#passport_preview").attr("src", row.passport);
    $("#address_id_preview").attr("src", row.address_id);
    if (row.is_approved_edit == "1") {
      $("#is_approved_partner").prop("checked", true);
    } else {
      $("#is_disapproved_partner").prop("checked", true);
    }
    if (row.monday_is_open == 1) {
      $("#monday_opening_time").val(row.monday_opening_time);
      $("#monday_closing_time").val(row.monday_closing_time);
      $("#monday").prop("checked", true);
      $("#monday_opening_time").removeAttr("readOnly");
      $("#monday_closing_time").removeAttr("readOnly");
    } else {
      $("#monday_opening_time").val();
      $("#monday_closing_time").val();
      $("#monday").prop("checked", false);
      $("#monday_opening_time").attr("readOnly", "readOnly");
      $("#monday_closing_time").attr("readOnly", "readOnly");
    }
    if (row.tuesday_is_open == 1) {
      $("#tuesday_opening_time").val(row.tuesday_opening_time);
      $("#tuesday_closing_time").val(row.tuesday_closing_time);
      $("#tuesday").prop("checked", true);
      $("#tuesday_opening_time").removeAttr("readOnly");
      $("#tuesday_closing_time").removeAttr("readOnly");
    } else {
      $("#tuesday_opening_time").val();
      $("#tuesday_closing_time").val();
      $("#tuesday").prop("checked", false);
      $("#tuesday_opening_time").attr("readOnly", "readOnly");
      $("#tuesday_closing_time").attr("readOnly", "readOnly");
    }
    if (row.wednesday_is_open == 1) {
      $("#wednesday_opening_time").val(row.wednesday_opening_time);
      $("#wednesday_closing_time").val(row.wednesday_closing_time);
      $("#wednesday").prop("checked", true);
      $("#wednesday_opening_time").removeAttr("readOnly");
      $("#wednesday_closing_time").removeAttr("readOnly");
    } else {
      $("#wednesday_opening_time").val();
      $("#wednesday_closing_time").val();
      $("#wednesday").prop("checked", false);
      $("#wednesday_opening_time").attr("readOnly", "readOnly");
      $("#wednesday_closing_time").attr("readOnly", "readOnly");
    }
    if (row.thursday_is_open == 1) {
      $("#thursday_opening_time").val(row.thursday_opening_time);
      $("#thursday_closing_time").val(row.thursday_closing_time);
      $("#thursday").prop("checked", true);
      $("#thursday_opening_time").removeAttr("readOnly");
      $("#thursday_closing_time").removeAttr("readOnly");
    } else {
      $("#thursday_opening_time").val();
      $("#thursday_closing_time").val();
      $("#thursday").prop("checked", false);
      $("#thursday_opening_time").attr("readOnly", "readOnly");
      $("#thursday_closing_time").attr("readOnly", "readOnly");
    }
    if (row.friday_is_open == 1) {
      $("#friday_opening_time").val(row.friday_opening_time);
      $("#friday_closing_time").val(row.friday_closing_time);
      $("#friday").prop("checked", true);
      $("#friday_opening_time").removeAttr("readOnly");
      $("#friday_closing_time").removeAttr("readOnly");
    } else {
      $("#friday_opening_time").val();
      $("#friday_closing_time").val();
      $("#friday").prop("checked", false);
      $("#friday_opening_time").attr("readOnly", "readOnly");
      $("#friday_closing_time").attr("readOnly", "readOnly");
    }
    if (row.saturday_is_open == 1) {
      $("#saturday_opening_time").val(row.saturday_opening_time);
      $("#saturday_closing_time").val(row.saturday_closing_time);
      $("#saturday").prop("checked", true);
      $("#saturday_opening_time").removeAttr("readOnly");
      $("#saturday_closing_time").removeAttr("readOnly");
    } else {
      $("#saturday_opening_time").val();
      $("#saturday_closing_time").val();
      $("#saturday").prop("checked", false);
      $("#saturday_opening_time").attr("readOnly", "readOnly");
      $("#saturday_closing_time").attr("readOnly", "readOnly");
    }
    if (row.sunday_is_open == 1) {
      $("#sunday_opening_time").val(row.sunday_opening_time);
      $("#sunday_closing_time").val(row.sunday_closing_time);
      $("#sunday").prop("checked", true);
      $("#sunday_opening_time").removeAttr("readOnly");
      $("#sunday_closing_time").removeAttr("readOnly");
    } else {
      $("#sunday_opening_time").val();
      $("#sunday_closing_time").val();
      $("#sunday").prop("checked", false);
      $("#sunday_opening_time").attr("readOnly", "readOnly");
      $("#sunday_closing_time").attr("readOnly", "readOnly");
    }
    $("#number_of_members").attr("readOnly", "readOnly");
    $("#type").change(function () {
      var doc = document.getElementById("type");
      if (doc.options[doc.selectedIndex].value == 0) {
        $("#number_of_members").val("1");
        $("#number_of_members").attr("readOnly", "readOnly");
      } else if (doc.options[doc.selectedIndex].value == 1) {
        $("#number_of_members").val("");
        $("#number_of_members").removeAttr("readOnly");
      }
    });
  },
};
window.rating_event = {
  "click .delete_rating": function (e, value, row, index) {
    var id = row.id;
    Swal.fire({
      title: are_your_sure,
      text: "Are you sure you want to delete this rating",
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      var input_body = {
        [csrfName]: csrfHash,
        id: id,
      };
      if (result.isConfirmed) {
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/partners/delete_rating",
          data: input_body,
          dataType: "json",
          success: function (response) {
            if (response.error == false) {
              showToastMessage(response.message, "success");
              setTimeout(() => {
                $("#rating_table").bootstrapTable("refresh");
              }, 2000);
              window.location.reload();
              return;
            } else {
              window.location.reload();
              return showToastMessage(response.message, "error");
            }
          },
        });
      }
    });
  },
};
window.order_service_events = {
  "click .cancel_order": function (e, value, row, index) {
    var id = row.id;
    var service_id = row.service_id;
    Swal.fire({
      title: are_your_sure,
      text: "Are you sure you want to cancel this service",
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      var input_body = {
        [csrfName]: csrfHash,
        id: id,
        service_id: service_id,
      };
      if (result.isConfirmed) {
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/orders/cancel_order_service",
          data: input_body,
          dataType: "json",
          success: function (response) {
            if (response.error == false) {
              showToastMessage(response.message, "success");
              setTimeout(() => {
                $("#ordered_services_list").bootstrapTable("refresh");
              }, 2000);
            } else {
              setTimeout(() => {
                $("#ordered_services_list").bootstrapTable("refresh");
              }, 2000);
              return showToastMessage(response.message, "error");
            }
          },
        });
      }
    });
  },
};
function cancel_service(e) {
  var id = $(e).data("id");
  var service_id = $(e).data("service_id");
  Swal.fire({
    title: are_your_sure,
    text: "Are you sure you want to cancel this service",
    icon: "error",
    showCancelButton: true,
    confirmButtonText: yes_proceed,
  }).then((result) => {
    var input_body = {
      [csrfName]: csrfHash,
      id: id,
      service_id: service_id,
    };
    if (result.isConfirmed) {
      $.ajax({
        type: "POST",
        url: baseUrl + "/admin/orders/cancel_order_service",
        data: input_body,
        dataType: "json",
        success: function (response) {
          if (response.error == false) {
            showToastMessage(response.message, "success");
            setTimeout(() => {
              $("#ordered_services_list").bootstrapTable("refresh");
            }, 2000);
            window.location.reload();
            // return;
          } else {
            setTimeout(() => {
              $("#ordered_services_list").bootstrapTable("refresh");
            }, 2000);
            window.location.reload();
            return showToastMessage(response.message, "error");
          }
        },
      });
    }
  });
}
$(document).ready(function () {
  $("#available-slots").hide();
  $(".rescheduled_date").hide();
  $(".work_started_proof").hide();
  $(".work_completed_proof").hide();
  $(".booking_ended_additional_charge").hide();
  $("#status").change(function (e) {
    e.preventDefault();
    var status = $("#status").val();
    if (status === "rescheduled") {
      $("#available-slots").show();
      $(".rescheduled_date").show();
      $(".work_started_proof").hide();
      $(".work_completed_proof").hide();
      $(".booking_ended_additional_charge").hide();
    } else {
      $("#available-slots").hide();
      $(".rescheduled_date").hide();
      $(".work_started_proof").hide();
      $(".work_completed_proof").hide();
      $(".booking_ended_additional_charge").hide();
    }
    if (status == "started") {
      $(".work_started_proof").show();
    } else {
      $(".work_started_proof").hide();
    }
    // if (status == "completed") {
    // } else {
    //   $(".work_completed_proof").hide();
    // }
    if (status == "booking_ended") {
      $(".booking_ended_additional_charge").show();
      $(".work_completed_proof").show();
    } else {
      $(".booking_ended_additional_charge").hide();
      $(".work_completed_proof").hide();
    }
  });
  $("#rescheduled_date").change(function (e) {
    $("#available-slots").empty();
    var weekday = new Array(7);
    e.preventDefault();
    var date = $("#rescheduled_date").val();
    var d = new Date(date);
    var id = $("#order_id").val();
    var input_body = {
      [csrfName]: csrfHash,
      id: id,
      date: date,
    };
    $.ajax({
      type: "POST",
      url: baseUrl + "/admin/orders/get_slots",
      data: input_body,
      dataType: "JSON",
      success: function (response) {
        if (response.error == false) {
          var slots = response.available_slots;
          var slot_selector = "";
          if (slots == "") {
            slot_selector += `   <div class="col-md-12 form-group">
                                       <div class="selectgroup">
                                           <label class="selectgroup-item">
                                           <span class="text-danger">There is no slot available on this date!</span>
                                           </label>                                    
                                       </div>
                                   </div>
                                    `;
          } else {
            slots.forEach((element) => {
              slot_selector += `   <div class="col-md-2 form-group">
                <div class="selectgroup">
                    <label class="selectgroup-item">
                        <input type="radio" name="reschedule" value="${element}" class="selectgroup-input">
                        <span class="selectgroup-button selectgroup-button-icon">
                            <i class="fas fa-sun"></i> &nbsp; 
                            <div class="text-dark">${element}</div>
                        </span>
                    </label>                                    
                </div>
            </div>`;
            });
          }
          $("#available-slots").append(slot_selector);
        } else {
          var slot_selector = "";
          if (response.error == true) {
            slot_selector +=
              `   <div class="col-md-12 form-group">
                                       <div class="selectgroup">
                                           <label class="selectgroup-item">
                                               <span class="text-danger">` +
              response.message +
              `</span>
                                           </label>                                    
                                       </div>
                                   </div>
                                    `;
          }
          $("#available-slots").append(slot_selector);
          setTimeout(() => {
            $("#ordered_services_list").bootstrapTable("refresh");
          }, 2000);
        }
      },
    });
  });
  $("#change_status").on("click", function (e) {
    e.preventDefault();
    var status = $("#status").val();
    var order_id = $("#order_id").val();
    var date = $("#rescheduled_date").val();
    var is_otp_enable = $("#is_otp_enable").val();
    var payment_method = $("#payment_method").val();

    var selected_time = "";
    var formdata = new FormData($("#myForm")[0]);
    if ($(".selectgroup-input").length > 1) {
      selected_time = $('input[name="reschedule"]:checked').val();
    }
    if (is_otp_enable == 1) {
      if (status == "completed") {
        Swal.fire({
          title: are_your_sure,
          text: you_wont_be_able_to_revert_this,
          icon: "error",
          input: "number",
          inputPlaceholder: enter_otp_here,
          inputAttributes: {
            autocapitalize: "off",
            required: "true",
            
          },
          showCancelButton: true,
          cancelButtonText: cancel,
          confirmButtonText: yes_proceed,
        }).then((result) => {
          if (result.value) {
            formdata.append("otp", result.value);
            $.ajaxSetup({
              headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
              },
            });
            $.ajax({
              url: baseUrl + "/admin/orders/change_order_status",
              data: formdata,
              processData: false,
              contentType: false,
              type: "post",
              dataType: "json",
              beforeSend: function () {
                $("#change_status").attr("disabled", true);
                $("#change_status").removeClass("btn-primary");
                $("#change_status").addClass("btn-secondary");
                $("#change_status").html(
                  '<div class="spinner-border text-primary spinner-border-sm mx-3" role="status"><span class="visually-hidden"></span></div>'
                );
              },
              success: function (response) {
                if (response.error == false) {
                  showToastMessage(response.message, "success");
                  setTimeout(() => {
                    window.location.reload();
                  }, 3000);
                } else {
                  showToastMessage(response.message, "error");
                  setTimeout(() => {
                    window.location.reload();
                  }, 3000);
                }
                return;
              },
              error: function (response) {
                showToastMessage(response.message, "error");
                setTimeout(() => {
                  window.location.reload();
                }, 3000);
              },
            });
          }
        });
      } else {
        $.ajaxSetup({
          headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
          },
        });
        $.ajax({
          url: baseUrl + "/admin/orders/change_order_status",
          data: formdata,
          type: "post",
          dataType: "json",
          processData: false,
          contentType: false,
          beforeSend: function () {
            $("#change_status").attr("disabled", true);
            $("#change_status").removeClass("btn-primary");
            $("#change_status").addClass("btn-secondary");
            $("#change_status").html(
              '<div class="spinner-border text-primary spinner-border-sm mx-3" role="status"><span class="visually-hidden"></span></div>'
            );
          },
          success: function (response) {
            if (response.error == false) {
              showToastMessage(response.message, "success");
              setTimeout(() => {
                window.location.reload();
              }, 3000);
            } else {
              showToastMessage(response.message, "error");
              setTimeout(() => {
                window.location.reload();
              }, 3000);
            }
            return;
          },
          error: function (xhr) {
            showToastMessage(response.message, "error");
            setTimeout(() => {
              window.location.reload();
            }, 3000);
          },
        });
      }
    } else {
      $.ajaxSetup({
        headers: {
          "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
      });

      if (status == "completed") {
        if (payment_method == "cod") {
          Swal.fire({
            title: are_your_sure,
            text: "Make sure you have collected cash amount before completing the booking.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes!",
          }).then((result) => {
            if (result.isConfirmed) {
              $.ajax({
                url: baseUrl + "/admin/orders/change_order_status",
                data: formdata,
                processData: false,
                contentType: false,
                type: "post",
                dataType: "json",
                beforeSend: function () {
                  $("#change_status").attr("disabled", true);
                  $("#change_status").removeClass("btn-primary");
                  $("#change_status").addClass("btn-secondary");
                  $("#change_status").html(
                    '<div class="spinner-border text-primary spinner-border-sm mx-3" role="status"><span class="visually-hidden"></span></div>'
                  );
                },
                success: function (response) {
                  if (response.error == false) {
                    showToastMessage(response.message, "success");
                    setTimeout(() => {
                      window.location.reload();
                    }, 3000);
                  } else {
                    showToastMessage(response.message, "error");
                    setTimeout(() => {
                      window.location.reload();
                    }, 3000);
                  }
                  return;
                },
                error: function (response) {
                  showToastMessage(response.message, "error");
                  setTimeout(() => {
                    window.location.reload();
                  }, 3000);
                },
              });
            }
          });
        } else {
          $.ajax({
            url: baseUrl + "/admin/orders/change_order_status",
            data: formdata,
            processData: false,
            contentType: false,
            type: "post",
            dataType: "json",
            beforeSend: function () {
              $("#change_status").attr("disabled", true);
              $("#change_status").removeClass("btn-primary");
              $("#change_status").addClass("btn-secondary");
              $("#change_status").html(
                '<div class="spinner-border text-primary spinner-border-sm mx-3" role="status"><span class="visually-hidden"></span></div>'
              );
            },
            success: function (response) {
              if (response.error == false) {
                showToastMessage(response.message, "success");
                setTimeout(() => {
                  window.location.reload();
                }, 3000);
              } else {
                showToastMessage(response.message, "error");
                setTimeout(() => {
                  window.location.reload();
                }, 3000);
              }
              return;
            },
            error: function (response) {
              showToastMessage(response.message, "error");
              setTimeout(() => {
                window.location.reload();
              }, 3000);
            },
          });
        }
      } else {
        $.ajax({
          url: baseUrl + "/admin/orders/change_order_status",
          data: formdata,
          processData: false,
          contentType: false,
          type: "post",
          dataType: "json",
          beforeSend: function () {
            $("#change_status").attr("disabled", true);
            $("#change_status").removeClass("btn-primary");
            $("#change_status").addClass("btn-secondary");
            $("#change_status").html(
              '<div class="spinner-border text-primary spinner-border-sm mx-3" role="status"><span class="visually-hidden"></span></div>'
            );
          },
          success: function (response) {
            if (response.error == false) {
              showToastMessage(response.message, "success");
              setTimeout(() => {
                window.location.reload();
              }, 3000);
            } else {
              showToastMessage(response.message, "error");
              setTimeout(() => {
                window.location.reload();
              }, 3000);
            }
            return;
          },
          error: function (response) {
            showToastMessage(response.message, "error");
            setTimeout(() => {
              window.location.reload();
            }, 3000);
          },
        });
      }
    }
  });
});
window.cash_collection_events = {
  "click .edit_cash_collection": function (e, value, row, index) {
    $("#partner_id").val(row.partner_id);
    $("#amount").val(row.payable_commision);
  },
};
window.email_events = {
  "click .delete-email": function (e, value, row, index) {
    var id = row.id;
    Swal.fire({
      title: are_your_sure,
      text: you_wont_be_able_to_revert_this,
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      if (result.isConfirmed) {
        $.post(
          baseUrl + "/admin/delete_email",
          {
            [csrfName]: csrfHash,
            id: id,
          },
          function (data) {
            csrfName = data.csrfName;
            csrfHash = data.csrfHash;
            if (data.error == false) {
              showToastMessage(data.message, "success");
              setTimeout(() => {
                $("#email_list").bootstrapTable("refresh");
              }, 2000);
              return;
            } else {
              return showToastMessage(data.message, "error");
            }
          }
        );
      }
    });
  },
};
window.sms_gateway_events = {
  "click .edit": function (e, value, row, index) {
    $("#partner_id").val(row.partner_id);
    $("#amount").val(row.payable_commision);
  },
};
