<?php
// App/views/reports/manage.php
?>
<div class="card glass-panel border-0 p-3 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-3">
        <div>
            <h4 class="text-white mb-1"><i class="fa-solid fa-file-invoice-dollar text-success me-2"></i>Registry Reporting & Exports</h4>
            <p class="text-secondary small mb-0">Export registries, owner records, revenue journals and audit logs with instant search, filters and print-ready reports.</p>
        </div>
        <div class="text-md-end text-start text-md-end">
            <span class="badge bg-success fs-6 py-2 px-3">Total vehicles: <?= number_format($totalVehicles) ?></span>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
            <div class="card report-summary-card bg-dark bg-opacity-35 border border-secondary border-opacity-15 h-100 shadow-sm">
                <div class="card-body p-3 d-flex align-items-start gap-3">
                    <div class="rounded-circle bg-success text-white p-3 d-flex align-items-center justify-content-center">
                        <i class="fa-solid fa-car-side fa-lg"></i>
                    </div>
                    <div>
                        <div class="text-white fw-semibold">Vehicle Onboardings</div>
                        <div class="text-secondary small">Full vehicle registry export.</div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 p-3 pt-0">
                    <a href="<?= BASE_URL ?>/?controller=vehicle&action=report" class="btn btn-sm btn-light w-100 text-dark mb-2">
                        <i class="fa-solid fa-table-list me-1"></i> Full Registry
                    </a>
                    <a href="<?= BASE_URL ?>/report/export/vehicles" class="btn btn-sm btn-success w-100">
                        <i class="fa-solid fa-file-csv me-1"></i> Export CSV
                    </a>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
            <div class="card report-summary-card bg-dark bg-opacity-35 border border-secondary border-opacity-15 h-100 shadow-sm">
                <div class="card-body p-3 d-flex align-items-start gap-3">
                    <div class="rounded-circle bg-info text-white p-3 d-flex align-items-center justify-content-center">
                        <i class="fa-solid fa-users-gear fa-lg"></i>
                    </div>
                    <div>
                        <div class="text-white fw-semibold">Owner Database</div>
                        <div class="text-secondary small">Verified owner export.</div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 p-3 pt-0">
                    <a href="<?= BASE_URL ?>/report/export/owners" class="btn btn-sm btn-success w-100">
                        <i class="fa-solid fa-file-csv me-1"></i> Export CSV
                    </a>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
            <div class="card report-summary-card bg-dark bg-opacity-35 border border-secondary border-opacity-15 h-100 shadow-sm">
                <div class="card-body p-3 d-flex align-items-start gap-3">
                    <div class="rounded-circle bg-warning text-dark p-3 d-flex align-items-center justify-content-center">
                        <i class="fa-solid fa-vault fa-lg"></i>
                    </div>
                    <div>
                        <div class="text-white fw-semibold">Revenue Ledger</div>
                        <div class="text-secondary small">Payment journal export.</div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 p-3 pt-0">
                    <a href="<?= BASE_URL ?>/report/export/revenue" class="btn btn-sm btn-success w-100">
                        <i class="fa-solid fa-file-csv me-1"></i> Export CSV
                    </a>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
            <div class="card report-summary-card bg-dark bg-opacity-35 border border-secondary border-opacity-15 h-100 shadow-sm">
                <div class="card-body p-3 d-flex align-items-start gap-3">
                    <div class="rounded-circle bg-danger text-white p-3 d-flex align-items-center justify-content-center">
                        <i class="fa-solid fa-shield-halved fa-lg"></i>
                    </div>
                    <div>
                        <div class="text-white fw-semibold">Verification Reports</div>
                        <div class="text-secondary small">Audit certificate export.</div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 p-3 pt-0">
                    <a href="<?= BASE_URL ?>/report/export/verifications" class="btn btn-sm btn-success w-100">
                        <i class="fa-solid fa-file-csv me-1"></i> Export CSV
                    </a>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
            <div class="card report-summary-card bg-dark bg-opacity-35 border border-secondary border-opacity-15 h-100 shadow-sm">
                <div class="card-body p-3 d-flex align-items-start gap-3">
                    <div class="rounded-circle bg-secondary text-white p-3 d-flex align-items-center justify-content-center">
                        <i class="fa-solid fa-clock-rotate-left fa-lg"></i>
                    </div>
                    <div>
                        <div class="text-white fw-semibold">Activity Tracker</div>
                        <div class="text-secondary small">System logs export.</div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 p-3 pt-0">
                    <a href="<?= BASE_URL ?>/report/export/activity" class="btn btn-sm btn-success w-100">
                        <i class="fa-solid fa-file-csv me-1"></i> Export CSV
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card glass-panel border-0 p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h5 class="text-white mb-1">Vehicle Registry</h5>
            <p class="text-secondary small mb-0">Search, filter and print the current vehicle list below.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="badge bg-secondary fs-6 py-2 px-3" id="filteredVehiclesBadge">Filtered: <?= number_format($totalVehicles) ?></span>
            <button type="button" id="printReport" class="btn btn-sm btn-outline-light">
                <i class="fa-solid fa-print me-1"></i> Print filtered
            </button>
            <button type="button" id="resetFilters" class="btn btn-sm btn-outline-light">
                <i class="fa-solid fa-filter-circle-xmark me-1"></i> Reset Filters
            </button>
        </div>
    </div>

    <div class="row g-3 mb-3 report-filter-row">
        <div class="col-12 col-md-6 col-xl-3">
            <label class="form-label text-secondary small mb-1">Filter by State</label>
            <select id="stateFilter" class="form-select form-select-sm bg-dark text-white border-secondary">
                <option value="">All states</option>
                <?php foreach ($states as $state): ?>
                    <option value="<?= htmlspecialchars($state['state']) ?>"><?= htmlspecialchars($state['state']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <label class="form-label text-secondary small mb-1">Filter by Category</label>
            <select id="categoryFilter" class="form-select form-select-sm bg-dark text-white border-secondary">
                <option value="">All categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= htmlspecialchars($category['category']) ?>"><?= htmlspecialchars($category['category']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <label class="form-label text-secondary small mb-1">Filter by Class</label>
            <select id="classFilter" class="form-select form-select-sm bg-dark text-white border-secondary">
                <option value="">All classes</option>
                <?php foreach ($classes as $class): ?>
                    <option value="<?= htmlspecialchars($class['class']) ?>"><?= htmlspecialchars($class['class']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <label class="form-label text-secondary small mb-1">Quick search</label>
            <input type="search" id="tableSearch" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Search VIN, plate, make, model...">
        </div>
    </div>

    <div class="table-responsive border-secondary border border-opacity-10 rounded-3 p-2 bg-dark bg-opacity-25">
        <table id="vehiclesTable" class="table table-dark table-striped table-bordered align-middle mb-0">
            <thead class="table-secondary text-dark align-middle">
                <tr>
                    <th>#</th>
                    <th>VIN</th>
                    <th>Plate Number</th>
                    <th>Make</th>
                    <th>Model</th>
                    <th>Year</th>
                    <th>Color</th>
                    <th>Category</th>
                    <th>Class</th>
                    <th>State</th>
                    <th>LGA</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vehicles as $vehicle): ?>
                    <tr>
                        <td><?= htmlspecialchars($vehicle['id']) ?></td>
                        <td><?= htmlspecialchars($vehicle['vin']) ?></td>
                        <td><?= htmlspecialchars($vehicle['plate_number']) ?></td>
                        <td><?= htmlspecialchars($vehicle['manufacturer']) ?></td>
                        <td><?= htmlspecialchars($vehicle['model']) ?></td>
                        <td><?= htmlspecialchars($vehicle['year']) ?></td>
                        <td><?= htmlspecialchars($vehicle['color']) ?></td>
                        <td><?= htmlspecialchars($vehicle['category']) ?></td>
                        <td><?= htmlspecialchars($vehicle['class']) ?></td>
                        <td><?= htmlspecialchars($vehicle['state']) ?></td>
                        <td><?= htmlspecialchars($vehicle['lga']) ?></td>
                        <td><?= htmlspecialchars($vehicle['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        var table = $('#vehiclesTable').DataTable({
            order: [[0, 'desc']],
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            pageLength: 25,
            responsive: true,
            dom: '<"d-flex flex-column flex-lg-row justify-content-between align-items-center gap-2 mb-3"l f>t<"d-flex justify-content-between align-items-center mt-3"ip>',
            language: {
                search: "",
                searchPlaceholder: "Type to search...",
                lengthMenu: "Show _MENU_ records"
            }
        });

        $('#tableSearch').on('keyup change', function () {
            table.search(this.value).draw();
        });

        $.fn.dataTable.ext.search.push(function (settings, data) {
            if (settings.nTable.id !== 'vehiclesTable') {
                return true;
            }
            var state = $('#stateFilter').val().toLowerCase();
            var category = $('#categoryFilter').val().toLowerCase();
            var cls = $('#classFilter').val().toLowerCase();
            var rowCategory = data[7] ? data[7].toString().toLowerCase() : '';
            var rowClass = data[8] ? data[8].toString().toLowerCase() : '';
            var rowState = data[9] ? data[9].toString().toLowerCase() : '';

            if ((state === '' || rowState === state) &&
                (category === '' || rowCategory === category) &&
                (cls === '' || rowClass === cls)) {
                return true;
            }
            return false;
        });

        $('#stateFilter, #categoryFilter, #classFilter').on('change', function () {
            table.draw();
        });

        table.on('draw', function () {
            var filteredCount = table.rows({ search: 'applied' }).count();
            $('#filteredVehiclesBadge').text('Filtered: ' + filteredCount);
        });

        $('#resetFilters').on('click', function () {
            $('#stateFilter, #categoryFilter, #classFilter').val('');
            $('#tableSearch').val('');
            table.search('').columns().search('').draw();
            $('#filteredVehiclesBadge').text('Filtered: ' + table.rows({ search: 'applied' }).count());
        });

        $('#printReport').on('click', function () {
            // Use DataTables API to get all rows that match current filters/search across all pages
            var data = table.rows({ search: 'applied', order: 'applied' }).data();
            if (!data || data.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'No records to print',
                    text: 'Please apply a filter or search query first.',
                    confirmButtonColor: '#10b981'
                });
                return;
            }

            // Get current filter text
            var filterState = $('#stateFilter').val() || 'All States';
            var filterCategory = $('#categoryFilter').val() || 'All Categories';
            var filterClass = $('#classFilter').val() || 'All Classes';
            var filterSearch = $('#tableSearch').val() || 'None';

            var columns = ['#', 'VIN', 'Plate Number', 'Make', 'Model', 'Year', 'Color', 'Category', 'Class', 'State', 'LGA', 'Created At'];

            var printRowsArr = [];
            for (var i = 0; i < data.length; i++) {
                var rowData = data[i];
                var cells = [];
                for (var j = 0; j < columns.length; j++) {
                    cells.push('<td>' + (rowData[j] !== undefined ? rowData[j] : '') + '</td>');
                }
                printRowsArr.push('<tr>' + cells.join('') + '</tr>');
            }

            var printRows = printRowsArr.join('');

            var printWindow = window.open('', '_blank');
            var printHtml = '<!doctype html><html><head><meta charset="utf-8"><title>Filtered Vehicle Registry - NVOTS</title>' +
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
                '@media print{' +
                '  body{padding:20px;}' +
                '  table.data-table th{background:#1a1a2e !important;color:#fff !important;-webkit-print-color-adjust:exact;print-color-adjust:exact;}' +
                '}' +
                '</style>' +
                '</head><body>' +
                '<div class="header-container">' +
                '  <div class="coat-arms">Federal Republic of Nigeria</div>' +
                '  <h1 class="main-title">National Vehicle Ownership & Traceability System</h1>' +
                '  <h2 class="subtitle">Official Registry Report Ledger</h2>' +
                '</div>' +
                '<table class="meta-table">' +
                '  <tr>' +
                '    <td class="meta-label">Date Generated:</td>' +
                '    <td class="meta-val">' + new Date().toLocaleString('en-NG', { dateStyle: 'full', timeStyle: 'short' }) + '</td>' +
                '    <td class="meta-label">Filter State:</td>' +
                '    <td class="meta-val">' + filterState + '</td>' +
                '  </tr>' +
                '  <tr>' +
                '    <td class="meta-label">Total Records:</td>' +
                '    <td class="meta-val"><strong>' + printRowsArr.length + ' vehicles matching</strong></td>' +
                '    <td class="meta-label">Filter Category:</td>' +
                '    <td class="meta-val">' + filterCategory + '</td>' +
                '  </tr>' +
                '  <tr>' +
                '    <td class="meta-label">Quick Search:</td>' +
                '    <td class="meta-val">' + filterSearch + '</td>' +
                '    <td class="meta-label">Filter Class:</td>' +
                '    <td class="meta-val">' + filterClass + '</td>' +
                '  </tr>' +
                '</table>' +
                '<table class="data-table"><thead><tr>' + columns.map(function (label) { return '<th>' + label + '</th>'; }).join('') + '</tr></thead><tbody>' + printRows + '</tbody></table>' +
                '<div class="footer">' +
                '  <strong>CONFIDENTIAL LEDGER</strong> — Generated from secure official administration node registry. Copyright &copy; ' + new Date().getFullYear() + ' NVOTS.' +
                '</div>' +
                '</body></html>';
            printWindow.document.write(printHtml);
            printWindow.document.close();
            printWindow.focus();
            // Give the browser a moment to render, then print
            setTimeout(function () { printWindow.print(); }, 250);
        });
    });
</script>
