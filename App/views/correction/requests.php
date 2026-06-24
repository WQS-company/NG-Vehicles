<?php
// App/views/correction/requests.php
// Professional Admin Dashboard to Manage Correction Requests & Payments
?>
<div class="row">
    <div class="col-12 mb-4">
        <h4 class="text-white"><i class="fa-solid fa-list-check text-success me-2"></i>Correction Requests Manager</h4>
        <p class="text-secondary">Review submitted proof of payments for vehicle and owner data corrections. Verify payments to unlock details for modification.</p>
    </div>

    <!-- Quick Stats Row -->
    <div class="col-md-3 mb-4">
        <div class="card glass-panel border-0 p-3 h-100 text-center">
            <span class="text-secondary small text-uppercase fw-bold">Total Requests</span>
            <h2 class="text-white fw-bold mt-2 mb-0"><?= count($requests) ?></h2>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <?php
        $pendingCount = count(array_filter($requests, fn($r) => $r['status'] === 'PENDING'));
        ?>
        <div class="card glass-panel border-0 p-3 h-100 text-center border-start border-3 border-warning">
            <span class="text-secondary small text-uppercase fw-bold">Pending Verification</span>
            <h2 class="text-warning fw-bold mt-2 mb-0"><?= $pendingCount ?></h2>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <?php
        $verifiedCount = count(array_filter($requests, fn($r) => $r['status'] === 'VERIFIED'));
        ?>
        <div class="card glass-panel border-0 p-3 h-100 text-center border-start border-3 border-success">
            <span class="text-secondary small text-uppercase fw-bold">Verified & Unlocked</span>
            <h2 class="text-success fw-bold mt-2 mb-0"><?= $verifiedCount ?></h2>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <?php
        $totalFees = array_sum(array_map(fn($r) => $r['status'] === 'VERIFIED' ? (float)$r['amount'] : 0, $requests));
        ?>
        <div class="card glass-panel border-0 p-3 h-100 text-center border-start border-3 border-info">
            <span class="text-secondary small text-uppercase fw-bold">Total Revenue (₦)</span>
            <h2 class="text-info fw-bold mt-2 mb-0">₦<?= number_format($totalFees, 2) ?></h2>
        </div>
    </div>

    <!-- Main Correction Table -->
    <div class="col-12">
        <div class="card glass-panel border-0 p-4">
            <h5 class="text-white mb-4"><i class="fa-solid fa-receipt text-success me-2"></i>Incoming Payment Submissions</h5>

            <?php if (isset($error) && $error): ?>
                <div class="alert alert-danger bg-danger bg-opacity-25 border-0 text-white mb-4"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (isset($success) && $success): ?>
                <div class="alert alert-success bg-success bg-opacity-25 border-0 text-white mb-4"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-hover align-middle text-white m-0" id="requestsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Target / Details</th>
                            <th>Requested By</th>
                            <th>Fee Amount</th>
                            <th>Payment Reference</th>
                            <th>Receipt Proof</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $r): ?>
                            <tr>
                                <td class="font-monospace small text-secondary">#<?= $r['id'] ?></td>
                                <td>
                                    <?php if ($r['entity_type'] === 'vehicle'): ?>
                                        <span class="badge bg-primary"><i class="fa-solid fa-car me-1"></i> Vehicle</span>
                                    <?php else: ?>
                                        <span class="badge bg-info text-dark"><i class="fa-solid fa-user me-1"></i> Owner</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($r['entity_type'] === 'vehicle'): ?>
                                        <div class="fw-bold"><?= htmlspecialchars($r['target_identifier'] ?? 'Vehicle ID: ' . $r['entity_id']) ?></div>
                                        <div class="small text-secondary"><?= htmlspecialchars($r['target_detail'] ?? '') ?></div>
                                    <?php else: ?>
                                        <div class="fw-bold"><?= htmlspecialchars($r['target_identifier'] ?? 'Owner ID: ' . $r['entity_id']) ?></div>
                                        <div class="small text-secondary"><?= htmlspecialchars($r['target_detail'] ?? '') ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="small fw-semibold"><?= htmlspecialchars($r['requester_name']) ?></div>
                                    <div class="x-small text-muted font-monospace"><?= htmlspecialchars($r['requester_email']) ?></div>
                                </td>
                                <td class="fw-bold text-success font-monospace">₦<?= number_format($r['amount'], 2) ?></td>
                                <td>
                                    <span class="small font-monospace"><?= htmlspecialchars($r['receipt_number'] ?: 'Auto-Generated') ?></span>
                                    <div class="x-small text-secondary"><?= htmlspecialchars($r['payment_method']) ?></div>
                                </td>
                                <td>
                                    <?php if (!empty($r['receipt_file'])): ?>
                                        <button type="button" class="btn btn-outline-info btn-xs px-2 py-1" onclick="viewReceipt('<?= BASE_URL ?>/<?= htmlspecialchars($r['receipt_file']) ?>')">
                                            <i class="fa-solid fa-file-invoice"></i> View Proof
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted small">No File</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($r['status'] === 'PENDING'): ?>
                                        <span class="badge bg-warning text-dark"><i class="fa-solid fa-hourglass-half me-1"></i> Pending Verification</span>
                                    <?php elseif ($r['status'] === 'VERIFIED'): ?>
                                        <span class="badge bg-success"><i class="fa-solid fa-circle-check me-1"></i> Verified</span>
                                        <?php if ($r['is_corrected']): ?>
                                            <div class="x-small text-success mt-1"><i class="fa-solid fa-check-double"></i> Corrected</div>
                                        <?php else: ?>
                                            <div class="x-small text-warning mt-1"><i class="fa-solid fa-lock-open"></i> Awaiting Edit</div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><i class="fa-solid fa-circle-xmark me-1"></i> Rejected</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($r['status'] === 'PENDING'): ?>
                                        <div class="d-inline-flex gap-2">
                                            <form method="POST" action="<?= BASE_URL ?>/correction/verify/<?= $r['id'] ?>" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                <input type="hidden" name="decision" value="approve">
                                                <button type="submit" class="btn btn-success btn-sm px-3 fw-bold">
                                                    <i class="fa-solid fa-check me-1"></i> Approve
                                                </button>
                                            </form>
                                            <form method="POST" action="<?= BASE_URL ?>/correction/verify/<?= $r['id'] ?>" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                <input type="hidden" name="decision" value="reject">
                                                <button type="submit" class="btn btn-danger btn-sm px-3 fw-bold">
                                                    <i class="fa-solid fa-xmark me-1"></i> Reject
                                                </button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-secondary small">Processed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark border-secondary border-opacity-30 text-white">
            <div class="modal-header border-secondary border-opacity-20">
                <h5 class="modal-title"><i class="fa-solid fa-receipt text-success me-2"></i>Payment Receipt Proof</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <div id="receiptPreviewContainer"></div>
            </div>
            <div class="modal-footer border-secondary border-opacity-20">
                <a id="downloadReceiptBtn" href="#" target="_blank" class="btn btn-outline-light btn-sm"><i class="fa-solid fa-up-right-from-square me-1"></i> Open in New Tab</a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    $('#requestsTable').DataTable({
        order: [[0, "desc"]],
        pageLength: 10,
        lengthMenu: [10, 25, 50]
    });
});

function viewReceipt(fileUrl) {
    const container = document.getElementById('receiptPreviewContainer');
    const downloadBtn = document.getElementById('downloadReceiptBtn');
    downloadBtn.href = fileUrl;
    
    container.innerHTML = '';
    
    if (fileUrl.toLowerCase().endsWith('.pdf')) {
        container.innerHTML = `<embed src="${fileUrl}" type="application/pdf" width="100%" height="450px" />`;
    } else {
        container.innerHTML = `<img src="${fileUrl}" class="img-fluid rounded shadow" style="max-height: 450px; object-fit: contain;" />`;
    }
    
    const modal = new bootstrap.Modal(document.getElementById('receiptModal'));
    modal.show();
}
</script>
