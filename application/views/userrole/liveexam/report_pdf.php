<html>

<head>
    
</head>

<body>

    <!-- Watermark -->
    <div class="watermark">FutureCampus</div>

   

    <!-- Footer -->
    <div class="footer">
        <p>Generated on <?= date('d M Y H:i'); ?> by FutureCampus</p>
    </div>

    <!-- QR Code bottom-right -->
    <div class="qr-code">
        <img src="<?= html_escape($qr_code); ?>"><br>
        <small>Scan to Verify Report</small>
    </div>

</body>

</html>
