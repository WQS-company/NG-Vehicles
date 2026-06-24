<?php /** @var array $rows */ ?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Vehicle Registration Report</title>
    <style>
        body { font-family: Arial, sans-serif; color:#111; }
        .header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
        .title { font-size:18px; font-weight:600; }
        table { width:100%; border-collapse:collapse; font-size:11px; }
        th, td { border:1px solid #ddd; padding:6px; word-break:break-word; vertical-align:top; }
        th { background:#f5f5f5; }
        .meta { font-size:12px; color:#666; }
        @media print { .no-print { display:none; } }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="title">Vehicle Registration Report</div>
            <div class="meta">Generated: <?= date('Y-m-d H:i:s') ?> | State: <?= htmlspecialchars($filter_state) ?> | LGA: <?= htmlspecialchars($filter_lga) ?></div>
        </div>
        <div class="no-print">
            <button onclick="window.print()">Print / Save as PDF</button>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>VIN</th>
                <th>Plate</th>
                <th>Make / Model</th>
                <th>Year</th>
                <th>Class</th>
                <th>Engine</th>
                <th>Chassis</th>
                <th>Color</th>
                <th>Fuel / Transmission</th>
                <th>Category</th>
                <th>Current Owner</th>
                <th>Prev Owner</th>
                <th>Total Paid</th>
                <th>Importer</th>
                <th>Port</th>
                <th>State</th>
                <th>LGA</th>
                <th>Image</th>
                <th>Receipt</th>
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
                $img = $v['image_path'] ?? '';
                $receipt = $r['receipt_url'] ?? '';
            ?>
            <tr>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($v['vin'] ?? '') ?></td>
                <td><?= htmlspecialchars($v['plate_number'] ?? '') ?></td>
                <td><?= htmlspecialchars(($v['manufacturer'] ?? '') . ' ' . ($v['model'] ?? '')) ?></td>
                <td><?= htmlspecialchars($v['year'] ?? '') ?></td>
                <td><?= htmlspecialchars($v['class'] ?? '') ?></td>
                <td><?= htmlspecialchars($v['engine_number'] ?? '') ?></td>
                <td><?= htmlspecialchars($v['chassis_number'] ?? '') ?></td>
                <td><?= htmlspecialchars($v['color'] ?? '') ?></td>
                <td><?= htmlspecialchars(($v['fuel_type'] ?? '') . ' / ' . ($v['transmission'] ?? '')) ?></td>
                <td><?= htmlspecialchars($v['category'] ?? '') ?></td>
                <td><?= htmlspecialchars($cur['full_name'] ?? '') ?><?= $cur['phone'] ? ' <br><small>' . htmlspecialchars($cur['phone']) . '</small>' : '' ?></td>
                <td><?= htmlspecialchars($prev['full_name'] ?? '') ?></td>
                <td><?= number_format((float)($pay['total_payments'] ?? 0),2) ?></td>
                <td><?= htmlspecialchars($c['importer_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($c['port_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($c['number_plate_state'] ?? '') ?></td>
                <td><?= htmlspecialchars($c['number_plate_lga'] ?? '') ?></td>
                <td><?= $img ? '<a href="' . htmlspecialchars((defined('BASE_URL') ? rtrim(BASE_URL,'/') . '/' : '') . ltrim($img, '/')) . '">Image</a>' : '' ?></td>
                <td><?= $receipt ? '<a href="' . htmlspecialchars($receipt) . '">Receipt</a>' : '' ?></td>
                <td><?= nl2br(htmlspecialchars($r['summary'] ?? '')) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
