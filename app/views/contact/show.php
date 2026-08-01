<?php
Core\View::partial('partials/page_header', [
    'title' => 'Contact Us',
    'subtitle' => "Have a question, feedback, or a partnership idea? Send us a message and we'll get back to you.",
]);
?>

<section class="py-5">
    <div class="container" style="max-width: 640px;">
        <form method="post" action="<?= url('/contact') ?>" class="card listing-card reveal" data-guard-submit>
            <div class="card-body p-4">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label" for="show-name">Your name</label>
                <input id="show-name" type="text" name="name" class="form-control" value="<?= old('name') ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="show-email">Email</label>
                <input id="show-email" type="email" name="email" class="form-control" value="<?= old('email') ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="show-subject">Subject</label>
                <input id="show-subject" type="text" name="subject" class="form-control" value="<?= old('subject') ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="show-message">Message</label>
                <textarea id="show-message" name="message" class="form-control" rows="5" required><?= old('message') ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary w-100">Send Message</button>
            </div>
        </form>
    </div>
</section>
