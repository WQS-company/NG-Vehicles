<?php
// App/views/admin/beneficiaries.php
// Admin view to manage beneficiaries
?>
<div class="row g-4">
    <!-- Left Column: Add Beneficiary Form & Account Detail Requests -->
    <div class="col-lg-5">
        <div class="d-flex flex-column gap-4">
            <!-- Add Beneficiary Card -->
            <div class="card glass-panel border-0 p-3">
                <h5 class="text-white fw-bold mb-3"><i class="fa-solid fa-user-plus me-2 text-success"></i>Add Beneficiary</h5>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                <form method="POST" action="<?= BASE_URL ?>/admin/beneficiaries">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-3">
                        <label class="form-label text-secondary small text-uppercase">Full Name</label>
                        <input type="text" name="first_name" class="form-control bg-dark text-white border-secondary" placeholder="e.g. John Doe" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small text-uppercase">Email Address</label>
                        <input type="email" name="email" class="form-control bg-dark text-white border-secondary" placeholder="john.doe@example.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small text-uppercase">Phone Number</label>
                        <input type="text" name="phone" class="form-control bg-dark text-white border-secondary" placeholder="08012345678" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small text-uppercase">Password</label>
                        <div class="position-relative">
                            <input type="password" name="password" id="add_password" class="form-control bg-dark text-white border-secondary pe-5" placeholder="Create secure password">
                            <span class="position-absolute end-0 top-50 translate-middle-y pe-3" style="cursor:pointer;" onclick="togglePasswordInput('add_password', 'add_password_icon')">
                                <i class="fa-solid fa-eye text-secondary" id="add_password_icon"></i>
                            </span>
                        </div>
                        <div class="form-text text-muted">Leave blank to generate a temporary password automatically.</div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary small text-uppercase">Role / Title</label>
                            <input type="text" name="role_title" class="form-control bg-dark text-white border-secondary" value="Officer">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small text-uppercase">Commission %</label>
                            <input type="number" step="0.01" name="commission_percentage" class="form-control bg-dark text-white border-secondary" value="0.00">
                        </div>
                    </div>
                    <div class="d-grid mt-4">
                        <button class="btn btn-success py-2 fw-semibold">Register Beneficiary</button>
                    </div>
                </form>
            </div>

            <!-- Beneficiary Account Update Requests Card -->
            <div class="card glass-panel border-0 p-3">
                <h5 class="text-white fw-bold mb-2"><i class="fa-solid fa-headset me-2 text-warning"></i>Account Details Requests</h5>
                <p class="text-muted small mb-3">Requests submitted by beneficiaries from their profile panels:</p>
                <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                    <table class="table table-sm table-striped align-middle">
                        <thead>
                            <tr class="small text-secondary">
                                <th>Date</th>
                                <th>Beneficiary</th>
                                <th>Request Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($requests)): ?>
                                <tr><td colspan="3" class="text-center text-muted py-3 small">No active requests logged.</td></tr>
                            <?php else: ?>
                                <?php foreach ($requests as $r): ?>
                                    <tr class="small">
                                        <td class="text-nowrap text-secondary" style="font-size: 0.72rem;"><?= date('M d, y h:i A', strtotime($r['performed_at'])) ?></td>
                                        <td>
                                            <div class="fw-semibold text-white" style="font-size: 0.8rem;"><?= htmlspecialchars($r['first_name'] ?? 'Officer') ?></div>
                                            <span class="text-muted" style="font-size: 0.7rem;"><?= htmlspecialchars($r['email']) ?></span>
                                        </td>
                                        <td class="text-white-50" style="font-size: 0.75rem; line-height: 1.3; min-width: 150px; word-break: break-word;">
                                            <?php 
                                                $parts = explode(' — ', $r['description']);
                                                echo htmlspecialchars(end($parts));
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Beneficiaries Table List -->
    <div class="col-lg-7">
        <div class="card glass-panel border-0 p-3 h-100">
            <h5 class="text-white fw-bold mb-3"><i class="fa-solid fa-users me-2 text-success"></i>Beneficiary Directory</h5>
            <div class="table-responsive">
                <table class="table table-striped table-dark align-middle" id="beneficiaryTable">
                    <thead>
                        <tr>
                            <th>Profile Info</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Rate</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($beneficiaries)): ?>
                            <tr><td colspan="6" class="text-center text-muted">No registered beneficiaries found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($beneficiaries as $b): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-white"><?= htmlspecialchars($b['first_name']) ?></div>
                                        <span class="text-muted small"><?= htmlspecialchars($b['role_title'] ?? 'Officer') ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($b['email']) ?></td>
                                    <td><?= htmlspecialchars($b['phone']) ?></td>
                                    <td><span class="badge bg-primary"><?= htmlspecialchars($b['commission_percentage']) ?>%</span></td>
                                    <td><?= $b['is_suspended'] ? '<span class="badge bg-warning text-dark">Suspended</span>' : '<span class="badge bg-success">Active</span>' ?></td>
                                    <td class="text-end">
                                        <div class="d-flex gap-1 justify-content-end">
                                            <!-- Edit Button -->
                                            <button class="btn btn-sm btn-outline-primary edit-beneficiary-btn" 
                                                    data-user-id="<?= $b['user_id'] ?>"
                                                    data-name="<?= htmlspecialchars($b['first_name']) ?>"
                                                    data-email="<?= htmlspecialchars($b['email']) ?>"
                                                    data-phone="<?= htmlspecialchars($b['phone']) ?>"
                                                    data-title="<?= htmlspecialchars($b['role_title'] ?? 'Officer') ?>"
                                                    data-commission="<?= htmlspecialchars($b['commission_percentage']) ?>"
                                                    title="Edit profile">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>

                                            <!-- Notify Button -->
                                            <button class="btn btn-sm btn-outline-info notify-beneficiary-btn" 
                                                    data-user-id="<?= $b['user_id'] ?>"
                                                    data-name="<?= htmlspecialchars($b['first_name']) ?>"
                                                    data-email="<?= htmlspecialchars($b['email']) ?>"
                                                    title="Send Alert notification">
                                                <i class="fa-solid fa-bell"></i>
                                            </button>

                                            <!-- Suspend/Reactivate -->
                                            <?php if (!$b['is_suspended']): ?>
                                                <form method="POST" action="<?= BASE_URL ?>/admin/beneficiaries" style="display:inline-block">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                    <input type="hidden" name="action" value="suspend">
                                                    <input type="hidden" name="beneficiary_id" value="<?= $b['id'] ?>">
                                                    <button class="btn btn-sm btn-outline-warning" title="Suspend account"><i class="fa-solid fa-ban"></i></button>
                                                </form>
                                            <?php else: ?>
                                                <form method="POST" action="<?= BASE_URL ?>/admin/beneficiaries" style="display:inline-block">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                    <input type="hidden" name="action" value="activate">
                                                    <input type="hidden" name="beneficiary_id" value="<?= $b['id'] ?>">
                                                    <button class="btn btn-sm btn-outline-success" title="Reactivate account"><i class="fa-solid fa-check"></i></button>
                                                </form>
                                            <?php endif; ?>

                                            <!-- Delete Button -->
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteBeneficiary(<?= $b['user_id'] ?>, '<?= htmlspecialchars($b['first_name'], ENT_QUOTES) ?>')" 
                                                    title="Permanently remove beneficiary">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Edit Beneficiary Modal -->
<div class="modal fade" id="editBeneficiaryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border-secondary text-white">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-pen me-2 text-success"></i>Edit Beneficiary</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin/beneficiaries">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary small text-uppercase">Full Name</label>
                        <input type="text" name="first_name" id="edit_first_name" class="form-control bg-dark text-white border-secondary" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small text-uppercase">Email Address</label>
                        <input type="email" name="email" id="edit_email" class="form-control bg-dark text-white border-secondary" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small text-uppercase">Phone Number</label>
                        <input type="text" name="phone" id="edit_phone" class="form-control bg-dark text-white border-secondary" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary small text-uppercase">Role / Title</label>
                            <input type="text" name="role_title" id="edit_role_title" class="form-control bg-dark text-white border-secondary">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small text-uppercase">Commission %</label>
                            <input type="number" step="0.01" name="commission_percentage" id="edit_commission_percentage" class="form-control bg-dark text-white border-secondary">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label text-secondary small text-uppercase">Reset Password (Optional)</label>
                        <div class="position-relative">
                            <input type="password" name="password" id="edit_password" class="form-control bg-dark text-white border-secondary pe-5" placeholder="Leave blank to retain password">
                            <span class="position-absolute end-0 top-50 translate-middle-y pe-3" style="cursor:pointer;" onclick="togglePasswordInput('edit_password', 'edit_password_icon')">
                                <i class="fa-solid fa-eye text-secondary" id="edit_password_icon"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Send Notification Modal -->
<div class="modal fade" id="notifyBeneficiaryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border-secondary text-white">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-paper-plane me-2 text-info"></i>Dispatch Notification</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin/beneficiaries">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="action" value="notify">
                <input type="hidden" name="user_id" id="notify_user_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary small text-uppercase">Recipient</label>
                        <input type="text" id="notify_recipient_name" class="form-control bg-dark text-white border-secondary" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small text-uppercase">Notification Mode</label>
                        <select name="type" class="form-select bg-dark text-white border-secondary" required>
                            <option value="DASHBOARD">Dashboard Alert Notification</option>
                            <option value="EMAIL">Email Notification Stub</option>
                            <option value="SMS">SMS Notification Stub</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label text-secondary small text-uppercase">Alert Message</label>
                        <textarea name="message" class="form-control bg-dark text-white border-secondary" rows="4" placeholder="Enter alert details..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Send Alert</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Hidden Cascade Delete Form -->
<form id="deleteBeneficiaryForm" method="POST" action="<?= BASE_URL ?>/admin/beneficiaries" style="display:none;">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="beneficiary_id" id="delete_beneficiary_id">
</form>

<!-- Modal scripts -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Populate Edit modal data
    $('.edit-beneficiary-btn').on('click', function() {
        const btn = $(this);
        $('#edit_user_id').val(btn.data('user-id'));
        $('#edit_first_name').val(btn.data('name'));
        $('#edit_email').val(btn.data('email'));
        $('#edit_phone').val(btn.data('phone'));
        $('#edit_role_title').val(btn.data('title'));
        $('#edit_commission_percentage').val(btn.data('commission'));

        const modal = new bootstrap.Modal(document.getElementById('editBeneficiaryModal'));
        modal.show();
    });

    // Populate Notify modal data
    $('.notify-beneficiary-btn').on('click', function() {
        const btn = $(this);
        $('#notify_user_id').val(btn.data('user-id'));
        $('#notify_recipient_name').val(btn.data('name') + ' (' + btn.data('email') + ')');

        const modal = new bootstrap.Modal(document.getElementById('notifyBeneficiaryModal'));
        modal.show();
    });

    // Setup DataTable for search & filters
    if ($('#beneficiaryTable tbody tr').length > 1 && !$('#beneficiaryTable tbody td').hasClass('text-center')) {
        $('#beneficiaryTable').DataTable({
            order: [[0, 'asc']],
            dom: '<"d-flex justify-content-between flex-wrap gap-2 mb-2"f>rtip',
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search beneficiaries..."
            }
        });
    }
});

// SweetAlert2 Confirmation Dialog for permanent deletion
function deleteBeneficiary(userId, name) {
    Swal.fire({
        title: 'Delete Beneficiary?',
        text: `You are about to permanently remove "${name}". This cascade-deletes their user login, beneficiary profile, and commission configuration. This action CANNOT be undone!`,
        icon: 'warning',
        showCancelButton: true,
        background: '#0f172a',
        color: '#f8fafc',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#475569',
        confirmButtonText: 'Yes, delete permanently',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete_beneficiary_id').value = userId;
            document.getElementById('deleteBeneficiaryForm').submit();
        }
    });
}

// Toggle password inputs
function togglePasswordInput(inputId, iconId) {
    const field = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (field.type === 'password') {
        field.type = 'text';
        icon.className = 'fa-solid fa-eye-slash text-secondary';
    } else {
        field.type = 'password';
        icon.className = 'fa-solid fa-eye text-secondary';
    }
}
</script>

<!-- Custom Styles for Dark Theme Datatables & Table Grid -->
<style>
/* DataTables dark theme styling overrides */
.dataTables_wrapper .dataTables_filter input {
    background-color: rgba(15, 23, 42, 0.6) !important;
    border: 1px solid rgba(255, 255, 255, 0.15) !important;
    color: #f8fafc !important;
    border-radius: 8px !important;
    padding: 6px 12px !important;
    outline: none !important;
    font-size: 0.9rem !important;
}
.dataTables_wrapper .dataTables_filter input::placeholder {
    color: #64748b !important;
}
.dataTables_wrapper .dataTables_filter input:focus {
    border-color: rgba(16, 185, 129, 0.5) !important;
    box-shadow: 0 0 0 1px rgba(16, 185, 129, 0.2) !important;
}
.dataTables_wrapper .dataTables_info {
    color: #94a3b8 !important;
    font-size: 0.8rem !important;
    margin-top: 10px !important;
}
.dataTables_wrapper .dataTables_paginate {
    margin-top: 10px !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button {
    color: #94a3b8 !important;
    border-radius: 6px !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    background: rgba(255, 255, 255, 0.02) !important;
    font-size: 0.8rem !important;
    padding: 4px 10px !important;
    margin: 0 2px !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background: rgba(16, 185, 129, 0.1) !important;
    border-color: rgba(16, 185, 129, 0.3) !important;
    color: #10b981 !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: #10b981 !important;
    border-color: #10b981 !important;
    color: #fff !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
    opacity: 0.5 !important;
    cursor: not-allowed !important;
}

/* Table styling overrides */
#beneficiaryTable {
    color: #e2e8f0 !important;
}
#beneficiaryTable thead th {
    color: #94a3b8 !important;
    font-size: 0.75rem !important;
    text-transform: uppercase !important;
    letter-spacing: 0.05em !important;
    font-weight: 700 !important;
    border-bottom: 2px solid rgba(255, 255, 255, 0.08) !important;
}
#beneficiaryTable tbody tr {
    background-color: rgba(255, 255, 255, 0.01) !important;
    transition: all 0.2s ease !important;
}
#beneficiaryTable tbody tr:hover {
    background-color: rgba(255, 255, 255, 0.03) !important;
}
#beneficiaryTable tbody td {
    border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
}
</style>
