<?php
$serviceLabels = [
    'mentorship' => 'General Mentorship',
    'resume-review' => 'Resume Review',
    'portfolio-review' => 'Portfolio Review',
    'career-counseling' => 'Career Counseling',
    'mock-interview-feedback' => 'Mock Interview Feedback',
];
$statusBadge = ['pending' => 'text-bg-secondary', 'accepted' => 'text-bg-success', 'declined' => 'text-bg-danger'];
?>
<h1 class="h4 mb-1">Career Services</h1>
<p class="text-muted mb-4">Everything to get placement-ready: resume/portfolio feedback, mock interviews, job alerts, and prep tools.</p>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="small text-muted">Resume completeness</div>
                    <span class="badge text-bg-primary"><?= (int) $resumeScore['percent'] ?>%</span>
                </div>
                <p class="small text-muted mb-3">Want a human opinion, not just a checklist?</p>
                <a href="<?= url('/mentors?service=resume-review') ?>" class="btn btn-sm btn-outline-primary">Request resume review</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-muted mb-2">Portfolio</div>
                <p class="small text-muted mb-3">Get feedback on your public portfolio page from a mentor.</p>
                <a href="<?= url('/mentors?service=portfolio-review') ?>" class="btn btn-sm btn-outline-primary">Request portfolio review</a>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <div class="small text-muted mb-1">Career Counseling</div>
            <p class="small text-muted mb-0">Talk to a mentor about direction, roles, or next steps.</p>
        </div>
        <a href="<?= url('/mentors?service=career-counseling') ?>" class="btn btn-sm btn-outline-primary">Request career counseling</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-camera-video me-2 text-primary"></i>Mock Interviews</span>
        <a href="<?= url('/dashboard/mock-interviews') ?>" class="btn btn-sm btn-primary">Manage</a>
    </div>
    <div class="card-body">
        <?php if (empty($mockSessions)): ?>
            <p class="text-muted small mb-0">No practice interviews yet. Start one anytime to rehearse technical, HR and coding rounds.</p>
        <?php else: ?>
            <?php foreach (array_slice($mockSessions, 0, 3) as $row): ?>
                <div class="profile-row d-flex justify-content-between align-items-center">
                    <span class="badge <?= $row['status'] === 'completed' ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= e(ucfirst($row['status'])) ?></span>
                    <span class="small text-muted"><?= e($row['created_at']) ?></span>
                    <a href="<?= url('/dashboard/mock-interviews/' . $row['id']) ?>" class="btn btn-sm btn-outline-primary">Open</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-bell me-2 text-primary"></i>Job Alerts</span>
        <a href="<?= url('/dashboard/job-alerts') ?>" class="btn btn-sm btn-primary">Manage</a>
    </div>
    <div class="card-body">
        <?php if (empty($jobAlerts)): ?>
            <p class="text-muted small mb-0">No alerts saved yet.</p>
        <?php else: ?>
            <p class="small text-muted mb-0"><?= count($jobAlerts) ?> alert<?= count($jobAlerts) === 1 ? '' : 's' ?> active.</p>
        <?php endif; ?>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><i class="bi bi-inbox me-2 text-primary"></i>My review requests</div>
    <div class="card-body">
        <?php if (empty($serviceRequests)): ?>
            <p class="text-muted small mb-0">No requests sent yet.</p>
        <?php endif; ?>
        <?php foreach ($serviceRequests as $row): ?>
            <div class="profile-row">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <span class="badge text-bg-light border"><?= e($serviceLabels[$row['service_type']] ?? $row['service_type']) ?></span>
                        <span class="small text-muted ms-1">to <?= e($row['mentor_name']) ?></span>
                    </div>
                    <span class="badge <?= $statusBadge[$row['status']] ?? 'text-bg-secondary' ?>"><?= e(ucfirst($row['status'])) ?></span>
                </div>
                <?php if (!empty($row['feedback'])): ?>
                    <p class="small mb-0 mt-2"><strong>Feedback:</strong> <?= nl2br(e($row['feedback'])) ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="row g-3">
    <?php
    $quickLinks = [
        ['title' => 'Assessments', 'text' => 'Practice tests across technical, aptitude and communication.', 'icon' => 'bi-clipboard-check', 'href' => '/dashboard/assessments'],
        ['title' => 'Roadmaps', 'text' => 'Structured learning paths toward your target role.', 'icon' => 'bi-signpost-2', 'href' => '/roadmaps'],
        ['title' => 'Mentors', 'text' => 'Browse all mentors and their areas of expertise.', 'icon' => 'bi-person-badge', 'href' => '/mentors'],
    ];
    ?>
    <?php foreach ($quickLinks as $link): ?>
        <div class="col-md-4">
            <a href="<?= url($link['href']) ?>" class="card h-100 text-decoration-none text-reset">
                <div class="card-body">
                    <div class="feature-icon mb-3"><i class="bi <?= e($link['icon']) ?>"></i></div>
                    <h2 class="h6 fw-semibold mb-1"><?= e($link['title']) ?></h2>
                    <p class="text-muted small mb-0"><?= e($link['text']) ?></p>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>
