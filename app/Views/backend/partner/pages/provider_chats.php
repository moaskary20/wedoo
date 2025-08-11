<!-- Main Content -->
<?php
$current_url = current_url();
$url = strpos($current_url, "provider-booking-chats");
$requestUri = $_SERVER['REQUEST_URI'];
$segments = explode('/', $requestUri);
$lastSegment = end($segments);
if (is_numeric($lastSegment)) {
    $bookingId = intval($lastSegment);
}
?>
<div class="main-content">
    <section class="section" id="pill-about_us" role="tabpanel">
        <div class="section-header mt-2">
            <h1> <?= labels('chat', "Chat") ?>
                <span class="breadcrumb-item p-3 pt-2 text-primary">
                </span>
            </h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item"></i> <?= labels('chat', "Chat") ?></div>
            </div>
        </div>
        <div id="notification_div" class="alert alert-warning alert-has-icon">
            <div class="alert-icon"><i class="fa-solid fa-circle-exclamation mr-2"></i></div>
            <div class="alert-body">
                <div class="alert-title"><?= labels('note', 'Note') ?></div>
                <div id="status" class=""></div>
            </div>
        </div>
        <div class="card" style="border:0!important;border-radius:0!important">
            <div class="card-body" style="padding: 0!important;">
                <div class="chat-app">
                <button id="toggleConversationAreaBtn"><?= labels('chat_list', "Chat List") ?></button>

                    <div class="wrapper">
                        <div class="conversation-area" id="">
                            <div class="customer_list_heading"><?= labels('customer_list', 'Customer List') ?></div>
                            <div id="customer" class="tabcontent">
                                <div class="search-bar">
                                    <input type="text" id="customer-search" placeholder="Search customer..." />
                                </div>
                                <hr class="mb-0">
                            </div>
                            <div id="customer" class="">
                                <div id="customer-list">
                                    <?php foreach ($customers as $user) : ?>
                                        <div class="msg" onclick="setallMessage(<?= $user['id'] ?>, this, 'customer','<?= $user['order_id'] ?>')" data-customer-id="<?= $user['id'] ?>">
                                            <img class="msg-profile" src="<?= $user['profile_image'] ?>" alt="" />
                                            <div class="msg-detail">
                                                <div class="msg-username"><?= $user['username']; ?></div>
                                                <div class="featured_tag">
                                                    <?php
                                                    if (strpos($user['order_id'], "enquire") !== false) { ?>
                                                        <div class="featured_lable">Enquiry :: </div>
                                                    <?php } else { ?>
                                                        <div class="featured_lable">Booking No ::<?= $user['order_id'] ?></div>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="overlay"></div>
                        </div>
                        <div class="chat-area myscroll">
                            <div class="chat_header d-none" style="padding:16px">
                                <img alt="" id="receiver_user_profile" class="img-circle medium-image" src="">
                                <div>
                                    <b id="receiver_username"> </b>
                                    <br>
                                    <b id="receiver_booking_id"></b>
                                </div>
                            </div>
                            <div class="chat-area-main myscroll" id="chat-area-main">
                                <div class="welcome-card">
                                    <p>
                                        <img width="200" height="200" src="<?= base_url('public/uploads/site/black chat section img.svg') ?>" alt="Welcome Image">
                                    </p>
                                    <?php $data = get_settings('general_settings', true); ?>
                                    <h1 class="welcome-title"><?= labels('welcome_to', 'Welcome to ') ?>&nbsp; <?= (isset($data['company_title']) && $data['company_title'] != "") ? $data['company_title'] : "eDemand"; ?></h1>
                                    <h6 class="welcome-subtitle">
                                        <?= labels('chat_welcome_card_subtitle', 'Pick a person from the left menu and start your conversation') ?>
                                    </h6>
                                </div>
                            </div>
                            <div id="filePreviewContainer"></div>
                            <div class="chat-area-footer1 d-none"></div>
                            <div class="chat-area-footer d-none" style="display: flex; align-items: center;">
                                <form action="<?= base_url('admin/store_chat') ?>" method="post" style="flex: 1; display: flex; align-items: center;" enctype="multipart/form-data">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus-circle" id="svgFileInput" style="margin-right: 5px;">
                                        <circle cx="12" cy="12" r="10" />
                                        <path d="M12 8v8M8 12h8" />
                                    </svg>
                                    <input id="fileInput" name="attachment[]" multiple type="file" style="display: none; margin-right: 5px;" />

                                    <textarea
                                        class="two"
                                        id="message"
                                        name="message"
                                        placeholder="Type something here..."
                                        style="flex: 1; margin-right: 5px;"
                                        rows="1">
                                    </textarea>
                                    <!-- <input type="text" class="one" id="message" name="message" placeholder="Type something here..." style="flex: 1; margin-right: 5px;" /> -->
                                    <input type="hidden" id="sender_id" name="sender_id" value="" />
                                    <input type="hidden" id="receiver_id" name="receiver_id" value="" />
                                    <input type="hidden" id="order_id" name="order_id" value="" />
                                    <input type="hidden" id="user_type_for_send_message" name="user_type_for_send_message" value="" />
                                    <button id="send_button" class="btn bg-primary text-white" onclick="OnsendMessage();" disabled>
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include "images_preview_cards.php"; ?>
</div>
</section>
<div class="modal fade" id="imageModal" role="dialog" aria-labelledby="view-video" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Images</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="imageContainer" class="row"></div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
<script>
    $(document).ready(function() {
        $("#filePreviewContainer").hide();
        $('#message').on('input', function() {
            var maxLength = <?= $maxCharactersInATextMessage ?>;
            var message = $(this).val().trim();
            var messageLength = message.length;
         
            if (messageLength > maxLength) {
                $(this).val(message.substring(0, maxLength));
                showToastMessage("Maximum length of " + <?= $maxCharactersInATextMessage ?> + " characters exceeded. Message trimmed", "error");
            }
            if (message === '' || messageLength >= maxLength) {
                $('#send_button').prop('disabled', true);
            } else {
                $('#send_button').prop('disabled', false);
            }
        });
    });
    function OnsendMessage() {
        var message = $('#message').val();
        var receiver_id = $('#receiver_id').val();
        var order_id = $('#order_id').val();
        var user_type_for_send_message = $('#user_type_for_send_message').val();
        $('#send_button').html('<i class="fas fa-spinner fa-spin"></i>');
        $('#send_button').prop('disabled', true);
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var fd = new FormData();
        fd.append('message', message);
        fd.append('sender_id', <?= $current_user_id ?>);
        fd.append('receiver_id', receiver_id);
        fd.append('order_id', order_id);
        fd.append('user_type_for_send_message', user_type_for_send_message);
        var fileInput = document.getElementById('fileInput');
        var files = fileInput.files;
        for (var i = 0; i < files.length; i++) {
            fd.append('attachment[]', files[i]);
        }
        $.ajax({
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = ((evt.loaded / evt.total) * 100);
                        $(".progress-bar").width(percentComplete + '%');
                        $(".progress-bar").html(percentComplete + '%');
                    }
                }, false);
                return xhr;
            },
            url: baseUrl + '/partner/store_booking_chat',
            enctype: 'multipart/form-data',
            type: "POST",
            dataType: 'json',
            data: fd,
            processData: false,
            contentType: false,
            async: true,
            cache: false,
            success: function(data) {
                if(data.error==true){
                    showToastMessage(data.message, "error");
                }

                if (data.error == false) {
                    $('#message').val('');
                    $('#fileInput').val('');
                    $("#filePreviewContainer").html('');
                    $("#filePreviewContainer").hide();
                    setTimeout(function() {
                        $('#send_button').html('<i class="fas fa-paper-plane"></i>');
                        $('#send_button').prop('disabled', true);
                    }, 2000);
                    var message = data.message;
                    appendMessageToChatArea(data.data);
                }
                if (data.error == true) {
                    submitButton.text('Send');
                    alert('there is No Chat');
                }
            },
            error: function(response) {
                return showToastMessage(response.message, "error");
            }
        });
    }
    $('#message').keypress(function(event) {
        var maxLength = <?= $maxCharactersInATextMessage ?>;
        var message = $(this).val().trim();
        var messageLength = message.length;

        if (event.which === 13 && !event.shiftKey) { // Enter key pressed without Shift
            event.preventDefault();

            if (message === '' || messageLength >= maxLength) {
                $('#send_button').prop('disabled', true);
                if (messageLength >= maxLength) {
                    showToastMessage("Maximum length of " + maxLength + " characters exceeded. Message not sent", "error");
                }
            } else {
                $('#send_button').prop('disabled', false);
                OnsendMessage();
            }
        }
    });
    var lastDisplayedDate = null;
    function appendMessageToChatArea(message) {
        var html = '';
        var profileImage = message.profile_image ? message.profile_image : '';
        var timeAgo = message.created_at ? extractTime(message.created_at) : '';
        var messageDate = new Date(message.created_at);
        var lastDisplayedDate = new Date(message.last_message_date);
        var dateStr = '';
        if (!lastDisplayedDate || messageDate.toDateString() !== lastDisplayedDate.toDateString()) {
            dateStr = getMessageDateHeading(messageDate);
            lastDisplayedDate = messageDate;
        }
        html += dateStr;
        html += '<div class="chat-msg owner">';
        html += '<div class="chat-msg-profile">';
        html += '<img class="chat-msg-img" src="' + profileImage + '" alt="" />';
        html += '<div class="chat-msg-date">' + timeAgo + '</div>';
        html += '</div>';
        html += '<div class="chat-msg-content">';
        var files = message.file;
        const chatMessageHTML = renderChatMessage(message, files);
        html += chatMessageHTML;
        if (files && files.length > 0) {
            files.forEach(function(file) {
                html += generateFileHTML(file);
            });
        }
        html += '</div>';
        html += '</div>';
        $('.chat-area-main').append(html);
        $('.myscroll').animate({
            scrollTop: $('.myscroll').get(0).scrollHeight
        }, 1500);
    }
    function checkBookingStatus(order_id, user_type, callback) {
        $('#order_id').val(order_id);
        $('#user_type_for_send_message').val(user_type);
        $.ajax({
            url: baseUrl + '/partner/check_booking_status',
            type: "POST",
            dataType: 'json',
            data: {
                order_id: order_id,
                user_type: user_type
            },
            success: function(data) {
                callback(data.status);
            },
            error: function(xhr, status, error) {
             
            }
        });
    }
    function setallMessage(id, element, user_type, order_id) {
        $("#filePreviewContainer").hide();
        var allProfiles = document.querySelectorAll('.msg');
        allProfiles.forEach(function(profile) {
            profile.classList.remove('active');
        });
        element.classList.add('active');
        var receiver_id = id;
        $('#receiver_id').val(receiver_id);
        $('#order_id').val(order_id);
        $('#sender_id').val(<?= $current_user_id ?>);
        $('#user_type_for_send_message').val(user_type);
        $('.chat-area-main').text('');
        $('#receiver_username').text('');
        $('#receiver_user_profile').attr('src', '');
        $('#receiver_booking_id').text('');
        checkBookingStatus(order_id, user_type, function(status) {
            $.ajax({
                url: baseUrl + '/partner/provider_booking_chat_list',
                type: "POST",
                dataType: 'json',
                data: {
                    receiver_id: receiver_id,
                    offset: 0,
                    limit: 10,
                    user_type: user_type,
                    order_id: order_id,
                },
                success: function(data) {

                    if(data.error==true){
                    showToastMessage(data.message, "error");
                }

                    $('.chat_header').removeClass('d-none');
                    if (status === 'completed' || status === 'cancelled') {
                        $('.chat-area-footer').prop('disabled', true);
                        $('.chat-area-footer').addClass('d-none');
                        $('.chat-area-footer1').removeClass('d-none');
                        $('.chat-area-footer1').html(`
                                <div class="card m-3">
                                    <div class="card-body">
                                        <p class="card-text">Sorry, you can't send a message to the provider since the booking has been cancelled or completed. If you have any further questions or need assistance, please feel free to contact our customer support team.</p>
                                    </div>
                                </div>
                            `);
                    } else {
                        $('.chat-area-footer').prop('disabled', false);
                        $('.chat-area-footer1').addClass('d-none');
                        $('.chat-area-footer').removeClass('d-none');
                    }
                    var html = '';
                    if (data.rows && data.rows.length > 0) {
                        var lastDisplayedDate = null;
                        $('#receiver_username').text(data.receiver_name);
                        $('#receiver_user_profile').attr('src', data.receiver_profile_image);
                        $('#receiver_booking_id').text(data.rows.booking_id);
                        data.rows.forEach(function(message) {
                            if (message.booking_id != null) {
                                $('#receiver_booking_id').text("Booking id -" + message.booking_id);
                            } else {
                                $('#receiver_booking_id').text("Enquiry");
                            }
                            if (message.hasOwnProperty('sender_id') && message.sender_id !== null && message.sender_id !== "") {
                                var messageDate = new Date(message.created_at);
                                var dateStr = '';
                                if (!lastDisplayedDate || messageDate.toDateString() !== lastDisplayedDate.toDateString()) {
                                    dateStr = getMessageDateHeading(messageDate);
                                    lastDisplayedDate = messageDate;
                                }
                                html += dateStr;
                                if (message.sender_id == <?= $current_user_id ?>) {
                                    html += '<div class="chat-msg owner">';
                                } else {
                                    html += '<div class="chat-msg">';
                                }
                                html += '<div class="chat-msg-profile">';
                                if (message.sender_id != <?= $current_user_id ?>) {
                                    html += '<img class="chat-msg-img" src="' + message.profile_image + '" alt="" />';
                                }
                                let createdAt = new Date(message.created_at);
                                if (message.sender_id == <?= $current_user_id ?>) {
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
                                html += '</div>';
                                html += '<div class="chat-msg-content">';
                                const chatMessageHTML = renderChatMessage(message, message.file);
                                html += chatMessageHTML;
                                if (message.file && message.file.length > 0) {
                                    message.file.forEach(function(file) {
                                        html += generateFileHTML(file);
                                    });
                                }
                                html += '</div>';
                                html += '</div>';
                            }
                        });
                    } else {
                        html += '<div class="no-message">No messages found.</div>';
                    }
                    $('.chat-area-main').html(html);
                    $('.myscroll').animate({
                        scrollTop: $('.myscroll').get(0).scrollHeight
                    }, 1500)
                },
                error: function(xhr, status, error) {}
            });
            // }
        });
    }
    function getMessageDateHeading(date) {
        var today = new Date();
        var yesterday = new Date(today);
        yesterday.setDate(today.getDate() - 1);
        if (date.toDateString() === today.toDateString()) {
            return '<div class="chat-msg-date-heading highlight">Today</div>';
        } else if (date.toDateString() === yesterday.toDateString()) {
            return '<div class="chat-msg-date-heading highlight">Yesterday</div>';
        } else {
            return '<div class="chat-msg-date-heading highlight">' + date.toLocaleDateString() + '</div>'; // Display full date if not today or yesterday
        }
    }
    function extractTime(dateTimeString) {
        var dateTimeParts = dateTimeString.split(" ");
        return dateTimeParts[1];
    }
</script>
<script>
    const svgFileInput = document.getElementById('svgFileInput');
    const fileInput = document.getElementById('fileInput');
    svgFileInput.addEventListener('click', function() {
        fileInput.click();
    });
    fileInput.addEventListener('change', function(event) {
        $("#filePreviewContainer").show();
        $('.myscroll').animate({
            scrollTop: $('.myscroll').get(0).scrollHeight
        }, 1500);
        $('#send_button').html('<i class="fas fa-paper-plane"></i>');
        $('#send_button').prop('disabled', false);
        const filePreviewContainer = document.getElementById('filePreviewContainer');
        filePreviewContainer.innerHTML = ''; // Clear previous previews
        const files = event.target.files;
        const maxFileAllowed = <?= $maxFilesOrImagesInOneMessage ?>;
        if (files.length > maxFileAllowed) {
            fileInput.value = '';
            filePreviewContainer.innerHTML = '';
            $("#filePreviewContainer").hide();
            showToastMessage("File  exceeds the maximum limit of " + <?= $maxFilesOrImagesInOneMessage ?>, "error");
            return;
        }
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const fileSizeBytes = file.size;
            const maxFileSizeBytes = <?= $maxFileSizeInBytesCanBeSent ?>;
            const maxFileSizeMB = maxFileSizeBytes / (1024 * 1024);
            const maxFileSizeReadable = maxFileSizeMB >= 1 ?
                maxFileSizeMB.toFixed(2) + " MB" :
                (maxFileSizeMB * 1024).toFixed(2) + " KB";
            if (fileSizeBytes > maxFileSizeBytes) {
                $("#filePreviewContainer").hide();
                fileInput.value = '';
                filePreviewContainer.innerHTML = '';
                showToastMessage("File size exceeds the maximum limit of " + maxFileSizeReadable + ". Please select a smaller file.", "error");
                return;
            }
            const filePreview = document.createElement('div');
            filePreview.classList.add('file-preview');
            if (file.type.includes('image')) {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                filePreview.appendChild(img);
            } else {
                const fileName = document.createElement('span');
                fileName.textContent = file.name;
                filePreview.appendChild(fileName);
            }
            const closeBtn = document.createElement('span');
            closeBtn.classList.add('close-btn');
            closeBtn.textContent = '×';
            closeBtn.addEventListener('click', function() {
                filePreview.remove();
                const latestFilesLength = filePreviewContainer.querySelectorAll('.file-preview').length;
                if (latestFilesLength == 0) {
                    $("#filePreviewContainer").hide();
                }
            });
            filePreview.appendChild(closeBtn);
            filePreviewContainer.appendChild(filePreview);
        }
    });
</script>
<script src="<?= base_url('public/backend/assets/js/vanillaEmojiPicker.js') ?>"></script>
<script>
   
    const conversationArea = document.querySelector('.conversation-area');
    const toggleConversationAreaBtn = document.getElementById('toggleConversationAreaBtn');
    const profileElements = document.querySelectorAll('.msg');
    toggleConversationAreaBtn.addEventListener('click', () => {
        conversationArea.classList.toggle('show');
    });
    profileElements.forEach(profileElement => {
        profileElement.addEventListener('click', () => {
            conversationArea.classList.remove('show');
        });
    });
</script>
<script>
    <?php if ($url !== false) : ?>
        // Get the booking ID from PHP
        const bookingId = <?= $bookingId ?>;
        // Find the customer element with the matching booking ID
        const customerList = document.getElementById('customer-list');
        const customerElements = customerList.querySelectorAll('.msg');
        let customerElement;
        customerElements.forEach(el => {
            const bookingNo = el.querySelector('.featured_lable').textContent.split('::')[1].trim();
            if (bookingNo === bookingId.toString()) {
                customerElement = el;
            }
        });
        if (customerElement) {
            const customerId = customerElement.dataset.customerId;
            $('#receiver_id').val(receiver_id);
            $('#order_id').val(order_id);
            $('#sender_id').val(<?= $current_user_id ?>);
            setallMessage(customerId, customerElement, 'customer', bookingId);
            function renderChatMessage(message, files) {
                let html = "";
                const totalImages = files.filter((image) => {
                    const fileType = image ? image.file_type.toLowerCase() : "";
                    return fileType.includes("image");
                }).length;
                // Filter files where type is image
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
                $.each(files, function(index, value) {
                    if (index < imageLimit) {
                        templateDiv.find("img").eq(index).attr("src", value.file);
                        templateDiv.find("a").eq(index).attr("href", value.file);
                    }
                });
                if (totalImages > imageLimit) {
                    // If there are more images than the limit, add a "Show More" button
                    templateDiv.find(".img_count").removeClass("d-none");
                    let countFile = totalImages - imageLimit;
                    templateDiv.find(".img_count").html(`<h2>+${countFile}</h2>`);
                    $(document).on("click", ".img_count", function() {
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
                $.each(files, function(index, value) {
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
                html +=
                    '<img class="chat-msg-img" src="' + message.profile_image + '" alt="" />';
                html += '<div class="chat-msg-date"> ' + message.created_at + "</div>";
                html += "</div>";
                html += '<div class="chat-msg-content">';
                const chatMessageHTML = renderChatMessage(message, message.file);
                html += chatMessageHTML;
                if (message.file && message.file.length > 0) {
                    message.file.forEach(function(file) {
                        html += generateFileHTML(file);
                    });
                }
                html += "</div>";
                html += "</div>";
                return html;
            }
        } else {}
    <?php else : ?>
    <?php endif; ?>
</script>
<script>
    function checkNotificationPermission() {
        if (!('Notification' in window)) {
            document.getElementById('status').innerHTML = 'This browser does not support desktop notifications.';
        } else {
            if (Notification.permission === 'granted') {
                document.getElementById('status').innerHTML = '';
                $('#notification_div').hide();
            } else if (Notification.permission === 'denied') {
                $('#notification_div').show();
                document.getElementById('status').innerHTML = 'You didn\'t allow Notification Permission. To get live messages please allow notification permission.';
            } else {
                $('#notification_div').show();
                document.getElementById('status').innerHTML = ' You didn\'t allow Notification Permission. To get live messages please allow notification permission.';
            }
        }
    }
    // Check notification permission on page load
    window.onload = function() {
        checkNotificationPermission();
    };
    $('#customer-search').on('keyup', function() {
        var searchTerm = $(this).val();
        fetchCustomerData(searchTerm);
    });
    function fetchCustomerData(searchTerm) {
        $.ajax({
            url: baseUrl + '/partner/get_customer',
            method: 'POST',
            data: {
                search: searchTerm
            },
            dataType: 'json',
            success: function(response) {
               
                if (response && response.length > 0) {
                    $('#customer-list').empty();
                    $.each(response, function(index, provider) {
                        var listItem = '<div class="msg" onclick="setallMessage(' + provider.id + ', this, \'customer\', \'' + provider.order_id + '\', \'customer\')">';
                        listItem += '<img class="msg-profile" src="' + provider.profile_image + '" alt="" />';
                        listItem += '<div class="msg-detail">';
                        listItem += '<div class="msg-username">' + provider.username + '</div>';
                        var featuredLabel = '';
                        if (provider.order_id && provider.order_id.toString().startsWith('enquire_')) {
                            featuredLabel = 'Enquiry';
                        } else if (provider.order_id) {
                            featuredLabel = 'Booking id - ' + provider.order_id;
                        }
                        if (featuredLabel !== '') {
                            listItem += '<div class="featured_tag"><div class="featured_lable">' + featuredLabel + '</div></div>';
                        }
                        listItem += '</div></div>';
                        $('#customer-list').append(listItem);
                    });
                } else {
                    $('#customer-list').empty();
                }
            },
            error: function(xhr, status, errorThrown) {
                console.error(errorThrown);
            }
        });
    }
</script>