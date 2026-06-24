<?php
// App/views/beneficiary/payroll.php
// Beneficiary Commission Payroll System view

// Compute simple analytics for Chart.js
$monthlyPayouts = [];
$totalPaid = 0.0;
$totalPending = 0.0;

foreach ($payouts as $p) {
    $amount = (float)$p['amount'];
    if ($p['status'] === 'SUCCESS') {
        $totalPaid += $amount;
        $month = date('M Y', strtotime($p['payout_date']));
        if (!isset($monthlyPayouts[$month])) {
            $monthlyPayouts[$month] = 0.0;
        }
        $monthlyPayouts[$month] += $amount;
    } elseif ($p['status'] === 'PENDING') {
        $totalPending += $amount;
    }
}

// Order monthly data chronologically (reverse since payouts were DESC)
$monthlyPayouts = array_reverse($monthlyPayouts);
$chartLabels = array_keys($monthlyPayouts);
$chartValues = array_values($monthlyPayouts);
?>

<div class="row g-4">
    <!-- Header Page Title -->
    <div class="col-12 animate-fade-in">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <div>
                <h4 class="text-white fw-bold m-0">Commission Payroll System</h4>
                <p class="text-secondary small m-0">View earnings, payroll disbursements, and payout statement records</p>
            </div>
            <button onclick="window.print()" class="btn btn-secondary btn-sm d-none d-md-inline-block">
                <i class="fa-solid fa-print me-2"></i>Print Statement
            </button>
        </div>
    </div>

    <!-- Summary Metrics Card -->
    <div class="col-md-3 animate-fade-in" style="animation-delay: 0.1s;">
        <div class="glass-panel p-3 text-center">
            <h6 class="text-secondary text-uppercase fw-semibold mb-1 small">Total Disbursed</h6>
            <h3 class="text-success fw-bold m-0">₦<?= number_format($totalPaid, 2) ?></h3>
            <span class="text-muted small fs-7">Transferred successfully</span>
        </div>
    </div>

    <div class="col-md-3 animate-fade-in" style="animation-delay: 0.2s;">
        <div class="glass-panel p-3 text-center">
            <h6 class="text-secondary text-uppercase fw-semibold mb-1 small">Pending Processing</h6>
            <h3 class="text-warning fw-bold m-0">₦<?= number_format($totalPending, 2) ?></h3>
            <span class="text-muted small fs-7">Awaiting execution</span>
        </div>
    </div>

    <div class="col-md-3 animate-fade-in" style="animation-delay: 0.3s;">
        <div class="glass-panel p-3 text-center">
            <h6 class="text-secondary text-uppercase fw-semibold mb-1 small">Active Share Rate</h6>
            <h3 class="text-info fw-bold m-0"><?= htmlspecialchars($profile['commission_percentage'] ?? '0.00') ?>%</h3>
            <span class="text-muted small fs-7">Per registration onboarding</span>
        </div>
    </div>

    <div class="col-md-3 animate-fade-in" style="animation-delay: 0.4s;">
        <div class="glass-panel p-3 text-center">
            <h6 class="text-secondary text-uppercase fw-semibold mb-1 small">Total Payouts Done</h6>
            <h3 class="text-white fw-bold m-0"><?= count($payouts) ?> Payouts</h3>
            <span class="text-muted small fs-7">Chronological events logged</span>
        </div>
    </div>

    <!-- Earnings Trend Chart -->
    <?php if (!empty($chartLabels)): ?>
    <div class="col-lg-12 animate-fade-in" style="animation-delay: 0.5s;">
        <div class="glass-panel p-4">
            <h5 class="text-white fw-bold mb-3">
                <i class="fa-solid fa-chart-line me-2 text-success"></i>Monthly Disbursement Analytics
            </h5>
            <div style="position: relative; height: 260px; width: 100%;">
                <canvas id="payrollTrendChart"></canvas>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Payout Items Statement List -->
    <div class="col-12 animate-fade-in" style="animation-delay: 0.6s;">
        <div class="glass-panel p-4">
            <h5 class="text-white fw-bold mb-3">
                <i class="fa-solid fa-receipt me-2 text-info"></i>Disbursement History
            </h5>
            
            <div class="table-responsive">
                <table class="table table-striped table-dark align-middle" id="payrollTable">
                    <thead>
                        <tr class="text-secondary small">
                            <th>Transaction Date</th>
                            <th>Description</th>
                            <th>Reference Code</th>
                            <th>Payout Amount</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payouts as $p): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold text-white">
                                        <?= date('M d, Y', strtotime($p['payout_date'])) ?>
                                    </div>
                                    <span class="text-muted small fs-8"><?= date('h:i A', strtotime($p['payout_date'])) ?></span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-white">
                                        <?= htmlspecialchars($p['payout_title'] ?? 'Commission Payout') ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="font-monospace text-secondary small bg-dark bg-opacity-50 px-2 py-1 rounded">
                                        <?= htmlspecialchars($p['paystack_transfer_id'] ?? 'Manual / Offline') ?>
                                    </span>
                                </td>
                                <td class="fw-bold text-success-light">₦<?= number_format($p['amount'], 2) ?></td>
                                <td>
                                    <?php if ($p['status'] === 'SUCCESS'): ?>
                                        <span class="badge fw-semibold px-2 py-1" style="background:#10b981;color:#fff;"><i class="fa-solid fa-circle-check me-1"></i>SUCCESS</span>
                                    <?php elseif ($p['status'] === 'PENDING'): ?>
                                        <span class="badge fw-semibold px-2 py-1" style="background:#f59e0b;color:#000;"><i class="fa-solid fa-clock-rotate-left me-1"></i>PENDING</span>
                                    <?php else: ?>
                                        <span class="badge fw-semibold px-2 py-1" style="background:#ef4444;color:#fff;"><i class="fa-solid fa-circle-xmark me-1"></i>FAILED</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-light view-slip-btn" 
                                            data-date="<?= date('F d, Y', strtotime($p['payout_date'])) ?>"
                                            data-title="<?= htmlspecialchars($p['payout_title'] ?? 'Commission Payout') ?>"
                                            data-ref="<?= htmlspecialchars($p['paystack_transfer_id'] ?? 'N/A') ?>"
                                            data-amount="₦<?= number_format($p['amount'], 2) ?>"
                                            data-status="<?= $p['status'] ?>"
                                            data-bank="<?= htmlspecialchars($recipient['bank_name'] ?? 'N/A') ?>"
                                            data-account="<?= htmlspecialchars($recipient['account_number'] ?? 'N/A') ?>">
                                        <i class="fa-solid fa-file-invoice me-1"></i>Slip
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($payouts)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fa-solid fa-money-bill-transfer fa-2x mb-2 opacity-50"></i>
                                    <p class="mb-0">No disbursements recorded under this recipient account.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.text-success-light {
    color: #34d399 !important;
}
.fs-7 { font-size: 0.8rem; }
.fs-8 { font-size: 0.72rem; }
.animate-fade-in {
    animation: fadeIn 0.5s ease-out both;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<!-- Chart.js and DataTables Script Setup -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Initialize Chart.js Trend
    <?php if (!empty($chartLabels)): ?>
    const ctx = document.getElementById('payrollTrendChart').getContext('2d');
    
    // Create subtle gradient fill
    const grad = ctx.createLinearGradient(0, 0, 0, 240);
    grad.addColorStop(0, 'rgba(16, 185, 129, 0.4)');
    grad.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($chartLabels) ?>,
            datasets: [{
                label: 'Monthly Earnings (₦)',
                data: <?= json_encode($chartValues) ?>,
                borderColor: '#10b981',
                borderWidth: 3,
                backgroundColor: grad,
                fill: true,
                tension: 0.3,
                pointBackgroundColor: '#10b981',
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(255, 255, 255, 0.05)' },
                    ticks: { color: '#cbd5e1' }
                },
                y: {
                    grid: { color: 'rgba(255, 255, 255, 0.05)' },
                    ticks: { color: '#cbd5e1' }
                }
            }
        }
    });
    <?php endif; ?>

    // 2. Initialize DataTables
    if ($('#payrollTable tbody tr').length > 1 && !$('#payrollTable tbody td').hasClass('text-center')) {
        $('#payrollTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 10,
            dom: '<"d-flex justify-content-between flex-wrap gap-2 mb-3"lf>rt<"d-flex justify-content-between flex-wrap gap-2 mt-3"ip>',
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search Payouts..."
            }
        });
    }

    // 3. View Payout Slip modal popup
    $('.view-slip-btn').on('click', function() {
        const btn = $(this);
        const date = btn.data('date');
        const title = btn.data('title');
        const ref = btn.data('ref');
        const amount = btn.data('amount');
        const status = btn.data('status');
        const bank = btn.data('bank');
        const account = btn.data('account');

        let statusBadge = '';
        if (status === 'SUCCESS') {
            statusBadge = '<span class="badge bg-success">SUCCESS</span>';
        } else if (status === 'PENDING') {
            statusBadge = '<span class="badge bg-warning text-dark">PENDING</span>';
        } else {
            statusBadge = '<span class="badge bg-danger">FAILED</span>';
        }

        Swal.fire({
            title: '<h4 class="text-white fw-bold">Commission Disbursement Slip</h4>',
            background: '#0f172a',
            color: '#f8fafc',
            confirmButtonColor: '#10b981',
            html: `
                <div class="text-start py-2" style="font-size: 0.9rem;">
                    <div class="border-bottom border-secondary border-opacity-20 pb-2 mb-2 text-center">
                        <h5 class="text-success fw-bold mb-0">${amount}</h5>
                        <p class="text-muted small mb-0">${title}</p>
                    </div>
                    <div class="row g-2">
                        <div class="col-5 text-secondary">Reference Code:</div>
                        <div class="col-7 text-white font-monospace">${ref}</div>

                        <div class="col-5 text-secondary">Payout Date:</div>
                        <div class="col-7 text-white">${date}</div>

                        <div class="col-5 text-secondary">Status:</div>
                        <div class="col-7">${statusBadge}</div>

                        <div class="col-5 text-secondary">Bank Target:</div>
                        <div class="col-7 text-white">${bank}</div>

                        <div class="col-5 text-secondary">Account # Target:</div>
                        <div class="col-7 text-white font-monospace">${account}</div>
                    </div>
                    <div class="mt-4 pt-2 border-top border-secondary border-opacity-20 text-center small text-muted">
                        Federal Government of Nigeria · NVOTS Payroll System
                    </div>
                </div>
            `,
            confirmButtonText: 'Close Statement'
        });
    });
});
</script>
