<?php
// Commissions History / Payouts
?>

<div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="text-white">Commission Payouts History</h4>
    <a href="<?= BASE_URL ?>/commission" class="btn btn-sm">Back to Board</a>
</div>

<?php if (!empty($payout)): ?>
    <div class="card p-3 mb-3">
        <h5>Payout: <?= htmlspecialchars($payout['title']) ?></h5>
        <p>Revenue: <?= format_currency($payout['revenue_amount'],2) ?> | Created: <?= $payout['created_at'] ?></p>

        <table class="table table-striped table-dark">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Recipient</th>
                    <th>Account</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Paid At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $i): ?>
                    <tr>
                        <td><?= $i['id'] ?></td>
                        <td><?= htmlspecialchars($i['name']) ?> (<?= htmlspecialchars($i['email']) ?>)</td>
                        <td><?= htmlspecialchars($i['bank_name']) ?> - <?= htmlspecialchars($i['account_number']) ?></td>
                        <td><?= format_currency($i['amount'],2) ?></td>
                        <td><?= $i['status'] ?></td>
                        <td><?= $i['paid_at'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <table class="table table-striped table-dark">
        <thead>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Revenue</th>
                <th>Processed By</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($payouts as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= htmlspecialchars($p['title']) ?></td>
                    <td><?= format_currency($p['revenue_amount'],2) ?></td>
                    <td><?= htmlspecialchars($p['processed_by_email'] ?? 'System') ?></td>
                    <td><?= $p['created_at'] ?></td>
                    <td><a href="<?= BASE_URL ?>/commission/history/<?= $p['id'] ?>" class="btn btn-sm btn-primary">View</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
