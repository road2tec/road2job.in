<?php
$typeLabels = [
    'research-paper' => 'Research Paper',
    'project' => 'Research Project',
    'publication' => 'Publication',
    'conference-paper' => 'Conference Paper',
    'patent' => 'Patent',
];
?>
<h1 class="h4 mb-1">Research Hub</h1>
<p class="text-muted mb-4">Showcase your research papers, projects, publications, conference papers and patents. Visible on your portfolio and the public Research Hub when your profile is public.</p>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-journal-bookmark me-2 text-primary"></i>Your research items</span>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#researchModal" onclick="resetResourceModal('researchModal', '<?= url('/dashboard/research') ?>')">Add</button>
    </div>
    <div class="card-body">
        <?php if (empty($items)): ?>
            <p class="text-muted small mb-0">No research items added yet.</p>
        <?php endif; ?>
        <?php foreach ($items as $row): ?>
            <div class="profile-row d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <strong><?= e($row['title']) ?></strong>
                    <span class="badge text-bg-light border ms-1"><?= e($typeLabels[$row['type']] ?? $row['type']) ?></span>
                    <br>
                    <span class="small text-muted">
                        <?php if (!empty($row['authors_collaborators'])): ?><?= e($row['authors_collaborators']) ?><?php endif; ?>
                        <?php if (!empty($row['publication_date'])): ?> &middot; <?= e($row['publication_date']) ?><?php endif; ?>
                        <?php if (!empty($row['attachment_path'])): ?> &middot; <a href="<?= url($row['attachment_path']) ?>" target="_blank">Attachment</a><?php endif; ?>
                    </span>
                </div>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#researchModal"
                        onclick='openResourceModal("researchModal", "<?= url('/dashboard/research/' . $row['id']) ?>", <?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Edit</button>
                    <form method="post" action="<?= url('/dashboard/research/' . $row['id'] . '/delete') ?>" onsubmit="return confirm('Delete this item?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php Core\View::partial('dashboard/student/_research_modals'); ?>

<script src="<?= asset('js/profile.js') ?>"></script>
