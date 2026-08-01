<div class="container py-5" style="max-width: 840px;">

    <div class="card mb-4">
        <div class="card-body d-flex flex-wrap gap-4 align-items-center">
            <?php if (!empty($college['logo_path'])): ?>
                <img src="<?= url($college['logo_path']) ?>" alt="<?= e($college['name']) ?>" class="rounded-circle border" loading="lazy" style="width:96px;height:96px;object-fit:cover;">
            <?php else: ?>
                <div class="rounded-circle d-flex align-items-center justify-content-center feature-icon" style="width:96px;height:96px;">
                    <i class="bi bi-bank fs-2"></i>
                </div>
            <?php endif; ?>
            <div>
                <h1 class="h3 fw-bold mb-1"><?= e($college['name']) ?></h1>
                <?php if (!empty($college['location'])): ?>
                    <p class="small text-muted mb-0"><i class="bi bi-geo-alt me-1"></i><?= e($college['location']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (!empty($college['description'])): ?>
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-quote me-2 text-primary"></i>About</div>
            <div class="card-body"><p class="mb-0"><?= nl2br(e($college['description'])) ?></p></div>
        </div>
    <?php endif; ?>

    <?php if (!empty($drives)): ?>
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-briefcase me-2 text-primary"></i>Campus Drives</div>
            <div class="card-body">
                <?php foreach ($drives as $drive): ?>
                    <div class="profile-row">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                            <div>
                                <strong><?= e($drive['company_name']) ?></strong>
                                <div class="small text-muted">
                                    <?php if (!empty($drive['drive_date'])): ?><?= e($drive['drive_date']) ?><?php endif; ?>
                                    <?php if (!empty($drive['eligible_departments'])): ?> &middot; <?= e($drive['eligible_departments']) ?><?php endif; ?>
                                    <?php if (!empty($drive['min_cgpa'])): ?> &middot; Min CGPA <?= e($drive['min_cgpa']) ?><?php endif; ?>
                                </div>
                            </div>
                            <?php if ($isStudent): ?>
                                <?php if (!empty($drive['already_registered'])): ?>
                                    <span class="badge text-bg-light border">Registered</span>
                                <?php else: ?>
                                    <form method="post" action="<?= url('/colleges/drives/' . $drive['id'] . '/register') ?>" data-guard-submit>
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-primary">Register</button>
                                    </form>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="<?= url('/login') ?>" class="btn btn-sm btn-outline-primary">Log in to register</a>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($drive['description'])): ?><p class="small text-muted mb-0 mt-1"><?= nl2br(e($drive['description'])) ?></p><?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($departmentStats)): ?>
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-bar-chart me-2 text-primary"></i>Department-wise Placement Stats</div>
            <div class="card-body">
                <span class="badge text-bg-light border mb-2">College-reported</span>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Department</th>
                                <th>Year</th>
                                <th>Placed</th>
                                <th>Avg package</th>
                                <th>Highest package</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($departmentStats as $stat): ?>
                                <tr>
                                    <td><?= e($stat['department_name']) ?></td>
                                    <td><?= e($stat['academic_year'] ?? '') ?></td>
                                    <td><?= e($stat['students_placed'] ?? '?') ?>/<?= e($stat['total_students'] ?? '?') ?></td>
                                    <td><?= !empty($stat['average_package']) ? '&#8377;' . number_format((float) $stat['average_package']) . '/yr' : '-' ?></td>
                                    <td><?= !empty($stat['highest_package']) ? '&#8377;' . number_format((float) $stat['highest_package']) . '/yr' : '-' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($alumni)): ?>
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-people me-2 text-primary"></i>Alumni Wall</div>
            <div class="card-body">
                <span class="badge text-bg-light border mb-2">College-reported</span>
                <div class="row g-3">
                    <?php foreach ($alumni as $alum): ?>
                        <div class="col-md-6 d-flex gap-3 align-items-start">
                            <?php if (!empty($alum['photo_path'])): ?>
                                <img src="<?= url($alum['photo_path']) ?>" alt="<?= e($alum['name']) ?>" class="rounded-circle" loading="lazy" style="width:48px;height:48px;object-fit:cover;">
                            <?php else: ?>
                                <div class="rounded-circle d-flex align-items-center justify-content-center feature-icon" style="width:48px;height:48px;"><i class="bi bi-person"></i></div>
                            <?php endif; ?>
                            <div>
                                <strong><?= e($alum['name']) ?></strong><?= !empty($alum['batch_year']) ? ' (' . e($alum['batch_year']) . ')' : '' ?>
                                <div class="small text-muted">
                                    <?= e($alum['current_position'] ?? '') ?><?= !empty($alum['current_company']) ? ' at ' . e($alum['current_company']) : '' ?>
                                </div>
                                <?php if (!empty($alum['testimonial'])): ?><p class="small text-muted mb-0 mt-1 fst-italic">"<?= e($alum['testimonial']) ?>"</p><?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <p class="text-center text-muted small"><a href="<?= url('/colleges') ?>">&larr; Back to all colleges</a></p>
</div>
