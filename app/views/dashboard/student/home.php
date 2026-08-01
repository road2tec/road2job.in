<h1 class="h4 mb-1">Welcome back, <?= e($user['full_name']) ?></h1>
<p class="text-muted mb-4">You're signed in as <strong><?= e($user['role_label']) ?></strong>.</p>

<?php if ($user['role_slug'] === 'mentor'): ?>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="small text-muted mb-1">Mentor profile</div>
                    <div class="h6 mb-0"><?= !empty($mentor['expertise']) ? e($mentor['expertise']) : 'Not set up yet' ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="small text-muted mb-1">Pending requests</div>
                    <div class="h6 mb-0"><?= (int) $pendingMentorshipCount ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <?php
        $quickLinks = [
            ['title' => 'Mentor profile', 'text' => 'Update your expertise, bio and availability.', 'icon' => 'bi-person-badge', 'href' => '/dashboard/mentor'],
            ['title' => 'Mentorship requests', 'text' => 'Review students asking for your guidance.', 'icon' => 'bi-person-check', 'href' => '/dashboard/mentor/requests'],
            ['title' => 'Community', 'text' => 'Answer questions and share career discussions.', 'icon' => 'bi-people', 'href' => '/community'],
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
<?php else: ?>
    <div class="row g-3 mb-4">
        <?php
        $quickLinks = [
            ['title' => 'Complete your profile', 'text' => 'Add education, skills, projects and more.', 'icon' => 'bi-person', 'href' => '/dashboard/profile'],
            ['title' => 'Preview your resume', 'text' => 'Auto-compiled from your profile, ready to print.', 'icon' => 'bi-file-earmark-text', 'href' => '/dashboard/resume'],
            ['title' => 'Account security', 'text' => 'Review active sessions and login history.', 'icon' => 'bi-shield-lock', 'href' => '/account/security'],
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

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header"><i class="bi bi-speedometer2 me-2 text-primary"></i>Placement Readiness</div>
                <div class="card-body">
                    <?php $readinessBadge = $readiness['percent'] >= 70 ? 'success' : ($readiness['percent'] >= 40 ? 'warning' : 'secondary'); ?>
                    <p class="mb-2"><span class="badge text-bg-<?= $readinessBadge ?> p-2"><?= (int) $readiness['percent'] ?>%</span></p>
                    <?php if (!empty($readiness['suggestions'])): ?>
                        <ul class="small text-muted mb-0 ps-3">
                            <?php foreach ($readiness['suggestions'] as $tip): ?>
                                <li><?= e($tip) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="small text-muted mb-0">Great work - your profile is in strong shape!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header"><i class="bi bi-lightbulb me-2 text-primary"></i>Career Suggestions</div>
                <div class="card-body">
                    <?php if (empty($careerSuggestions)): ?>
                        <p class="small text-muted mb-0">Add skills to your <a href="<?= url('/dashboard/profile') ?>">profile</a> to get personalized career suggestions.</p>
                    <?php else: ?>
                        <?php foreach ($careerSuggestions as $suggestion): ?>
                            <p class="mb-2">
                                <?php foreach ($suggestion['roles'] as $role): ?><span class="badge text-bg-primary me-1"><?= e($role) ?></span><?php endforeach; ?>
                                <br><span class="small text-muted">Because you know: <?= e(implode(', ', $suggestion['matchedSkills'])) ?></span>
                            </p>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header"><i class="bi bi-briefcase me-2 text-primary"></i>Recommended Jobs</div>
                <div class="card-body">
                    <?php if (empty($recommendedJobs)): ?>
                        <p class="small text-muted mb-0">No strong matches yet - add more skills to your profile or <a href="<?= url('/jobs') ?>">browse all jobs</a>.</p>
                    <?php else: ?>
                        <?php foreach ($recommendedJobs as $job): ?>
                            <a href="<?= url('/jobs/' . $job['id']) ?>" class="d-block profile-row text-decoration-none text-reset">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-1">
                                    <strong><?= e($job['title']) ?></strong>
                                    <span class="badge text-bg-light border"><?= (int) $job['matchPercent'] ?>% match</span>
                                </div>
                                <span class="small text-muted"><?= e($job['company_name']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-trophy me-2 text-primary"></i>Top Institutes</span>
                    <a href="<?= url('/institutes') ?>" class="small">View all</a>
                </div>
                <div class="card-body">
                    <?php if (empty($topInstitutes)): ?>
                        <p class="small text-muted mb-0">No institutes ranked yet.</p>
                    <?php else: ?>
                        <?php foreach ($topInstitutes as $institute): ?>
                            <a href="<?= url('/institutes/' . $institute['id']) ?>" class="d-block profile-row text-decoration-none text-reset">
                                <div class="d-flex gap-2 align-items-center">
                                    <?php if (!empty($institute['logo_path'])): ?>
                                        <img src="<?= url($institute['logo_path']) ?>" alt="" class="rounded" style="width:32px;height:32px;object-fit:cover;">
                                    <?php else: ?>
                                        <div class="listing-card__logo-fallback" style="width:32px;height:32px;font-size:.9rem;"><i class="bi bi-easel"></i></div>
                                    <?php endif; ?>
                                    <div>
                                        <strong class="small"><?= e($institute['name']) ?></strong>
                                        <?php if (($institute['verification_status'] ?? 'unverified') === 'verified'): ?><i class="bi bi-patch-check-fill text-primary small ms-1"></i><?php endif; ?>
                                        <?php $loc = trim(($institute['city'] ?? '') . (!empty($institute['state']) ? ', ' . $institute['state'] : '')) ?: ($institute['location'] ?? ''); ?>
                                        <?php if (!empty($loc)): ?><br><span class="small text-muted"><?= e($loc) ?></span><?php endif; ?>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Trending Institutes</span>
                    <a href="<?= url('/institutes') ?>" class="small">View all</a>
                </div>
                <div class="card-body">
                    <?php if (empty($trendingInstitutes)): ?>
                        <p class="small text-muted mb-0">No recent institute activity yet.</p>
                    <?php else: ?>
                        <?php foreach ($trendingInstitutes as $institute): ?>
                            <a href="<?= url('/institutes/' . $institute['id']) ?>" class="d-block profile-row text-decoration-none text-reset">
                                <div class="d-flex gap-2 align-items-center">
                                    <?php if (!empty($institute['logo_path'])): ?>
                                        <img src="<?= url($institute['logo_path']) ?>" alt="" class="rounded" style="width:32px;height:32px;object-fit:cover;">
                                    <?php else: ?>
                                        <div class="listing-card__logo-fallback" style="width:32px;height:32px;font-size:.9rem;"><i class="bi bi-easel"></i></div>
                                    <?php endif; ?>
                                    <div>
                                        <strong class="small"><?= e($institute['name']) ?></strong>
                                        <?php $loc = trim(($institute['city'] ?? '') . (!empty($institute['state']) ? ', ' . $institute['state'] : '')) ?: ($institute['location'] ?? ''); ?>
                                        <?php if (!empty($loc)): ?><br><span class="small text-muted"><?= e($loc) ?></span><?php endif; ?>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
