<!-- ======= Footer ======= -->
<?php
$data = [];
try {
    $data = get_settings('general_settings', true);
} catch (Exception $e) {
    echo "<script>console.log('$e')</script>";
}
isset($data['phone']) && $data['phone'] != "" ?  $phone = $data['phone'] : $phone =  '+919999999999';
isset($data['support_email']) && $data['support_email'] != "" ?  $email = $data['support_email'] : $email =  'admin@admin.com';
?>
<footer id="footer" class="footer">
</footer>
<!-- End Footer -->


<!-- Vendor JS Files -->
<script src="<?= base_url('public/frontend/retro/vendor/bootstrap/js/bootstrap.bundle.js') ?>"></script>
<script src="<?= base_url('public/frontend/retro/vendor/aos/aos.js') ?>"></script>
<script src="<?= base_url('public/frontend/retro/vendor/lottie/lottie.js') ?>"></script>
<script src="<?= base_url('public/frontend/retro/vendor/swiper/swiper-bundle.min.js') ?>"></script>
<script src="<?= base_url('public/backend/assets/js/vendor/select2.min.js') ?>"></script>
<script src="<?= base_url('public/backend/assets/js/vendor/iziToast.min.js') ?>"></script>
<script src="<?= base_url('public/backend/assets/js/vendor/sweetalert.js') ?>"></script>


<!-- Template Main JS File -->
<script src="<?= base_url('public/frontend/retro/js/stisla.js') ?>"></script>
<script src="<?= base_url('public/frontend/retro/js/main.js') ?>"></script>
<script src="<?= base_url('public/frontend/retro/js/scripts.js') ?>"></script>

<script src="https://rawgit.com/RobinHerbots/jquery.inputmask/3.x/dist/jquery.inputmask.bundle.js"></script>
<!-- firebase thing -->

<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase.js"></script>
<script src="<?= base_url('public/frontend/retro/js/custom.js') ?>"></script>


<script src="https://www.gstatic.com/firebasejs/8.2.0/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.2.0/firebase-messaging.js"></script>

<!-- <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js"></script>-->
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-auth.js"></script> 
<script>

</script>
<?php
$firebase_setting = get_settings('firebase_settings', true);


?>
<script>

	   var firebaseConfig = {
        apiKey: '<?= isset($firebase_setting['apiKey']) ? $firebase_setting['apiKey'] : '1' ?>',
        authDomain: '<?= isset($firebase_setting['authDomain']) ? $firebase_setting['authDomain'] : 0 ?>',
        projectId: '<?= isset($firebase_setting['projectId']) ? $firebase_setting['projectId'] : 0 ?>',
        storageBucket: '<?= isset($firebase_setting['storageBucket']) ? $firebase_setting['storageBucket'] : 0 ?>',
        messagingSenderId: '<?= isset($firebase_setting['messagingSenderId']) ? $firebase_setting['messagingSenderId'] : 0 ?>',
        appId: '<?= isset($firebase_setting['appId']) ? $firebase_setting['appId'] : 0 ?>',
        measurementId: '<?= isset($firebase_setting['measurementId']) ? $firebase_setting['measurementId'] : 0 ?>'
    };

			firebase.initializeApp(firebaseConfig);
			render();

			function render() {

				window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('rec');
				recaptchaVerifier.render()
			}
		</script>