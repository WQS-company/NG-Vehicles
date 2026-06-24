<?php
// App/views/beneficiary/profile.php
// Beneficiary profile settings and bank update view

$banks = require BASE_PATH . '/config/paystack_banks.php';

// Detect if custom bank is selected/saved
$is_other_bank = false;
if ($recipient && !empty($recipient['bank_name']) && !in_array($recipient['bank_name'], $banks)) {
    $is_other_bank = true;
}
?>

<div class="container-fluid px-4 py-3">
    <!-- Header with title and description -->
    <div class="row mb-4">
        <div class="col-12 animate-fade-in">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-success bg-opacity-20 p-3 rounded-3 border border-success border-opacity-30">
                    <i class="fa-solid fa-user-gear fa-2x text-success"></i>
                </div>
                <div>
                    <h4 class="text-white fw-bold mb-1">Account & Payout Settings</h4>
                    <p class="text-secondary small mb-0">Manage profile graphics, personal identity data, and automated Paystack settlement routes.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert notifications -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger d-flex align-items-center mb-4 border-danger border-opacity-30 bg-danger bg-opacity-10 text-danger animate-fade-in">
            <i class="fa-solid fa-circle-exclamation me-2 fs-5"></i>
            <div><?= htmlspecialchars($error) ?></div>
        </div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success d-flex align-items-center mb-4 border-success border-opacity-30 bg-success bg-opacity-10 text-success animate-fade-in">
            <i class="fa-solid fa-circle-check me-2 fs-5"></i>
            <div><?= htmlspecialchars($success) ?></div>
        </div>
    <?php endif; ?>

    <div class="row g-4 justify-content-center">
        <!-- Left Column: Premium Profile Summary -->
        <div class="col-lg-4 animate-fade-in" style="animation-delay: 0.1s;">
            <div class="glass-panel p-4 text-center h-100 d-flex flex-column justify-content-between position-relative overflow-hidden">
                <!-- Decorative background elements -->
                <div class="position-absolute top-0 end-0 bg-success opacity-10 rounded-circle" style="width: 150px; height: 150px; transform: translate(50px, -50px); filter: blur(30px);"></div>
                
                <div>
                    <div class="mb-4 position-relative d-inline-block">
                        <img src="<?= !empty($user['avatar']) ? BASE_URL . '/' . htmlspecialchars($user['avatar']) : BASE_URL . '/public/images/no-avatar.png' ?>" 
                             alt="Avatar" 
                             class="rounded-circle border border-4 border-success border-opacity-50 object-fit-cover shadow-lg" 
                             width="130" height="130" style="object-fit: cover;">
                        <span class="position-absolute bottom-0 end-0 bg-success border border-3 border-dark rounded-circle" style="width: 18px; height: 18px;" title="Online Status"></span>
                    </div>
                    
                    <h5 class="text-white fw-bold mb-1"><?= htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?></h5>
                    <p class="text-success small fw-semibold mb-3"><i class="fa-solid fa-circle-check me-1"></i><?= htmlspecialchars($profile['role_title'] ?? 'Beneficiary') ?></p>
                    
                    <div class="badge bg-dark border border-secondary border-opacity-20 py-2 px-3 rounded-pill text-secondary font-monospace mb-4">
                        <?= htmlspecialchars($user['email']) ?>
                    </div>

                    <div class="border-top border-secondary border-opacity-10 pt-4 text-start">
                        <div class="d-flex justify-content-between mb-3 align-items-center">
                            <span class="text-secondary small">Commission Rate</span>
                            <span class="text-white fw-bold fs-5"><?= htmlspecialchars($profile['commission_percentage'] ?? '0.00') ?>%</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 align-items-center">
                            <span class="text-secondary small">Total Payouts Paid</span>
                            <span class="text-success fw-bold font-monospace">₦<?= number_format($recipient['total_paid'] ?? 0, 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-secondary small">Account Status</span>
                            <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-30">Active & Sync'd</span>
                        </div>
                    </div>
                </div>

                <div class="border-top border-secondary border-opacity-10 pt-4 mt-4">
                    <a href="<?= BASE_URL ?>/beneficiary/payroll" class="btn btn-outline-success btn-sm w-100 py-2.5 rounded-3 mb-2">
                        <i class="fa-solid fa-file-invoice-dollar me-2"></i>View Payroll & Payouts
                    </a>
                    <a href="<?= BASE_URL ?>/beneficiary/dashboard" class="btn btn-outline-light btn-sm w-100 py-2.5 rounded-3 border-opacity-20">
                        <i class="fa-solid fa-chart-line me-2"></i>Go to Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Right Column: Settings and Sync forms -->
        <div class="col-lg-8 animate-fade-in" style="animation-delay: 0.2s;">
            <form method="POST" action="<?= BASE_URL ?>/beneficiary/profile" enctype="multipart/form-data" class="d-flex flex-column gap-4">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                
                <!-- Main Form Settings Panel -->
                <div class="glass-panel p-4">
                    <h5 class="text-white fw-bold mb-4 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-sliders text-success"></i>
                        General Settings
                    </h5>
                    
                    <!-- Avatar Graphic Upload -->
                    <div class="mb-4 pb-4 border-bottom border-secondary border-opacity-10">
                        <label class="form-label text-secondary small text-uppercase fw-semibold mb-2">Upload Profile Image</label>
                        <div class="d-flex align-items-center gap-3">
                            <div class="flex-grow-1">
                                <input type="file" name="avatar" class="form-control bg-dark border-secondary text-white" accept="image/*">
                            </div>
                            <span class="text-muted small">Max 3MB (JPG/PNG)</span>
                        </div>
                    </div>

                    <h5 class="text-white fw-bold mb-3 mt-2 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-building-columns text-info"></i>
                        Receiving Bank Account Details
                    </h5>
                    <p class="text-secondary small mb-4">Set your banking details below. NVOTS payouts are sent to this account. You can select standard banks or type yours if not listed.</p>
                    
                    <input type="hidden" name="bank_code" id="bankCodeHidden" value="<?= htmlspecialchars($recipient['bank_code'] ?? '') ?>">

                    <div class="row g-3">
                        <!-- Receiving Bank Name -->
                        <div class="col-md-6">
                            <label class="form-label text-secondary small text-uppercase fw-semibold">Receiving Bank</label>
                            <select name="bank_name" id="bankNameSelect" class="form-select bg-dark border-secondary text-white" required>
                                <option value="">— Select Bank —</option>
                                <?php foreach ($banks as $code => $label): ?>
                                    <option value="<?= htmlspecialchars($label) ?>" 
                                            data-code="<?= htmlspecialchars($code) ?>"
                                            <?= (!$is_other_bank && $recipient && $recipient['bank_name'] === $label) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($label) ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="other" data-code="custom" <?= $is_other_bank ? 'selected' : '' ?>>— Other Bank (Type Manually) —</option>
                            </select>
                        </div>

                        <!-- Custom Bank Input (Conditional) -->
                        <div class="col-md-6" id="customBankDiv" style="display: <?= $is_other_bank ? 'block' : 'none' ?>;">
                            <label class="form-label text-secondary small text-uppercase fw-semibold">Custom Bank Name</label>
                            <input type="text" 
                                   name="custom_bank_name" 
                                   id="customBankInput" 
                                   class="form-control bg-dark border-secondary text-white" 
                                   placeholder="Enter Bank Name" 
                                   value="<?= $is_other_bank ? htmlspecialchars($recipient['bank_name']) : '' ?>">
                        </div>

                        <!-- Account Number (10 Digits) -->
                        <div class="col-md-6">
                            <label class="form-label text-secondary small text-uppercase fw-semibold">Account Number (10 Digits)</label>
                            <input type="text" 
                                   name="account_number" 
                                   id="accountNoInput"
                                   maxlength="10"
                                   class="form-control font-monospace bg-dark border-secondary text-white" 
                                   placeholder="e.g. 0123456789"
                                   value="<?= htmlspecialchars($recipient['account_number'] ?? '') ?>" 
                                   required>
                        </div>

                        <!-- Account Name -->
                        <div class="col-md-12">
                            <label class="form-label text-secondary small text-uppercase fw-semibold">Account Name (Full Legal Name)</label>
                            <input type="text" 
                                   name="account_name" 
                                   class="form-control bg-dark border-secondary text-white" 
                                   placeholder="e.g. John A. Doe" 
                                   value="<?= htmlspecialchars($recipient['account_name'] ?? '') ?>" 
                                   required>
                        </div>
                    </div>
                </div>

                <!-- Admin change requests panel -->
                <div class="glass-panel p-4">
                    <h5 class="text-white fw-bold mb-3 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-headset text-warning"></i>
                        Contact Registry Admin
                    </h5>
                    <p class="text-secondary small mb-3">To update profile attributes managed under administrative review (e.g. email, phone numbers, commission shares), submit a ticket note below.</p>
                    <div class="mb-3">
                        <textarea name="request_change" 
                                  class="form-control bg-dark border-secondary text-white" 
                                  rows="3"
                                  placeholder="Describe the profile update details you wish to request..."></textarea>
                    </div>
                    <div class="d-flex align-items-center gap-2 text-muted small">
                        <i class="fa-solid fa-clock-rotate-left text-warning"></i>
                        <span>Requests are logged and reviewed by the portal's SUPER_ADMIN in due course.</span>
                    </div>
                </div>

                <!-- Action Button -->
                <div>
                    <button type="submit" class="btn btn-success w-100 py-3 rounded-3 fs-6 fw-bold shadow-lg transition-transform hover-scale">
                        <i class="fa-solid fa-cloud-arrow-up me-2"></i>Save Settings & Sync Portal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const bankSelect = document.getElementById('bankNameSelect');
    const bankCodeHidden = document.getElementById('bankCodeHidden');
    const customBankDiv = document.getElementById('customBankDiv');
    const customBankInput = document.getElementById('customBankInput');

    function toggleCustomBank() {
        if (bankSelect.value === 'other') {
            customBankDiv.style.display = 'block';
            customBankInput.setAttribute('required', 'required');
        } else {
            customBankDiv.style.display = 'none';
            customBankInput.removeAttribute('required');
            customBankInput.value = '';
        }
    }

    bankSelect.addEventListener('change', function() {
        const selectedOption = bankSelect.options[bankSelect.selectedIndex];
        const code = selectedOption.getAttribute('data-code') || '';
        bankCodeHidden.value = code;
        toggleCustomBank();
    });

    // Run once on load
    toggleCustomBank();

    // Enforce 10-digit numeric constraint
    const accNoInput = document.getElementById('accountNoInput');
    accNoInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
});
</script>

<style>
.animate-fade-in {
    animation: fadeIn 0.5s ease-out both;
}
.hover-scale {
    transition: all 0.2s ease-in-out;
}
.hover-scale:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(16, 185, 129, 0.25) !important;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
