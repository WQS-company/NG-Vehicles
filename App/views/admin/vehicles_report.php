<?php /** @var array $rows */ ?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Vehicle Registration Report</h5>
        <div>
            <a href="?controller=vehicle&action=printReport&state=<?= urlencode($filter_state) ?>&lga=<?= urlencode($filter_lga) ?>&sort=<?= urlencode($filter_sort) ?>" class="btn btn-outline-primary" target="_blank">Print / View</a>
            <a href="?controller=vehicle&action=pdfReport&state=<?= urlencode($filter_state) ?>&lga=<?= urlencode($filter_lga) ?>&sort=<?= urlencode($filter_sort) ?>" class="btn btn-outline-dark">Download PDF</a>
            <a href="?controller=vehicle&action=exportReport&format=csv&state=<?= urlencode($filter_state) ?>&lga=<?= urlencode($filter_lga) ?>&sort=<?= urlencode($filter_sort) ?>" class="btn btn-success">Download CSV</a>
            <a href="?controller=vehicle&action=exportReport&format=xlsx&state=<?= urlencode($filter_state) ?>&lga=<?= urlencode($filter_lga) ?>&sort=<?= urlencode($filter_sort) ?>" class="btn btn-secondary">Download Excel (XLSX)</a>
        </div>
    </div>
    <div class="card-body">
        <form method="get" class="row g-2 mb-3">
            <input type="hidden" name="controller" value="vehicle">
            <input type="hidden" name="action" value="report">
            <div class="col-auto">
                <label class="form-label">State(s)</label>
                <input type="text" name="state" class="form-control" value="<?= htmlspecialchars($filter_state) ?>" placeholder="Enter one or more states, separated by commas">
            </div>
            <div class="col-auto">
                <label class="form-label">Local Govt</label>
                <input type="text" name="lga" class="form-control" value="<?= htmlspecialchars($filter_lga) ?>" placeholder="Filter by LGA">
            </div>
            <div class="col-auto">
                <label class="form-label">Sort</label>
                <select name="sort" class="form-select">
                    <option value="" <?= $filter_sort === '' ? 'selected' : '' ?>>Default</option>
                    <option value="state" <?= $filter_sort === 'state' ? 'selected' : '' ?>>Sort by State / LGA</option>
                    <option value="category" <?= $filter_sort === 'category' ? 'selected' : '' ?>>Sort by Category</option>
                </select>
            </div>
            <div class="col-auto align-self-end">
                <button class="btn btn-primary">Apply</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>VIN</th>
                        <th>Plate</th>
                        <th>Make / Model</th>
                        <th>Year</th>
                        <th>Class</th>
                        <th>Current Owner</th>
                        <th>Prev Owner</th>
                        <th>Total Paid</th>
                        <th>State / LGA</th>
                        <th>Importer / Port</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $i => $r):
                        $v = $r['vehicle'];
                        $cur = $r['current_owner'];
                        $prev = $r['previous_owner'];
                        $pay = $r['payments'];
                        $c = $r['custom'];
                    ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td><?= htmlspecialchars($v['vin'] ?? '') ?></td>
                        <td><?= htmlspecialchars($v['plate_number'] ?? '') ?></td>
                        <td><?= htmlspecialchars(($v['manufacturer'] ?? '') . ' ' . ($v['model'] ?? '')) ?></td>
                        <td><?= htmlspecialchars($v['year'] ?? '') ?></td>
                        <td><?= htmlspecialchars($v['class'] ?? '') ?></td>
                        <td><?= htmlspecialchars($cur['full_name'] ?? '') ?><?= $cur['phone'] ? ' <br><small>' . htmlspecialchars($cur['phone']) . '</small>' : '' ?></td>
                        <td><?= htmlspecialchars($prev['full_name'] ?? '') ?></td>
                        <td><?= number_format((float)($pay['total_payments'] ?? 0), 2) ?></td>
                        <td><?= htmlspecialchars(($c['number_plate_state'] ?? '') . ' / ' . ($c['number_plate_lga'] ?? '')) ?></td>
                        <td><?= htmlspecialchars(($c['importer_name'] ?? '') . ' / ' . ($c['port_name'] ?? '')) ?></td>
                        <td><?= nl2br(htmlspecialchars($r['summary'] ?? '')) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
