<?php
// App/views/vehicles/list.php
?>

<!-- Page Header -->
<div class="card glass-panel border-0 p-3 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div>
            <h4 class="text-white mb-1">
                <i class="fa-solid fa-car-side text-success me-2"></i>All Registered Vehicles
            </h4>
            <p class="text-secondary small mb-0">
                Complete national registry of all vehicles registered across Nigeria.
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="badge bg-success fs-6 py-2 px-3">
                <i class="fa-solid fa-database me-1"></i>Total: <?= number_format($totalVehicles) ?> Vehicles
            </span>
        </div>
    </div>
</div>

<!-- Summary Stats Row -->
<div class="row g-3 mb-4">
    <?php
        $activeCount = 0;
        $pendingCount = 0;
        $approvedCount = 0;
        $rejectedCount = 0;
        foreach ($vehicles as $v) {
            $status = $v['verification_status'] ?? 'PENDING';
            if ($status === 'APPROVED') $approvedCount++;
            elseif ($status === 'REJECTED') $rejectedCount++;
            else $pendingCount++;
        }
        $activeCount = $approvedCount;
    ?>
    <div class="col-6 col-lg-3">
        <div class="card glass-panel border-0 p-3 text-center">
            <div class="text-white fw-bold fs-4"><?= number_format($totalVehicles) ?></div>
            <div class="text-secondary small"><i class="fa-solid fa-car text-info me-1"></i>Total Vehicles</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card glass-panel border-0 p-3 text-center">
            <div class="text-success fw-bold fs-4"><?= number_format($approvedCount) ?></div>
            <div class="text-secondary small"><i class="fa-solid fa-circle-check text-success me-1"></i>Verified</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card glass-panel border-0 p-3 text-center">
            <div class="text-warning fw-bold fs-4"><?= number_format($pendingCount) ?></div>
            <div class="text-secondary small"><i class="fa-solid fa-clock text-warning me-1"></i>Pending Audit</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card glass-panel border-0 p-3 text-center">
            <div class="text-danger fw-bold fs-4"><?= number_format($rejectedCount) ?></div>
            <div class="text-secondary small"><i class="fa-solid fa-circle-xmark text-danger me-1"></i>Rejected</div>
        </div>
    </div>
</div>

<!-- Main DataTable Card -->
<div class="card glass-panel border-0 p-4">
    <!-- Toolbar -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h5 class="text-white mb-1"><i class="fa-solid fa-table-list text-info me-2"></i>Vehicle Registry</h5>
            <p class="text-secondary small mb-0">Search, filter and export the complete vehicle list below.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="badge bg-secondary fs-6 py-2 px-3" id="filteredVehiclesBadge">Filtered: <?= number_format($totalVehicles) ?></span>
            <button type="button" id="printVehicles" class="btn btn-sm btn-outline-light">
                <i class="fa-solid fa-print me-1"></i> Print
            </button>
            <button type="button" id="resetFilters" class="btn btn-sm btn-outline-light">
                <i class="fa-solid fa-filter-circle-xmark me-1"></i> Reset
            </button>
        </div>
    </div>

    <!-- Filters Row -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-xl-2">
            <label class="form-label text-secondary small mb-1">Verification Status</label>
            <select id="statusFilter" class="form-select form-select-sm bg-dark text-white border-secondary">
                <option value="">All statuses</option>
                <option value="APPROVED">✅ Approved</option>
                <option value="PENDING">⏳ Pending</option>
                <option value="REJECTED">❌ Rejected</option>
            </select>
        </div>
        <div class="col-12 col-md-6 col-xl-2">
            <label class="form-label text-secondary small mb-1">Category</label>
            <select id="categoryFilter" class="form-select form-select-sm bg-dark text-white border-secondary">
                <option value="">All categories</option>
                <?php
                    $cats = array_unique(array_filter(array_column($vehicles, 'category')));
                    sort($cats);
                    foreach ($cats as $cat):
                ?>
                    <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-6 col-xl-2">
            <label class="form-label text-secondary small mb-1">State</label>
            <select id="stateFilter" class="form-select form-select-sm bg-dark text-white border-secondary">
                <option value="">All states</option>
                <?php
                    $states = array_unique(array_filter(array_column($vehicles, 'reg_state')));
                    sort($states);
                    foreach ($states as $st):
                ?>
                    <option value="<?= htmlspecialchars($st) ?>"><?= htmlspecialchars($st) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <label class="form-label text-secondary small mb-1">Manufacturer</label>
            <select id="makeFilter" class="form-select form-select-sm bg-dark text-white border-secondary">
                <option value="">All manufacturers</option>
                <?php
                    $makes = array_unique(array_filter(array_column($vehicles, 'manufacturer')));
                    sort($makes);
                    foreach ($makes as $mk):
                ?>
                    <option value="<?= htmlspecialchars($mk) ?>"><?= htmlspecialchars($mk) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <label class="form-label text-secondary small mb-1">Quick Search</label>
            <input type="search" id="tableSearch" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Search VIN, plate, owner, engine...">
        </div>
    </div>

    <!-- DataTable -->
    <div class="table-responsive border-secondary border border-opacity-10 rounded-3 p-2 bg-dark bg-opacity-25">
        <table id="vehiclesListTable" class="table table-dark table-striped table-bordered align-middle mb-0" style="width:100%">
            <thead class="table-secondary text-dark align-middle">
                <tr>
                    <th style="width:40px">#</th>
                    <th>Plate Number</th>
                    <th>VIN</th>
                    <th>Manufacturer</th>
                    <th>Model</th>
                    <th>Year</th>
                    <th>Color</th>
                    <th>Category</th>
                    <th>Owner</th>
                    <th>State</th>
                    <th>Status</th>
                    <th>Registered</th>
                    <th style="width:70px" class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vehicles as $index => $vehicle): ?>
                    <?php
                        $verStatus = $vehicle['verification_status'] ?? 'PENDING';
                        $statusBadge = match($verStatus) {
                            'APPROVED' => '<span class="badge bg-success"><i class="fa-solid fa-circle-check me-1"></i>Approved</span>',
                            'REJECTED' => '<span class="badge bg-danger"><i class="fa-solid fa-circle-xmark me-1"></i>Rejected</span>',
                            default     => '<span class="badge bg-warning text-dark"><i class="fa-solid fa-clock me-1"></i>Pending</span>',
                        };
                        $regDate = !empty($vehicle['created_at']) ? date('d M Y', strtotime($vehicle['created_at'])) : '—';
                    ?>
                    <tr>
                        <td class="text-center text-secondary"><?= $index + 1 ?></td>
                        <td>
                            <span class="fw-semibold text-info"><?= htmlspecialchars($vehicle['plate_number']) ?></span>
                        </td>
                        <td><code class="text-white-50"><?= htmlspecialchars($vehicle['vin']) ?></code></td>
                        <td><?= htmlspecialchars($vehicle['manufacturer'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($vehicle['model'] ?? '—') ?></td>
                        <td class="text-center"><?= htmlspecialchars($vehicle['year'] ?? '—') ?></td>
                        <td>
                            <?php if (!empty($vehicle['color'])): ?>
                                <span class="d-inline-flex align-items-center gap-1">
                                    <span class="d-inline-block rounded-circle border border-secondary" style="width:12px;height:12px;background:<?= htmlspecialchars(strtolower($vehicle['color'])) ?>;"></span>
                                    <?= htmlspecialchars($vehicle['color']) ?>
                                </span>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($vehicle['category'] ?? '—') ?></td>
                        <td>
                            <?php if (!empty($vehicle['owner_name'])): ?>
                                <div class="fw-semibold"><?= htmlspecialchars($vehicle['owner_name']) ?></div>
                                <small class="text-secondary"><?= htmlspecialchars($vehicle['owner_phone'] ?? '') ?></small>
                            <?php else: ?>
                                <span class="text-secondary fst-italic">No owner</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($vehicle['reg_state'] ?: '—') ?></td>
                        <td class="text-center"><?= $statusBadge ?></td>
                        <td>
                            <small class="text-secondary"><?= $regDate ?></small>
                        </td>
                        <td class="text-center">
                            <a href="<?= BASE_URL ?>/vehicle/view/<?= $vehicle['id'] ?>" class="btn btn-sm btn-outline-info" title="View Details">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Initialize DataTable
    var table = $('#vehiclesListTable').DataTable({
        order: [[0, 'asc']],
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
        pageLength: 25,
        responsive: true,
        columnDefs: [
            { orderable: false, searchable: false, targets: -1 }
        ],
        dom: '<"d-flex flex-column flex-lg-row justify-content-between align-items-center gap-2 mb-3"l f>t<"d-flex justify-content-between align-items-center mt-3"ip>',
        language: {
            search: "",
            searchPlaceholder: "Type to search...",
            lengthMenu: "Show _MENU_ records",
            info: "Showing _START_ to _END_ of _TOTAL_ vehicles",
            infoEmpty: "No vehicles found",
            infoFiltered: "(filtered from _MAX_ total entries)",
            zeroRecords: '<div class="text-center py-4"><i class="fa-solid fa-car-burst fa-3x text-secondary mb-3"></i><br><span class="text-secondary">No matching vehicles found</span></div>'
        }
    });

    // Quick search input
    $('#tableSearch').on('keyup change', function () {
        table.search(this.value).draw();
    });

    // Custom column filters
    $.fn.dataTable.ext.search.push(function (settings, data) {
        if (settings.nTable.id !== 'vehiclesListTable') return true;

        var status = $('#statusFilter').val().toUpperCase();
        var category = $('#categoryFilter').val().toLowerCase();
        var state = $('#stateFilter').val().toLowerCase();
        var make = $('#makeFilter').val().toLowerCase();

        // Column indexes: 7=Category, 9=State, 10=Status, 3=Manufacturer
        var rowCategory = data[7] ? data[7].toString().toLowerCase().trim() : '';
        var rowState    = data[9] ? data[9].toString().toLowerCase().trim() : '';
        var rowStatus   = data[10] ? data[10].toString().toUpperCase().trim() : '';
        var rowMake     = data[3] ? data[3].toString().toLowerCase().trim() : '';

        if ((status === '' || rowStatus.indexOf(status) !== -1) &&
            (category === '' || rowCategory === category) &&
            (state === '' || rowState === state) &&
            (make === '' || rowMake === make)) {
            return true;
        }
        return false;
    });

    // Trigger filter on dropdown change
    $('#statusFilter, #categoryFilter, #stateFilter, #makeFilter').on('change', function () {
        table.draw();
    });

    // Update filtered count badge
    table.on('draw', function () {
        var filteredCount = table.rows({ search: 'applied' }).count();
        $('#filteredVehiclesBadge').text('Filtered: ' + filteredCount.toLocaleString());
    });

    // Reset all filters
    $('#resetFilters').on('click', function () {
        $('#statusFilter, #categoryFilter, #stateFilter, #makeFilter').val('');
        $('#tableSearch').val('');
        table.search('').columns().search('').draw();
    });

    // Print filtered results
    $('#printVehicles').on('click', function () {
        var data = table.rows({ search: 'applied', order: 'applied' }).data();
        if (!data || data.length === 0) {
            Swal.fire({
                icon: 'info',
                title: 'No records to print',
                text: 'There are no vehicles matching the current filter.',
                confirmButtonColor: '#10b981'
            });
            return;
        }

        var filterStatus = $('#statusFilter').val() || 'All Statuses';
        var filterCategory = $('#categoryFilter').val() || 'All Categories';
        var filterState = $('#stateFilter').val() || 'All States';
        var filterMake = $('#makeFilter').val() || 'All Manufacturers';
        var filterSearch = $('#tableSearch').val() || 'None';

        var columns = ['#', 'Plate Number', 'VIN', 'Manufacturer', 'Model', 'Year', 'Color', 'Category', 'Owner', 'State', 'Status', 'Registered'];
        var printRowsArr = [];
        var totalCols = columns.length;

        for (var i = 0; i < data.length; i++) {
            var rowData = data[i];
            var cells = [];
            for (var j = 0; j < totalCols; j++) {
                cells.push('<td>' + (rowData[j] !== undefined ? rowData[j] : '') + '</td>');
            }
            printRowsArr.push('<tr>' + cells.join('') + '</tr>');
        }

        var printWindow = window.open('', '_blank');
        var printHtml = '<!doctype html><html><head><meta charset="utf-8"><title>Nigeria Vehicle Registry - NVOTS</title>' +
            '<style>' +
            'body{font-family:"Helvetica Neue",Helvetica,Arial,sans-serif;color:#222;background:#fff;padding:40px;line-height:1.4;}' +
            '.header-container{text-align:center;margin-bottom:30px;border-bottom:3px double #1a1a2e;padding-bottom:15px;}' +
            '.coat-arms{font-size:0.75rem;letter-spacing:0.15em;text-transform:uppercase;color:#444;font-weight:600;margin-bottom:4px;}' +
            '.main-title{font-size:1.6rem;font-weight:800;color:#1a1a2e;margin:0 0 6px 0;letter-spacing:0.02em;}' +
            '.subtitle{font-size:1.05rem;font-weight:700;color:#10b981;margin:0;letter-spacing:0.05em;text-transform:uppercase;}' +
            '.meta-table{width:100%;margin-bottom:25px;border-collapse:collapse;border:none;}' +
            '.meta-table td{border:none;padding:5px 10px;font-size:0.82rem;color:#444;}' +
            '.meta-label{font-weight:bold;color:#1a1a2e;width:15%;}' +
            '.meta-val{width:35%;}' +
            'table.data-table{width:100%;border-collapse:collapse;margin-top:10px;page-break-inside:auto;}' +
            'table.data-table tr{page-break-inside:avoid;page-break-after:auto;}' +
            'table.data-table th, table.data-table td{border:1px solid #ddd;padding:8px 10px;text-align:left;font-size:0.8rem;}' +
            'table.data-table th{background:#1a1a2e;color:#fff;font-weight:700;text-transform:uppercase;font-size:0.75rem;letter-spacing:0.03em;}' +
            'table.data-table tbody tr:nth-child(even){background:#f9f9f9;}' +
            '.footer{margin-top:40px;text-align:center;font-size:0.72rem;color:#777;border-top:1px solid #ddd;padding-top:10px;page-break-inside:avoid;}' +
            '.badge { display: inline-block; padding: 0.25em 0.4em; font-size: 75%; font-weight: 700; line-height: 1; text-align: center; white-space: nowrap; vertical-align: baseline; border-radius: 0.25rem; text-decoration: none; }' +
            '.bg-success { background-color: #198754; color: #fff; }' +
            '.bg-danger { background-color: #dc3545; color: #fff; }' +
            '.bg-warning { background-color: #ffc107; color: #000; }' +
            '.text-dark { color: #212529 !important; }' +
            '.text-info { color: #0a58ca !important; font-weight: 600; text-decoration: none; }' +
            '.text-success { color: #198754 !important; }' +
            '.text-secondary { color: #6c757d !important; font-size: 0.75rem; display: block; }' +
            '.fw-semibold { font-weight: 600; }' +
            '.d-inline-flex, .d-inline-block { display: inline-block; vertical-align: middle; }' +
            '.align-items-center { vertical-align: middle; }' +
            '.gap-1 { margin-right: 0.25rem; }' +
            '.rounded-circle { border-radius: 50% !important; }' +
            '.border { border: 1px solid #dee2e6 !important; }' +
            '.border-secondary { border-color: #6c757d !important; }' +
            'code { font-family: monospace; color: #333; background: #f4f4f4; padding: 2px 4px; border-radius: 3px; }' +
            '@media print{' +
            '  body{padding:20px;}' +
            '  table.data-table th{background:#1a1a2e !important;color:#fff !important;-webkit-print-color-adjust:exact;print-color-adjust:exact;}' +
            '}' +
            '</style></head><body>' +
            '<div class="header-container">' +
            '  <div class="coat-arms">Federal Republic of Nigeria</div>' +
            '  <h1 class="main-title">National Vehicle Ownership & Traceability System</h1>' +
            '  <h2 class="subtitle">Vehicle Onboarding Registry Ledger</h2>' +
            '</div>' +
            '<table class="meta-table">' +
            '  <tr>' +
            '    <td class="meta-label">Date Generated:</td>' +
            '    <td class="meta-val">' + new Date().toLocaleString('en-NG', { dateStyle: 'full', timeStyle: 'short' }) + '</td>' +
            '    <td class="meta-label">State Filter:</td>' +
            '    <td class="meta-val">' + filterState + '</td>' +
            '  </tr>' +
            '  <tr>' +
            '    <td class="meta-label">Total Records:</td>' +
            '    <td class="meta-val"><strong>' + printRowsArr.length + ' vehicles matching</strong></td>' +
            '    <td class="meta-label">Category Filter:</td>' +
            '    <td class="meta-val">' + filterCategory + '</td>' +
            '  </tr>' +
            '  <tr>' +
            '    <td class="meta-label">Verification Status:</td>' +
            '    <td class="meta-val">' + filterStatus + '</td>' +
            '    <td class="meta-label">Make/Manufacturer:</td>' +
            '    <td class="meta-val">' + filterMake + '</td>' +
            '  </tr>' +
            '  <tr>' +
            '    <td class="meta-label">Quick Search:</td>' +
            '    <td class="meta-val">' + filterSearch + '</td>' +
            '    <td class="meta-label"></td>' +
            '    <td class="meta-val"></td>' +
            '  </tr>' +
            '</table>' +
            '<table class="data-table"><thead><tr>' + columns.map(function (l) { return '<th>' + l + '</th>'; }).join('') + '</tr></thead><tbody>' + printRowsArr.join('') + '</tbody></table>' +
            '<div class="footer">' +
            '  <strong>CONFIDENTIAL LEDGER</strong> — Generated from secure official administration node registry. Copyright &copy; ' + new Date().getFullYear() + ' NVOTS.' +
            '</div>' +
            '</body></html>';
        printWindow.document.write(printHtml);
        printWindow.document.close();
        printWindow.focus();
        setTimeout(function () { printWindow.print(); }, 300);
    });
});
</script>
