</div>


<script data-cfasync="false" src="assets/js/jquery-3.7.1.min.js"></script>
<script data-cfasync="false" src="assets/js/bootstrap.bundle.min.js"></script>
<script data-cfasync="false" src="assets/js/moment.js"></script>
<script data-cfasync="false" src="assets/plugins/daterangepicker/daterangepicker.js"></script>
<script data-cfasync="false" src="assets/js/moment.js"></script>
<script data-cfasync="false" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/tr.min.js"></script>
<script data-cfasync="false" src="assets/js/bootstrap-datetimepicker.min.js"></script>
<script data-cfasync="false" src="assets/plugins/select2/js/select2.min.js"></script>
<script data-cfasync="false" src="assets/plugins/bootstrap-tagsinput/bootstrap-tagsinput.js"></script>
<script data-cfasync="false" src="assets/js/feather.min.js"></script>
<script data-cfasync="false" src="assets/js/jquery.slimscroll.min.js"></script>
<script data-cfasync="false" src="assets/js/script.js"></script>

<script>
    (function () {
        if (!window.jQuery) return console.error('jQuery yok');

        jQuery(function ($) {
            if ($.fn.select2) $('.select').each(function(){ $(this).select2({ width: '100%' }); });
            if ($.fn.daterangepicker) $('.daterange').daterangepicker();
            if ($.fn.datetimepicker) $('.datetimepicker').datetimepicker({ icons: { time: 'ti ti-clock' } });
            if ($.fn.tagsinput) $('.input-tags').tagsinput();
            if (window.feather && feather.replace) feather.replace();
        });
    })();
</script>



</body>
</html>