<!-- Main Content -->
<div class="main-content">
    <section class="section" id="pill-about_us" role="tabpanel">
        <div class="section-header mt-2">
            <h1> <?= labels('chat', "Chat") ?>
                <span class="breadcrumb-item p-3 pt-2 text-primary">
                </span>
            </h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/partners') ?>"><i class="fas fa-handshake text-warning"></i> </i> <?= labels('provider', 'Provider') ?></a></div>
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
                    <div class="wrapper">
                        <button id="toggleConversationAreaBtn"><?= labels('chat_list', "Chat List") ?></button>
                        <div class="conversation-area" id="">
                            <ul class="nav nav-tabs fixed-tabs" style="padding: 1.25rem!important;">
                                <div class="row w-100 ml-1 mr-1">
                                    <div class="col-md-6 m-0 p-0">
                                        <li class="nav-item">
                                            <a class="nav-link test active" href="#" onclick="openTab(event, 'customer')"><?= labels('customer', "Customer") ?></a>
                                        </li>
                                    </div>
                                    <div class="col-md-6 m-0 p-0">
                                        <li class="nav-item">
                                            <a class="nav-link test" href="#" onclick="openTab(event, 'provider')"><?= labels('provider', "Provider") ?></a>
                                        </li>
                                    </div>
                                </div>
                            </ul>
                            <div id="customer" class="tabcontent">
                                <div class="search-bar">
                                    <input type="text" id="customer-search" placeholder="<?= labels('search_customer', 'Search Customer') ?>..." />
                                </div>
                                <hr class="mb-0">
                                <div id="customer-list">
                                    <?php foreach ($customers as $user) : ?>
                                        <div class="msg" onclick="setallMessage(<?= $user['id'] ?>, this, 'customer')">
                                            <img class="msg-profile" src="<?= $user['profile_image'] ?>" alt="" />
                                            <div class="msg-detail">
                                                <div class="msg-username"><?= $user['username']; ?></div>
                                                <div class="msg-phone">
                                                    <?= ((defined('ALLOW_VIEW_KEYS') && ALLOW_VIEW_KEYS == 0)) ? substr_replace($user['phone'], '********', 3, 8) : $user['country_code'] . $user['phone'] ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div id="provider" class="tabcontent" style="display:none;">
                                <div class="search-bar">
                                    <input type="text" id="provider-search" placeholder="<?= labels('search_provider', 'Search Provider') ?>..." />
                                </div>
                                <hr class="mb-0">
                                <div id="provider-list">
                                    <?php foreach ($providers as $user) : ?>
                                        <div class="msg" onclick="setallMessage(<?= $user['id'] ?>, this, 'provider')">
                                            <img class="msg-profile" src="<?= $user['profile_image']; ?>" alt="" />
                                            <div class="msg-detail">
                                                <div class="msg-username"><?= $user['username']; ?></div>
                                                <div class="msg-phone"><?= $user['country_code'] ?> <?= $user['phone']; ?></div>
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
                                </div>
                            </div>
                            <div class="chat-area-main myscroll" id="chat-area-main">
                                <div class="welcome-card">
                                    <p>
                                        <img width="200" height="200" src="<?= base_url('public/uploads/site/black chat section img.svg') ?>" alt="Welcome Image">
                                    </p>
                                    <?php $data = get_settings('general_settings', true); ?>
                                    <h1 class="welcome-title"><?= labels('welcome_to', 'Welcome to') ?>&nbsp;<?= (isset($data['company_title']) && $data['company_title'] != "") ? $data['company_title'] : "eDemand"; ?></h1>
                                    <h6 class="welcome-subtitle">
                                        <?= labels('chat_welcome_card_subtitle', 'Pick a person from the left menu and start your conversation') ?>
                                    </h6>
                                </div>
                            </div>
                            <div id="filePreviewContainer"></div>
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
                                        placeholder="<?= labels('type_something_here', 'Type something here') ?>..."
                                        style="flex: 1; margin-right: 5px;"
                                        rows="1">
                                    </textarea>
                                    <!-- <input type="text" class="two" id="message" name="message" placeholder="Type something here..." style="flex: 1; margin-right: 5px;" /> -->
                                    <input type="hidden" id="sender_id" name="sender_id" value="" />
                                    <input type="hidden" id="receiver_id" name="receiver_id" value="" />
                                    <input type="hidden" id="booking_id" name="booking_id" value="" />
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
            url: baseUrl + '/admin/store_chat',
            enctype: 'multipart/form-data',
            type: "POST",
            dataType: 'json',
            data: fd,
            processData: false,
            contentType: false,
            async: true,
            cache: false,
            success: function(data) {
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

    function setallMessage(id, element, user_type) {
        $("#filePreviewContainer").hide();
        var allProfiles = document.querySelectorAll('.msg');
        allProfiles.forEach(function(profile) {
            profile.classList.remove('active');
        });
        element.classList.add('active');
        var receiver_id = id;
        $('#receiver_id').val(receiver_id);
        $('#user_type_for_send_message').val(user_type);
        $('.chat-area-main').text('');
        $('#receiver_username').text('');
        $('#receiver_user_profile').attr('src', '');
        $.ajax({
            url: baseUrl + '/admin/chat_get_all_messages',
            type: "POST",
            dataType: 'json',
            data: {
                receiver_id: receiver_id,
                offset: 0,
                limit: 10,
                user_type: user_type,
            },
            success: function(data) {
                if (data.error == true) {
                    showToastMessage(data.message, "error");
                }
                $('.chat_header').removeClass('d-none');
                $('.chat-area-footer').removeClass('d-none');
                var html = '';
                if (data.rows && data.rows.length > 0) {
                    var lastDisplayedDate = null;
                    $('#receiver_username').text(data.receiver_name);
                    $('#receiver_user_profile').attr('src', data.receiver_profile_image);
                    data.rows.forEach(function(message) {
                        if (message.hasOwnProperty('sender_id') && message.sender_id !== null && message.sender_id !== "") {
                            html += renderMessage(message, <?= $current_user_id ?>);
                        }
                    });
                } else {
                    html += '<div class="no-message">No messages found.</div>';
                }
                $('.chat-area-main').html(html);
                // Ensure that the chat scrolls to the bottom
                var chatArea = $('.myscroll').get(0);
                chatArea.scrollTop = chatArea.scrollHeight;
                // $('.myscroll').animate({
                //     scrollTop: $('.myscroll').get(0).scrollHeight
                // }, 1500)
            },
            error: function(xhr, status, error) {}
        });
    }

    function getMessageDateHeading(date) {
        var today = new Date();
        var yesterday = new Date(today);
        yesterday.setDate(today.getDate() - 1);
        if (date.toDateString() === today.toDateString()) {
            return '<div class="chat_divider">Today</div>';
        } else if (date.toDateString() === yesterday.toDateString()) {
            return '<div class="chat_divider">Yesterday</div>';
        } else {
            return '<div class="chat_divider">' + date.toLocaleDateString() + '</div>'; // Display full date if not today or yesterday
        }
    }

    function extractTime(dateTimeString) {
        var dateTimeParts = dateTimeString.split(" ");
        return dateTimeParts[1];
    }
</script>
<script>
    function openTab(evt, tabName) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }
        tablinks = document.getElementsByClassName("nav-link");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].classList.remove("active");
        }
        document.getElementById(tabName).style.display = "block";
        evt.currentTarget.classList.add("active");
    }
    document.addEventListener("DOMContentLoaded", function() {
        var activeTabLink = document.querySelector(".nav-link.active");
        if (activeTabLink) {
            var activeTab = activeTabLink.getAttribute("href").substring(1);
            var activeTabContent = document.getElementById(activeTab);
            if (activeTabContent) {
                activeTabContent.style.display = "block";
            }
        }
    });
    const svgFileInput = document.getElementById('svgFileInput');
    const fileInput = document.getElementById('fileInput');
    const filePreviewArea = document.querySelector('.file_previews');
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
        filePreviewContainer.innerHTML = '';
        const files = event.target.files;
        const maxFileAllowed = <?= $maxFilesOrImagesInOneMessage ?>;
        if (files.length > maxFileAllowed) {
            fileInput.value = '';
            filePreviewContainer.innerHTML = '';
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
    $(document).ready(function() {
        var baseUrl = '<?php echo base_url(); ?>';

        function fetchCustomerData(searchTerm) {
            $.ajax({
                url: baseUrl + '/admin/get_customers',
                method: 'POST',
                data: {
                    search: searchTerm
                },
                dataType: 'json',
                success: function(response) {
                    if (response && response.length > 0) {
                        $('#customer-list').empty();
                        $.each(response, function(index, customer) {
                            var listItem = '<div class="msg " onclick="setallMessage(' + customer.id + ', this, \'customer\')">';
                            listItem += '<img class="msg-profile" src="' + customer.profile_image + '" alt="" />';
                            listItem += '<div class="msg-detail">';
                            listItem += '<div class="msg-username">' + customer.username + '</div>';
                            listItem += '<div class="msg-phone">' + customer.country_code + ' ' + (customer.phone) + '</div>';
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
        $('#customer-search').on('keyup', function() {
            var searchTerm = $(this).val();
            fetchCustomerData(searchTerm);
        });

        function fetchProviderData(searchTerm) {
            $.ajax({
                url: baseUrl + '/admin/get_providers',
                method: 'POST',
                data: {
                    search: searchTerm
                },
                dataType: 'json',
                success: function(response) {
                    if (response && response.length > 0) {
                        $('#provider-list').empty();
                        $.each(response, function(index, provider) {
                            var listItem = '<div class="msg " onclick="setallMessage(' + provider.id + ', this,\'provider\')">';
                            listItem += '<img class="msg-profile" src="' + provider.profile_image + '" alt="" />';
                            listItem += '<div class="msg-detail">';
                            listItem += '<div class="msg-username">' + provider.username + '</div>';
                            listItem += '<div class="msg-phone">' + provider.country_code + ' ' + provider.phone + '</div>';
                            listItem += '</div></div>';
                            $('#provider-list').append(listItem);
                        });
                    } else {
                        $('#provider-list').empty();
                    }
                },
                error: function(xhr, status, errorThrown) {
                    console.error(errorThrown);
                }
            });
        }
        $('#provider-search').on('keyup', function() {
            var searchTerm = $(this).val();
            fetchProviderData(searchTerm);
        });
    });
</script>
<script src="<?= base_url('public/backend/assets/js/vanillaEmojiPicker.js') ?>"></script>
<script>
    new EmojiPicker({
        trigger: [{
            selector: '.first-btn',
            insertInto: ['.one', '.two']
        }, ],
        closeButton: true,
    });
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
    function checkNotificationPermission() {
        if (!('Notification' in window)) {
            document.getElementById('status').innerHTML = '<?= labels('browser_doesnt_support_notifications', 'This browser does not support desktop notifications') ?>.';
        } else {
            if (Notification.permission === 'granted') {
                document.getElementById('status').innerHTML = '';
                $('#notification_div').hide();
            } else if (Notification.permission === 'denied') {
                $('#notification_div').show();
                document.getElementById('status').innerHTML = '<?= labels('didnt_allow_notification_permission', "You didn\'t allow Notification Permission. To get live messages please allow notification permission") ?>.';
            } else {
                $('#notification_div').show();
                document.getElementById('status').innerHTML = '<?= labels('didnt_allow_notification_permission', "You didn\'t allow Notification Permission. To get live messages please allow notification permission") ?>.';
            }
        }
    }
    window.onload = function() {
        checkNotificationPermission();
    };
</script>