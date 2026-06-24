<?php
// App/views/admin/admins.php
$featureMap = [
    'registration' => ['label' => 'Registration', 'icon' => 'fa-car-rear', 'color' => '#3b82f6'],
    'verification' => ['label' => 'Verification', 'icon' => 'fa-file-signature', 'color' => '#8b5cf6'],
    'payments'     => ['label' => 'Payments', 'icon' => 'fa-receipt', 'color' => '#f59e0b'],
    'reports'      => ['label' => 'Reports', 'icon' => 'fa-chart-bar', 'color' => '#06b6d4'],
    'correction'   => ['label' => 'Data Correction', 'icon' => 'fa-file-pen', 'color' => '#ef4444'],
];
$allFeatureKeys = array_keys($featureMap);
?>

<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h4 class="text-white fw-bold m-0"><i class="fa-solid fa-users-gear text-success me-2"></i>Administrator Management</h4>
                <p class="text-secondary small m-0 mt-1">Create, edit roles, manage feature access, reset passwords, and remove administrators.</p>
            </div>
            <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createAdminModal">
                <i class="fa-solid fa-user-plus"></i> Register New Admin
            </button>
        </div>
    </div>
</div>

<!-- Alerts -->
<?php if (isset($error) && $error): ?>
    <div class="col-12 mb-3">
        <div class="alert alert-danger bg-danger bg-opacity-15 border border-danger border-opacity-25 text-white d-flex align-items-center gap-2">
            <i class="fa-solid fa-circle-exclamation text-danger"></i> <?= htmlspecialchars($error) ?>
        </div>
    </div>
<?php endif; ?>

<?php if (isset($success) && $success): ?>
    <div class="col-12 mb-3">
        <div class="alert alert-success bg-success bg-opacity-15 border border-success border-opacity-25 text-white d-flex align-items-center gap-2">
            <i class="fa-solid fa-circle-check text-success"></i> <?= htmlspecialchars($success) ?>
        </div>
    </div>
<?php endif; ?>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <?php
    $totalAdmins = count($admins);
    $activeAdmins = count(array_filter($admins, fn($a) => $a['is_active']));
    $blockedAdmins = $totalAdmins - $activeAdmins;
    $superAdmins = count(array_filter($admins, fn($a) => $a['role'] === 'SUPER_ADMIN'));
    ?>
    <div class="col-6 col-md-3">
        <div class="card glass-panel border-0 p-3 text-center">
            <div class="text-success fs-4 fw-bold"><?= $totalAdmins ?></div>
            <div class="text-secondary small">Total Admins</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card glass-panel border-0 p-3 text-center">
            <div class="text-primary fs-4 fw-bold"><?= $activeAdmins ?></div>
            <div class="text-secondary small">Active</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card glass-panel border-0 p-3 text-center">
            <div class="text-danger fs-4 fw-bold"><?= $blockedAdmins ?></div>
            <div class="text-secondary small">Blocked</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card glass-panel border-0 p-3 text-center">
            <div class="text-warning fs-4 fw-bold"><?= $superAdmins ?></div>
            <div class="text-secondary small">Super Admins</div>
        </div>
    </div>
</div>

<!-- Admin Cards Grid -->
<div class="row g-3">
    <?php foreach ($admins as $admin): 
        $isSelf = ($admin['id'] === (int)$_SESSION['user_id']);
        $adminFeatures = !empty($admin['features']) ? explode(',', $admin['features']) : [];
        $roleColors = [
            'SUPER_ADMIN' => ['bg' => 'rgba(245,158,11,0.15)', 'border' => 'rgba(245,158,11,0.35)', 'text' => '#f59e0b', 'label' => 'Super Admin'],
            'REGISTRATION_ADMIN' => ['bg' => 'rgba(59,130,246,0.15)', 'border' => 'rgba(59,130,246,0.35)', 'text' => '#3b82f6', 'label' => 'Registration Admin'],
            'VERIFICATION_ADMIN' => ['bg' => 'rgba(139,92,246,0.15)', 'border' => 'rgba(139,92,246,0.35)', 'text' => '#8b5cf6', 'label' => 'Verification Admin'],
        ];
        $rc = $roleColors[$admin['role']] ?? $roleColors['REGISTRATION_ADMIN'];
    ?>
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card glass-panel border-0 h-100 admin-card <?= !$admin['is_active'] ? 'admin-blocked' : '' ?>">
            <!-- Card Header with avatar + name -->
            <div class="card-body p-4">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="position-relative">
                        <img src="<?= !empty($admin['avatar']) ? BASE_URL . '/' . htmlspecialchars($admin['avatar']) : BASE_URL . '/public/images/no-avatar.png' ?>" 
                             alt="Avatar" class="rounded-circle object-fit-cover border border-2 shadow-sm" 
                             width="56" height="56" 
                             style="object-fit: cover; border-color: <?= $rc['text'] ?> !important;">
                        <!-- Status dot -->
                        <span class="position-absolute bottom-0 end-0 rounded-circle border border-2 border-dark" 
                              style="width: 14px; height: 14px; background: <?= $admin['is_active'] ? '#10b981' : '#ef4444' ?>;"></span>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h6 class="text-white fw-bold m-0 text-truncate"><?= htmlspecialchars($admin['first_name'] ?? 'Admin') ?></h6>
                            <?php if ($isSelf): ?>
                                <span class="badge bg-primary" style="font-size: 0.65rem;">You</span>
                            <?php endif; ?>
                        </div>
                        <div class="text-secondary small text-truncate"><i class="fa-solid fa-envelope me-1" style="font-size:0.65rem;"></i><?= htmlspecialchars($admin['email']) ?></div>
                        <div class="text-secondary small"><i class="fa-solid fa-phone me-1" style="font-size:0.65rem;"></i><?= htmlspecialchars($admin['phone']) ?></div>
                    </div>
                </div>

                <!-- Role Badge -->
                <div class="mb-3">
                    <span class="badge px-3 py-2" style="background: <?= $rc['bg'] ?>; border: 1px solid <?= $rc['border'] ?>; color: <?= $rc['text'] ?>;">
                        <i class="fa-solid fa-shield-halved me-1"></i><?= $rc['label'] ?>
                    </span>
                    <?php if (!$admin['is_active']): ?>
                        <span class="badge bg-danger bg-opacity-15 border border-danger border-opacity-30 text-danger px-2 py-2 ms-1">
                            <i class="fa-solid fa-ban me-1"></i>Blocked
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Feature Pills -->
                <div class="mb-3">
                    <div class="text-secondary small mb-2" style="font-size:0.7rem; text-transform:uppercase; letter-spacing:0.5px;">
                        <i class="fa-solid fa-key me-1"></i>Feature Access
                    </div>
                    <?php if ($admin['role'] === 'SUPER_ADMIN'): ?>
                        <span class="badge bg-success bg-opacity-15 border border-success border-opacity-30 text-success px-2 py-1">
                            <i class="fa-solid fa-infinity me-1"></i>Full Platform Access
                        </span>
                    <?php elseif (empty($adminFeatures) || (count($adminFeatures) === 1 && $adminFeatures[0] === '')): ?>
                        <span class="text-muted small fst-italic"><i class="fa-solid fa-circle-xmark me-1"></i>No features allocated</span>
                    <?php else: ?>
                        <div class="d-flex flex-wrap gap-1">
                            <?php foreach ($adminFeatures as $feat): 
                                $feat = trim($feat);
                                $fm = $featureMap[$feat] ?? null;
                                if (!$fm) continue;
                            ?>
                                <span class="badge px-2 py-1" style="background: <?= $fm['color'] ?>22; border: 1px solid <?= $fm['color'] ?>55; color: <?= $fm['color'] ?>; font-size: 0.7rem;">
                                    <i class="fa-solid <?= $fm['icon'] ?> me-1"></i><?= $fm['label'] ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Action Buttons -->
                <?php if (!$isSelf): ?>
                <div class="d-flex flex-wrap gap-2 pt-2 border-top border-secondary border-opacity-15">
                    <button class="btn btn-sm btn-outline-light flex-grow-1 btn-edit-admin" 
                            data-id="<?= $admin['id'] ?>"
                            data-name="<?= htmlspecialchars($admin['first_name'] ?? '') ?>"
                            data-email="<?= htmlspecialchars($admin['email']) ?>"
                            data-phone="<?= htmlspecialchars($admin['phone']) ?>"
                            data-role="<?= htmlspecialchars($admin['role']) ?>"
                            data-features="<?= htmlspecialchars($admin['features'] ?? '') ?>"
                            data-active="<?= $admin['is_active'] ?>"
                            data-bs-toggle="modal" 
                            data-bs-target="#editAdminModal">
                        <i class="fa-solid fa-user-pen me-1"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-outline-warning btn-reset-pwd"
                            data-id="<?= $admin['id'] ?>"
                            data-name="<?= htmlspecialchars($admin['first_name'] ?? 'Admin') ?>"
                            data-bs-toggle="modal"
                            data-bs-target="#resetPwdModal">
                        <i class="fa-solid fa-key me-1"></i> Reset
                    </button>
                    <button class="btn btn-sm btn-outline-danger btn-delete-admin"
                            data-id="<?= $admin['id'] ?>"
                            data-name="<?= htmlspecialchars($admin['first_name'] ?? 'Admin') ?>"
                            data-email="<?= htmlspecialchars($admin['email']) ?>">
                        <i class="fa-solid fa-trash-can me-1"></i>
                    </button>
                </div>
                <?php else: ?>
                <div class="pt-2 border-top border-secondary border-opacity-15">
                    <a href="<?= BASE_URL ?>/admin/profile" class="btn btn-sm btn-outline-success w-100">
                        <i class="fa-solid fa-user-gear me-1"></i> Manage via Profile
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ════════════════════════════════════════════ -->
<!-- CREATE ADMIN MODAL                          -->
<!-- ════════════════════════════════════════════ -->
<div class="modal fade" id="createAdminModal" tabindex="-1" aria-labelledby="createAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark text-white border border-secondary border-opacity-35 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-secondary border-opacity-20 px-4 pt-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-3" style="width:42px;height:42px;background:linear-gradient(135deg,#10b981,#059669);">
                        <i class="fa-solid fa-user-plus text-white fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold m-0" id="createAdminModalLabel">Register New Administrator</h5>
                        <small class="text-secondary">Set up credentials, role, and feature access</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin/admins">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="action" value="create">
                <div class="modal-body px-4 py-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary small">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="first_name" required placeholder="e.g. Alao Ibrahim">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" required placeholder="e.g. admin@example.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" name="phone" required placeholder="e.g. 08030000000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="password" required placeholder="Min 6 characters" minlength="6">
                        </div>
                        <div class="col-12">
                            <label class="form-label text-secondary small">System Role <span class="text-danger">*</span></label>
                            <select class="form-select" name="role" id="create_role" required>
                                <option value="REGISTRATION_ADMIN">Registration Admin</option>
                                <option value="VERIFICATION_ADMIN">Verification Admin</option>
                                <option value="SUPER_ADMIN">Super Admin (Full Access)</option>
                            </select>
                        </div>
                        <div class="col-12" id="create_features_section">
                            <label class="form-label text-secondary small d-block"><i class="fa-solid fa-key me-1"></i>Feature Access Modules</label>
                            <div class="row g-2">
                                <?php foreach ($featureMap as $key => $fm): ?>
                                <div class="col-6 col-md-4">
                                    <label class="feature-checkbox-card w-100">
                                        <input type="checkbox" name="features[]" value="<?= $key ?>" class="d-none create-feat-check">
                                        <div class="feature-card-inner">
                                            <i class="fa-solid <?= $fm['icon'] ?>" style="color: <?= $fm['color'] ?>"></i>
                                            <span><?= $fm['label'] ?></span>
                                        </div>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-20 px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="fa-solid fa-circle-check me-1"></i> Register Admin</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════ -->
<!-- EDIT ADMIN MODAL                            -->
<!-- ════════════════════════════════════════════ -->
<div class="modal fade" id="editAdminModal" tabindex="-1" aria-labelledby="editAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark text-white border border-secondary border-opacity-35 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-secondary border-opacity-20 px-4 pt-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-3" style="width:42px;height:42px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);">
                        <i class="fa-solid fa-user-pen text-white fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold m-0" id="editAdminModalLabel">Edit Administrator</h5>
                        <small class="text-secondary">Update profile, role, feature access, and status</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin/admins">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="admin_id" id="edit_admin_id">
                <div class="modal-body px-4 py-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary small">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="first_name" id="edit_first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" id="edit_email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" name="phone" id="edit_phone" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small">System Role <span class="text-danger">*</span></label>
                            <select class="form-select" name="role" id="edit_role" required>
                                <option value="REGISTRATION_ADMIN">Registration Admin</option>
                                <option value="VERIFICATION_ADMIN">Verification Admin</option>
                                <option value="SUPER_ADMIN">Super Admin (Full Access)</option>
                            </select>
                        </div>
                        <div class="col-12" id="edit_features_section">
                            <label class="form-label text-secondary small d-block"><i class="fa-solid fa-key me-1"></i>Feature Access Modules</label>
                            <div class="row g-2">
                                <?php foreach ($featureMap as $key => $fm): ?>
                                <div class="col-6 col-md-4">
                                    <label class="feature-checkbox-card w-100">
                                        <input type="checkbox" name="features[]" value="<?= $key ?>" class="d-none edit-feat-check" id="edit_feat_<?= $key ?>">
                                        <div class="feature-card-inner">
                                            <i class="fa-solid <?= $fm['icon'] ?>" style="color: <?= $fm['color'] ?>"></i>
                                            <span><?= $fm['label'] ?></span>
                                        </div>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between p-3 rounded-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);">
                                <div>
                                    <div class="text-white fw-semibold small">Account Status</div>
                                    <div class="text-secondary small">Toggle to activate or block this administrator</div>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active" value="1" role="switch" style="width: 3rem; height: 1.5rem;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-20 px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="fa-solid fa-circle-check me-1"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════ -->
<!-- RESET PASSWORD MODAL                        -->
<!-- ════════════════════════════════════════════ -->
<div class="modal fade" id="resetPwdModal" tabindex="-1" aria-labelledby="resetPwdModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white border border-warning border-opacity-35 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-secondary border-opacity-20 px-4 pt-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-3" style="width:42px;height:42px;background:linear-gradient(135deg,#f59e0b,#d97706);">
                        <i class="fa-solid fa-key text-white fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold m-0" id="resetPwdModalLabel">Reset Password</h5>
                        <small class="text-secondary">Set a new password for <strong class="text-white" id="reset_admin_display">Admin</strong></small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin/admins">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="admin_id" id="reset_admin_id">
                <div class="modal-body px-4 py-4">
                    <div class="alert alert-warning bg-warning bg-opacity-10 border-warning border-opacity-25 text-warning small">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i>
                        This will immediately change the admin's password. They will need to use the new password on their next login.
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small">New Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="new_password" required placeholder="Min 6 characters" minlength="6">
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-20 px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-dark px-4"><i class="fa-solid fa-key me-1"></i> Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Hidden Delete Form -->
<form method="POST" action="<?= BASE_URL ?>/admin/admins" id="deleteAdminForm" class="d-none">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="admin_id" id="delete_admin_id">
</form>

<!-- Styles -->
<style>
/* Admin card styling */
.admin-card {
    transition: all 0.3s ease;
    overflow: hidden;
}
.admin-card:hover {
    transform: translateY(-2px);
}
.admin-blocked {
    opacity: 0.65;
}
.admin-blocked:hover {
    opacity: 0.85;
}

/* Feature checkbox card styling */
.feature-checkbox-card {
    cursor: pointer;
    display: block;
}
.feature-checkbox-card .feature-card-inner {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    border-radius: 10px;
    border: 1px solid rgba(255,255,255,0.08);
    background: rgba(255,255,255,0.03);
    color: #94a3b8;
    font-size: 0.82rem;
    transition: all 0.2s ease;
}
.feature-checkbox-card .feature-card-inner:hover {
    border-color: rgba(255,255,255,0.2);
    background: rgba(255,255,255,0.06);
}
.feature-checkbox-card input:checked + .feature-card-inner {
    border-color: rgba(16,185,129,0.5);
    background: rgba(16,185,129,0.08);
    color: #e2e8f0;
    box-shadow: 0 0 0 1px rgba(16,185,129,0.2);
}
.feature-checkbox-card input:checked + .feature-card-inner::before {
    content: '✓ ';
    color: #10b981;
    font-weight: 700;
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {

    // ── Edit Admin Modal Population ──
    $('.btn-edit-admin').on('click', function() {
        const btn = $(this);
        $('#edit_admin_id').val(btn.data('id'));
        $('#edit_first_name').val(btn.data('name'));
        $('#edit_email').val(btn.data('email'));
        $('#edit_phone').val(btn.data('phone'));
        $('#edit_role').val(btn.data('role'));
        $('#edit_is_active').prop('checked', btn.data('active') == 1);

        // Reset all feature checkboxes
        $('.edit-feat-check').prop('checked', false);

        // Check allocated features
        const features = (btn.data('features') || '').toString();
        if (features) {
            features.split(',').forEach(function(feat) {
                feat = feat.trim();
                if (feat) $('#edit_feat_' + feat).prop('checked', true);
            });
        }

        // Show/hide features section based on role
        toggleFeaturesSection('#edit_role', '#edit_features_section');
    });

    // ── Reset Password Modal Population ──
    $('.btn-reset-pwd').on('click', function() {
        const btn = $(this);
        $('#reset_admin_id').val(btn.data('id'));
        $('#reset_admin_display').text(btn.data('name'));
    });

    // ── Delete Admin with SweetAlert2 confirmation ──
    $('.btn-delete-admin').on('click', function() {
        const btn = $(this);
        const adminId = btn.data('id');
        const adminName = btn.data('name');
        const adminEmail = btn.data('email');

        Swal.fire({
            title: 'Delete Administrator?',
            html: `<div style="color:#94a3b8;">
                     <p>You are about to permanently remove:</p>
                     <p class="fw-bold text-white">${adminName} (${adminEmail})</p>
                     <p class="small text-danger">This action cannot be undone.</p>
                   </div>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fa-solid fa-trash-can me-1"></i> Yes, Delete',
            cancelButtonText: 'Cancel',
            background: '#1e293b',
            color: '#f8fafc'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#delete_admin_id').val(adminId);
                $('#deleteAdminForm').submit();
            }
        });
    });

    // ── Toggle features section visibility based on role ──
    function toggleFeaturesSection(roleSelector, featuresSection) {
        const role = $(roleSelector).val();
        if (role === 'SUPER_ADMIN') {
            $(featuresSection).slideUp(200);
        } else {
            $(featuresSection).slideDown(200);
        }
    }

    // Role change handlers
    $('#create_role').on('change', function() {
        toggleFeaturesSection('#create_role', '#create_features_section');
    });
    $('#edit_role').on('change', function() {
        toggleFeaturesSection('#edit_role', '#edit_features_section');
    });

    // Initial check
    toggleFeaturesSection('#create_role', '#create_features_section');
});
</script>
