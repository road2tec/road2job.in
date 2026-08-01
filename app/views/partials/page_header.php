<section class="page-header">
    <div class="container" style="max-width: 840px;">
        <h1 class="fw-bold font-display"><?= e($title) ?></h1>
        <?php if (!empty($subtitle)): ?>
            <p class="text-muted <?= empty($below) ? 'mb-0' : 'mb-4' ?>"><?= e($subtitle) ?></p>
        <?php endif; ?>
        <?php if (!empty($below)): ?>
            <?= $below /* pre-rendered by the calling page via ob_start()/ob_get_clean() - not user input */ ?>
        <?php endif; ?>
    </div>
</section>
