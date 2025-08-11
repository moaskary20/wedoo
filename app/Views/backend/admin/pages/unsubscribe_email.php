<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe Message</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
        }

        .success-message {
            border: 3px solid green;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);

        }


        .error-message {
            border: 3px solid red;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);

        }
    </style>
    <!-- Include SweetAlert library -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php
    // Assuming you have session variables set in PHP
    $success = session()->has('success');
    $error = session()->has('error');
    $successMessage = $success ? session('success') : '';
    $errorMessage = $error ? session('error') : '';
    ?>

    <div class="container">
        <p>Click on "Unsubscribe" to stop receiving emails from this sender on this email address:</p>
        <p><small>Note: You will no longer receive emails after unsubscribing.</small></p>
        <form id="unsubscribeForm" method="POST" action="<?= base_url() . '/admin/unsubscribe_email_op' ?>">
            <input type="hidden" name="data" id="dataField" value="" />
            <button type="submit" id="unsubscribeButton">Unsubscribe</button>
        </form>
    </div>
    <div class="success-message" id="successMessage" style="display: none;">Successfully unsubscribed!</div>
    <div class="error-message" id="erroMessage" style="display: none;">Already Unscribed!</div>

    <script>
        // Get the encrypted data from the URL path
        const urlPath = window.location.pathname;
        const unsubscribeLinkIndex = urlPath.indexOf('unsubscribe_link');
        const encryptedData = urlPath.substring(unsubscribeLinkIndex + 'unsubscribe_link/'.length);

        // Set the encrypted data in the hidden field
        const dataField = document.getElementById('dataField');
        dataField.value = encryptedData;

        // Check if success or error session variables are set
        const successMessage = '<?php echo $successMessage; ?>';
        const errorMessage = '<?php echo $errorMessage; ?>';

        if (successMessage) {
            Swal.fire({
                title: "Success!",
                text: successMessage,
                icon: "success"
            }).then(() => {
                $('.container').hide();
                $('#erroMessage').hide();
                <?php unset($_SESSION['success']); ?>
                $('#successMessage').show();
            });
        }

        if (errorMessage) {
            Swal.fire({
                title: "Error!",
                text: errorMessage,
                icon: "error"
            }).then(() => {
                $('.container').hide();
                $('#successMessage').hide();
                <?php unset($_SESSION['error']); ?>
                $('#erroMessage').show();
            });
        }
    </script>
</body>

</html>