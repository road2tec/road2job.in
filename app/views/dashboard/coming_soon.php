<div class="text-center py-5">
    <div class="feature-icon mx-auto mb-3">
        <i class="bi <?= e($page['icon']) ?>"></i>
    </div>
    <span class="badge text-bg-light border mb-2">Coming Soon</span>
    <h1 class="h4 fw-bold mb-2"><?= e($page['title']) ?></h1>
    <p class="text-muted mx-auto" style="max-width: 480px;"><?= e($page['description']) ?></p>
    <p class="text-muted small">This section is on our roadmap (<?= e($page['phase']) ?>) and isn't live yet.</p>

    <?php if (!empty($showDemoQr)): ?>
        <div class="card mx-auto mt-4" style="max-width: 260px;">
            <div class="card-body text-center">
                <div id="paymentsDemoQr" class="d-flex justify-content-center mb-2"></div>
                <span class="badge text-bg-light border">Demo QR - not a real payment method</span>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($showDemoQr)): ?>
    <script src="<?= asset('js/vendor/qrcode.min.js') ?>"></script>
    <script>
        new QRCode(document.getElementById('paymentsDemoQr'), {
            text: 'Road2Job Payments - demo only, not a real payment method',
            width: 180,
            height: 180,
            correctLevel: QRCode.CorrectLevel.M,
        });
    </script>
<?php endif; ?>
