<script>
    window.qrCodeReady = function (callback) {
        if (typeof QRCode !== 'undefined') {
            callback();
            return;
        }

        var script = document.createElement('script');
        script.src = '{{ asset('assets/js/qrcode.min.js') }}';
        script.onload = function () { callback(); };
        script.onerror = function () { callback(); };
        document.head.appendChild(script);
    };
</script>
