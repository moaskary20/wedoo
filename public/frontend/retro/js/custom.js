/**
 *
 * You can write your JS code here, DO NOT touch the default style file
 * because it will make it harder for you to update.
 *
 */

"use strict";

$("#identity").keydown(function (e) {
  if (e.which === 38 || e.which === 40) {
    e.preventDefault();
  }
});
$("#number").keydown(function (e) {
  if (e.which === 38 || e.which === 40) {
    e.preventDefault();
  }
});
$("#otp").keydown(function (e) {
  if (e.which === 38 || e.which === 40) {
    e.preventDefault();
  }
});

setTimeout(function () {
  $("#logout_msg").hide("slow");
}, 2000);
$(".otp_show").hide();
$("#step_2").hide();
$("#steper_1").addClass("bg-primary");
$("#steper_2").addClass("bg-dark");

function render() {
  window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier("rec");
  recaptchaVerifier.render();
}
// function for send message
var cd = {};
// const code_result = [];
function phoneAuth(code_result) {
  var number =
    "" +
    document.getElementById("country_code").value +
    document.getElementById("number").value;
  document.getElementById("phone").value =
    document.getElementById("number").value;
  document.getElementById("store_country_code").value =
    document.getElementById("country_code").value;

  firebase
    .auth()
    .signInWithPhoneNumber(number, window.recaptchaVerifier)
    .then(function (confirmationResult) {
      window.confirmationResult = confirmationResult;
      // console.log(confirmationResult);
      code_result = confirmationResult;
      cd = code_result;
      $("#send").hide();
      $(".otp_show").show();
      $(".step").html(2);
    })
    .catch(function (error) {
      alert(error.message);
    });
}

// function for code verify

function sms_codeverify() {
  if ($("#otp").val() == "") {
    Swal.fire({
      icon: "warning",
      title: "Oops...",
      text: "Please Enter OTP before proceeding any further",
    });
  } else {
    var code = document.getElementById("otp").value;
    $.ajax({
      type: "POST",
      url: "verify_sms_otp",
      data: {
        code: code,
        number: document.getElementById("number").value,
      },
      dataType: "json",
      success: function (response) {
        if (response.error == false) {
          showToastMessage(response.message, "success");
          $("#step_2").show();
          $("#step_1").hide();
          $(".step").html(3);
          $("#steper_1").addClass("bg-dark");
          $("#steper_2").removeClass("bg-dark");
          $("#steper_2").addClass("bg-primary");
        } else {
          Swal.fire({
            icon: "error",
            title: "Oops...",
            text: "Entered Otp is Wrong please Confirm it... and try Again",
          });
        }
      },
    });
  }
}

function codeverify() {
  if ($("#otp").val() == "") {
    Swal.fire({
      icon: "warning",
      title: "Oops...",
      text: "Please Enter OTP before proceeding any further",
    });
  } else {
    var code = document.getElementById("otp").value;
    cd.confirm(code)
      .then(function () {
        $("#step_2").show();
        $("#step_1").hide();
        $(".step").html(3);

        $("#steper_1").addClass("bg-dark");
        $("#steper_2").removeClass("bg-dark");
        $("#steper_2").addClass("bg-primary");
      })
      .catch(function () {
        Swal.fire({
          icon: "error",
          title: "Oops...",
          text: "Entered Otp is Wrong please Confirm it... and try Again",
        });
      });
  }
}
$(document).ready(() => {
  //select2
  setTimeout(() => {
    $("#country_code").select2({
      placeholder: "Select Country Code",
    });
  }, 100);
});

function sms_phoneAuth() {
  $.ajax({
    type: "POST",
    url: "send_sms_otp",
    data: {
      number: document.getElementById("number").value,
      country_code: document.getElementById("country_code").value,
    },
    dataType: "json",
    success: function (response) {
      if (response.error == false) {
        showToastMessage(response.message, "success");
        $("#send").hide();
        $(".otp_show").show();
        $(".step").html(2);
      } else {
        showToastMessage(response.message, "error");
      }
    },
  });
}

$("#sender").on("click", function () {
  $.ajax({
    type: "POST",
    url: "check_number",
    data: {
      number: document.getElementById("number").value,
      country_code: document.getElementById("country_code").value,
    },

    dataType: "json",
    success: function (response) {
      if (response.error == false) {
        if (response.data.authentication_mode == "sms_gateway") {
          sms_phoneAuth();
        } else if (response.data.authentication_mode == "firebase") {
          phoneAuth();
        }
      } else {
        showToastMessage(response.message, "error");

        setTimeout(function() {
          window.location.href = baseUrl + "/auth/create_user";
        }, 60000);
      
      }
    },
  });
});

$("#sender_forgot_password").on("click", function () {
  $.ajax({
    type: "POST",
    url: "check_number_for_forgot_password",
    data: {
      number: document.getElementById("number").value,
      country_code: document.getElementById("country_code").value,
    },

    dataType: "json",
    success: function (response) {
      csrfName = response.csrfName;
      csrfHash = response.csrfHash;

      if (response.error == false) {
        if (response.data.authentication_mode == "sms_gateway") {
          SMSphoneAuthForForgotPassword();
        } else if (response.data.authentication_mode == "firebase") {
          phoneAuthForForgotPassword();
        }
      } else {
    
        showToastMessage(response.message, "error");
        setTimeout(function() {
          window.location.href = baseUrl + "/auth/forgot-password/";
        }, 60000);
        
      }
    },
    error: function (jqXHR, textStatus, errorThrown) {
      // Handle the error here
      console.error("Ajax request failed:", jqXHR.status, textStatus);
      console.error("Error Details:", errorThrown);

      // Check if the response is HTML or some other non-JSON content
      if (jqXHR.status === 200 && jqXHR.responseText.startsWith("<")) {
        // The response is HTML; handle it as needed
        console.error("HTML response received:", jqXHR.responseText);
        // Display an error message or take appropriate action
      } else {
        // The response is not HTML; it might be another format or an unexpected error
        // Handle it as needed
      }
    },
  });
});

function SMSphoneAuthForForgotPassword() {
  $.ajax({
    type: "POST",
    url: "send_sms_otp",
    data: {
      number: document.getElementById("number").value,
      country_code: document.getElementById("country_code").value,
    },
    dataType: "json",
    success: function (response) {
      if (response.error == false) {
        showToastMessage(response.message, "success");
        $("#send").hide();
        $(".otp_show").show();
        setTimeout(function () {
          $(".step").html(2);
        }, 60000);
        // $(".step").html(2);
      } else {
        showToastMessage(response.message, "error");
      }
    },
  });
}
function phoneAuthForForgotPassword(code_result) {
  var number =
    "" +
    document.getElementById("country_code").value +
    document.getElementById("number").value;
  document.getElementById("phone").value =
    document.getElementById("number").value;
  document.getElementById("store_country_code").value =
    document.getElementById("country_code").value;

  firebase
    .auth()
    .signInWithPhoneNumber(number, window.recaptchaVerifier)
    .then(function (confirmationResult) {
      window.confirmationResult = confirmationResult;
      // console.log(confirmationResult);
      code_result = confirmationResult;
      cd = code_result;

      $("#send").hide();
      $(".otp_show").show();
      // $(".step").html(2);

      setTimeout(function () {
        $(".step").html(2);
      }, 60000);
    })
    .catch(function (error) {
      alert(error.message);
    });
}
$("#register").on("submit", function (e) {
  e.preventDefault();

  var form = $(this);
  $.ajax({
    type: "POST",
    url: baseUrl + "/auth/reset",
    data: form.serialize(),
    dataType: "json",

    success: function (response) {
      // console.log(response);
      // console.log("success");
      if (response.error == false) {
        window.location.href = baseUrl + "/partner/login";
      } else {
        iziToast.error({
          title: "Error",
          message: response.message,
          position: "topRight",
        });
      }
    },
  });
});

$("#forgot_password").on("submit", function (e) {
  e.preventDefault();

  var form = $(this);
  $.ajax({
    type: "POST",
    url: baseUrl + "/auth/reset_password_otp",
    data: form.serialize(),
    dataType: "json",

    success: function (response) {
      // console.log(response);
      if (response.error == false) {
        window.location.href = baseUrl + "/partner/login";
      } else {
        iziToast.error({
          title: "Error",
          message: response.message,
          position: "topRight",
        });
      }
    },
  });
});
