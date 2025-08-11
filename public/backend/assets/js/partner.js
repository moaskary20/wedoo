"use strict";
function showToastMessage(message, type) {
  switch (type) {
    case "error":
      $().ready(
        iziToast.error({
          title: "Error",
          message: message,
          position: "topRight",
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
function set_locale(language_code) {
  $.ajax({
    url: baseUrl + "/lang/" + language_code,
    type: "GET",
    success: function (result) {},
  }).then(() => {
    location.reload();
  });
}
function detailFormatter(index, row) {
  var html = [];
  $.each(row, function (key, value) {
    if (key != "base_64" && key != "is_ssml" && key != "operate") {
      html.push("<p><b>" + key + ":</b> " + value + "</p>");
    }
  });
  return html.join("");
}
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
              setTimeout(() => {
                $("#user_list").bootstrapTable("refresh");
              }, 500);
              window.location.reload();
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
window.promo_codes_events = {
  "click .delete": function (e, value, row, index) {
    e.preventDefault();
    var id = row.id;
    Swal.fire({
      title: are_your_sure,
      text: "be aware this shall fordid the data",
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      if (result.isConfirmed) {
        $.post(
          baseUrl + "/partner/promo_codes/delete",
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
                $("#promocode_table").bootstrapTable("refresh");
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
  "click .edit": function (e, value, row, index) {
    $("#image_edit").html("");
    e.preventDefault();
    var img = row.image;
    $('input[name="promo_id"]').val(row.id);
    $('input[name="promo_code"]').val(row.promo_code);
    $('input[name="start_date"]').val(row.start_date);
    $('input[name="end_date"]').val(row.end_date);
    $('textarea[name="message"]').val(row.message);
    $('input[name="discount"]').val(row.discount);
    $('input[name="max_discount_amount"]').val(row.max_discount_amount);
    $('input[name="minimum_order_amount"]').val(row.minimum_order_amount);
    $("#discount_type").val(row.discount_type).trigger("change");
    setTimeout(function () {
      if (row.status == "1") {
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
$(document).on("submit", "#withdrawal_request_form", function (e) {
  e.preventDefault();
  var formData = new FormData(this);
  formData.append(csrfName, csrfHash);
  $.ajax({
    type: "post",
    url: this.action,
    data: formData,
    cache: false,
    processData: false,
    contentType: false,
    dataType: "json",
    success: function (result) {
      csrfName = result["csrf_token"];
      csrfHash = result["csrf_hash"];
      if (result.error == true) {
        var message = "";
        Object.keys(result.message).map((key) => {
          iziToast.error({
            title: "Error!",
            message: result.message[key],
            position: "topRight",
          });
        });
      } else {
        showToastMessage(result.message, "success");
        setTimeout(function () {
          location.href = baseUrl + "/partner/withdrawal_requests";
        }, 500);
      }
    },
  });
});
window.payment_request_events = {
  "click .delete": function (e, value, row, index) {
    e.preventDefault();
    var id = row.id;
    Swal.fire({
      title: are_your_sure,
      text: "be aware this shall fordid the data",
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      if (result.isConfirmed) {
        $.post(
          baseUrl + "/partner/withdrawal_requests/delete",
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
                $("#withdrawal_requests_table").bootstrapTable("refresh");
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
  "click .edit": function (e, value, row, index) {
    e.preventDefault();
    $('input[name="user_id"]').val(row.user_id);
    $('input[name="request_id"]').val(row.id);
    $('input[name="amount"]').val(row.amount);
    $('textarea[name="payment_address"]').val(row.payment_address);
  },
};
$(document).on("submit", ".form-submit-event", function (e) {
  e.preventDefault();
  var formData = new FormData(this);
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
        showToastMessage(response.message, "success");
        setTimeout(() => {
          $("#user_list").bootstrapTable("refresh");
          window.location.reload();
          submit_btn.attr("disabled", false);
          submit_btn.html(button_text);
        }, 500);
        $(".close").click();
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
function readURL(input) {
  var reader = new FileReader();
  reader.onload = function (e) {
    document
      .querySelector("#service_image")
      .setAttribute("src", e.target.result);
    document
      .querySelector("#update_service_image")
      .setAttribute("src", e.target.result);
  };
  reader.readAsDataURL(input.files[0]);
}
$(document).ready(() => {
  setTimeout(() => {
    $("#category_item").select2({
      placeholder: "Select Category",
    });
    $("#sub_category").select2({
      placeholder: "Select sub Category",
    });
  }, 100);
});
$("#category_item").on("change", function (e) {
  e.preventDefault();
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
          $("#sub_category").val(element.id);
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
window.services_events = {
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
        $.post(
          baseUrl + "/partner/services/delete_service",
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
            }
          }
        );
      }
    });
  },
  "click .edit": function (e, value, row, index) {
    e.preventDefault();
    $("#sub_category").empty();
    $(".image").empty();
    $("#update_modal").on("hide.bs.modal", function () {
      $('input[name="is_cancelable"]').prop("checked", false);
    });
    $('input[name="service_id"]').val(row.id);
    $('input[name="user_id"]').val(row.user_id);
    $('input[name="title"]').val(row.title);
    $('textarea[name="description"]').val(row.description);
    $('input[name="tags[]"]').val(row.tags);
    $('input[name="price"]').val(row.price);
    $('input[name="discounted_price"]').val(row.discounted_price);
    $('input[name="members"]').val(row.number_of_members_required);
    $('input[name="duration"]').val(row.duration);
    $('input[name="max_qty"]').val(row.max_quantity_allowed);
    $('input[name="tax"]').val(row.tax);
    $('input[name="tax_type"]').val(row.tax_type);
    $("#category_item").val(row.category_id);
    $("#tax_id").val(row.tax_id);
    $("#category_item").val(row.category_id).trigger("change");
    $("#edit_tax_type").val(row.tax_type.trim());
    $("#edit_tax").val(row.tax_id);
    if (row.cancelable == "1") {
      $('input[name="is_cancelable"]').prop("checked", true);
      $(".cancelable-till").show();
      $("#cancelable_till").val(row.cancelable_till);
    } else {
      $('input[name="is_cancelable"]').prop("checked", false);
      $(".cancelable-till").hide();
      $("#cancelable_till").val("");
    }
    if (row.status_number == "1") {
      $("#edit_status_active").prop("checked", true);
    } else {
      $("#edit_status_deactive").prop("checked", true);
    }
    if (row.is_pay_later_allowed == "1") {
      $('input[name="pay_later"]').attr("checked", true);
    } else {
      $('input[name="pay_later"]').attr("checked", false);
    }
    $(".image").append(row.image_of_the_service);
    $('input[name="old_icon"]').val(row.image_of_the_service);
  },
};
$(".cancelable-till").hide();
if ($("input[name='is_cancelable']").is(":checked")) {
  $(".cancelable-till").show();
}
$("#is_cancelable").on("click", function () {
  $(".cancelable-till").hide();
  if ($("input[name='is_cancelable']").is(":checked")) {
    $(".cancelable-till").show();
  }
});
var order_status_filter = "";
$("#order_status_filter").on("change", function () {
  order_status_filter = $(this).find("option:selected").val();
});
$("#filter").on("click", function (e) {
  $("#user_list").bootstrapTable("refresh");
});
function orders_query(p) {
  return {
    search: p.search,
    limit: p.limit,
    sort: p.sort,
    order: p.order,
    offset: p.offset,
    order_status_filter: order_status_filter,
  };
}
function withdraw_request_query(p) {
  return {
    search: p.search,
    limit: p.limit,
    sort: p.sort,
    order: p.order,
    offset: p.offset,
  };
}
function update_order_status() {
  var status = $(".update_order_status").val();
  var customer_id = $(".update_order_status").attr("data-customer_id");
  var order_id = $('input[name="order_id"]').val();
  Swal.fire({
    title: are_your_sure,
    text: "you want to update order status!",
    icon: "error",
    showCancelButton: true,
    confirmButtonText: yes_proceed,
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        type: "get",
        url: siteUrl + "/partner/orders/update_order_status",
        data: {
          status: status,
          order_id: order_id,
          customer_id: customer_id,
        },
        cache: false,
        dataType: "json",
        success: function (result) {
          if (result.error == false) {
            if (result.contact != null) {
              Swal.fire({
                title: "call?",
                text: result.contact,
                icon: "error",
                showCancelButton: true,
                confirmButtonText: "Ok!",
              });
            }
            showToastMessage(result.message, "success");
          } else {
            showToastMessage(result.message, "error");
            setTimeout(() => {
              location.reload();
            }, 2000);
            return;
          }
        },
      });
    }
  });
}

let autocomplete;
let map;
let marker = "";
let partner_location = "";
var partner_map = document.getElementById("map");
var latitude = $("#partner_latitude").val();
var longitude = $("#partner_longitude").val();
let center = {
  lat: parseFloat(latitude),
  lng: parseFloat(longitude),
};
function initautocomplete() {
  if ($("#city_search").length > 0) {
    autocomplete = new google.maps.places.Autocomplete(
      document.getElementById("city_search"),
      {
        types: ["establishment"],
        componentRestriction: {
          country: ["India"],
        },
        fields: ["place_id", "geometry", "name"],
      }
    );
    autocomplete.addListener("place_changed", onPlaceChanged);
    var place = autocomplete.getPlace();
  }
  latitude =
    typeof place != "undefined"
      ? place.geometry.location.lat()
      : parseFloat(latitude);
  longitude =
    typeof place != "undefined"
      ? place.geometry.location.lng()
      : parseFloat(longitude);
  var center = {
    lat: latitude,
    lng: longitude,
  };
  if (document.getElementById("map") != null) {
    partner_location = new google.maps.Map(document.getElementById("map"), {
      center,
      zoom: 16,
    });
    set_map_marker_for_partner("", latitude, longitude, "", partner_location);
    /* add marker on clicked location */
    google.maps.event.addListener(partner_location, "click", function (event) {
      var latitude = event.latLng.lat();
      var longitude = event.latLng.lng();
      set_map_marker_for_partner("", latitude, longitude, "", partner_location);
      $("#partner_latitude").val(latitude);
      $("#partner_longitude").val(longitude);
    }); //end addListener
  }
  function onPlaceChanged(e) {
    place = autocomplete.getPlace();
    let latitude = place.geometry.location.lat();
    let longitude = place.geometry.location.lng();
    set_map_marker_for_partner(place, "", "", "", partner_location);
    $("#partner_latitude").val(latitude);
    $("#partner_longitude").val(longitude);
  }
}
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
window.initMap = initautocomplete;
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

$(".filepond-other_image").filepond({
  credits: null,
  allowFileSizeValidation: "true",
  maxFileSize: "25MB",
  labelMaxFileSizeExceeded: "File is too large",
  labelMaxFileSize: "Maximum file size is {filesize}",
  allowFileTypeValidation: true,
  acceptedFileTypes: ["image/*", "video/*", "application/pdf"],
  labelFileTypeNotAllowed: "File of invalid type",
  fileValidateTypeLabelExpectedTypes: "Expects {allButLastType} or {lastType}",
  storeAsFile: true,
  allowPdfPreview: true,
  pdfPreviewHeight: 320,
  pdfComponentExtraParams: "toolbar=0&navpanes=0&scrollbar=0&view=fitH",
  allowVideoPreview: true,
  allowAudioPreview: true,
  allowMultiple: true,
});
if ($(".summernotes").length) {
  tinymce.init({
    selector: ".summernotes",
    height: 300,
    menubar: false,
    plugins: [
      "advlist autolink lists link image charmap print preview anchor",
      "searchreplace visualblocks code fullscreen",
      "insertdatetime media table paste",
    ],
    toolbar:
      "undo redo | styleselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image table code",
    maxlength: null,
    relative_urls: false,
    remove_script_host: false,
    document_base_url: baseUrl,
  });
}
var stripe1;
$("#make_payment_for_subscription").on("submit", function (event) {
  event.preventDefault();
  stripe1 = stripe_setup($("#stripe_key_id").val());
  $.post(
    baseUrl + "/partner/subscription/pre-payment-setup",
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
});
function stripe_setup(key) {
  var stripe = Stripe(key);
  var elements = stripe.elements();
  var style = {
    base: {
      color: "#32325d",
      fontFamily: "Arial, sans-serif",
      fontSmoothing: "antialiased",
      fontSize: "16px",
      "::placeholder": {
        color: "#32325d",
      },
    },
    invalid: {
      fontFamily: "Arial, sans-serif",
      color: "#fa755a",
      iconColor: "#fa755a",
    },
  };
  var card = elements.create("card", {
    style: style,
  });
  card.mount("#stripe-card-element");
  card.on("change", function (event) {
    document.querySelector("button").disabled = event.empty;
    document.querySelector("#card-error").textContent = event.error
      ? event.error.message
      : "";
  });
  return {
    stripe: stripe,
    card: card,
  };
}
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
              location.href = baseUrl + "payment/success";
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
    url: base_url + "partner/subscription-payment",
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
var stripe1;
$(document).ready(function () {
  $("#make_payment_for_subscription").on("submit", function (event) {
    stripe1 = stripe_setup($("#stripe_key_id").val());
    event.preventDefault();
    $.post(
      baseUrl + "/partner/subscription/pre-payment-setup",
      {
        [csrfName]: csrfHash,
        payment_method: "stripe",
      },
      function (data) {
        $("#stripe_client_secret").val(data.client_secret);
        $("#stripe_payment_id").val(data.id);
        var stripe_client_secret = data.client_secret;
        stripe_payment(stripe_client_secret);
        csrfName = data.csrfName;
        csrfHash = data.csrfHash;
      },
      "json"
    );
  });
});
function stripe_setup(key) {
  var stripe = Stripe(key);
  var elements = stripe.elements();
  var style = {
    base: {
      color: "#32325d",
      fontFamily: "Arial, sans-serif",
      fontSmoothing: "antialiased",
      fontSize: "16px",
      "::placeholder": {
        color: "#32325d",
      },
    },
    invalid: {
      fontFamily: "Arial, sans-serif",
      color: "#fa755a",
      iconColor: "#fa755a",
    },
  };
  var card = elements.create("card", {
    style: style,
  });
  card.mount("#stripe-card-element");
  card.on("change", function (event) {
    document.querySelector("button").disabled = event.empty;
    document.querySelector("#card-error").textContent = event.error
      ? event.error.message
      : "";
  });
  return {
    stripe: stripe,
    card: card,
  };
}
function stripe_payment(clientSecret) {
  stripe1.stripe
    .confirmCardPayment(clientSecret, {
      payment_method: {
        card: stripe1.card,
      },
    })
    .then(function (result) {
      if (result.error) {
        var errorMsg = document.querySelector("#card-error");
        errorMsg.textContent = result.error.message;
        setTimeout(function () {
          errorMsg.textContent = "";
        }, 4000);
        $("#buy").attr("disabled", false).html("Buy");
        return showToastMessage(result.error.message, "error");
      } else {
        purchase_subscription().done(function (result) {
          if (result.error == false) {
            setTimeout(function () {
              location.href = baseUrl + "payment/success";
            }, 1000);
          }
        });
      }
    });
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
function custome_export(type, label, table_name) {
  var selector = "#" + table_name;
  if (type === "pdf") {
    $(selector).tableExport({
      fileName: label,
      type: "pdf",
      jspdf: {
        format: "bestfit",
        margins: {
          left: 20,
          right: 10,
          top: 50,
          bottom: 20,
        },
        autotable: {
          styles: {
            overflow: "linebreak",
          },
          tableWidth: "wrap",
          tableExport: {
            onBeforeAutotable: DoBeforeAutotable,
            onCellData: DoCellData,
          },
        },
      },
    });
  } else if (type === "excel") {
    $(selector).tableExport({
      fileName: label,
      type: "excel",
    });
  } else if (type === "csv") {
    $(selector).tableExport({
      fileName: label,
      type: "csv",
    });
  }
}
function DoCellData(cell, row, col, data) {}
function DoBeforeAutotable(table, headers, rows, AutotableSettings) {}
function doDocCreated(doc) {
  var PartName = $("#filter_party").find("option:selected").data("name");
  PartName = "WayBill Report | " + PartName + " | " + $("#filter_date").val();
  doc.text(500, 30, PartName);
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
    templateDiv.find(".img_count").removeClass("d-none");
    let countFile = totalImages - imageLimit;
    templateDiv.find(".img_count").html(`<h2>+${countFile}</h2>`);
    $(document).on("click", ".img_count", function () {
      const images = files.map(
        (
          file
        ) => `<div class="col-md-3"><a href="${file.file}" data-lightbox="image-1"><img height="200px" width="200px" style="    padding: 8px;
      border-radius: 11px;
      box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px;
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
        '" class="text-white">' +
        fileName +
        "</a>";
      html += '<i class="fa-solid fa-circle-down text-white ml-2"></i>';
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
        '" class="text-white">';
      html += '<i class="fa-solid fa-circle-down text-white ml-2"></i>';
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
  if (message.receiver_id != currentUserId) {
    let hours = createdAt.getHours();
    let minutes = createdAt.getMinutes();
    let ampm = hours >= 12 ? "PM" : "AM";
    hours = hours % 12;
    hours = hours ? hours : 12;
    minutes = minutes < 10 ? "0" + minutes : minutes;
    let formattedTime = hours + ":" + minutes + " " + ampm;
    let displayMessage = formattedTime;
    html += '<div class="chat-msg-date">' + displayMessage + "</div>";
  } else {
    let hours = createdAt.getHours();
    let minutes = createdAt.getMinutes();
    let ampm = hours >= 12 ? "PM" : "AM";
    hours = hours % 12;
    hours = hours ? hours : 12;
    minutes = minutes < 10 ? "0" + minutes : minutes;
    let formattedTime = hours + ":" + minutes + " " + ampm;
    let displayMessage = message.sender_name + ", " + formattedTime;
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
function openBookingChat(bookingId, sender_id, receiver_id) {
  $.ajax({
    url: baseUrl + "/partner/provider_booking_chat_list",
    type: "POST",
    data: {
      order_id: bookingId,
      sender_id: sender_id,
      receiver_id: receiver_id,
    },
    dataType: "json",
    success: function (response) {
      window.location.href =
        baseUrl + "/partner/provider-booking-chats/" + bookingId;
    },
    error: function (xhr, status, error) {},
  });
}