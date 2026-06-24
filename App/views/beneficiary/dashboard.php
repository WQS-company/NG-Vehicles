<?php
// App/views/beneficiary/dashboard.php
// Professional Beneficiary Dashboard
?>
<div class="row g-4">
    <!-- Greeting & Quick Info Header -->
    <div class="col-12 animate-fade-in">
        <div class="glass-panel p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <h3 class="m-0 text-white font-weight-bold">
                    Welcome, <?= htmlspecialchars($user['first_name'] ?? ucfirst(explode('@', $user['email'])[0])) ?>!
                </h3>
                <p class="text-secondary m-0 mt-1">
                    <i class="fa-regular fa-calendar-check me-2"></i><?= date('F d, Y') ?> · Accessing Federal Commission Payroll
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-success p-2 fs-6">
                    <i class="fa-solid fa-circle-check me-1"></i>Active Beneficiary
                </span>
                <?php if ($profile && !empty($profile['role_title'])): ?>
                    <span class="badge bg-primary p-2 fs-6">
                        <i class="fa-solid fa-briefcase me-1"></i><?= htmlspecialchars($profile['role_title']) ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Stats Row -->
    <div class="col-md-4 animate-fade-in" style="animation-delay: 0.1s;">
        <div class="glass-panel p-4 text-center stat-card h-100">
            <div class="stat-icon mb-3">
                <i class="fa-solid fa-wallet fa-2x text-emerald"></i>
            </div>
            <h6 class="text-secondary text-uppercase fw-semibold mb-2">Total Balance Earned</h6>
            <h2 class="text-white fw-bold mb-1">
                ₦<?= number_format($earnings, 2) ?>
            </h2>
            <p class="text-muted small mb-0">Commissions accrued from all processed payments</p>
        </div>
    </div>

    <div class="col-md-4 animate-fade-in" style="animation-delay: 0.2s;">
        <div class="glass-panel p-4 text-center stat-card h-100">
            <div class="stat-icon mb-3">
                <i class="fa-solid fa-percent fa-2x text-blue"></i>
            </div>
            <h6 class="text-secondary text-uppercase fw-semibold mb-2">Active Commission Share</h6>
            <h2 class="text-white fw-bold mb-1">
                <?= htmlspecialchars($profile['commission_percentage'] ?? '0.00') ?>%
            </h2>
            <p class="text-muted small mb-0">Your contract percentage allocated by administrator</p>
        </div>
    </div>

    <div class="col-md-4 animate-fade-in" style="animation-delay: 0.3s;">
        <div class="glass-panel p-4 text-center stat-card h-100">
            <div class="stat-icon mb-3">
                <i class="fa-solid fa-paper-plane fa-2x text-warning"></i>
            </div>
            <h6 class="text-secondary text-uppercase fw-semibold mb-2">Total Paid to Date</h6>
            <?php 
                $totalPaidVal = $recipient ? (float)$recipient['total_paid'] : 0.0;
            ?>
            <h2 class="text-white fw-bold mb-1">
                ₦<?= number_format($totalPaidVal, 2) ?>
            </h2>
            <p class="text-muted small mb-0">Total funds successfully disbursed to your bank</p>
        </div>
    </div>

    <!-- Bank Payout Details Card -->
    <div class="col-lg-5 animate-fade-in" style="animation-delay: 0.4s;">
        <div class="glass-panel p-4 h-100 d-flex flex-column justify-content-between">
            <div>
                <h5 class="text-white mb-3 fw-bold">
                    <i class="fa-solid fa-building-columns me-2 text-success"></i>Payout Bank Settings
                </h5>
                <?php if ($recipient && !empty($recipient['account_number'])): ?>
                    <div class="mb-3 p-3 bg-dark bg-opacity-40 border border-secondary border-opacity-20 rounded-lg">
                        <div class="row g-2">
                            <div class="col-6 text-secondary small">Bank Name:</div>
                            <div class="col-6 text-white fw-semibold"><?= htmlspecialchars($recipient['bank_name']) ?></div>
                            
                            <div class="col-6 text-secondary small">Account Number:</div>
                            <div class="col-6 text-white fw-semibold font-monospace"><?= htmlspecialchars($recipient['account_number']) ?></div>

                            <div class="col-6 text-secondary small">Account Name:</div>
                            <div class="col-6 text-white fw-semibold"><?= htmlspecialchars($recipient['account_name']) ?></div>
                        </div>
                    </div>
                    <div class="alert alert-success d-flex align-items-center mb-0 p-2" style="font-size: 0.8rem;">
                        <i class="fa-solid fa-circle-check me-2"></i> Verified & Active for Automated Transfers.
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning mb-3">
                        <i class="fa-solid fa-circle-exclamation me-2"></i> No active payout account linked. Payouts cannot be automatically processed.
                    </div>
                    <p class="text-secondary small">
                        Please update your bank detail settings in your profile to enable auto-disbursement via Paystack.
                    </p>
                <?php endif; ?>
            </div>
            
            <div class="mt-4 pt-3 border-top border-secondary border-opacity-20">
                <a href="<?= BASE_URL ?>/beneficiary/profile" class="btn btn-secondary btn-sm w-100">
                    <i class="fa-solid fa-user-gear me-2"></i>Update Bank Details
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Payout Events -->
    <div class="col-lg-7 animate-fade-in" style="animation-delay: 0.5s;">
        <div class="glass-panel p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-white m-0 fw-bold">
                    <i class="fa-solid fa-history me-2 text-info"></i>Recent Payroll Transfers
                </h5>
                <a href="<?= BASE_URL ?>/beneficiary/payroll" class="text-emerald text-decoration-none small fw-semibold">
                    View All<i class="fa-solid fa-chevron-right ms-1"></i>
                </a>
            </div>

            <?php if (!empty($payouts)): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-dark align-middle mb-0" style="background: transparent;">
                        <thead>
                            <tr class="text-secondary small">
                                <th>Date</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th class="text-end">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payouts as $p): ?>
                                <tr>
                                    <td class="small"><?= date('M d, Y', strtotime($p['payout_date'])) ?></td>
                                    <td>
                                        <div class="fw-semibold text-white text-truncate" style="max-width: 200px;">
                                            <?= htmlspecialchars($p['payout_title'] ?? 'Payroll Payout') ?>
                                        </div>
                                        <span class="text-muted small" style="font-size: 0.72rem;">Ref: <?= htmlspecialchars($p['paystack_transfer_id'] ?? 'Manual') ?></span>
                                    </td>
                                    <td class="fw-bold text-emerald">₦<?= number_format($p['amount'], 2) ?></td>
                                    <td class="text-end">
                                        <?php if ($p['status'] === 'SUCCESS'): ?>
                                            <span class="badge fw-semibold px-2 py-1" style="background:#10b981;color:#fff;"><i class="fa-solid fa-circle-check me-1"></i>SUCCESS</span>
                                        <?php elseif ($p['status'] === 'PENDING'): ?>
                                            <span class="badge fw-semibold px-2 py-1" style="background:#f59e0b;color:#000;"><i class="fa-solid fa-clock me-1"></i>PENDING</span>
                                        <?php else: ?>
                                            <span class="badge fw-semibold px-2 py-1" style="background:#ef4444;color:#fff;"><i class="fa-solid fa-circle-xmark me-1"></i>FAILED</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="fa-solid fa-money-bill-transfer fa-2x mb-2 opacity-50"></i>
                    <p class="mb-0">No payroll payouts recorded yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.stat-card:hover {
    transform: translateY(-4px);
    border-color: rgba(16, 185, 129, 0.3);
}
.stat-icon i {
    opacity: 0.85;
}
.text-emerald {
    color: #10b981 !important;
}
.text-blue {
    color: #3b82f6 !important;
}
.animate-fade-in {
    animation: fadeIn 0.5s ease-out both;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
