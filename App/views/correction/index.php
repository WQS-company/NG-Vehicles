<?php
// App/views/correction/index.php
?>
<div class="row">
    <div class="col-12 mb-4">
        <h4 class="text-white"><i class="fa-solid fa-file-pen text-success me-2"></i>Data Correction Center</h4>
        <p class="text-secondary">Select a registered vehicle or owner profile below to make information updates or correct biometric details. Note: Corrections will log a charge in the system based on the defined Platform Settings.</p>
    </div>

    <!-- Vehicles List Card -->
    <div class="col-lg-6 mb-4">
        <div class="card glass-panel border-0 p-4 h-100">
            <h5 class="text-white mb-3"><i class="fa-solid fa-car text-success me-2"></i>Registered Vehicles</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle text-white m-0" id="vehiclesTable">
                    <thead>
                        <tr>
                            <th>Plate Number</th>
                            <th>VIN</th>
                            <th>Make & Model</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vehicles as $v): ?>
                            <tr>
                                <td><span class="badge bg-success font-monospace"><?= htmlspecialchars($v['plate_number']) ?></span></td>
                                <td class="small font-monospace"><?= htmlspecialchars($v['vin']) ?></td>
                                <td class="small"><?= htmlspecialchars($v['manufacturer'] . ' ' . $v['model']) ?></td>
                                <td class="text-end">
                                    <a href="<?= BASE_URL ?>/correction/vehicle/<?= $v['id'] ?>" class="btn btn-outline-light btn-sm">
                                        <i class="fa-solid fa-pen-to-square"></i> Correct
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Owners List Card -->
    <div class="col-lg-6 mb-4">
        <div class="card glass-panel border-0 p-4 h-100">
            <h5 class="text-white mb-3"><i class="fa-solid fa-user text-success me-2"></i>Owner Profiles</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle text-white m-0" id="ownersTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>NIN</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($owners as $o): ?>
                            <tr>
                                <td class="fw-bold small"><?= htmlspecialchars($o['full_name']) ?></td>
                                <td class="small"><?= htmlspecialchars($o['phone']) ?></td>
                                <td class="small font-monospace"><?= htmlspecialchars($o['nin']) ?></td>
                                <td class="text-end">
                                    <a href="<?= BASE_URL ?>/correction/owner/<?= $o['id'] ?>" class="btn btn-outline-light btn-sm">
                                        <i class="fa-solid fa-pen-to-square"></i> Correct
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    $('#vehiclesTable').DataTable({
        pageLength: 5,
        lengthMenu: [5, 10, 20]
    });
    $('#ownersTable').DataTable({
        pageLength: 5,
        lengthMenu: [5, 10, 20]
    });
});
</script>
