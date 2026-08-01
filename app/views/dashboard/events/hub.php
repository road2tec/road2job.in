<?php
$categoryLabels = [
    'hackathon' => 'Hackathon',
    'job-fair' => 'Job Fair',
    'seminar' => 'Seminar',
    'webinar' => 'Webinar',
];
$statusBadge = ['draft' => 'text-bg-secondary', 'published' => 'text-bg-success', 'completed' => 'text-bg-primary'];
?>
<h1 class="h4 mb-1">Events</h1>
<p class="text-muted mb-4">Hackathons, job fairs, seminars and webinars.</p>

<?php if ($isOrganizer): ?>
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-calendar-event me-2 text-primary"></i>Your hosted events</span>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#eventModal" onclick="resetResourceModal('eventModal', '<?= url('/dashboard/events') ?>')">Create event</button>
    </div>
    <div class="card-body">
        <?php if (empty($hostedEvents)): ?>
            <p class="text-muted small mb-0">No events created yet.</p>
        <?php endif; ?>
        <?php foreach ($hostedEvents as $row): ?>
            <div class="profile-row d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <strong><?= e($row['title']) ?></strong>
                    <span class="badge <?= $statusBadge[$row['status']] ?? 'text-bg-secondary' ?> ms-1"><?= e(ucfirst($row['status'])) ?></span>
                    <br>
                    <span class="badge text-bg-light border ms-1"><?= e($categoryLabels[$row['category']] ?? $row['category']) ?></span>
                    <br>
                    <span class="small text-muted">
                        <?= e(date('M j, Y g:i A', strtotime($row['starts_at']))) ?>
                        <?php if (!empty($row['location'])): ?> &middot; <?= e($row['location']) ?><?php endif; ?>
                    </span>
                </div>
                <div class="d-flex gap-1">
                    <a href="<?= url('/dashboard/events/' . $row['id'] . '/registrations') ?>" class="btn btn-sm btn-outline-primary">Registrations</a>
                    <?php
                        $modalRow = $row;
                        $modalRow['starts_at'] = str_replace(' ', 'T', substr((string) $row['starts_at'], 0, 16));
                        $modalRow['ends_at'] = $row['ends_at'] ? str_replace(' ', 'T', substr((string) $row['ends_at'], 0, 16)) : '';
                    ?>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#eventModal"
                        onclick='openResourceModal("eventModal", "<?= url('/dashboard/events/' . $row['id']) ?>", <?= json_encode($modalRow, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Edit</button>
                    <form method="post" action="<?= url('/dashboard/events/' . $row['id'] . '/delete') ?>" onsubmit="return confirm('Delete this event?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><i class="bi bi-ticket-perforated me-2 text-primary"></i>My registrations</div>
    <div class="card-body">
        <?php if (empty($registrations)): ?>
            <p class="text-muted small mb-0">You haven't registered for any events yet. <a href="<?= url('/events') ?>">Browse events</a>.</p>
        <?php endif; ?>
        <?php foreach ($registrations as $row): ?>
            <div class="profile-row d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <strong><?= e($row['event_title']) ?></strong>
                    <span class="badge text-bg-light border ms-1"><?= e($categoryLabels[$row['category']] ?? $row['category']) ?></span>
                    <br>
                    <span class="small text-muted"><?= e(date('M j, Y g:i A', strtotime($row['starts_at']))) ?></span>
                </div>
                <div>
                    <?php if ($row['status'] === 'attended'): ?>
                        <a href="<?= url('/dashboard/events/' . $row['event_id'] . '/certificate') ?>" class="btn btn-sm btn-outline-success">View certificate</a>
                    <?php else: ?>
                        <span class="badge text-bg-light border">Registered</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php if ($isOrganizer): ?>
<!-- Event modal -->
<div class="modal fade" id="eventModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2"><label class="form-label" for="hub-title">Title</label><input id="hub-title" type="text" name="title" class="form-control" required></div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label" for="hub-category">Category</label>
                            <select id="hub-category" name="category" class="form-select">
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= e($category) ?>"><?= e($categoryLabels[$category]) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="hub-status">Status</label>
                            <select id="hub-status" name="status" class="form-select">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label" for="hub-starts-at">Starts at</label>
                            <input id="hub-starts-at" type="datetime-local" name="starts_at" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="hub-ends-at">Ends at</label>
                            <input id="hub-ends-at" type="datetime-local" name="ends_at" class="form-control">
                        </div>
                    </div>

                    <div class="mb-2"><label class="form-label" for="hub-location">Location</label><input id="hub-location" type="text" name="location" class="form-control" placeholder="Venue, or leave blank if online"></div>
                    <div class="mb-2 form-check">
                        <input type="checkbox" name="is_online" value="1" class="form-check-input" id="eventIsOnline">
                        <label class="form-check-label" for="eventIsOnline">This is an online event</label>
                    </div>
                    <div class="mb-2"><label class="form-label" for="hub-description">Description</label><textarea id="hub-description" name="description" class="form-control" rows="3"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script src="<?= asset('js/profile.js') ?>"></script>
