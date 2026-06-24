<?php
// Commission Summary Report
?>

<div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="text-white">Commission Report</h4>
    <div>
        <a href="<?= BASE_URL ?>/commission" class="btn btn-sm">Back to Board</a>
        <a href="<?= BASE_URL ?>/commission/exportReport" class="btn btn-sm">Export CSV</a>
    </div>
</div>

<table class="table table-striped table-dark">
    <thead>
        <tr>
            <th>Recipient</th>
            <th>Email</th>
            <th>% Share</th>
            <th>Total Paid</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($summary as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['name']) ?></td>
                <td><?= htmlspecialchars($s['email']) ?></td>
                <td><?= htmlspecialchars($s['percentage_share']) ?>%</td>
                <td><?= format_currency($s['total_paid'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
