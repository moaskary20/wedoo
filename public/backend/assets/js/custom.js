"use strict";
$(document).ready(function () {
  $("#loading").hide();
});
function showToastMessage(message, type) {
  switch (type) {
    case "error":
      $().ready(
        iziToast.error({
          title: "Error",
          message: message,
          position: "topRight",
          pauseOnHover: true,
        })
      );
      break;
    case "success":
      $().ready(
        iziToast.success({
          title: "Success",
          message: message,
          position: "topRight",
        })
      );
      break;
  }
}
$(document).on("submit", ".add-provider-with-subscription", function (e) {
  e.preventDefault();
  var formData = new FormData(this);
  var form_id = $(this).attr("id");
  var error_box = $("#error_box", this);
  var submit_btn = $(this).find(".submit_btn");
  var btn_html = $(this).find(".submit_btn").html();
  var btn_val = $(this).find(".submit_btn").val();
  var button_text =
    btn_html != "" || btn_html != "undefined" ? btn_html : btn_val;
  // password section for system users
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
      submit_btn.prop("disabled", true);
      submit_btn.removeClass("btn-primary");
      submit_btn.addClass("btn-secondary");
      submit_btn.html(
        '<div class="spinner-border text-light spinner-border-sm mx-3" role="status"><span class="visually-hidden"></span></div>'
      );
    },
    success: function (response) {
      csrfName = response["csrfName"];
      csrfHash = response["csrfHash"];
      if (response.error == false) {
        submit_btn.html(button_text);
        Swal.fire({
          title: response.message,
          text: "Do you want to assign subscription?",
          icon: "success",
          showCancelButton: true,
          confirmButtonColor: "#3085d6",
          cancelButtonColor: "#d33",
          confirmButtonText: "Yes",
          cancelButtonText: "No",
          didOpen: () => {
            $('input[name="partner_id"]').val(response.data.partner_id);
          },
        }).then((result) => {
          if (result.isConfirmed) {
            var partner_id = response.data.partner_id;
            window.location.href =
              baseUrl + "/admin//partners/partner_subscription/" + partner_id;
          } else {
            location.reload();
          }
        });
        $("form#" + form_id).trigger("reset");
        $(".close").click();
        $("#user_list").bootstrapTable("refresh");
        $("#slider_list").bootstrapTable("refresh");
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
function displaySubscriptionModal() {
  setTimeout(function () {
    $("#partner_subscriptions_add").modal("show");
  }, 200);
}
if ($(".summernotes").length) {
  tinymce.init({
    selector: ".summernotes",
    height: 200,
    menubar: true,
    plugins: [
      "a11ychecker",
      "advlist",
      "advcode",
      "advtable",
      "autolink",
      "checklist",
      "export",
      "lists",
      "link",
      "image",
      "charmap",
      "preview",
      "code",
      "anchor",
      "searchreplace",
      "visualblocks",
      "powerpaste",
      "fullscreen",
      "formatpainter",
      "insertdatetime",
      "media",
      "directionality",
      "table",
      "help",
      "wordcount",
      "imagetools",
    ],
    toolbar:
      "undo redo | image media | code fullscreen| formatpainter casechange blocks fontsize | bold italic forecolor backcolor | " +
      "alignleft aligncenter alignright alignjustify | " +
      "bullist numlist checklist outdent indent | removeformat | ltr rtl |a11ycheck table help",
    maxlength: null, // Remove text limit
    relative_urls: false,
    remove_script_host: false,
    document_base_url: baseUrl,
    file_picker_callback: function (callback, value, meta) {
      if (meta.filetype == "media" || meta.filetype == "image") {
        const input = document.createElement("input");
        input.setAttribute("type", "file");
        input.setAttribute("accept", "image/* audio/* video/*");
        input.addEventListener("change", (e) => {
          const file = e.target.files[0];
          var reader = new FileReader();
          var fd = new FormData();
          var files = file;
          fd.append("documents[]", files);
          fd.append("filetype", meta.filetype);
          fd.append(csrfName, csrfHash);
          var filename = "";
          jQuery.ajax({
            url: baseUrl + "/admin/media/upload",
            type: "post",
            data: fd,
            contentType: false,
            processData: false,
            async: false,
            success: function (response) {
              filename = response.file_name;
            },
          });
          reader.onload = function (e) {
            const imageUrl = baseUrl + "/public/uploads/media/" + filename;
            callback(imageUrl.replace(/&quot;/g, ""));
          };
          reader.readAsDataURL(file);
        });
        input.click();
      }
    },
    image_uploadtab: true,
  });
}
function comming_soon(element) {}
$(document).ready(function () {
  var check_box = $(".check_box");
  var start_time = $(".start_time");
  var end_time = $(".end_time");
  $(".check_box").on("click", function () {
    for (let index = 0; index < check_box.length; index++) {
      if (!$(check_box[index]).is(":checked")) {
        $(start_time[index]).attr("readOnly", "readOnly");
        $(end_time[index]).attr("readOnly", "readOnly");
      } else {
        $(start_time[index]).removeAttr("readOnly");
        $(end_time[index]).removeAttr("readOnly");
      }
    }
  });
  for (let index = 0; index < check_box.length; index++) {
    if (!$(check_box[index]).is(":checked")) {
      $(start_time[index]).attr("readOnly", "readOnly");
      $(end_time[index]).attr("readOnly", "readOnly");
    } else {
      $(start_time[index]).removeAttr("readOnly");
      $(end_time[index]).removeAttr("readOnly");
    }
  }
});
var order_status_filter = "";
$("#order_status_filter").on("change", function () {
  order_status_filter = $(this).find("option:selected").val();
});
var order_provider_filter = "";
$("#order_provider_filter").on("change", function () {
  order_provider_filter = $(this).find("option:selected").val();
});
$("#filter").on("click", function (e) {
  $("#user_list").bootstrapTable("refresh");
});
function orders_query(p) {
  return {
    search: $("#customSearch").val() ? $("#customSearch").val() : p.search,
    limit: p.limit,
    sort: p.sort,
    order: p.order,
    offset: p.offset,
    order_status_filter: order_status_filter,
    order_provider_filter: order_provider_filter,
  };
}
$("#filter").on("click", function (e) {
  $("#user_list").bootstrapTable("refresh");
});
function fetch_cites(element) {
  $.ajax({
    type: "POST",
    url: "delete_details",
    data: {
      id: $(element).data("id"),
    },
    dataType: "json",
    success: function (result) {
      csrfName = result.csrfName;
      csrfHash = result.csrfHash;
      if (result.error == false) {
        iziToast.success({
          title: "Success",
          message: result.message,
          position: "topRight",
        });
        var tableId = $(element).data("table-id");
      } else {
        iziToast.error({
          title: "Error",
          message: result.message,
          position: "topRight",
        });
      }
    },
  });
}
function delete_details(element) {
  $.ajax({
    type: "POST",
    url: "delete_details",
    data: {
      id: $(element).data("id"),
      table: $(element).data("table"),
      csrf_test_name: csrfHash,
    },
    dataType: "json",
    success: function (result) {
      csrfName = result.csrfName;
      csrfHash = result.csrfHash;
      if (result.error == false) {
        iziToast.success({
          title: "Success",
          message: result.message,
          position: "topRight",
        });
        var tableId = $(element).data("table-id");
        $("#" + tableId).bootstrapTable("refresh");
      } else {
        iziToast.error({
          title: "Error",
          message: result.message,
          position: "topRight",
        });
      }
    },
  });
}
function set_locale(language_code) {
  $.ajax({
    url: baseUrl + "/lang/" + language_code,
    type: "GET",
    dataType: "json",
    success: function (result) {
      var is_rtl = result.is_rtl;
      var language = result.language;
      localStorage.setItem("is_rtl", JSON.stringify(is_rtl));
      localStorage.setItem("language", JSON.stringify(language));
      location.reload();
    },
    error: function (xhr, status, error) {
      console.error("Failed to fetch language details.", status, error);
      location.reload();
    },
  });
}
$(".delete-language-btn").on("click", function (e) {
  e.preventDefault();
  if (confirm("Are you sure want to delete language?")) {
    window.location.href = $(this).attr("href");
  }
});
function active_sub(element) {
  $("#user_id").val($(element).data("uid"));
  $("#id").val($(element).data("sid"));
}
function receipt_check(element) {
  $("#bank_transfer_id").val($(element).data("id"));
  $("#user_id").val($(element).data("uid"));
}
function activate_user(element) {
  $("#user_id_active").val($(element).data("uid"));
}
function deactivate_user(element) {
  $("#user_id").val($(element).data("uid"));
}
$(document).ready(function () {
  $("#deactivate_user_form").on("submit", function (e) {
    e.preventDefault();
    let formdata = new FormData(this);
    formdata.append(csrfName, csrfHash);
    $.ajax({
      type: $(this).attr("method"),
      url: $(this).attr("action"),
      data: formdata,
      dataType: "json",
      cache: false,
      beforeSend: function () {
        $("#deactive_btn").attr("disabled", true);
        $("#deactive_btn").html("Deactivating.. .");
      },
      processData: false,
      contentType: false,
      success: function (response) {
        if (response.error == false) {
          iziToast.success({
            title: "Success",
            message: response.message,
            position: "topRight",
          });
          $("#deactive_btn").attr("disabled", false);
          $("#deactive_btn").html("Deactivate User");
          $(".close").click();
          $("#user_list").bootstrapTable("refresh");
        } else {
          iziToast.error({
            title: "Error",
            message: response.message,
            position: "topRight",
          });
          $(".close").click();
          window.location.reload();
        }
      },
    });
  });
});
$(document).ready(function () {
  $("#activate_user_form").on("submit", function (e) {
    e.preventDefault();
    let formdata = new FormData(this);
    formdata.append(csrfName, csrfHash);
    $.ajax({
      type: $(this).attr("method"),
      url: $(this).attr("action"),
      data: formdata,
      dataType: "json",
      cache: false,
      beforeSend: function () {
        $("#activate_btn").attr("disabled", true);
        $("#activate_btn").html("Activating.. .");
      },
      processData: false,
      contentType: false,
      success: function (response) {
        if (response.error == false) {
          iziToast.success({
            title: "Success",
            message: response.message,
            position: "topRight",
          });
          $("#activate_btn").attr("disabled", false);
          $("#activate_btn").html("Activated...");
          $(".close").click();
          $("#user_list").bootstrapTable("refresh");
        } else {
          iziToast.error({
            title: "Error",
            message: response.message,
            position: "topRight",
          });
          $(".close").click();
          window.location.reload();
        }
      },
    });
  });
});
$(document).ready(function () {
  $("#update_category_process").on("submit", function (e) {
    e.preventDefault();
    let formdata = new FormData($(this)[0]);
    formdata.append(csrfName, csrfHash);
    var name = $("#name").val();
    $.ajax({
      type: $(this).attr("method"),
      url: $(this).attr("action"),
      data: formdata,
      dataType: "json",
      processData: false,
      contentType: false,
      beforeSend: function () {
        $("#Category_btn").attr("disabled", true);
        $("#Category_btn").html("Adding.. .");
      },
      success: function (response) {
        if (response.error == false) {
          iziToast.success({
            title: "Success",
            message: response.message,
            position: "topRight",
          });
          setTimeout(function () {
            location.href = baseUrl + "/admin/categories";
          }, 500);
        } else {
          iziToast.error({
            title: "Error",
            message: response.message,
            position: "topRight",
          });
          setTimeout(function () {
            location.href = baseUrl + "admin/categories";
          }, 500);
        }
      },
    });
  });
});
$(document).ready(function () {
  if ($("#password") != null && $("#confirm_password") != null) {
    $("#confirm_password").on("blur", function (e) {
      if ($("#password").val() == "") {
        $("#password").css("border-color", "#FF3300");
        showToastMessage("Empty Password", "error");
        return false;
      }
    });
    $("#confirm_password").on("blur", function (e) {
      if ($("#confirm_password").val() == "") {
        $("#password").css("border-color", "#FF3300");
        $("#confirm_password").css("border-color", "#FF3300");
        showToastMessage("Empty Confirm Password", "error");
        return false;
      } else if ($("#password").val() != $("#confirm_password").val()) {
        e.preventDefault();
        $("#password").css("border-color", "#FF3300");
        $("#confirm_password").css("border-color", "#FF3300");
        showToastMessage("Mis Match Password", "error");
        return false;
      } else {
        $("#password").css("border-color", "#66FF00");
        $("#confirm_password").css("border-color", "#66FF00");
        return true;
      }
    });
  }
  $(document).on("submit", ".form-submit-event", function (e) {
    // Skip forms with normal-form-submit class
    if ($(this).hasClass('normal-form-submit')) {
      return true;
    }
    
    console.log("Form submit event triggered");
    console.log("csrfName:", csrfName);
    console.log("csrfHash:", csrfHash);
    console.log("Action URL:", $(this).attr("action"));
    
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
    
    console.log("FormData entries:");
    for (let [key, value] of formData.entries()) {
      console.log(key, value);
    }
    $.ajax({
      type: "POST",
      url: $(this).attr("action"),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      beforeSend: function () {
        submit_btn.prop("disabled", true);
        submit_btn.removeClass("btn-primary");
        submit_btn.addClass("btn-secondary");
        submit_btn.html(
          '<div class="spinner-border text-light spinner-border-sm mx-3" role="status"><span class="visually-hidden"></span></div>'
        );
      },
      success: function (response) {
        csrfName = response["csrfName"];
        csrfHash = response["csrfHash"];
        if (response.error == false) {
          showToastMessage(response.message, "success");
          // location.reload();
          $("form#" + form_id).trigger("reset");
          submit_btn.html(button_text);
          $(".close").click();
          $("#user_list").bootstrapTable("refresh");
          $("#category_list").bootstrapTable("refresh");

          $("#slider_list").bootstrapTable("refresh");
          $("#update_modal").modal("hide");

          submit_btn.attr("disabled", false);
          // Call the function for each class
          removeFilesFromClass("filepond");
          removeFilesFromClass("filepond-docs");
          removeFilesFromClass("filepond-excel");
          removeFilesFromClass("filepond-only-images-and-videos");

          $("select").val(false).trigger("change");
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
      error: function(xhr, status, error) {
        console.log("AJAX Error:");
        console.log("Status:", status);
        console.log("Error:", error);
        console.log("Response Text:", xhr.responseText);
        console.log("Status Code:", xhr.status);
        
        submit_btn.attr("disabled", false);
        submit_btn.removeClass("btn-secondary");
        submit_btn.addClass("btn-primary");
        submit_btn.html(button_text);
        
        showToastMessage("AJAX Error: " + status + " - " + error, "error");
      },
      complete: function(xhr, status) {
        console.log("AJAX Complete. Status:", status);
      }
    });
  });
  $(document).on(
    "submit",
    ".for-payment-request-form-submit-event",
    function (e) {
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
          submit_btn.prop("disabled", true);
          submit_btn.removeClass("btn-primary");
          submit_btn.addClass("btn-secondary");
          submit_btn.html(
            '<div class="spinner-border text-light spinner-border-sm mx-3" role="status"><span class="visually-hidden"></span></div>'
          );
        },
        success: function (response) {
          csrfName = response["csrfName"];
          csrfHash = response["csrfHash"];
          if (response.error == false) {
            showToastMessage(response.message, "success");
            // location.reload();
            $("form#" + form_id).trigger("reset");
            submit_btn.html(button_text);
            $(".close").click();
            $("#user_list").bootstrapTable("refresh");
            $("#category_list").bootstrapTable("refresh");

            $("#slider_list").bootstrapTable("refresh");
            $("#update_modal").modal("hide");

            submit_btn.attr("disabled", false);
            // Call the function for each class
            removeFilesFromClass("filepond");
            removeFilesFromClass("filepond-docs");
            removeFilesFromClass("filepond-excel");
            removeFilesFromClass("filepond-only-images-and-videos");

            // $("select").val(false).trigger("change");
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
    }
  );

  $(document).on("submit", ".update-form", function (e) {
    // Skip forms with normal-form-submit class
    if ($(this).hasClass('normal-form-submit')) {
      return true;
    }
    
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
        submit_btn.prop("disabled", true);
        submit_btn.removeClass("btn-primary");
        submit_btn.addClass("btn-secondary");
        submit_btn.html(
          '<div class="spinner-border text-light spinner-border-sm mx-3" role="status"><span class="visually-hidden"></span></div>'
        );
      },
      success: function (response) {
        csrfName = response["csrfName"];
        csrfHash = response["csrfHash"];
        if (response.error == false) {
          showToastMessage(response.message, "success");
          location.reload();
          $("form#" + form_id).trigger("reset");
          submit_btn.html(button_text);
          $(".close").click();
          $("#user_list").bootstrapTable("refresh");
          $("#category_list").bootstrapTable("refresh");

          $("#slider_list").bootstrapTable("refresh");
          $("#update_modal").modal("hide");

          submit_btn.attr("disabled", false);
          // Call the function for each class
          removeFilesFromClass("filepond");
          removeFilesFromClass("filepond-docs");
          removeFilesFromClass("filepond-excel");
          removeFilesFromClass("filepond-only-images-and-videos");

          $("select").val(false).trigger("change");
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
});
function notification_id(element) {
  $("#id").val($(element).data("id"));
  $("#did").val($(element).data("id"));
}
$("#gen-list a").on("click", function (e) {
  $(this).tab("show");
});
$(document).ready(function () {});
function category_id(element) {
  $("#id").val($(element).data("id"));
  $("#did").val($(element).data("id"));
}
function language_id(element) {
  $("#id").val($(element).data("id"));
  $("#did").val($(element).data("id"));
}
function template_id(element) {
  $("#template_id").val($(element).data("id"));
}
$("#categories_select1").hide();
$("#user_select").hide();
$("#provider_select").hide();
$("#category_select").hide();
$("#url").hide();
$(document).ready(function () {
  $("#type1").change(function (e) {
    if ($("#type1").val() == "general") {
      $("#categories_select").show();
      $("#provider_select").hide();
      $("#category_select").hide();
      $("#url").hide();
    }
    if ($("#type1").val() == "provider") {
      $("#provider_select").show();
      $("#categories_select").hide();
      $("#category_select").hide();
      $("#url").hide();
    } else if ($("#type1").val() == "category") {
      $("#provider_select").hide();
      $("#categories_select").hide();
      $("#category_select").show();
      $("#url").hide();
    } else if ($("#type1").val() == "url") {
      $("#provider_select").hide();
      $("#categories_select").hide();
      $("#category_select").hide();
      $("#url").show();
    } else {
      $("#provider_select").hide();
      $("#category_select").hide();
      $("#url").hide();
    }
  });
});
$(document).ready(function () {
  $("#user_type").change(function (e) {
    if ($("#user_type").val() == "all_users") {
      $("#user_select").hide();
    } else if ($("#user_type").val() == "specific_user") {
      $("#user_select").show();
    } else if ($("#user_type").val() == "existing_user") {
      $("#user_select").hide();
      $("#email").prop("required", false);
      $("#name").prop("required", false);
      $("#mobile").prop("required", false);
      $("#password").prop("required", false);
      $("#confirm_password").prop("required", false);
    } else if ($("#user_type").val() == "new_user") {
      $("#user_select").hide();
      $("#email").prop("required", true);
      $("#name").prop("required", true);
      $("#mobile").prop("required", true);
      $("#password").prop("required", true);
      $("#confirm_password").prop("required", true);
    } else {
      $("#user_select").hide();
    }
  });
});
$("#image_checkbox").on("click", function () {
  if (this.checked) {
    $(this).prop("checked", true);
    $(".include_image").removeClass("d-none");
  } else {
    $(this).prop("checked", false);
    $(".include_image").addClass("d-none");
  }
});
$("#categories_select").hide();
$("#services_select").hide();
$("#url_section").hide();
$(document).ready(function () {
  $("#type").change(function (e) {
    if ($("#type").val() == "default") {
      $("#categories_select").hide();
      $("#services_select").hide();
      $("#url_section").hide();
    } else if ($("#type").val() == "Category") {
      $("#categories_select").show();
      $("#services_select").hide();
      $("#url_section").hide();
    } else if ($("#type").val() == "provider") {
      $("#categories_select").hide();
      $("#services_select").show();
      $("#url_section").hide();
    } else if ($("#type").val() == "url") {
      $("#categories_select").hide();
      $("#services_select").hide();
      $("#url_section").show();
    }
  });
});
function update_slider(element) {
  $("#id").val($(element).data("id"));
  $("#id").val($(element).data("id"));
}
$("#gen-list a").on("click", function (e) {
  $(this).tab("show");
});
window.Category_events = {
  "click .delete-Category": function (e, value, row, index) {
    var users_id = row.id;
    Swal.fire({
      title: are_your_sure,
      text:
        you_wont_be_able_to_revert_this +
        " " +
        subcategories_and_services_will_be_deactivated,
      icon: "error",
      showCancelButton: true,
      cancelButtonText: cancel,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      if (result.isConfirmed) {
        $.post(
          baseUrl + "/admin/category/remove_category",
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
                $("#category_list").bootstrapTable("refresh");
                $("#edit_category_ids")
                  .children("option[value^=" + users_id + "]")
                  .remove();
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
  "click .edite-Category": function (e, value, row, index) {
    $("#edit_category_ids").children("option").show();
    $("#edit_category_ids")
      .children("option[value^=" + row.id + "]")
      .hide();
    $("#id").val(row.id);
    $("#edit_parent_category").val(row.parent_category_name);
    $("#edit_name").val(row.name);
    $("#commision_1").val(row.admin_commission);
    $("#edit_dark_theme_color").val(row.dark_color);
    $("#edit_light_theme_color").val(row.light_color);

    $("#edit_category_slug").val(row.slug);
    const commissions = row.admin_commission;
    $("#commision_1").val(commissions);
    let opv = row.type;
    var regex = /<img.*?src="(.*?)"/;
    var src = regex.exec(row.category_image)[1];
    $("#id").val(row.id);
    $("#category_image").attr("src", src);
    if (row.parent_id == "0") {
      $("#edit_make_parent").val("0");
      $("#edit_parent").hide();
    } else {
      $("#edit_make_parent").val("1");
      $("#edit_parent").show();
      $("#edit_category_ids").val(row.parent_id);
    }
    if (row.og_status == true) {
      $("#changer_1").prop("checked", true);
      $("#category_para_edit").text("Enable");
    } else {
      $("#changer_1").prop("checked", false);
      $("#category_para_edit").text("Disable");
    }
  },
};
function feature_section_id(element) {
  $("#id").val($(element).data("id"));
  $("#id").val($(element).data("id"));
}
$("#gen-list a").on("click", function (e) {
  $(this).tab("show");
});
$(document).ready(function () {});
function order_id(element) {
  $("#id").val($(element).data("id"));
}
function view_order(e) {
  var order_id = $(e).attr("data-id");
  $.post(baseUrl + "/admin/orders/view_details", {
    [csrfName]: csrfHash,
  });
}
$("#gen-list a").on("click", function (e) {
  $(this).tab("show");
});
$(document).ready(function () {});
window.orders_events = {
  "click .delete_orders": function (e, value, row, index) {
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
          baseUrl + "/admin/Orders/delete_orders",
          {
            [csrfName]: csrfHash,
            id: id,
          },
          function (data) {
            csrfName = data.csrfName;
            csrfHash = data.csrfHash;
            if (data.error == false) {
              showToastMessage(data.message, "success");
              window.location.reload();
            } else {
              return showToastMessage(data.message, "error");
            }
          }
        );
      }
    });
  },
};
function services_id(element) {
  $("#id").val($(element).data("id"));
  $("#id").val($(element).data("id"));
}
$("#gen-list a").on("click", function (e) {
  $(this).tab("show");
});
$(document).ready(function () {});
window.services_events = {
  "click .delete-services": function (e, value, row, index) {
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
          baseUrl + "/admin/services/delete-services",
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
};
function promo_codes_id(element) {
  $("#id").val($(element).data("id"));
}
$("#gen-list a").on("click", function (e) {
  $(this).tab("show");
});
function readURL(input) {
  var reader = new FileReader();
  reader.onload = function (e) {
    document
      .querySelector("#service_image")
      .setAttribute("src", e.target.result);
    if (document.querySelector("#update_service_image") != null) {
      document
        .querySelector("#update_service_image")
        .setAttribute("src", e.target.result);
    }
  };
  reader.readAsDataURL(input.files[0]);
}
function readURLCategory(input) {
  var reader = new FileReader();
  reader.onload = function (e) {
    document
      .querySelector("#catgeory_image")
      .setAttribute("src", e.target.result);
    if (document.querySelector("#update_service_image") != null) {
      document
        .querySelector("#update_service_image")
        .setAttribute("src", e.target.result);
    }
  };
  reader.readAsDataURL(input.files[0]);
}
$("#section_type").on("change", function () {
  // Define the classes for each section type
  const sections = {
    partners: ".partners_ids",
    categories: ".Category_item",
    top_rated_partner: ".top_rated_providers",
    previous_order: ".previous_order",
    ongoing_order: ".ongoing_order",
    near_by_provider: ".near_by_providers",
    banner: ".banner_section",
  };
  // Get the selected value from the dropdown
  const selectedSection = $(this).val();
  // Hide all sections
  $(
    ".Category_item, .partners_ids, .top_rated_providers, .previous_order, .ongoing_order, .near_by_providers,.banner_section"
  ).addClass("d-none");
  if (selectedSection == "banner") {
    $(".title").hide();
  } else {
    $(".title").show();
  }
  // Show the selected section if it exists in the sections map
  if (sections[selectedSection]) {
    $(sections[selectedSection]).removeClass("d-none");
  }
});
$(
  "#banner_providers_select,#banner_categories_select,#banner_url_section"
).hide();
$("#banner_type").on("change", function () {
  if ($("#banner_type").val() == "banner_default") {
    $("#banner_providers_select").hide();
    $("#banner_categories_select").hide();
    $("#banner_url_section").hide();
  }
  if ($("#banner_type").val() == "banner_provider") {
    $("#banner_providers_select").show();
    $("#banner_categories_select").hide();
    $("#banner_url_section").hide();
  } else if ($("#banner_type").val() == "banner_category") {
    $("#banner_providers_select").hide();
    $("#banner_categories_select").show();
    $("#banner_url_section").hide();
  } else if ($("#banner_type").val() == "banner_url") {
    $("#banner_providers_select").hide();
    $("#banner_categories_select").hide();
    $("#banner_url_section").show();
  } else {
    $("#banner_providers_select").hide();
    $("#banner_categories_select").hide();
    $("#banner_url_section").hide();
  }
});
$("#category_item").on("change", function () {
  $(".error").remove();
  $.post(
    baseUrl + "/admin/categories/list",
    {
      [csrfName]: csrfHash,
      id: $(this).val(),
      from_app: true,
    },
    function (data) {
      csrfName = data.csrfName;
      csrfHash = data.csrfHash;
      if (data.error == false) {
        var sub_categories = data.data;
        sub_categories.forEach((element) => {
          Option =
            "<option value='" + element.id + "'>" + element.name + "</option>";
          $("#sub_category").append(Option);
        });
        $("#sub_category").attr("disabled", false);
        $("#sub_category")
          .parent()
          .append('<span class="text-danger error"></span>');
      } else {
        $("#sub_category").empty();
        $("#sub_category").attr("disabled", true);
        $("#sub_category")
          .parent()
          .append(
            '<span class="text-danger error">No Found sub categories on this category Please change categories</span>'
          );
      }
    }
  );
});
$("#edit_category_item").on("change", function () {
  $(".error").remove();
  $.post(
    baseUrl + "/admin/categories/list",
    {
      [csrfName]: csrfHash,
      id: $(this).val(),
      from_app: true,
    },
    function (data) {
      csrfName = data.csrfName;
      csrfHash = data.csrfHash;
      if (data.error == false) {
        var sub_categories = data.data;
        sub_categories.forEach((element) => {
          Option =
            "<option value='" + element.id + "'>" + element.name + "</option>";
          $("#edit_sub_category").append(Option);
        });
        $("#edit_sub_category").attr("disabled", false);
        $("#edit_sub_category")
          .parent()
          .append('<span class="text-danger error"></span>');
      } else {
        $("#edit_sub_category").empty();
        $("#edit_sub_category").attr("disabled", true);
        $("#edit_sub_category")
          .parent()
          .append(
            '<span class="text-danger error">No Found sub categories on this category Please change categories</span>'
          );
      }
    }
  );
});
function faqs_id(element) {
  $("#id").val($(element).data("id"));
  $("#id").val($(element).data("id"));
}
$("#gen-list a").on("click", function (e) {
  $(this).tab("show");
});
$(document).ready(function () {});
window.faqs_events = {
  "click .remove_faqs": function (e, value, row, index) {
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
          baseUrl + "/admin/faqs/remove_faqs",
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
  "click .edit_faqs": function (e, value, row, index) {
    $("#id").val(row.id);
    $("#edit_question").val(row.question);
    $("#edit_answer").val(row.answer);
  },
};
function taxes_id(element) {
  $("#id").val($(element).data("id"));
  $("#id").val($(element).data("id"));
}
$("#gen-list a").on("click", function (e) {
  $(this).tab("show");
});
$(document).ready(function () {});
window.taxes_events = {
  "click .remove_taxes": function (e, value, row, index) {
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
          baseUrl + "/admin/tax/remove_taxes",
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
  "click .edit_taxes": function (e, value, row, index) {
    $("#id").val(row.id);
    $("#edit_title").val(row.title);
    $("#edit_percentage").val(row.percentage);
    if (row.og_status == 1) {
      $("#status_edit").prop("checked", true);
      $("#tax_status_edit").text("Enable");
    } else {
      $("#status_edit").prop("checked", false);
      $("#tax_status_edit").text("Disable");
    }
  },
};
function tickets_id(element) {
  $("#id").val($(element).data("id"));
  $("#id").val($(element).data("id"));
}
$("#gen-list a").on("click", function (e) {
  $(this).tab("show");
});
$(document).ready(function () {});
window.tickets_events = {
  "click .remove_tickets": function (e, value, row, index) {
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
          baseUrl + "/admin/tickets/remove_tickets",
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
};
// mini map
// code for map start
let update_location = "";
let map_update = "";
let partner_location = "";
let marker = "";
let autocomplete = "";
let add_partner_location = "";
let view_partner_location = "";
let map_view = "";
let map = "";
var latitude = $("#latitude").val();
var longitude = $("#longitude").val();
let center = {
  lat: parseFloat(latitude),
  lng: parseFloat(longitude),
};
// div for maps
var map_location = document.getElementById("map");
var map_location_update = document.getElementById("map_u");
var partner_map = document.getElementById("partner_map");
function initautocomplete() {
  if (document.getElementById("search_places") != null) {
    autocomplete = new google.maps.places.Autocomplete(
      document.getElementById("search_places"),
      {
        types: ["locality"],
        fields: ["place_id", "geometry", "name"],
      }
    );
    autocomplete.addListener("place_changed", onPlaceChanged);
  }
  $("#update_modal").on("show.bs.modal", function (e) {
    // for update
    if (document.getElementById("search_places_u") != null) {
      update_location = new google.maps.places.Autocomplete(
        document.getElementById("search_places_u"),
        {
          types: ["locality"],
          fields: ["place_id", "geometry", "name"],
        }
      );
    }
  });
  // add
  function onPlaceChanged(e) {
    place = autocomplete.getPlace();
    let contentString = "<h6> " + place.name + " </h6>";
    center = {
      lat: place.geometry.location.lat(),
      lng: place.geometry.location.lng(),
    };
    const infowindow = new google.maps.InfoWindow({
      content: contentString,
    });
    map = new google.maps.Map(map_location, {
      center,
      zoom: 10,
    });
    const marker = new google.maps.Marker({
      title: place.name,
      animation: google.maps.Animation.DROP,
      position: center,
      map: map,
    });
    marker.addListener("click", () => {
      infowindow.open({
        anchor: marker,
        map,
        shouldFocus: false,
      });
    });
    $("#latitude").val(latitude);
    $("#longitude").val(longitude);
    $("#city_name").val(place.name);
  }
  // for update
  if (document.getElementById("search_places_u") != null) {
    update_location = new google.maps.places.Autocomplete(
      document.getElementById("search_places_u")
    );
    update_location.addListener("place_changed", onUpdatePlace);
  }
  if (document.getElementById("partner_location") != null) {
    add_partner_location = new google.maps.places.Autocomplete(
      document.getElementById("partner_location")
    );
    add_partner_location.addListener("place_changed", on_add_partner);
  }
  if (autocomplete) {
    var place = autocomplete.getPlace();
  }
  var latitude =
    typeof place != "undefined"
      ? place.geometry.location.lat()
      : parseFloat("23.242697188102483");
  var longitude =
    typeof place != "undefined"
      ? place.geometry.location.lng()
      : parseFloat("69.6639650758625");
  var name =
    typeof place != "undefined" ? place.geometry.location.lng() : "Bhuj";
  center = {
    lat: latitude,
    lng: longitude,
  };
  if (partner_map != null) {
    if (
      $.trim($("#partner_latitude").val()) !== "" &&
      $.trim($("#partner_longitude").val()) !== ""
    ) {
      var edit_latitude = parseFloat($("#partner_latitude").val());
      var edit_longitude = parseFloat($("#partner_longitude").val());
      center = {
        lat: edit_latitude,
        lng: edit_longitude,
      };
    }
    partner_location = new google.maps.Map(partner_map, {
      center,
      zoom: 5,
    });
    if (
      $.trim($("#partner_latitude").val()) !== "" &&
      $.trim($("#partner_longitude").val()) !== ""
    ) {
      var edit_latitude = parseFloat($("#partner_latitude").val());
      var edit_longitude = parseFloat($("#partner_longitude").val());
      set_map_marker_for_partner(
        "",
        edit_latitude,
        edit_longitude,
        "",
        partner_location
      );
      var geocoder = new google.maps.Geocoder();
      geocoder.geocode({ location: center }, function (results, status) {
        if (status === "OK") {
          if (results[0]) {
            var placeName = results[0].formatted_address;
            $("#partner_location").val(placeName);
          } else {
            console.error("No results found");
          }
        } else {
          console.error("Geocoder failed due to: " + status);
        }
      });
    }
    /* add marker on clicked location */
    google.maps.event.addListener(partner_location, "click", function (event) {
      var latitude = event.latLng.lat();
      var longitude = event.latLng.lng();
      set_map_marker_for_partner("", latitude, longitude, "", partner_location);
      $("#partner_latitude").val(latitude);
      $("#partner_longitude").val(longitude);
    }); //end addListener
  }
  function on_add_partner() {
    place = add_partner_location.getPlace();
    let latitude = place.geometry.location.lat();
    let longitude = place.geometry.location.lng();
    set_map_marker_for_partner(place, "", "", "", partner_location);
    $("#partner_latitude").val(latitude);
    $("#partner_longitude").val(longitude);
  }
  if (map_location != null) {
    map = new google.maps.Map(map_location, {
      center,
      zoom: 8,
    });
  }
  if (map_location_update != null) {
    map_update = new google.maps.Map(map_location_update, {
      center,
      zoom: 8,
    });
  }
  function onUpdatePlace(e) {
    place = update_location.getPlace();
    let latitude = place.geometry.location.lat();
    let longitude = place.geometry.location.lng();
    set_map_marker(place);
    $("#u_city_name").val(place.name);
    $("#u_latitude").val(latitude);
    $("#u_longitude").val(longitude);
  }
  var info_window = "";
  view_partner_location = document.getElementById("map_tuts");
  if (view_partner_location != null) {
    var view_latitude = parseFloat($("#lat").val());
    var view_longitude = parseFloat($("#lon").val());
    if (view_latitude != "" && view_longitude != "") {
      center = {
        lat: view_latitude,
        lng: view_longitude,
      };
      map_view = new google.maps.Map(view_partner_location, {
        center,
        zoom: 16,
      });
      const marker = new google.maps.Marker({
        // title: title,
        animation: google.maps.Animation.DROP,
        position: center,
        map: map_view,
      });
      marker.addListener("click", () => {
        info_window.open({
          anchor: marker,
          map_view,
          shouldFocus: false,
        });
      });
    } else {
      $(view_partner_location).text("<h6> No Data passed </h6>");
    }
  } else {
    // console.log("view_partner_location is empty");
  }
}
window.initMap = initautocomplete;
// google.maps.event.addDomListener(window, 'load', initAutocomplete);
// mini map ends here
function set_map_marker_for_partner(
  place = "",
  latitude = "",
  longitude = "",
  name = "",
  map = ""
) {
  if (place !== "") {
    latitude = place.geometry.location.lat();
    longitude = place.geometry.location.lng();
  } else {
    latitude = parseFloat(latitude);
    longitude = parseFloat(longitude);
  }
  let title = place.name ? place.name : name;
  let contentString = "<h6> " + title + " </h6>";
  center = {
    lat: place ? place.geometry.location.lat() : latitude,
    lng: place ? place.geometry.location.lng() : longitude,
  };
  const infowindow = new google.maps.InfoWindow({
    content: contentString,
  });
  if (!map) {
    partner_location = new google.maps.Map(partner_map, {
      center,
      zoom: 16,
    });
  } else {
    partner_location = map;
  }
  if (marker == "") {
    marker = new google.maps.Marker({
      title: title,
      animation: google.maps.Animation.DROP,
      position: center,
      map: partner_location,
      // draggable: true
    });
  } else {
    marker.setPosition({ lat: latitude, lng: longitude });
  }
  if (place != "") {
    partner_location.setCenter(center);
    partner_location.setZoom(16);
  }
  marker.addListener("click", () => {
    infowindow.open({
      anchor: marker,
      map: partner_location,
      shouldFocus: false,
    });
  });
}
function set_map_marker(place = "", latitude = "", longitude = "", name = "") {
  if (place !== "") {
    latitude = place.geometry.location.lat();
    longitude = place.geometry.location.lng();
  } else {
    latitude = parseFloat(latitude);
    longitude = parseFloat(longitude);
  }
  let title = place.name ? place.name : name;
  let contentString = "<h6> " + title + " </h6>";
  center = {
    lat: place ? place.geometry.location.lat() : latitude,
    lng: place ? place.geometry.location.lng() : longitude,
  };
  const infowindow = new google.maps.InfoWindow({
    content: contentString,
  });
  map = new google.maps.Map(map_location_update, {
    center,
    zoom: 10,
  });
  const marker = new google.maps.Marker({
    title: title,
    animation: google.maps.Animation.DROP,
    position: center,
    map: map,
  });
  marker.addListener("click", () => {
    infowindow.open({
      anchor: marker,
      map,
      shouldFocus: false,
    });
  });
}
$("#member").hide();
$(document).ready(function () {
  $("#type").on("change", function (e) {
    if ($("#type").val() == "0" || $("#type").val() == "sel") {
      $("#member").hide();
    } else {
      $("#member").show();
    }
  });
});
window.payment_events = {
  "click .edit_request": function (e, value, row, index) {
    $("#request_id").val(row.id);
    $("#user_id").val(row.user_id);
    $("#amount").val(row.amount);
  },
};
function get_message(messages) {
  var messages_html;
  var data = JSON.parse(messages);
  let message_html;
  for (let i = 0; i < data["rows"].length; i++) {
    let element = data["rows"][i];
    var user_type = element["user_type"];
    var user_name = element["username"];
    var updated_at = element["updated_at"];
    var message = element["message"];
    var is_left = user_type == "user" ? "left" : "right";
    var bg_color =
      is_left == "left" ? "bg-primary text-white" : "bg-success text-white";
    var atch_html;
    let attachments =
      element["attachments"] != "" ? JSON.parse(element["attachments"]) : null;
    if (attachments != null && attachments.length > 0) {
      attachments.forEach((element) => {
        let attachment = element;
        atch_html =
          "<div class='container-fluid image-upload-section'>" +
          "<a class='btn btn-danger btn-xs mr-1 mb-1' href=' " +
          attachment +
          "'  target='_blank' alt='Attachment Not Found'>Attachment</a>" +
          "<div class='col-md-3 col-sm-12 shadow p-3 mb-5 bg-white rounded m-4 text-center grow image d-none'></div>" +
          "</div>";
        messages_html =
          "<div class='direct-chat-msg " +
          is_left +
          "'>" +
          "<div class='direct-chat-infos clearfix'>" +
          "<span class='direct-chat-name float-" +
          is_left +
          "' id='name'> " +
          user_name +
          "</span>" +
          "<span class='direct-chat-timestamp float-" +
          is_left +
          "' id='last_updated'> &nbsp;" +
          updated_at +
          "</span>" +
          "</div>";
        if (message != null) {
          messages_html +=
            "<div class='direct-chat-text " +
            bg_color +
            " float-" +
            is_left +
            "' id=" +
            user_type +
            ">" +
            message +
            "</div> <br> <br>";
        }
        messages_html +=
          "<div class='direct-chat-text  float-" +
          is_left +
          "' id='message'> " +
          atch_html +
          "</div> <br> <br>" +
          "</div>";
      });
    } else {
      messages_html =
        "<div class='direct-chat-msg " +
        is_left +
        "'>" +
        "<div class='direct-chat-infos clearfix'>" +
        "<span class='direct-chat-name float-" +
        is_left +
        "' id='name'> " +
        user_name +
        "</span>" +
        "<span class='direct-chat-timestamp float-" +
        is_left +
        "' id='last_updated'> &nbsp;" +
        updated_at +
        "</span>" +
        "</div>" +
        "<div class='direct-chat-text " +
        bg_color +
        " float-" +
        is_left +
        "' id=" +
        user_type +
        ">" +
        message +
        "</div>  <br> <br>" +
        "</div>";
    }
    $(".ticket_msg").prepend(messages_html);
  }
}
$(document).ready(function () {});
function printDiv(divName) {
  var printContents = document.getElementById(divName).innerHTML;
  var originalContents = document.body.innerHTML;
  document.body.innerHTML = printContents;
  window.print();
  document.body.innerHTML = originalContents;
}
$(document).ready(function () {
  $("#old_user").hide();
  $("#new_user").hide();
  $("#user_type").on("change", function (e) {
    if ($("#user_type").val() == "new_user") {
      $("#old_user").hide();
      $("#new_user").show();
    } else {
      $("#old_user").show();
      $("#new_user").hide();
    }
  });
});
function change_order_Status() {
  var status = $(".update_order_status").val();
  var order_id = $("#order_id").val();
  var input_body = {
    [csrfName]: csrfHash,
    status: status,
    order_id: order_id,
  };
  $.ajax({
    type: "POST",
    url: baseUrl + "/admin/orders/change_order_status",
    data: input_body,
    dataType: "json",
    success: function (response) {
      csrfName = response["csrfName"];
      csrfHash = response["csrfHash"];
      if (response.error != false) {
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
    },
  });
}
$(window).ready(function () {
  const checkDiv = setInterval(() => {
    if ($(".partner-rating").length > 0) {
      clearInterval(checkDiv);
      for (let i = 0; i < $(".partner-rating").length; i++) {
        let element = $(".partner-rating")[i];
        let id = $(".partner-rating")[i]["id"];
        let ratings = $(element).attr("data-value");
        $(document).ready(function () {
          $("#" + id).rateYo({
            rating: ratings,
            spacing: "5px",
            readOnly: true,
            starWidth: "15px",
            starHeight: "85px",
          });
        });
      }
    }
  }, 100);
});
$(window).ready(function () {
  $("#partner_list").on({
    "": function (e) {},
  });
  $("#partner_list").on({
    "load-success.bs.table , page-change.bs.table, check.bs.table, uncheck.bs.table, column-switch.bs.table":
      function (e) {
        for (let i = 0; i < $(".partner-rating").length; i++) {
          let element = $(".partner-rating")[i];
          let id = $(".partner-rating")[i]["id"];
          let ratings = $(element).attr("data-value");
          $(document).ready(function () {
            $("#" + id).rateYo({
              rating: ratings,
              spacing: "5px",
              readOnly: true,
              starWidth: "25px",
              starHeight: "85px",
            });
          });
        }
      },
  });
});
$(document).ready(function () {
  const checkDiv = setInterval(() => {
    if ($(".service-ratings").length > 0) {
      clearInterval(checkDiv);
      for (let i = 0; i < $(".service-ratings").length; i++) {
        let element = $(".service-ratings")[i];
        let id = $(".service-ratings")[i]["id"];
        let ratings = $(element).attr("data-value");
        $(document).ready(function () {
          $("#" + id).rateYo({
            rating: ratings,
            spacing: "5px",
            readOnly: true,
            starWidth: "25px",
          });
        });
      }
    }
  }, 1);
  $("#view_rating_model").on("show.bs.modal ", function (e) {
    $("#rating_table").on({
      "load-success.bs.table , page-change.bs.table, check.bs.table, uncheck.bs.table, column-switch.bs.table":
        function (e) {
          for (let i = 0; i < $(".service-ratings").length; i++) {
            let element = $(".service-ratings")[i];
            let id = $(".service-ratings")[i]["id"];
            let ratings = $(element).attr("data-value");
            $(document).ready(function () {
              $("#" + id).rateYo({
                rating: ratings,
                spacing: "5px",
                readOnly: true,
                starWidth: "25px",
              });
            });
          }
        },
    });
  });
});
$(document).ready(function () {
  $(".fa-search").addClass("d-none");
});
window.customSearchFormatter = function (value, searchText) {
  return value
    .toString()
    .replace(
      new RegExp("(" + searchText + ")", "gim"),
      '<span style="background-color: pink;border: 1px solid red;border-radius:90px;padding:4px">$1</span>'
    );
};
$(document).ready(function () {
  $("#parent").hide();
  var option = $("#make_parent").val();
  $("#make_parent").change(function (e) {
    e.preventDefault();
    if ($(this).val() == 1) {
      $("#parent").show();
    } else {
      $("#parent").hide();
    }
  });
});
$(document).ready(function () {
  $("#edit_make_parent").trigger("change");
  $("#edit_parent").hide();
  var option = $("#edit_make_parent").val();
  $("#edit_make_parent").change(function (e) {
    if ($(this).val() == "1") {
      $("#edit_parent").show();
    } else {
      $("#edit_parent").hide();
    }
  });
});
$("#rescheduled_form").on("submit", function (e) {
  e.preventDefault();
});
$(function () {
  FilePond.registerPlugin(
    FilePondPluginImagePreview,
    FilePondPluginFileValidateSize,
    FilePondPluginFileValidateType
  );
  $(".filepond").filepond({
    credits: null,
    allowFileSizeValidation: "true",
    maxFileSize: "5MB",
    labelMaxFileSizeExceeded: "File is too large",
    labelMaxFileSize: "Maximum file size is {filesize}",
    allowFileTypeValidation: true,
    acceptedFileTypes: ["image/*", "video/*", "application/pdf"],
    labelFileTypeNotAllowed: "File of invalid type",
    fileValidateTypeLabelExpectedTypes:
      "Expects {allButLastType} or {lastType}",
    storeAsFile: true,
    allowPdfPreview: true,
    pdfPreviewHeight: 320,
    pdfComponentExtraParams: "toolbar=0&navpanes=0&scrollbar=0&view=fitH",
    allowVideoPreview: true,
    allowAudioPreview: true,
  });
  $(".filepond-docs").filepond({
    credits: null,
    allowFileSizeValidation: "true",
    maxFileSize: "25MB",
    labelMaxFileSizeExceeded: "File is too large",
    labelMaxFileSize: "Maximum file size is {filesize}",
    allowFileTypeValidation: true,
    acceptedFileTypes: [
      "application/pdf",
      "application/msword",
      "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
    ],
    labelFileTypeNotAllowed: "File of invalid type",
    fileValidateTypeLabelExpectedTypes:
      "Expects {allButLastType} or {lastType}",
    storeAsFile: true,
    allowPdfPreview: true,
    pdfPreviewHeight: 320,
    pdfComponentExtraParams: "toolbar=0&navpanes=0&scrollbar=0&view=fitH",
    allowVideoPreview: true,
    allowAudioPreview: true,
  });
  $(".filepond-excel").filepond({
    credits: null,
    allowFileSizeValidation: true,
    maxFileSize: "25MB",
    labelMaxFileSizeExceeded: "File is too large",
    labelMaxFileSize: "Maximum file size is {filesize}",
    allowFileTypeValidation: true,
    acceptedFileTypes: [
      "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
      "application/vnd.ms-excel",
      "text/csv",
      "application/csv",
      "text/plain",
    ],
    labelFileTypeNotAllowed:
      "Invalid file type. Please upload an Excel or CSV file.",
    fileValidateTypeLabelExpectedTypes:
      "Expects {allButLastType} or {lastType}",
    storeAsFile: true,
    allowPdfPreview: false,
    allowVideoPreview: false,
    allowAudioPreview: false,
  });
  $(".filepond-only-images-and-videos").filepond({
    credits: null,
    allowFileSizeValidation: "true",
    maxFileSize: "5MB",
    labelMaxFileSizeExceeded: "File is too large",
    labelMaxFileSize: "Maximum file size is {filesize}",
    allowFileTypeValidation: true,
    acceptedFileTypes: ["image/*", "video/*"],
    labelFileTypeNotAllowed: "File of invalid type",
    fileValidateTypeLabelExpectedTypes:
      "Expects {allButLastType} or {lastType}",
    storeAsFile: true,
    allowPdfPreview: true,
    pdfPreviewHeight: 320,
    pdfComponentExtraParams: "toolbar=0&navpanes=0&scrollbar=0&view=fitH",
    allowVideoPreview: true,
    allowAudioPreview: true,
  });
});
var elems = Array.prototype.slice.call(
  document.querySelectorAll(".status-switch")
);
elems.forEach(function (elem) {
  var switchery = new Switchery(elem, {
    size: "small",
    color: "#47C363",
    secondaryColor: "#EB4141",
    jackColor: "#ffff",
    jackSecondaryColor: "#ffff",
  });
});
var elems1 = Array.prototype.slice.call(
  document.querySelectorAll(".switchery-yes-no")
);
elems1.forEach(function (elems1) {
  var switchery = new Switchery(elems1, {
    size: "small",
    color: "#47C363",
    secondaryColor: "#EB4141",
    jackColor: "#ffff",
    jackSecondaryColor: "#FFFF",
  });
});
$(document).ready(function () {
  for (let i = 0; i < $(".average_service-ratings").length; i++) {
    let element = $(".average_service-ratings")[i];
    let id = $(".average_service-ratingss")[i]["id"];
    let ratings = $(element).attr("data-value");
    $(document).ready(function () {
      $("#" + id).rateYo({
        rating: ratings,
        spacing: "5px",
        readOnly: true,
        starWidth: "25px",
      });
    });
  }
});
var partner_filter = "";
$("#partner_filter_all").on("click", function () {
  partner_filter = "";
  $("#partner_list").bootstrapTable("refresh");
});
$("#partner_filter_active").on("click", function () {
  partner_filter = "1";
  $("#partner_list").bootstrapTable("refresh");
});
$("#partner_filter_deactivate").on("click", function () {
  partner_filter = "0";
  $("#partner_list").bootstrapTable("refresh");
});
// partner list params
function partner_list_query_params(p) {
  return {
    search: p.search,
    limit: p.limit,
    sort: p.sort,
    order: p.order,
    offset: p.offset,
    partner_filter: partner_filter,
  };
}
var top_rated_provider_filter = "";
$("#order_status_filter").on("change", function () {
  order_status_filter = $(this).find("option:selected").val();
});
$("#filter").on("click", function (e) {
  $("#user_list").bootstrapTable("refresh");
});
$(".repeat_usage").hide();
if ($("input[name='repeat_usage']").is(":checked")) {
  $(".repeat_usage").show();
}
$("#repeat_usage").on("click", function () {
  $(".repeat_usage").hide();
  if ($("input[name='repeat_usage']").is(":checked")) {
    $(".repeat_usage").show();
  }
});
$("#make_payment_for_subscription").on("submit", function (event) {
  event.preventDefault();
  $.post(
    base_url + "/partner/subscription/pre-payment-setup233",
    {
      [csrfName]: csrfHash,
      payment_method: "stripe",
    },
    function (data) {
      $("#stripe_client_secret").val(data.client_secret);
      $("#stripe_payment_id").val(data.id);
      var stripe_client_secret = data.client_secret;
      stripe_payment(stripe1.stripe, stripe1.card, stripe_client_secret);
      csrfName = data.csrfName;
      csrfHash = data.csrfHash;
    },
    "json"
  );
  // }
});
function stripe_payment(stripe, card, clientSecret) {
  stripe
    .confirmCardPayment(clientSecret, {
      payment_method: {
        card: card,
      },
    })
    .then(function (result) {
      if (result.error) {
        var errorMsg = document.querySelector("#card-error");
        errorMsg.textContent = result.error.message;
        setTimeout(function () {
          errorMsg.textContent = "";
        }, 4000);
        Toast.fire({
          icon: "error",
          title: result.error.message,
        });
        $("#buy").attr("disabled", false).html("Buy");
      } else {
        purchase_subscription().done(function (result) {
          if (result.error == false) {
            setTimeout(function () {
              location.href = base_url + "/payment/success";
            }, 1000);
          }
        });
      }
    });
}
function purchase_subscription() {
  let myForm = document.getElementById("make_payment_for_subscription");
  var formdata = new FormData(myForm);
  return $.ajax({
    type: "POST",
    data: formdata,
    url: base_url + "/partner/subscription-payment",
    dataType: "json",
    cache: false,
    processData: false,
    contentType: false,
    beforeSend: function () {
      $("#buy").attr("disabled", true).html("Please Wait...");
    },
    success: function (data) {
      csrfName = data.csrfName;
      csrfHash = data.csrfHash;
      $("#buy").attr("disabled", false).html("Buy");
      if (data.error == false) {
        Toast.fire({
          icon: "success",
          title: data.message,
        });
      } else {
        Toast.fire({
          icon: "error",
          title: data.message,
        });
      }
    },
  });
}
function custome_export(type, label, table_name, excludeColumns = []) {
  var selector = "#" + table_name;
  var $table = $(selector);
  // Check if required libraries are loaded
  // if (type === "pdf" &&(typeof window.jspdf === "undefined" ||  typeof window.jspdf.jsPDF === "undefined")) {
  //   console.error(
  //     "jsPDF library is not loaded. Please check your script inclusions."
  //   );
  //   alert(
  //     "Unable to export to PDF due to missing library. Please contact support."
  //   );
  //   return;
  // }
  // if ( type === "pdf" && typeof window.jspdf.jsPDF.API.autoTable === "undefined") {
  //   console.error(
  //     "jsPDF-AutoTable plugin is not loaded. Please check your script inclusions."
  //   );
  //   alert(
  //     "Unable to export to PDF due to missing plugin. Please contact support."
  //   );
  //   return;
  // }
  // if ((type === "excel" || type === "csv") && typeof XLSX === "undefined") {
  //   console.error(
  //     "SheetJS (XLSX) library is not loaded. Please check your script inclusions."
  //   );
  //   alert(
  //     "Unable to export to Excel/CSV due to missing library. Please contact support."
  //   );
  //   return;
  // }
  // Manually prepare data for export
  var headers = [];
  var data = [];
  $table.find("thead th").each(function (index, th) {
    var headerText = $(th).text().trim();
    if (!excludeColumns.includes(headerText)) {
      headers.push({
        title: headerText,
        dataKey: $(th).data("field") || "column" + index,
        style: {
          fillColor: [240, 240, 240],
          textColor: 50,
          fontStyle: "bold",
        },
      });
    }
  });
  $table.find("tbody tr").each(function (rowIndex, tr) {
    var row = [];
    $(tr)
      .find("td")
      .each(function (colIndex, td) {
        if (colIndex < headers.length) {
          row.push($(td).text().trim());
        }
      });
    if (row.length > 0) {
      data.push(row);
    }
  });
  if (type === "pdf") {
    try {
      var doc = new window.jspdf.jsPDF("l", "pt", "a4");
      doc.autoTable({
        head: [headers.map((h) => h.title)],
        body: data,
        styles: {
          overflow: "linebreak",
          cellWidth: "wrap",
        },
        columnStyles: headers.reduce((acc, h, i) => {
          acc[i] = { cellWidth: "auto" };
          return acc;
        }, {}),
        margin: { top: 50 },
        didDrawPage: function (data) {
          doc.text(label, 40, 30);
        },
      });
      doc.save(label + ".pdf");
    } catch (error) {
      console.error("Error during PDF export:", error);
      alert(
        "An error occurred during PDF export. Please try again or contact support."
      );
    }
  } else if (type === "excel" || type === "csv") {
    try {
      var wb = XLSX.utils.book_new();
      var ws = XLSX.utils.aoa_to_sheet(
        [headers.map((h) => h.title)].concat(data)
      );
      XLSX.utils.book_append_sheet(wb, ws, "Sheet1");
      XLSX.writeFile(wb, label + "." + (type === "excel" ? "xlsx" : "csv"));
    } catch (error) {
      console.error("Error during " + type.toUpperCase() + " export:", error);
      alert(
        "An error occurred during " +
          type.toUpperCase() +
          " export. Please try again or contact support."
      );
    }
  }
}
function DoBeforeAutotable(
  table,
  headers,
  rows,
  AutotableSettings,
  excludeColumns
) {
  if (excludeColumns.length > 0) {
    let headerIndexesToRemove = [];
    headers.forEach((header, index) => {
      if (excludeColumns.includes(header.title)) {
        headerIndexesToRemove.push(index);
      }
    });
    // Sort indices in descending order to prevent index shifting issues
    headerIndexesToRemove.sort((a, b) => b - a);
    // Remove corresponding columns from headers and rows
    headerIndexesToRemove.forEach((index) => {
      headers.splice(index, 1);
      rows.forEach((row) => row.splice(index, 1));
    });
    // Ensure all headers have necessary properties
    headers = headers.map((header) => ({
      title: header.title || "",
      dataKey: header.dataKey || "",
      style: header.style || {},
    }));
    // Update AutotableSettings to reflect changes
    AutotableSettings.columns = headers;
  }
  // Ensure all rows have the correct number of cells
  const headerCount = headers.length;
  rows.forEach((row) => {
    while (row.length < headerCount) {
      row.push(""); // Add empty cells if necessary
    }
  });
}

var service_filter = "";
var service_custom_provider_filter = "";
var service_filter_approve = "";
var service_custom_provider_filter = "";
$("#service_custom_provider_filter").on("change", function () {
  service_custom_provider_filter = $(this).find("option:selected").val();
});
var service_category_custom_filter = "";
$("#service_category_custom_filter").on("change", function () {
  service_category_custom_filter = $(this).find("option:selected").val();
});

$("#service_filter_all").on("click", function (e) {
  $("#service_list").bootstrapTable("refresh");
});

$("#service_filter").on("click", function (e) {
  $("#service_list").bootstrapTable("refresh");
});
$("#customSearch").on("keydown", function () {
  $("#service_list").bootstrapTable("refresh");
  $("#partner_list").bootstrapTable("refresh");
  $("#user_list").bootstrapTable("refresh");
});

function service_list_query_params1(p) {
  return {
    search: $("#customSearch").val() ? $("#customSearch").val() : p.search,
    limit: p.limit,
    sort: p.sort,
    order: p.order,
    offset: p.offset,
    service_filter: service_filter,
    service_custom_provider_filter: service_custom_provider_filter,
    service_category_custom_filter: service_category_custom_filter,
    service_filter_approve: service_filter_approve,
  };
}
function setupColumnToggle(tableId, columns_name, containerId) {
  $(document).ready(function () {
    var $table = $("#" + tableId);
    function toggleColumnVisibility() {
      $(".column-toggle").each(function () {
        var field = $(this).data("field");
        var isVisible = $(this).prop("checked");
        if (isVisible) {
          $table.bootstrapTable("showColumn", field);
        } else {
          $table.bootstrapTable("hideColumn", field);
        }
      });
    }
    $("#columnToggleContainer").on("change", ".column-toggle", function () {
      toggleColumnVisibility();
    });
    var container = $("#" + containerId);
    var row;
    $.each(columns_name, function (index, column) {
      if (index % 2 === 0) {
        row = $("<div>").addClass("row");
      }
      var checkbox = $("<input>")
        .attr("type", "checkbox")
        .addClass("column-toggle")
        .data("field", column.field)
        .prop("checked", column.visible !== false);
      var label = $("<label>")
        .append(checkbox)
        .append(" " + column.label);
      var columnDiv = $("<div>").addClass("col-md-6");
      columnDiv.append(label);
      row.append(columnDiv);
      container.append(row);
    });
    toggleColumnVisibility();
  });
}
function for_drawer(buttonId, drawerId, backdropId, cancelButtonId) {
  $(buttonId).click(function () {
    $(drawerId).toggleClass("open");
    $(backdropId).toggle();
  });
  $(cancelButtonId).click(function () {
    $(drawerId).removeClass("open");
    $(backdropId).hide();
  });
}
var filterBackdrop = document.getElementById("filterBackdrop");
var drawer = document.querySelector(".drawer");
filterBackdrop.addEventListener("click", function () {
  drawer.classList.remove("open");
  filterBackdrop.style.display = "none";
});
$("#filter").click(function () {
  $("#filterDrawer").removeClass("open");
  $("#filterBackdrop").hide();
});
function fetchColumns(tableId) {
  var columns = [];
  $("#" + tableId + " thead th").each(function () {
    var field = $(this).data("field");
    var label = $(this).text().trim();
    var visible = $(this).data("visible") !== false;
    columns.push({
      field: field,
      label: label,
      visible: visible,
    });
  });
  return columns;
}
function copyToClipboard(name) {
  var copyText = document.querySelector("[name=" + name + "]");
  if (copyText) {
    copyText.select();
    document.execCommand("copy");
    showToastMessage("Copied", "success");
  } else {
    showToastMessage("Error copying text", "error");
  }
}
function partner_settlement_and_cash_collection_history_query_params(p) {
  return {
    search: $("#customSearch").val() ? $("#customSearch").val() : p.search,
    limit: p.limit,
    sort: p.sort,
    order: p.order,
    offset: p.offset,
    history_filter: history_filter,
  };
}
function renderChatMessage(message, files) {
  let html = "";
  const totalImages = files.filter((image) => {
    const fileType = image ? image.file_type.toLowerCase() : "";
    return fileType.includes("image");
  }).length;
  files = files.filter((file) => {
    const fileType = file ? file.file_type.toLowerCase() : "";
    return fileType.includes("image");
  });
  if (message.message !== "" && totalImages === 0) {
    html += '<div class="chat-msg-text">' + message.message + "</div>";
  }
  let templateDiv;
  if (totalImages >= 5) {
    html += generateChatMessageHTML(
      message,
      files,
      "five_plus_img_div",
      totalImages
    );
  } else if (totalImages === 4) {
    html += generateChatMessageHTML(
      message,
      files,
      "four_img_div",
      totalImages
    );
  } else if (totalImages === 3) {
    html += generateChatMessageHTML(
      message,
      files,
      "three_img_div",
      totalImages
    );
  } else if (totalImages === 2) {
    html += generateChatMessageHTML(message, files, "two_img_div", totalImages);
  } else if (totalImages === 1) {
    html += generateSingleImageHTML(message, files);
  }
  return html;
}
function generateChatMessageHTML(message, files, templateClass, totalImages) {
  let templateDivHTML = '<div class="chat-msg-text">';
  let templateDiv = $(`.${templateClass}`).clone().removeClass("d-none");
  let templateDiv1 = $("<div></div>");
  let imageLimit =
    templateClass === "five_plus_img_div" ? 5 : templateClass.split("_")[0];
  if (imageLimit == "two") {
    imageLimit = 2;
  } else if (imageLimit == "three") {
    imageLimit = 3;
  } else if (imageLimit == "four") {
    imageLimit = 4;
  }
  $.each(files, function (index, value) {
    if (index < imageLimit) {
      templateDiv.find("img").eq(index).attr("src", value.file);
      templateDiv.find("a").eq(index).attr("href", value.file);
    }
  });
  if (totalImages > imageLimit) {
    let countFile = totalImages - imageLimit;
    templateDiv.find(".img_count").html(`<h2>+${countFile}</h2>`);
    $(document).on("click", ".img_count", function () {
      const images = files.map(
        (file) =>
          `<div class="col-md-3"><a href="${file.file}" data-lightbox="image-1"><img height="200px"width="200px" style="    padding: 8px;
          box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px;
          border-radius: 11px;
          margin: 8px;" src="${file.file}" alt=""></a></div>`
      );
      const rowHtml = `<div class="row">${images.join("")}</div>`;
      $("#imageContainer").html(rowHtml);
      $("#imageModal").modal("show");
    });
  }
  if (message.message !== "") {
    templateDiv1.append(
      '<div style="display: block;">' + message.message + "</div>"
    );
  }
  templateDivHTML += templateDiv.prop("outerHTML");
  templateDivHTML += templateDiv1.prop("outerHTML");
  templateDivHTML += "</div>";
  return templateDivHTML;
}
function generateSingleImageHTML(message, files) {
  let html = "";
  $.each(files, function (index, value) {
    if (index < 1) {
      html += '<div class="chat-msg-text">';
      html +=
        '<a href="' +
        value.file +
        '" data-lightbox="image-1"><img height="80px" src="' +
        value.file +
        '" alt=""></a>';
      if (message.message !== "") {
        html += '<div class="">' + message.message + "</div>";
      }
      html += "</div>";
    }
  });
  return html;
}
function generateFileHTML(file) {
  var html = "";
  if (file && file.file) {
    var fileName = file.file.substring(file.file.lastIndexOf("/") + 1);
    var fileType = file.file_type ? file.file_type.toLowerCase() : "";
    if (
      fileType.includes("excel") ||
      fileType.includes("word") ||
      fileType.includes("text") ||
      fileType.includes("zip") ||
      fileType.includes("sql") ||
      fileType.includes("php") ||
      fileType.includes("json") ||
      fileType.includes("doc") ||
      fileType.includes("octet-stream") ||
      fileType.includes("pdf")
    ) {
      html += '<div class="chat-msg-text">';
      html +=
        '<a href="' +
        file.file +
        '" download="' +
        fileName +
        '" class="text-dark">' +
        fileName +
        "</a>";
      html += '<i class="fa-solid fa-circle-down text-dark ml-2"></i>';
      html += "</div>";
    } else if (fileType.includes("video")) {
      html += '<div class="chat-msg-text ">';
      html +=
        '<video controls class="w-100 h-100" style="height:200px!important;;width:200px!important;">';
      html +=
        '<source src="' +
        file.file +
        '" type="' +
        fileType +
        '" class="text-dark">';
      html += '<i class="fa-solid fa-circle-down text-dark ml-2"></i>';
      html += "</video>";
      html += "</div>";
    }
  }
  return html;
}
function renderMessage(message, currentUserId) {
  var html = "";
  var messageDate = new Date(message.created_at);
  var messageDateStr = "";
  if (
    !lastDisplayedDate ||
    messageDate.toDateString() !== lastDisplayedDate.toDateString()
  ) {
    messageDateStr = getMessageDateHeading(messageDate);
    lastDisplayedDate = messageDate;
  }
  html += messageDateStr;
  var messageClass = message.sender_id == currentUserId ? "owner" : "";
  html += '<div class="chat-msg ' + messageClass + '">';
  html += '<div class="chat-msg-profile">';
  if (message.sender_id != currentUserId) {
    html +=
      '<img class="chat-msg-img" src="' + message.profile_image + '" alt="" />';
  }
  let createdAt = new Date(message.created_at);
  if (message.sender_id != currentUserId) {
    let hours = createdAt.getHours();
    let minutes = createdAt.getMinutes();
    let ampm = hours >= 12 ? "PM" : "AM";
    hours = hours % 12;
    hours = hours ? hours : 12;
    minutes = minutes < 10 ? "0" + minutes : minutes;
    let formattedTime = hours + ":" + minutes + " " + ampm;
    let displayMessage = message.sender_name + ", " + formattedTime;
    html += '<div class="chat-msg-date">' + displayMessage + "</div>";
  } else {
    let hours = createdAt.getHours();
    let minutes = createdAt.getMinutes();
    let ampm = hours >= 12 ? "PM" : "AM";
    hours = hours % 12;
    hours = hours ? hours : 12;
    minutes = minutes < 10 ? "0" + minutes : minutes;
    let formattedTime = hours + ":" + minutes + " " + ampm;
    let displayMessage = formattedTime;
    html += '<div class="chat-msg-date">' + displayMessage + "</div>";
  }
  html += "</div>";
  html += '<div class="chat-msg-content">';
  const chatMessageHTML = renderChatMessage(message, message.file);
  html += chatMessageHTML;
  if (message.file && message.file.length > 0) {
    message.file.forEach(function (file) {
      html += generateFileHTML(file);
    });
  }
  html += "</div>";
  html += "</div>";
  return html;
}
$(".delete-email-template").on("click", function (e) {
  e.preventDefault();
  if (confirm("Are you sure want to delete email template?")) {
    window.location.href = $(this).attr("href");
  }
});
function email_id(element) {
  $("#id").val($(element).data("id"));
}

function removeFilesFromClass(className) {
  let filePondElements = document.getElementsByClassName(className);
  for (let i = 0; i < filePondElements.length; i++) {
    let filePond = FilePond.find(filePondElements[i]);
    if (filePond != null) {
      filePond.removeFiles();
    }
  }
}

function generateSlug(text) {
  return text
    .toLowerCase()
    .replace(/\s+/g, "-")
    .replace(/[^\w-]+/g, "");
}

$("#category_name").on("input", function () {
  let slug = generateSlug($(this).val());
  $("#category_slug").val(slug);
});

// $("#edit_name").on("input", function () {
//   let slug = generateSlug($(this).val());
//   $("#edit_category_slug").val(slug);
// });
