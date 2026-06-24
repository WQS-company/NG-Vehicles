<?php
// App/views/payments/manage.php
?>
<div class="row g-4 mb-4">
    <!-- Stat: Daily -->
    <div class="col-md-3">
        <div class="card glass-panel border-0 p-3 text-center">
            <h6 class="text-muted small">Daily Revenue</h6>
            <h4 class="text-success fw-bold mb-0">₦<?= number_format($stats['daily_revenue'] ?? 0, 2) ?></h4>
        </div>
    </div>
    <!-- Stat: Weekly -->
    <div class="col-md-3">
        <div class="card glass-panel border-0 p-3 text-center">
            <h6 class="text-muted small">Weekly Revenue</h6>
            <h4 class="text-success fw-bold mb-0">₦<?= number_format($stats['weekly_revenue'] ?? 0, 2) ?></h4>
        </div>
    </div>
    <!-- Stat: Monthly -->
    <div class="col-md-3">
        <div class="card glass-panel border-0 p-3 text-center">
            <h6 class="text-muted small">Monthly Revenue</h6>
            <h4 class="text-success fw-bold mb-0">₦<?= number_format($stats['monthly_revenue'] ?? 0, 2) ?></h4>
        </div>
    </div>
    <!-- Stat: Total -->
    <div class="col-md-3">
        <div class="card glass-panel border-0 p-3 text-center">
            <h6 class="text-muted small">Total System Revenue</h6>
            <h4 class="text-success fw-bold mb-0">₦<?= number_format($stats['total_revenue'] ?? 0, 2) ?></h4>
        </div>
    </div>
</div>

<div class="card glass-panel border-0 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="text-white m-0"><i class="fa-solid fa-receipt text-success me-2"></i>Payment Collection Ledger</h5>
    </div>

    <?php if (isset($error) && $error): ?>
        <div class="alert alert-danger bg-danger bg-opacity-25 border-0 text-white"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (isset($success) && $success): ?>
        <div class="alert alert-success bg-success bg-opacity-25 border-0 text-white"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table text-secondary w-100" id="paymentsTable">
            <thead>
                <tr>
                    <th>Receipt Number</th>
                    <th>Plate Number</th>
                    <th>Owner</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Collected By</th>
                    <th>Payment Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $p): ?>
                    <tr>
                        <td class="text-white"><code><?= htmlspecialchars($p['receipt_number']) ?></code></td>
                        <td><span class="badge bg-dark border border-secondary text-white"><?= htmlspecialchars($p['plate_number']) ?></span></td>
                        <td><?= htmlspecialchars($p['owner_name']) ?></td>
                        <td class="text-white">₦<?= number_format($p['amount'], 2) ?></td>
                        <td>
                            <span class="badge bg-<?= $p['payment_method'] === 'CASH' ? 'success' : 'info' ?>">
                                <?= htmlspecialchars($p['payment_method']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($p['collected_by_email']) ?></td>
                        <td><?= htmlspecialchars($p['payment_date']) ?></td>
                        <td>
                            <button class="btn btn-sm btn-secondary btn-view-receipt" 
                                    data-receipt="<?= htmlspecialchars($p['receipt_number']) ?>"
                                    data-plate="<?= htmlspecialchars($p['plate_number']) ?>"
                                    data-owner="<?= htmlspecialchars($p['owner_name']) ?>"
                                    data-amount="₦<?= number_format($p['amount'], 2) ?>"
                                    data-method="<?= htmlspecialchars($p['payment_method']) ?>"
                                    data-date="<?= htmlspecialchars($p['payment_date']) ?>"
                                    data-file="<?= htmlspecialchars($p['receipt_file'] ?? '') ?>">
                                <i class="fa-solid fa-eye me-1"></i> Receipt
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        $('#paymentsTable').DataTable({
            order: [[6, 'desc']]
        });

        // Trigger Receipt Modal Popup
        $('.btn-view-receipt').on('click', function() {
            const data = $(this).data();
            
            let fileHtml = '';
            if (data.file) {
                fileHtml = '<p><b>Receipt Evidence:</b> <a href="<?= BASE_URL ?>/' + data.file + '" target="_blank" class="btn btn-xs btn-outline-info text-info border-info border-opacity-50 p-1 py-0 ms-1" style="font-size:0.78rem;"><i class="fa-solid fa-file-arrow-down me-1"></i> View Document</a></p>';
            } else {
                fileHtml = '<p><b>Receipt Evidence:</b> <span class="text-muted">Not Uploaded</span></p>';
            }

            Swal.fire({
                title: '<i class="fa-solid fa-receipt text-success me-2"></i>Federal Onboarding Receipt',
                html: '<div class="text-start border border-secondary border-opacity-20 p-3 rounded bg-dark bg-opacity-50 small">' +
                      '<p><b>Receipt Reference:</b> <code class="text-white">' + data.receipt + '</code></p>' +
                      '<p><b>Plate Number:</b> <span class="badge bg-secondary">' + data.plate + '</span></p>' +
                      '<p><b>Payer Name:</b> <span class="text-white">' + data.owner + '</span></p>' +
                      '<p><b>Paid Amount:</b> <span class="text-success fw-bold">' + data.amount + '</span></p>' +
                      '<p><b>Payment Mode:</b> ' + data.method + '</p>' +
                      fileHtml +
                      '<p><b>Date Processed:</b> ' + data.date + '</p>' +
                      '<hr class="border-secondary border-opacity-30">' +
                      '<p class="text-center text-muted m-0" style="font-size: 11px;">NVOTS Nigeria Federal Traceability Registry Receipt</p>' +
                      '</div>',
                confirmButtonColor: '#10b981',
                confirmButtonText: 'Print / OK'
            });
        });
    });
</script>
