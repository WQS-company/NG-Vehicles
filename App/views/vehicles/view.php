<?php
// App/views/vehicles/view.php
$v = $vehicle;
$cf = $customFields;

// Calculate data completeness
$optionalFields = [
    'rfid_tag' => ['label' => 'RFID Tag', 'rec' => 'Needed for automated tolls, security checkpoints, and border control logs.'],
    'qr_code' => ['label' => 'QR Code', 'rec' => 'Enables quick scanning and mobile verification by transit officers.'],
    'color' => ['label' => 'Color', 'rec' => 'Critical visual attribute for police identification and tracking.'],
    'fuel_type' => ['label' => 'Fuel Type', 'rec' => 'Required for environmental regulations and engine auditing.'],
    'transmission' => ['label' => 'Transmission Type', 'rec' => 'Important mechanical attribute for registry specifications.'],
    'category' => ['label' => 'Vehicle Category', 'rec' => 'Necessary for classifying private vs utility transport.'],
    'class' => ['label' => 'Vehicle Class', 'rec' => 'Determines registration tax bracket and operational limits.'],
    'image_path' => ['label' => 'Vehicle Photo', 'rec' => 'Visual record of the vehicle shape, condition, and identity.'],
    'country_of_origin' => ['label' => 'Country of Origin', 'rec' => 'Required for tracking import tariffs and manufacturer integrity.'],
    'country_of_manufacture' => ['label' => 'Country of Manufacture', 'rec' => 'Helps identify parts availability and manufacturing standards.'],
    'importer_name' => ['label' => 'Importer Name', 'rec' => 'Establishes legal chain of custody from import to local ownership.'],
    'clearing_agent_name' => ['label' => 'Clearing Agent Name', 'rec' => 'Documents custom clearing process and agent accountability.'],
    'port_name' => ['label' => 'Port of Landing', 'rec' => 'Identifies entry point into Nigeria for border security tracing.'],
    'custom_papers_status' => ['label' => 'Custom Papers Status', 'rec' => 'Verifies customs duties payments and clearance certificates.'],
    'insurance_cover' => ['label' => 'Insurance Cover Policy', 'rec' => 'Confirms legal insurance protection on public roads.'],
    'number_plate_state' => ['label' => 'Plate Registration State', 'rec' => 'Identifies jurisdictional authority for plates issuance.'],
    'number_plate_lga' => ['label' => 'Plate Registration LGA', 'rec' => 'Fines down registration locality for local government levies.'],
    'tax_number' => ['label' => 'Tax Identification Number', 'rec' => 'Ensures taxation compliance and business registry audits.'],
    'vehicle_particulars_number' => ['label' => 'Particulars Certificate No.', 'rec' => 'Identifies official registration certificate serial.'],
    'pol_clearance_name' => ['label' => 'Police Clearance Officer', 'rec' => 'Verifies that vehicle has been checked against stolen vehicle databases.']
];

$completedCount = 0;
$totalCount = count($optionalFields);
$auditResults = [];

foreach ($optionalFields as $key => $meta) {
    // Check if field is in main vehicle or custom fields
    $val = '';
    if (isset($v[$key]) && $v[$key] !== '') {
        $val = $v[$key];
    } elseif (isset($cf[$key]) && $cf[$key] !== '') {
        $val = $cf[$key];
    }
    
    // Clean string representations
    $isComplete = !empty($val) && $val !== '—';
    if ($isComplete) {
        $completedCount++;
    }
    $auditResults[$key] = [
        'label' => $meta['label'],
        'rec' => $meta['rec'],
        'value' => $val,
        'complete' => $isComplete
    ];
}

$completenessScore = round(($completedCount / $totalCount) * 100);

// Status badge helper
$verStatus = $latestVerification['status'] ?? 'PENDING';
$statusBadgeClass = match($verStatus) {
    'APPROVED' => 'bg-success',
    'REJECTED' => 'bg-danger',
    default    => 'bg-warning text-dark',
};
$statusIcon = match($verStatus) {
    'APPROVED' => 'fa-circle-check',
    'REJECTED' => 'fa-circle-xmark',
    default    => 'fa-clock',
};
$statusLabel = match($verStatus) {
    'APPROVED' => 'Verified & Approved',
    'REJECTED' => 'Rejected',
    default    => 'Pending Audit',
};

$regDate = !empty($v['created_at']) ? date('d M Y, h:i A', strtotime($v['created_at'])) : '—';
?>

<style>
    .detail-label { color: #8b95a5; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 2px; font-weight: 600; }
    .detail-value { color: #f1f5f9; font-size: 0.95rem; font-weight: 500; word-break: break-word; }
    .detail-value.mono { font-family: 'Courier New', monospace; letter-spacing: 0.05em; }
    .section-title { font-size: 1.05rem; font-weight: 700; color: #f1f5f9; display: flex; align-items: center; gap: 0.5rem; }
    .section-title i { font-size: 0.95rem; }
    .owner-card { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 1.25rem; transition: border-color 0.3s ease; }
    .owner-card:hover { border-color: rgba(16,185,129,0.4); }
    .owner-avatar { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 2px solid rgba(16,185,129,0.5); }
    .owner-avatar-placeholder { width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #1e293b, #334155); display: flex; align-items: center; justify-content: center; color: #64748b; font-size: 1.4rem; border: 2px solid rgba(100,116,139,0.3); }
    .vehicle-hero-img { max-height: 280px; width: 100%; object-fit: cover; border-radius: 12px; border: 2px solid rgba(16,185,129,0.2); }
    .vehicle-img-placeholder { height: 200px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.03); border: 2px dashed rgba(255,255,255,0.1); border-radius: 12px; color: #64748b; }
    .timeline-item { position: relative; padding-left: 2rem; padding-bottom: 1.5rem; border-left: 2px solid rgba(255,255,255,0.08); }
    .timeline-item:last-child { border-left-color: transparent; padding-bottom: 0; }
    .timeline-dot { position: absolute; left: -7px; top: 4px; width: 12px; height: 12px; border-radius: 50%; border: 2px solid; }
    .info-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; }
    .info-grid-2 { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1rem; }
    .custom-field-group { background: rgba(255,255,255,0.03); border-radius: 8px; padding: 0.75rem 1rem; }
    .tab-pane { animation: fadeTabIn 0.3s ease; }
    @keyframes fadeTabIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
    .hover-border-active { transition: all 0.3s ease; }
    .hover-border-active:hover { border-color: rgba(167, 139, 250, 0.25) !important; background-color: rgba(255, 255, 255, 0.03) !important; }
    .bg-purple-badge { background-color: #a78bfa !important; }
    .bg-purple-badge.bg-opacity-10 { background-color: rgba(167, 139, 250, 0.1) !important; }
    .border-purple-badge { border-color: #a78bfa !important; }
    .border-purple-badge.border-opacity-25 { border-color: rgba(167, 139, 250, 0.25) !important; }
    .text-purple { color: #a78bfa !important; }

    /* Badge contrast fixes for dark dashboard theme */
    .badge.text-success {
        color: #4ade80 !important;
    }
    .badge.text-danger {
        color: #f87171 !important;
    }
    .badge.text-warning {
        color: #fbbf24 !important;
    }
    .badge.text-secondary {
        color: #cbd5e1 !important;
    }
    .badge.border-success {
        border-color: rgba(74, 222, 128, 0.3) !important;
    }
    .badge.border-secondary {
        border-color: rgba(203, 213, 225, 0.3) !important;
    }
    /* Document Upload styles for edit form */
    .upload-zone {
        transition: border-color 0.3s ease, background-color 0.3s ease;
    }
    .upload-zone:hover {
        border-color: rgba(16, 185, 129, 0.4) !important;
        background-color: rgba(16, 185, 129, 0.04) !important;
    }
    /* Document preview styling in dossier */
    .document-preview-box img {
        transition: transform 0.3s ease;
    }
    .document-preview-box img:hover {
        transform: scale(1.05);
    }
</style>

<!-- Breadcrumb & Back -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0" style="background: transparent;">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/vehicle/list" class="text-info text-decoration-none"><i class="fa-solid fa-car-side me-1"></i>Vehicles</a></li>
            <li class="breadcrumb-item active text-secondary" aria-current="page"><?= htmlspecialchars($v['plate_number']) ?></li>
        </ol>
    </nav>
    <a href="<?= BASE_URL ?>/vehicle/list" class="btn btn-sm btn-outline-light">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to List
    </a>
</div>

<?php if (isset($_SESSION['vehicle_update_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show bg-success bg-opacity-10 border border-success border-opacity-30 text-success mb-4" role="alert" style="color: #10b981 !important;">
        <i class="fa-solid fa-circle-check me-2"></i><?= htmlspecialchars($_SESSION['vehicle_update_success']) ?>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['vehicle_update_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['vehicle_update_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show bg-danger bg-opacity-10 border border-danger border-opacity-30 text-danger mb-4" role="alert" style="color: #f87171 !important;">
        <i class="fa-solid fa-circle-xmark me-2"></i><?= htmlspecialchars($_SESSION['vehicle_update_error']) ?>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['vehicle_update_error']); ?>
<?php endif; ?>

<!-- Hero Card: Vehicle Identity -->
<div class="card glass-panel border-0 p-4 mb-4">
    <div class="row g-4">
        <!-- Vehicle Image -->
        <div class="col-12 col-lg-4">
            <?php if (!empty($vehicleImageUrl)): ?>
                <img src="<?= htmlspecialchars($vehicleImageUrl) ?>" alt="Vehicle Image" class="vehicle-hero-img shadow-lg" id="vehicleHeroImg" style="cursor:pointer;">
            <?php else: ?>
                <div class="vehicle-img-placeholder">
                    <div class="text-center">
                        <i class="fa-solid fa-image fa-3x mb-2"></i>
                        <div class="small">No image uploaded</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Vehicle Primary Info -->
        <div class="col-12 col-lg-8">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3">
                <div>
                    <h3 class="text-white fw-bold mb-1">
                        <?= htmlspecialchars($v['manufacturer'] ?? '') ?> <?= htmlspecialchars($v['model'] ?? '') ?>
                        <span class="text-secondary fs-5">(<?= htmlspecialchars($v['year'] ?? '—') ?>)</span>
                    </h3>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="badge bg-info text-dark fs-6 px-3 py-2">
                            <i class="fa-solid fa-hashtag me-1"></i><?= htmlspecialchars($v['plate_number']) ?>
                        </span>
                        <span class="badge <?= $statusBadgeClass ?> fs-6 px-3 py-2">
                            <i class="fa-solid <?= $statusIcon ?> me-1"></i><?= $statusLabel ?>
                        </span>
                    </div>
                </div>
                <div class="text-end">
                    <div class="detail-label">Registered</div>
                    <div class="detail-value"><i class="fa-regular fa-calendar me-1 text-info"></i><?= $regDate ?></div>
                </div>
            </div>

            <hr class="border-secondary border-opacity-20 my-3">

            <!-- Quick Identifiers Grid -->
            <div class="info-grid">
                <div>
                    <div class="detail-label">VIN</div>
                    <div class="detail-value mono"><?= htmlspecialchars($v['vin']) ?></div>
                </div>
                <div>
                    <div class="detail-label">Engine Number</div>
                    <div class="detail-value mono"><?= htmlspecialchars($v['engine_number']) ?></div>
                </div>
                <div>
                    <div class="detail-label">Chassis Number</div>
                    <div class="detail-value mono"><?= htmlspecialchars($v['chassis_number']) ?></div>
                </div>
                <div>
                    <div class="detail-label">RFID Tag</div>
                    <div class="detail-value mono"><?= htmlspecialchars($v['rfid_tag'] ?? '—') ?></div>
                </div>
                <div>
                    <div class="detail-label">QR Code</div>
                    <div class="detail-value mono"><?= htmlspecialchars($v['qr_code'] ?? '—') ?></div>
                </div>
                <div>
                    <div class="detail-label">Color</div>
                    <div class="detail-value d-flex align-items-center gap-2">
                        <?php if (!empty($v['color'])): ?>
                            <span class="d-inline-block rounded-circle border border-secondary" style="width:14px;height:14px;background:<?= htmlspecialchars(strtolower($v['color'])) ?>;"></span>
                            <?= htmlspecialchars($v['color']) ?>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </div>
                </div>
                <div>
                    <div class="detail-label">Fuel Type</div>
                    <div class="detail-value"><?= htmlspecialchars($v['fuel_type'] ?: '—') ?></div>
                </div>
                <div>
                    <div class="detail-label">Transmission</div>
                    <div class="detail-value"><?= htmlspecialchars($v['transmission'] ?: '—') ?></div>
                </div>
                <div>
                    <div class="detail-label">Category</div>
                    <div class="detail-value"><?= htmlspecialchars($v['category'] ?: '—') ?></div>
                </div>
                <div>
                    <div class="detail-label">Class</div>
                    <div class="detail-value"><?= htmlspecialchars($v['class'] ?: '—') ?></div>
                </div>
                <div>
                    <div class="detail-label">Vehicle Status</div>
                    <div class="detail-value"><?= htmlspecialchars($v['vehicle_status'] ?? 'PENDING') ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats Bar -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card glass-panel border-0 p-3 text-center">
            <div class="text-info fw-bold fs-5"><?= count($ownershipHistory) ?></div>
            <div class="text-secondary small">Ownership Records</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card glass-panel border-0 p-3 text-center">
            <div class="text-success fw-bold fs-5">₦<?= number_format((float)$totalPayments, 2) ?></div>
            <div class="text-secondary small">Total Payments</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card glass-panel border-0 p-3 text-center">
            <div class="text-warning fw-bold fs-5"><?= count($transfers) ?></div>
            <div class="text-secondary small">Ownership Transfers</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card glass-panel border-0 p-3 text-center">
            <div class="text-purple fw-bold fs-5" style="color:#a78bfa;"><?= count($verifications) ?></div>
            <div class="text-secondary small">Verification Audits</div>
        </div>
    </div>
</div>

<!-- Tabbed Sections -->
<div class="card glass-panel border-0 p-4">
    <ul class="nav nav-pills mb-4 gap-2 flex-wrap" id="vehicleDetailTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active bg-dark text-white border border-secondary border-opacity-30" id="tab-owner" data-bs-toggle="pill" data-bs-target="#pane-owner" type="button" role="tab">
                <i class="fa-solid fa-user-shield me-1"></i> Ownership
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link bg-dark text-white border border-secondary border-opacity-30" id="tab-registration" data-bs-toggle="pill" data-bs-target="#pane-registration" type="button" role="tab">
                <i class="fa-solid fa-file-lines me-1"></i> Registration Data
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link bg-dark text-white border border-secondary border-opacity-30" id="tab-payments" data-bs-toggle="pill" data-bs-target="#pane-payments" type="button" role="tab">
                <i class="fa-solid fa-receipt me-1"></i> Payments
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link bg-dark text-white border border-secondary border-opacity-30" id="tab-verification" data-bs-toggle="pill" data-bs-target="#pane-verification" type="button" role="tab">
                <i class="fa-solid fa-shield-halved me-1"></i> Verification
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link bg-dark text-white border border-secondary border-opacity-30" id="tab-transfers" data-bs-toggle="pill" data-bs-target="#pane-transfers" type="button" role="tab">
                <i class="fa-solid fa-right-left me-1"></i> Transfers
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link bg-dark text-white border border-secondary border-opacity-30" id="tab-completeness" data-bs-toggle="pill" data-bs-target="#pane-completeness" type="button" role="tab">
                <i class="fa-solid fa-gauge-high text-success me-1"></i> Completeness Audit
            </button>
        </li>
        <?php if ($verStatus === 'APPROVED'): ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link bg-dark text-white border border-success border-opacity-40 text-success fw-semibold" id="tab-dossier" data-bs-toggle="pill" data-bs-target="#pane-dossier" type="button" role="tab" style="box-shadow: 0 0 10px rgba(16,185,129,0.15);">
                <i class="fa-solid fa-file-shield text-success me-1"></i> Verified Dossier
            </button>
        </li>
        <?php endif; ?>
    </ul>

    <div class="tab-content" id="vehicleDetailContent">

        <!-- ═══ TAB: Ownership ═══ -->
        <div class="tab-pane fade show active" id="pane-owner" role="tabpanel">
            <!-- Current Owner -->
            <?php if ($currentOwner): ?>
                <div class="section-title mb-3">
                    <i class="fa-solid fa-user-check text-success"></i> Current Owner
                </div>
                <div class="owner-card mb-4">
                    <div class="d-flex flex-wrap gap-3 align-items-start">
                        <!-- Avatar -->
                        <div class="flex-shrink-0">
                            <?php if (!empty($currentOwner['passport_photo_path'])): ?>
                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($currentOwner['passport_photo_path']) ?>" alt="Owner Photo" class="owner-avatar">
                            <?php else: ?>
                                <div class="owner-avatar-placeholder"><i class="fa-solid fa-user"></i></div>
                            <?php endif; ?>
                        </div>
                        <!-- Owner Info -->
                        <div class="flex-grow-1">
                            <h5 class="text-white fw-bold mb-1"><?= htmlspecialchars($currentOwner['full_name'] ?? '—') ?></h5>
                            <div class="info-grid-2 mt-2">
                                <div>
                                    <div class="detail-label">Phone</div>
                                    <div class="detail-value"><?= htmlspecialchars($currentOwner['phone'] ?? '—') ?></div>
                                </div>
                                <div>
                                    <div class="detail-label">Email</div>
                                    <div class="detail-value"><?= htmlspecialchars($currentOwner['owner_email'] ?? '—') ?></div>
                                </div>
                                <div>
                                    <div class="detail-label">NIN</div>
                                    <div class="detail-value mono"><?= htmlspecialchars($currentOwner['nin'] ?? '—') ?></div>
                                </div>
                                <div>
                                    <div class="detail-label">BVN</div>
                                    <div class="detail-value mono"><?= htmlspecialchars($currentOwner['bvn'] ?? '—') ?></div>
                                </div>
                                <div>
                                    <div class="detail-label">Gender</div>
                                    <div class="detail-value"><?= htmlspecialchars($currentOwner['gender'] ?? '—') ?></div>
                                </div>
                                <div>
                                    <div class="detail-label">Date of Birth</div>
                                    <div class="detail-value"><?= !empty($currentOwner['date_of_birth']) ? date('d M Y', strtotime($currentOwner['date_of_birth'])) : '—' ?></div>
                                </div>
                                <div>
                                    <div class="detail-label">Nationality</div>
                                    <div class="detail-value"><?= htmlspecialchars($currentOwner['nationality'] ?? '—') ?></div>
                                </div>
                                <div>
                                    <div class="detail-label">Occupation</div>
                                    <div class="detail-value"><?= htmlspecialchars($currentOwner['occupation'] ?? '—') ?></div>
                                </div>
                                <div>
                                    <div class="detail-label">State / LGA</div>
                                    <div class="detail-value"><?= htmlspecialchars(($currentOwner['owner_state'] ?? '') . ($currentOwner['owner_lga'] ? ', ' . $currentOwner['owner_lga'] : '')) ?: '—' ?></div>
                                </div>
                                <div>
                                    <div class="detail-label">Address</div>
                                    <div class="detail-value"><?= htmlspecialchars($currentOwner['owner_address'] ?? '—') ?></div>
                                </div>
                                <div>
                                    <div class="detail-label">Purchase Date</div>
                                    <div class="detail-value"><?= !empty($currentOwner['purchase_date']) ? date('d M Y', strtotime($currentOwner['purchase_date'])) : '—' ?></div>
                                </div>
                                <div>
                                    <div class="detail-label">Purchase Amount</div>
                                    <div class="detail-value">₦<?= number_format((float)($currentOwner['purchase_amount'] ?? 0), 2) ?></div>
                                </div>
                            </div>
                            <?php if (!empty($currentOwner['signature_path'])): ?>
                                <div class="mt-3">
                                    <div class="detail-label mb-1">Signature</div>
                                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($currentOwner['signature_path']) ?>" alt="Signature" class="rounded border border-secondary border-opacity-30" style="max-height:60px; background:#fff; padding:4px;">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-4 text-secondary">
                    <i class="fa-solid fa-user-slash fa-2x mb-2"></i>
                    <p>No owner record found for this vehicle.</p>
                </div>
            <?php endif; ?>

            <!-- Previous Owners -->
            <?php if (!empty($previousOwners)): ?>
                <div class="section-title mb-3 mt-2">
                    <i class="fa-solid fa-users text-warning"></i> Previous Owners
                    <span class="badge bg-secondary ms-1"><?= count($previousOwners) ?></span>
                </div>
                <div class="ps-2">
                    <?php foreach ($previousOwners as $idx => $prev): ?>
                        <div class="timeline-item">
                            <div class="timeline-dot bg-dark" style="border-color: #f59e0b;"></div>
                            <div class="owner-card mb-0">
                                <div class="d-flex flex-wrap gap-3 align-items-start">
                                    <div class="flex-shrink-0">
                                        <?php if (!empty($prev['passport_photo_path'])): ?>
                                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($prev['passport_photo_path']) ?>" alt="Owner Photo" class="owner-avatar" style="width:45px;height:45px;">
                                        <?php else: ?>
                                            <div class="owner-avatar-placeholder" style="width:45px;height:45px;font-size:1rem;"><i class="fa-solid fa-user"></i></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="text-white fw-semibold mb-1"><?= htmlspecialchars($prev['full_name'] ?? '—') ?></h6>
                                        <div class="d-flex flex-wrap gap-3">
                                            <div>
                                                <span class="detail-label">Phone: </span>
                                                <span class="text-white-50"><?= htmlspecialchars($prev['phone'] ?? '—') ?></span>
                                            </div>
                                            <div>
                                                <span class="detail-label">Purchased: </span>
                                                <span class="text-white-50"><?= !empty($prev['purchase_date']) ? date('d M Y', strtotime($prev['purchase_date'])) : '—' ?></span>
                                            </div>
                                            <div>
                                                <span class="detail-label">Amount: </span>
                                                <span class="text-white-50">₦<?= number_format((float)($prev['purchase_amount'] ?? 0), 2) ?></span>
                                            </div>
                                            <div>
                                                <span class="detail-label">State: </span>
                                                <span class="text-white-50"><?= htmlspecialchars(($prev['owner_state'] ?? '') ?: '—') ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ═══ TAB: Registration Data ═══ -->
        <div class="tab-pane fade" id="pane-registration" role="tabpanel">
            <?php
                // Helper to get custom field values safely
                $getVal = function($key) use ($cf) {
                    $val = $cf[$key] ?? '';
                    if (is_array($val)) {
                        $val = implode(', ', $val);
                    }
                    $val = trim((string)$val);
                    return $val !== '' ? $val : '—';
                };

                // Helper to format currency values
                $formatAmt = function($key) use ($cf) {
                    $val = trim((string)($cf[$key] ?? ''));
                    return $val !== '' ? '₦' . number_format((float)$val, 2) : '—';
                };

                // Helper to format date values
                $formatDate = function($key) use ($cf) {
                    $val = trim((string)($cf[$key] ?? ''));
                    return $val !== '' ? date('d M Y', strtotime($val)) : '—';
                };

                // Officer types definition
                $officerTypes = [
                    'Custom Officers' => 'custom_officer',
                    'Police Officers' => 'police_officer',
                    'DSS Officers' => 'dss_officer',
                    'NIA Officers' => 'nia_officer',
                ];
            ?>

            <!-- SECTION 1: Local Registration & Acquisition -->
            <div class="mb-4">
                <div class="section-title mb-3 border-bottom border-secondary border-opacity-15 pb-2">
                    <i class="fa-solid fa-location-dot text-success"></i> Location & Ownership Acquisition
                </div>
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="custom-field-group">
                            <div class="detail-label">Plate Registration State</div>
                            <div class="detail-value"><?= htmlspecialchars($getVal('number_plate_state')) ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="custom-field-group">
                            <div class="detail-label">Plate Registration LGA</div>
                            <div class="detail-value"><?= htmlspecialchars($getVal('number_plate_lga')) ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="custom-field-group">
                            <div class="detail-label">Purchase Date</div>
                            <div class="detail-value"><?= htmlspecialchars($formatDate('purchase_date')) ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="custom-field-group">
                            <div class="detail-label">Purchase Amount</div>
                            <div class="detail-value"><?= htmlspecialchars($formatAmt('purchase_amount')) ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="custom-field-group">
                            <div class="detail-label">Means of Identification (Owner)</div>
                            <div class="detail-value"><?= htmlspecialchars($getVal('means_of_identification')) ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="custom-field-group">
                            <div class="detail-label">Insurance Cover Policy</div>
                            <div class="detail-value"><?= htmlspecialchars($getVal('insurance_cover')) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: Importation & Custom Declarations -->
            <div class="mb-4">
                <div class="section-title mb-3 border-bottom border-secondary border-opacity-15 pb-2">
                    <i class="fa-solid fa-ship text-info"></i> Importation & Customs Clearance
                </div>
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="custom-field-group">
                            <div class="detail-label">Country of Origin</div>
                            <div class="detail-value"><?= htmlspecialchars($getVal('country_of_origin')) ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="custom-field-group">
                            <div class="detail-label">Country of Manufacture</div>
                            <div class="detail-value"><?= htmlspecialchars($getVal('country_of_manufacture')) ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="custom-field-group">
                            <div class="detail-label">Year of Importation</div>
                            <div class="detail-value"><?= htmlspecialchars($getVal('ship_year')) ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="custom-field-group">
                            <div class="detail-label">Custom Papers Status</div>
                            <div class="detail-value">
                                <?php $papers = $getVal('custom_papers_status'); ?>
                                <span class="badge <?= $papers === 'Complete' ? 'bg-success bg-opacity-20 text-success border border-success border-opacity-30' : 'bg-secondary bg-opacity-20 text-secondary border border-secondary border-opacity-30' ?>">
                                    <?= htmlspecialchars($papers) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: Shipping & Port details -->
            <div class="mb-4">
                <div class="section-title mb-3 border-bottom border-secondary border-opacity-15 pb-2">
                    <i class="fa-solid fa-anchor text-warning"></i> Shipping Log & Landing Port Details
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="custom-field-group">
                            <div class="detail-label">Departure Port</div>
                            <div class="detail-value"><?= htmlspecialchars($getVal('ship_departure_port')) ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="custom-field-group">
                            <div class="detail-label">Departure Date</div>
                            <div class="detail-value"><?= htmlspecialchars($formatDate('ship_departure_date')) ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="custom-field-group">
                            <div class="detail-label">Landing Date</div>
                            <div class="detail-value"><?= htmlspecialchars($formatDate('ship_landing_date')) ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="custom-field-group">
                            <div class="detail-label">Port Operator Name</div>
                            <div class="detail-value"><?= htmlspecialchars($getVal('port_name')) ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="custom-field-group">
                            <div class="detail-label">Port Company</div>
                            <div class="detail-value"><?= htmlspecialchars($getVal('port_company')) ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="custom-field-group">
                            <div class="detail-label">Port Contact Phone</div>
                            <div class="detail-value"><?= htmlspecialchars($getVal('port_tel')) ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="custom-field-group">
                            <div class="detail-label">Port Contact Email</div>
                            <div class="detail-value"><?= htmlspecialchars($getVal('port_email')) ?></div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="custom-field-group">
                            <div class="detail-label">Port Office Address</div>
                            <div class="detail-value"><?= htmlspecialchars($getVal('port_address')) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 4: Importer & Clearing Agent Profiles -->
            <div class="row g-4 mb-4">
                <!-- Importer Card -->
                <div class="col-md-6">
                    <div class="owner-card h-100">
                        <h6 class="text-white fw-bold mb-3"><i class="fa-solid fa-plane-arrival text-info me-2"></i>Importer Profile</h6>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="detail-label">Importer Name</div>
                                <div class="detail-value text-white"><?= htmlspecialchars($getVal('importer_name')) ?></div>
                            </div>
                            <div class="col-6">
                                <div class="detail-label">Importer Company</div>
                                <div class="detail-value"><?= htmlspecialchars($getVal('importer_company')) ?></div>
                            </div>
                            <div class="col-6">
                                <div class="detail-label">Phone Number</div>
                                <div class="detail-value"><?= htmlspecialchars($getVal('importer_tel')) ?></div>
                            </div>
                            <div class="col-6">
                                <div class="detail-label">Email Address</div>
                                <div class="detail-value text-wrap small"><?= htmlspecialchars($getVal('importer_email')) ?></div>
                            </div>
                            <div class="col-12">
                                <div class="detail-label">Office Address</div>
                                <div class="detail-value"><?= htmlspecialchars($getVal('importer_address')) ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Clearing Agent Card -->
                <div class="col-md-6">
                    <div class="owner-card h-100">
                        <h6 class="text-white fw-bold mb-3"><i class="fa-solid fa-file-signature text-success me-2"></i>Clearing Agent Info</h6>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="detail-label">Agent Name</div>
                                <div class="detail-value text-white"><?= htmlspecialchars($getVal('clearing_agent_name')) ?></div>
                            </div>
                            <div class="col-6">
                                <div class="detail-label">Agent Company</div>
                                <div class="detail-value"><?= htmlspecialchars($getVal('clearing_agent_company')) ?></div>
                            </div>
                            <div class="col-6">
                                <div class="detail-label">Phone Number</div>
                                <div class="detail-value"><?= htmlspecialchars($getVal('clearing_agent_tel')) ?></div>
                            </div>
                            <div class="col-6">
                                <div class="detail-label">Email Address</div>
                                <div class="detail-value text-wrap small"><?= htmlspecialchars($getVal('clearing_agent_email')) ?></div>
                            </div>
                            <div class="col-12">
                                <div class="detail-label">Office Address</div>
                                <div class="detail-value"><?= htmlspecialchars($getVal('clearing_agent_address')) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 5: Foreign Office & Local Agent Details -->
            <div class="row g-4 mb-4">
                <!-- Foreign Office Card -->
                <div class="col-md-6">
                    <div class="owner-card h-100">
                        <h6 class="text-white fw-bold mb-3"><i class="fa-solid fa-globe text-warning me-2"></i>Foreign Export Office</h6>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="detail-label">Representative Name</div>
                                <div class="detail-value text-white"><?= htmlspecialchars($getVal('foreign_office_name')) ?></div>
                            </div>
                            <div class="col-6">
                                <div class="detail-label">Office Company</div>
                                <div class="detail-value"><?= htmlspecialchars($getVal('foreign_office_company')) ?></div>
                            </div>
                            <div class="col-6">
                                <div class="detail-label">Phone Number</div>
                                <div class="detail-value"><?= htmlspecialchars($getVal('foreign_office_tel')) ?></div>
                            </div>
                            <div class="col-6">
                                <div class="detail-label">Email Address</div>
                                <div class="detail-value text-wrap small"><?= htmlspecialchars($getVal('foreign_office_email')) ?></div>
                            </div>
                            <div class="col-12">
                                <div class="detail-label">Office Address</div>
                                <div class="detail-value"><?= htmlspecialchars($getVal('foreign_office_address')) ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Local Agent Card -->
                <div class="col-md-6">
                    <div class="owner-card h-100">
                        <h6 class="text-white fw-bold mb-3"><i class="fa-solid fa-handshake text-info me-2"></i>Local Onboarding Agent</h6>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="detail-label">Agent Name</div>
                                <div class="detail-value text-white"><?= htmlspecialchars($getVal('agent_name')) ?></div>
                            </div>
                            <div class="col-6">
                                <div class="detail-label">Phone Number</div>
                                <div class="detail-value"><?= htmlspecialchars($getVal('agent_tel')) ?></div>
                            </div>
                            <div class="col-12">
                                <div class="detail-label">Email Address</div>
                                <div class="detail-value"><?= htmlspecialchars($getVal('agent_email')) ?></div>
                            </div>
                            <div class="col-12">
                                <div class="detail-label">Office Address</div>
                                <div class="detail-value"><?= htmlspecialchars($getVal('agent_address')) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 6: Tax & Particulars Registration -->
            <div class="mb-4">
                <div class="section-title mb-3 border-bottom border-secondary border-opacity-15 pb-2">
                    <i class="fa-solid fa-calculator text-success"></i> Tax & Vehicle Particulars Ledgers
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="custom-field-group">
                            <div class="detail-label">Tax Identification Number (TIN)</div>
                            <div class="detail-value font-monospace text-info"><?= htmlspecialchars($getVal('tax_number')) ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="custom-field-group">
                            <div class="detail-label">Vehicle Particulars License Number</div>
                            <div class="detail-value font-monospace"><?= htmlspecialchars($getVal('vehicle_particulars_number')) ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="custom-field-group">
                            <div class="detail-label">Particulars Purchase Amount</div>
                            <div class="detail-value"><?= htmlspecialchars($formatAmt('vehicle_particulars_amount')) ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="custom-field-group">
                            <div class="detail-label">Particulars Purchase Date</div>
                            <div class="detail-value"><?= htmlspecialchars($formatDate('vehicle_particulars_purchase_date')) ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="custom-field-group">
                            <div class="detail-label">Particulars Expiry Date</div>
                            <div class="detail-value">
                                <?php 
                                    $expiryVal = $getVal('vehicle_particulars_expiry_date'); 
                                    $expiryText = $formatDate('vehicle_particulars_expiry_date');
                                    if ($expiryVal !== '—') {
                                        $expired = strtotime($expiryVal) < time();
                                        if ($expired) {
                                            echo '<span class="text-danger fw-semibold"><i class="fa-solid fa-triangle-exclamation me-1"></i>' . htmlspecialchars($expiryText) . ' (Expired)</span>';
                                        } else {
                                            echo '<span class="text-success fw-semibold"><i class="fa-solid fa-circle-check me-1"></i>' . htmlspecialchars($expiryText) . ' (Active)</span>';
                                        }
                                    } else {
                                        echo '—';
                                    }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 7: Security Clearances (Police, Drivers License) -->
            <div class="row g-4 mb-4">
                <!-- Police Clearance Card -->
                <div class="col-md-6">
                    <div class="owner-card h-100">
                        <h6 class="text-white fw-bold mb-3"><i class="fa-solid fa-shield text-danger me-2"></i>Police Clearance Certificate</h6>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="detail-label">Signee Officer Name</div>
                                <div class="detail-value text-white"><?= htmlspecialchars($getVal('pol_clearance_name')) ?></div>
                            </div>
                            <div class="col-6">
                                <div class="detail-label">Officer Rank</div>
                                <div class="detail-value"><?= htmlspecialchars($getVal('pol_clearance_rank')) ?></div>
                            </div>
                            <div class="col-6">
                                <div class="detail-label">Clearance State</div>
                                <div class="detail-value"><?= htmlspecialchars($getVal('pol_clearance_state')) ?></div>
                            </div>
                            <div class="col-6">
                                <div class="detail-label">Clearance LGA</div>
                                <div class="detail-value"><?= htmlspecialchars($getVal('pol_clearance_local_govt')) ?></div>
                            </div>
                            <div class="col-6">
                                <div class="detail-label">Phone Number</div>
                                <div class="detail-value"><?= htmlspecialchars($getVal('pol_clearance_tel')) ?></div>
                            </div>
                            <div class="col-6">
                                <div class="detail-label">Email Address</div>
                                <div class="detail-value text-wrap small"><?= htmlspecialchars($getVal('pol_clearance_email')) ?></div>
                            </div>
                            <div class="col-12">
                                <div class="detail-label">Signee Office Address</div>
                                <div class="detail-value"><?= htmlspecialchars($getVal('pol_clearance_office_address')) ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Driver's License Officer Card -->
                <div class="col-md-6">
                    <div class="owner-card h-100">
                        <h6 class="text-white fw-bold mb-3"><i class="fa-solid fa-id-card text-success me-2"></i>Driver's License Signee</h6>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="detail-label">Signee Officer Name</div>
                                <div class="detail-value text-white"><?= htmlspecialchars($getVal('dl_name')) ?></div>
                            </div>
                            <div class="col-6">
                                <div class="detail-label">Officer Rank</div>
                                <div class="detail-value"><?= htmlspecialchars($getVal('dl_rank')) ?></div>
                            </div>
                            <div class="col-6">
                                <div class="detail-label">Phone Number</div>
                                <div class="detail-value"><?= htmlspecialchars($getVal('dl_tel')) ?></div>
                            </div>
                            <div class="col-6">
                                <div class="detail-label">Email Address</div>
                                <div class="detail-value text-wrap small"><?= htmlspecialchars($getVal('dl_email')) ?></div>
                            </div>
                            <div class="col-12">
                                <div class="detail-label">Officer Station/Address</div>
                                <div class="detail-value"><?= htmlspecialchars($getVal('dl_address')) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 8: Security Service Audits (Officers detail logs) -->
            <div class="mb-4">
                <div class="section-title mb-3 border-bottom border-secondary border-opacity-15 pb-2">
                    <i class="fa-solid fa-user-shield text-purple" style="color:#a78bfa;"></i> National Security Officers Audits
                </div>
                
                <!-- Tabbed list for Officer Types -->
                <div class="row g-3">
                    <?php foreach ($officerTypes as $officerTitle => $prefix): ?>
                        <div class="col-12 col-xl-6">
                            <div class="owner-card h-100 bg-dark bg-opacity-40">
                                <h6 class="text-white fw-bold mb-3 text-uppercase small tracking-wider"><i class="fa-solid fa-shield-halved text-purple me-2"></i><?= $officerTitle ?></h6>
                                <div class="d-flex flex-column gap-3">
                                    <?php for ($i = 1; $i <= 3; $i++): 
                                        $name = $getVal("{$prefix}_{$i}_name");
                                        $rank = $getVal("{$prefix}_{$i}_rank");
                                        $tel = $getVal("{$prefix}_{$i}_tel");
                                        $email = $getVal("{$prefix}_{$i}_email");
                                        $address = $getVal("{$prefix}_{$i}_address");
                                        
                                        $isFilled = ($name !== '—' || $rank !== '—' || $tel !== '—');
                                        
                                        $serviceColor = match($prefix) {
                                            'custom_officer' => 'info',
                                            'police_officer' => 'danger',
                                            'dss_officer'    => 'warning',
                                            'nia_officer'    => 'purple',
                                        };
                                        $serviceIcon = match($prefix) {
                                            'custom_officer' => 'fa-anchor',
                                            'police_officer' => 'fa-shield-halved',
                                            'dss_officer'    => 'fa-fingerprint',
                                            'nia_officer'    => 'fa-globe',
                                        };
                                    ?>
                                        <?php if ($isFilled): ?>
                                            <!-- Professional Active Officer Card -->
                                            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3 p-3 rounded-3 border border-secondary border-opacity-10 bg-dark bg-opacity-20 hover-border-active">
                                                <!-- Left: Officer Avatar with corner service badge -->
                                                <div class="flex-shrink-0 mx-auto mx-md-0">
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center position-relative shadow border border-secondary border-opacity-30" style="width: 60px; height: 60px; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); overflow: visible;">
                                                        <i class="fa-solid fa-user-shield text-white-50" style="font-size: 1.8rem; transform: translateY(4px);"></i>
                                                        <span class="position-absolute bottom-0 end-0 bg-<?= $serviceColor === 'purple' ? 'purple-badge' : $serviceColor ?> rounded-circle border border-2 border-dark d-flex align-items-center justify-content-center shadow-sm" style="width: 24px; height: 24px;" title="<?= $officerTitle ?>">
                                                            <i class="fa-solid <?= $serviceIcon ?> text-white" style="font-size: 0.75rem;"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                                <!-- Right: Officer details and contact details -->
                                                <div class="flex-grow-1 w-100">
                                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                                        <div>
                                                            <h6 class="text-white fw-bold mb-0"><?= htmlspecialchars($name) ?></h6>
                                                            <span class="text-secondary small" style="font-size: 0.72rem;">Officer Slot <?= $i ?></span>
                                                        </div>
                                                        <?php if ($rank !== '—'): ?>
                                                            <span class="badge bg-<?= $serviceColor === 'purple' ? 'purple-badge' : $serviceColor ?> bg-opacity-10 text-<?= $serviceColor ?> border border-<?= $serviceColor === 'purple' ? 'purple-badge' : $serviceColor ?> border-opacity-25 px-2 py-1 rounded small" style="font-size: 0.72rem;">
                                                                <?= htmlspecialchars($rank) ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <!-- Contact info row/grid -->
                                                    <div class="row g-2 text-secondary small">
                                                        <div class="col-sm-6">
                                                            <i class="fa-solid fa-phone-volume me-2 text-<?= $serviceColor ?> opacity-75"></i>
                                                            <span class="text-white-50"><?= htmlspecialchars($tel) ?></span>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <i class="fa-solid fa-envelope me-2 text-<?= $serviceColor ?> opacity-75"></i>
                                                            <span class="text-white-50 text-wrap"><?= htmlspecialchars($email) ?></span>
                                                        </div>
                                                        <div class="col-12">
                                                            <i class="fa-solid fa-building me-2 text-<?= $serviceColor ?> opacity-75"></i>
                                                            <span class="text-white-50"><?= htmlspecialchars($address) ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <!-- Muted Placeholder Officer Card -->
                                            <div class="d-flex align-items-center gap-3 p-3 rounded-3 border border-secondary border-opacity-5 bg-dark bg-opacity-10 opacity-50">
                                                <div class="flex-shrink-0">
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center position-relative border border-secondary border-opacity-10" style="width: 50px; height: 50px; background: rgba(255,255,255,0.02);">
                                                        <i class="fa-solid fa-user text-secondary opacity-30" style="font-size: 1.4rem;"></i>
                                                    </div>
                                                </div>
                                                <div>
                                                    <h6 class="text-secondary fw-semibold mb-1">No Officer Logged</h6>
                                                    <p class="text-muted small mb-0" style="font-size: 0.75rem;">Officer Slot <?= $i ?> is unassigned.</p>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- SECTION 9: Dynamically Configured Fields (Additional fields) -->
            <?php
                // Build a list of displayed keys to isolate dynamic fields
                $displayedKeys = [
                    'number_plate_state', 'number_plate_lga',
                    'country_of_origin', 'country_of_manufacture', 'ship_year', 'ship_departure_port', 'ship_departure_date', 'ship_landing_date', 'custom_papers_status',
                    'purchase_date', 'purchase_amount', 'means_of_identification', 'insurance_cover',
                    'importer_name', 'importer_company', 'importer_address', 'importer_email', 'importer_tel',
                    'clearing_agent_name', 'clearing_agent_company', 'clearing_agent_address', 'clearing_agent_email', 'clearing_agent_tel',
                    'foreign_office_name', 'foreign_office_company', 'foreign_office_address', 'foreign_office_email', 'foreign_office_tel',
                    'port_name', 'port_company', 'port_address', 'port_email', 'port_tel',
                    'agent_name', 'agent_address', 'agent_tel', 'agent_email',
                    'tax_number', 'vehicle_particulars_number', 'vehicle_particulars_purchase_date', 'vehicle_particulars_amount', 'vehicle_particulars_expiry_date',
                    'pol_clearance_name', 'pol_clearance_rank', 'pol_clearance_office_address', 'pol_clearance_local_govt', 'pol_clearance_state', 'pol_clearance_tel', 'pol_clearance_email',
                    'dl_name', 'dl_rank', 'dl_address', 'dl_tel', 'dl_email'
                ];
                foreach ($officerTypes as $prefix) {
                    for ($i = 1; $i <= 3; $i++) {
                        foreach (['name', 'rank', 'address', 'tel', 'email'] as $suffix) {
                            $displayedKeys[] = "{$prefix}_{$i}_{$suffix}";
                        }
                    }
                }
                $extraFields = [];
                foreach ($cf as $fieldKey => $fieldValue) {
                    if (in_array($fieldKey, $displayedKeys, true)) {
                        continue;
                    }
                    if (is_array($fieldValue)) {
                        $fieldValue = implode(', ', $fieldValue);
                    }
                    $fieldValue = trim((string)$fieldValue);
                    if ($fieldValue !== '') {
                        $extraFields[$fieldKey] = $fieldValue;
                    }
                }
            ?>

            <?php if (!empty($extraFields)): ?>
                <div class="mb-4">
                    <div class="section-title mb-3 border-bottom border-secondary border-opacity-15 pb-2">
                        <i class="fa-solid fa-bars-progress text-info"></i> Custom Dynamic Form Fields
                    </div>
                    <div class="row g-3">
                        <?php foreach ($extraFields as $fieldKey => $fieldValue): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="custom-field-group">
                                    <div class="detail-label"><?= htmlspecialchars(ucwords(str_replace(['_', '-'], ' ', preg_replace('/^custom_/', '', $fieldKey)))) ?></div>
                                    <div class="detail-value text-white"><?= htmlspecialchars($fieldValue) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- ═══ TAB: Payments ═══ -->
        <div class="tab-pane fade" id="pane-payments" role="tabpanel">
            <div class="section-title mb-3">
                <i class="fa-solid fa-receipt text-success"></i> Payment History
                <span class="badge bg-secondary ms-1"><?= count($payments) ?></span>
                <span class="ms-auto badge bg-success fs-6 py-2 px-3">Total: ₦<?= number_format((float)$totalPayments, 2) ?></span>
            </div>
            <?php if (!empty($payments)): ?>
                <div class="table-responsive border-secondary border border-opacity-10 rounded-3 bg-dark bg-opacity-25">
                    <table class="table table-dark table-striped align-middle mb-0">
                        <thead class="table-secondary text-dark">
                            <tr>
                                <th>#</th>
                                <th>Payer</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Receipt #</th>
                                <th>Date</th>
                                <th>Collected By</th>
                                <th>Receipt File</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $pi => $pay): ?>
                                <tr>
                                    <td class="text-secondary"><?= $pi + 1 ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($pay['payer_name'] ?? '—') ?></td>
                                    <td class="text-success fw-bold">₦<?= number_format((float)$pay['amount'], 2) ?></td>
                                    <td>
                                        <?php
                                            $methodBadge = match($pay['payment_method'] ?? '') {
                                                'CASH' => '<span class="badge bg-warning text-dark"><i class="fa-solid fa-money-bill me-1"></i>Cash</span>',
                                                'BANK_TRANSFER' => '<span class="badge bg-info text-dark"><i class="fa-solid fa-building-columns me-1"></i>Bank Transfer</span>',
                                                'PAYSTACK' => '<span class="badge bg-primary"><i class="fa-solid fa-credit-card me-1"></i>Paystack</span>',
                                                default => '<span class="badge bg-secondary">' . htmlspecialchars($pay['payment_method'] ?? '—') . '</span>',
                                            };
                                            echo $methodBadge;
                                        ?>
                                    </td>
                                    <td><code class="text-white-50"><?= htmlspecialchars($pay['receipt_number'] ?? '—') ?></code></td>
                                    <td><?= !empty($pay['payment_date']) ? date('d M Y', strtotime($pay['payment_date'])) : '—' ?></td>
                                    <td class="text-secondary"><?= htmlspecialchars(($pay['collector_name'] ?? '') ?: ($pay['collector_email'] ?? '—')) ?></td>
                                    <td>
                                        <?php if (!empty($pay['receipt_file'])): ?>
                                            <a href="<?= BASE_URL ?>/<?= htmlspecialchars($pay['receipt_file']) ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                                <i class="fa-solid fa-file-arrow-down me-1"></i>View
                                            </a>
                                        <?php else: ?>
                                            <span class="text-secondary">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5 text-secondary">
                    <i class="fa-solid fa-coins fa-3x mb-3"></i>
                    <p>No payment records found for this vehicle.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- ═══ TAB: Verification ═══ -->
        <div class="tab-pane fade" id="pane-verification" role="tabpanel">
            <div class="section-title mb-3">
                <i class="fa-solid fa-shield-halved" style="color:#a78bfa;"></i> Verification Audit Trail
                <span class="badge bg-secondary ms-1"><?= count($verifications) ?></span>
            </div>
            <?php if (!empty($verifications)): ?>
                <div class="ps-2">
                    <?php foreach ($verifications as $vi => $ver): ?>
                        <?php
                            $vBadgeClass = match($ver['status']) {
                                'APPROVED' => 'bg-success',
                                'REJECTED' => 'bg-danger',
                                default    => 'bg-warning text-dark',
                            };
                            $vDotColor = match($ver['status']) {
                                'APPROVED' => '#10b981',
                                'REJECTED' => '#ef4444',
                                default    => '#f59e0b',
                            };
                        ?>
                        <div class="timeline-item">
                            <div class="timeline-dot" style="border-color: <?= $vDotColor ?>; background: #0f172a;"></div>
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-1">
                                <div>
                                    <span class="badge <?= $vBadgeClass ?>"><?= $ver['status'] ?></span>
                                    <span class="text-white-50 small ms-2"><?= htmlspecialchars($ver['verification_type'] ?? '') ?> audit</span>
                                </div>
                                <small class="text-secondary">
                                    <i class="fa-regular fa-clock me-1"></i>
                                    <?= !empty($ver['created_at']) ? date('d M Y, h:i A', strtotime($ver['created_at'])) : '—' ?>
                                </small>
                            </div>
                            <div class="text-secondary small mb-1">
                                Verifier: <strong class="text-white-50"><?= htmlspecialchars(($ver['verifier_name'] ?? '') ?: ($ver['verifier_email'] ?? '—')) ?></strong>
                            </div>
                            <?php if (!empty($ver['notes'])): ?>
                                <div class="text-secondary small fst-italic">"<?= htmlspecialchars($ver['notes']) ?>"</div>
                            <?php endif; ?>
                            <?php if (!empty($ver['verified_at'])): ?>
                                <div class="text-secondary small mt-1">
                                    <i class="fa-solid fa-stamp me-1 text-success"></i>Verified at: <?= date('d M Y, h:i A', strtotime($ver['verified_at'])) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5 text-secondary">
                    <i class="fa-solid fa-clipboard-check fa-3x mb-3"></i>
                    <p>No verification records found.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- ═══ TAB: Transfers ═══ -->
        <div class="tab-pane fade" id="pane-transfers" role="tabpanel">
            <div class="section-title mb-3">
                <i class="fa-solid fa-right-left text-info"></i> Ownership Transfer History
                <span class="badge bg-secondary ms-1"><?= count($transfers) ?></span>
            </div>
            <?php if (!empty($transfers)): ?>
                <div class="table-responsive border-secondary border border-opacity-10 rounded-3 bg-dark bg-opacity-25">
                    <table class="table table-dark table-striped align-middle mb-0">
                        <thead class="table-secondary text-dark">
                            <tr>
                                <th>#</th>
                                <th>Seller</th>
                                <th>Buyer</th>
                                <th>Sale Price</th>
                                <th>Transfer Date</th>
                                <th>Market</th>
                                <th>Witness</th>
                                <th>Approved By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transfers as $ti => $tr): ?>
                                <tr>
                                    <td class="text-secondary"><?= $ti + 1 ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($tr['seller_name'] ?? '—') ?></div>
                                        <small class="text-secondary"><?= htmlspecialchars($tr['seller_phone'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-success"><?= htmlspecialchars($tr['buyer_name'] ?? '—') ?></div>
                                        <small class="text-secondary"><?= htmlspecialchars($tr['buyer_phone'] ?? '') ?></small>
                                    </td>
                                    <td class="text-info fw-bold">₦<?= number_format((float)($tr['sale_price'] ?? 0), 2) ?></td>
                                    <td><?= !empty($tr['transfer_date']) ? date('d M Y', strtotime($tr['transfer_date'])) : '—' ?></td>
                                    <td class="text-secondary"><?= htmlspecialchars($tr['market_name'] ?? '—') ?></td>
                                    <td class="text-secondary"><?= htmlspecialchars($tr['witness_name'] ?? '—') ?></td>
                                    <td class="text-secondary"><?= htmlspecialchars(($tr['approved_by_name'] ?? '') ?: ($tr['approved_by_email'] ?? '—')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5 text-secondary">
                    <i class="fa-solid fa-shuffle fa-3x mb-3"></i>
                    <p>No ownership transfers have been recorded for this vehicle.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- ═══ TAB: Completeness Audit ═══ -->
        <div class="tab-pane fade" id="pane-completeness" role="tabpanel">
            
            <div class="row g-4 align-items-center mb-4 pb-3 border-bottom border-secondary border-opacity-10">
                <div class="col-md-3 text-center border-end border-secondary border-opacity-15">
                    <div style="font-size: 3.5rem; font-weight: 800; color: #10b981; line-height: 1;" class="mb-1"><?= $completenessScore ?>%</div>
                    <div class="text-secondary small text-uppercase fw-bold" style="letter-spacing: 0.05em;">Registry Score</div>
                    <div class="progress mt-3 mx-auto" style="height: 6px; background: rgba(255,255,255,0.08); border-radius: 4px; max-width: 140px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $completenessScore ?>%;" aria-valuenow="<?= $completenessScore ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="text-secondary small mt-2"><?= $completedCount ?> of <?= $totalCount ?> fields completed</div>
                </div>
                <div class="col-md-9">
                    <h5 class="text-white fw-bold d-flex align-items-center gap-2">
                        <i class="fa-solid fa-gauge-high text-success"></i> Vehicle Completeness Audit
                    </h5>
                    <p class="text-secondary small mb-3">
                        This registry audit highlights any skipped details during the initial onboarding. To ensure maximum traceability, security compliance, and fraud prevention, we recommend completing the missing parameters. Transit police, customs, and inspection checkpoints rely on this information for instant tracking.
                    </p>
                    <button class="btn btn-sm btn-outline-success" id="btnToggleUpdateForm">
                        <i class="fa-solid fa-pen-to-square me-1"></i> Edit & Complete Details
                    </button>
                </div>
            </div>

            <!-- Completeness Update Form -->
            <div id="updateFormSection" style="display:none;" class="mb-4">
                <form method="POST" action="<?= BASE_URL ?>/vehicle/update/<?= $v['id'] ?>" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                    <!-- SPECIFICATIONS GROUP -->
                    <div class="mb-4 p-3 bg-dark bg-opacity-25 rounded-3 border border-secondary border-opacity-10">
                        <h6 class="text-white fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-sliders text-info"></i> Primary Specifications
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label text-secondary small">Color</label>
                                <input type="text" name="color" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($v['color'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-secondary small">Fuel Type</label>
                                <select name="fuel_type" class="form-select bg-dark text-white border-secondary">
                                    <option value="">— Select —</option>
                                    <option value="Petrol" <?= ($v['fuel_type'] === 'Petrol') ? 'selected' : '' ?>>Petrol</option>
                                    <option value="Diesel" <?= ($v['fuel_type'] === 'Diesel') ? 'selected' : '' ?>>Diesel</option>
                                    <option value="Hybrid" <?= ($v['fuel_type'] === 'Hybrid') ? 'selected' : '' ?>>Hybrid</option>
                                    <option value="Electric" <?= ($v['fuel_type'] === 'Electric') ? 'selected' : '' ?>>Electric</option>
                                    <option value="Gas (CNG/LPG)" <?= ($v['fuel_type'] === 'Gas (CNG/LPG)') ? 'selected' : '' ?>>Gas (CNG/LPG)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-secondary small">Transmission</label>
                                <select name="transmission" class="form-select bg-dark text-white border-secondary">
                                    <option value="">— Select —</option>
                                    <option value="Automatic" <?= ($v['transmission'] === 'Automatic') ? 'selected' : '' ?>>Automatic</option>
                                    <option value="Manual" <?= ($v['transmission'] === 'Manual') ? 'selected' : '' ?>>Manual</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-secondary small">Category</label>
                                <input type="text" name="category" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($v['category'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-secondary small">Class</label>
                                <input type="text" name="class" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($v['class'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-secondary small">RFID Tag</label>
                                <input type="text" name="rfid_tag" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($v['rfid_tag'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-secondary small">QR Code</label>
                                <input type="text" name="qr_code" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($v['qr_code'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-secondary small">Update Photo</label>
                                <input type="file" name="vehicle_image" class="form-control bg-dark text-white border-secondary" accept="image/*">
                            </div>
                        </div>
                    </div>

                    <!-- LOCAL REGISTRATION GROUP -->
                    <div class="mb-4 p-3 bg-dark bg-opacity-25 rounded-3 border border-secondary border-opacity-10">
                        <h6 class="text-white fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-location-dot text-success"></i> Local Registration Particulars
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label text-secondary small">Number Plate State</label>
                                <input type="text" name="number_plate_state" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($cf['number_plate_state'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-secondary small">Number Plate LGA</label>
                                <input type="text" name="number_plate_lga" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($cf['number_plate_lga'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-secondary small">Tax Identification Number</label>
                                <input type="text" name="tax_number" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($cf['tax_number'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-secondary small">Particulars Cert Number</label>
                                <input type="text" name="vehicle_particulars_number" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($cf['vehicle_particulars_number'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <!-- IMPORTATION & CUSTOMS GROUP -->
                    <div class="mb-4 p-3 bg-dark bg-opacity-25 rounded-3 border border-secondary border-opacity-10">
                        <h6 class="text-white fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-ship text-info"></i> Importation & Customs Details
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label text-secondary small">Country of Origin</label>
                                <input type="text" name="country_of_origin" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($cf['country_of_origin'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-secondary small">Country of Manufacture</label>
                                <input type="text" name="country_of_manufacture" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($cf['country_of_manufacture'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-secondary small">Importer Name</label>
                                <input type="text" name="importer_name" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($cf['importer_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-secondary small">Clearing Agent Name</label>
                                <input type="text" name="clearing_agent_name" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($cf['clearing_agent_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-secondary small">Port of Landing</label>
                                <input type="text" name="port_name" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($cf['port_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-secondary small">Custom Papers Status</label>
                                <input type="text" name="custom_papers_status" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($cf['custom_papers_status'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-secondary small">Insurance Cover Policy</label>
                                <input type="text" name="insurance_cover" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($cf['insurance_cover'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mt-2">
                                <label class="form-label text-secondary small">Customs Document (Image / PDF)</label>
                                <div class="upload-zone py-2 px-3 border border-secondary border-dashed rounded-3 text-center position-relative" id="editCustomsUploadZone" style="cursor: pointer; background: rgba(255,255,255,0.02);">
                                    <input type="file" name="customs_doc" id="edit_customs_doc" accept="image/jpeg,image/png,image/jpg,application/pdf" style="position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; z-index: 2;">
                                    <div class="edit-upload-preview-container" style="margin-bottom:0.5rem; z-index: 1; position: relative;">
                                        <?php if (!empty($cf['customs_doc_path'])): ?>
                                            <?php if (strtolower(pathinfo($cf['customs_doc_path'], PATHINFO_EXTENSION)) === 'pdf'): ?>
                                                <div id="editCustomsPdfPreview" style="color:#ef4444;font-size:2rem;"><i class="fa-solid fa-file-pdf"></i></div>
                                                <img class="upload-preview" id="editCustomsImagePreview" src="#" alt="Preview" style="display:none; max-height: 80px; object-fit: contain; border-radius: 6px; border: 1px solid #10b981; margin: 0 auto;">
                                            <?php else: ?>
                                                <img class="upload-preview" id="editCustomsImagePreview" src="<?= BASE_URL . '/' . htmlspecialchars($cf['customs_doc_path']) ?>" alt="Preview" style="max-height: 80px; object-fit: contain; border-radius: 6px; border: 1px solid #10b981; margin: 0 auto;">
                                                <div id="editCustomsPdfPreview" style="display:none; color:#ef4444;font-size:2rem;"><i class="fa-solid fa-file-pdf"></i></div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <img class="upload-preview" id="editCustomsImagePreview" src="#" alt="Preview" style="display:none; max-height: 80px; object-fit: contain; border-radius: 6px; border: 1px solid #10b981; margin: 0 auto;">
                                            <div id="editCustomsPdfPreview" style="display:none; color:#ef4444;font-size:2rem;"><i class="fa-solid fa-file-pdf"></i></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="upload-placeholder" id="editCustomsPlaceholder" style="<?= !empty($cf['customs_doc_path']) ? 'display:none;' : '' ?>; z-index: 1; position: relative;">
                                        <i class="fa-solid fa-file-arrow-up text-secondary mb-1"></i>
                                        <div class="small text-secondary">Click or drag Customs document</div>
                                    </div>
                                    <div id="editCustomsUploadFileName" class="small text-success fw-semibold mt-1" style="<?= empty($cf['customs_doc_path']) ? 'display:none;' : '' ?>; z-index: 1; position: relative;"><?= !empty($cf['customs_doc_path']) ? '📎 ' . basename($cf['customs_doc_path']) : '' ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECURITY CLEARANCE GROUP -->
                    <div class="mb-4 p-3 bg-dark bg-opacity-25 rounded-3 border border-secondary border-opacity-10">
                        <h6 class="text-white fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-shield-halved text-warning"></i> Police Security Clearance
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-secondary small">Police Clearance Officer</label>
                                <input type="text" name="pol_clearance_name" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($cf['pol_clearance_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small">Police Document (Image / PDF)</label>
                                <div class="upload-zone py-2 px-3 border border-secondary border-dashed rounded-3 text-center position-relative" id="editPoliceUploadZone" style="cursor: pointer; background: rgba(255,255,255,0.02);">
                                    <input type="file" name="police_doc" id="edit_police_doc" accept="image/jpeg,image/png,image/jpg,application/pdf" style="position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; z-index: 2;">
                                    <div class="edit-upload-preview-container" style="margin-bottom:0.5rem; z-index: 1; position: relative;">
                                        <?php if (!empty($cf['police_doc_path'])): ?>
                                            <?php if (strtolower(pathinfo($cf['police_doc_path'], PATHINFO_EXTENSION)) === 'pdf'): ?>
                                                <div id="editPolicePdfPreview" style="color:#ef4444;font-size:2rem;"><i class="fa-solid fa-file-pdf"></i></div>
                                                <img class="upload-preview" id="editPoliceImagePreview" src="#" alt="Preview" style="display:none; max-height: 80px; object-fit: contain; border-radius: 6px; border: 1px solid #10b981; margin: 0 auto;">
                                            <?php else: ?>
                                                <img class="upload-preview" id="editPoliceImagePreview" src="<?= BASE_URL . '/' . htmlspecialchars($cf['police_doc_path']) ?>" alt="Preview" style="max-height: 80px; object-fit: contain; border-radius: 6px; border: 1px solid #10b981; margin: 0 auto;">
                                                <div id="editPolicePdfPreview" style="display:none; color:#ef4444;font-size:2rem;"><i class="fa-solid fa-file-pdf"></i></div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <img class="upload-preview" id="editPoliceImagePreview" src="#" alt="Preview" style="display:none; max-height: 80px; object-fit: contain; border-radius: 6px; border: 1px solid #10b981; margin: 0 auto;">
                                            <div id="editPolicePdfPreview" style="display:none; color:#ef4444;font-size:2rem;"><i class="fa-solid fa-file-pdf"></i></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="upload-placeholder" id="editPolicePlaceholder" style="<?= !empty($cf['police_doc_path']) ? 'display:none;' : '' ?>; z-index: 1; position: relative;">
                                        <i class="fa-solid fa-file-arrow-up text-secondary mb-1"></i>
                                        <div class="small text-secondary">Click or drag Police document</div>
                                    </div>
                                    <div id="editPoliceUploadFileName" class="small text-success fw-semibold mt-1" style="<?= empty($cf['police_doc_path']) ? 'display:none;' : '' ?>; z-index: 1; position: relative;"><?= !empty($cf['police_doc_path']) ? '📎 ' . basename($cf['police_doc_path']) : '' ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success"><i class="fa-solid fa-save me-1"></i> Save Registry Updates</button>
                        <button type="button" class="btn btn-outline-light" id="btnCancelUpdate">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Completeness Checklist Grid -->
            <div id="checklistSection" class="row g-4">
                <!-- Left: Missing/Skipped Fields -->
                <div class="col-md-6">
                    <div class="p-3 bg-dark bg-opacity-25 rounded-3 border border-warning border-opacity-10 h-100">
                        <h6 class="text-warning fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-triangle-exclamation"></i> Skipped / Recommended Details
                        </h6>
                        <?php if ($completenessScore == 100): ?>
                            <div class="text-success small py-3"><i class="fa-solid fa-circle-check me-1"></i> Excellent! No recommended fields are currently skipped.</div>
                        <?php else: ?>
                            <div class="d-flex flex-column gap-3">
                                <?php foreach ($auditResults as $key => $res): ?>
                                    <?php if (!$res['complete']): ?>
                                        <div class="p-2 rounded hover-border-active" style="background: rgba(255,255,255,0.05);">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <strong class="text-white" style="font-size:0.85rem;"><?= htmlspecialchars($res['label']) ?></strong>
                                                <span class="badge bg-warning text-dark" style="font-size:0.65rem;">Recommended</span>
                                            </div>
                                            <div class="text-secondary small" style="font-size:0.75rem;"><?= htmlspecialchars($res['rec']) ?></div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right: Completed Fields -->
                <div class="col-md-6">
                    <div class="p-3 bg-dark bg-opacity-25 rounded-3 border border-success border-opacity-10 h-100">
                        <h6 class="text-success fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-circle-check"></i> Completed Details
                        </h6>
                        <div class="d-flex flex-column gap-2">
                            <?php foreach ($auditResults as $key => $res): ?>
                                <?php if ($res['complete']): ?>
                                    <div class="d-flex align-items-center justify-content-between p-2 rounded" style="background: rgba(255,255,255,0.05);">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fa-solid fa-circle-check text-success" style="font-size:0.85rem;"></i>
                                            <span class="text-white-50" style="font-size:0.85rem;"><?= htmlspecialchars($res['label']) ?></span>
                                        </div>
                                        <small class="text-secondary text-truncate" style="max-width:180px;"><?= htmlspecialchars($res['value']) ?></small>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ═══ TAB: Verified Dossier ═══ -->
        <?php if ($verStatus === 'APPROVED'): ?>
        <div class="tab-pane fade" id="pane-dossier" role="tabpanel">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3 border-bottom border-secondary border-opacity-20">
                <div>
                    <h5 class="text-success fw-bold mb-1 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-file-shield text-success"></i> Official Verified Dossier
                    </h5>
                    <p class="text-secondary small mb-0">Security-verified government registry profile &amp; legal files.</p>
                </div>
                <!-- Verification Stamp Sign -->
                <div class="d-flex align-items-center gap-3 bg-success bg-opacity-10 border border-success border-opacity-20 rounded-3 px-3 py-2">
                    <div style="font-size: 1.8rem;" class="text-success"><i class="fa-solid fa-stamp"></i></div>
                    <div>
                        <div class="text-white small fw-bold" style="letter-spacing: 0.05em; text-transform: uppercase;">System Verified Seal</div>
                        <div class="text-success font-monospace" style="font-size: 0.75rem;">SEC-ID: <?= strtoupper(substr(md5($v['vin']), 0, 12)) ?></div>
                    </div>
                </div>
            </div>

            <!-- Quick Meta Summary Card -->
            <div class="card bg-dark bg-opacity-40 border border-secondary border-opacity-10 rounded-3 p-3 mb-4">
                <div class="row g-3">
                    <div class="col-md-3 col-sm-6">
                        <div class="text-secondary small fw-bold">VEHICLE OWNER</div>
                        <div class="text-white fw-semibold"><?= htmlspecialchars($owner['full_name'] ?? '—') ?></div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="text-secondary small fw-bold">VIN NUMBER</div>
                        <div class="text-white font-monospace small"><?= htmlspecialchars($v['vin']) ?></div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="text-secondary small fw-bold">PLATE NUMBER</div>
                        <div class="text-white font-monospace small"><?= htmlspecialchars($v['plate_number']) ?></div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="text-secondary small fw-bold">AUDIT DATE</div>
                        <div class="text-white small">
                            <?= !empty($latestVerification['created_at']) ? date('d M Y, h:i A', strtotime($latestVerification['created_at'])) : '—' ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Official Certificates -->
            <h6 class="text-white fw-bold mb-3 d-flex align-items-center gap-2 mt-2">
                <i class="fa-solid fa-print text-warning"></i> Print Official Certificates
            </h6>
            <div class="row g-3 mb-5">
                <div class="col-md-4">
                    <a href="<?= BASE_URL ?>/document/proof_of_ownership/<?= $v['id'] ?>" target="_blank" class="btn btn-outline-success w-100 py-3 text-start d-flex align-items-center gap-3 hover-border-active" style="background: rgba(16,185,129,0.05);">
                        <div class="fs-3"><i class="fa-solid fa-file-contract"></i></div>
                        <div>
                            <div class="fw-bold text-white">Proof of Ownership</div>
                            <div class="small text-secondary">Official Ownership Cert</div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="<?= BASE_URL ?>/document/vehicle_license/<?= $v['id'] ?>" target="_blank" class="btn btn-outline-info w-100 py-3 text-start d-flex align-items-center gap-3 hover-border-active" style="background: rgba(14,165,233,0.05);">
                        <div class="fs-3"><i class="fa-solid fa-id-card"></i></div>
                        <div>
                            <div class="fw-bold text-white">Vehicle License</div>
                            <div class="small text-secondary">Receipt & QR License</div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="<?= BASE_URL ?>/document/certificate_of_insurance/<?= $v['id'] ?>" target="_blank" class="btn btn-outline-warning w-100 py-3 text-start d-flex align-items-center gap-3 hover-border-active" style="background: rgba(245,158,11,0.05);">
                        <div class="fs-3"><i class="fa-solid fa-shield-heart"></i></div>
                        <div>
                            <div class="fw-bold text-white">Insurance Cert</div>
                            <div class="small text-secondary">3rd Party Policy Document</div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Documents Grid -->
            <h6 class="text-white fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="fa-solid fa-folder-open text-info"></i> Security Documents &amp; Certificates
            </h6>
            <div class="row g-4">
                <!-- 1. Customs Clearance -->
                <div class="col-md-6 col-lg-3">
                    <div class="card glass-panel border border-secondary border-opacity-10 p-3 h-100 hover-border-active d-flex flex-column justify-content-between" style="background: rgba(255,255,255,0.02);">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-primary text-xs">Customs Service</span>
                                <?php if (!empty($cf['customs_doc_path'])): ?>
                                    <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i>Verified</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark"><i class="fa-solid fa-triangle-exclamation me-1"></i>Missing</span>
                                <?php endif; ?>
                            </div>
                            <h6 class="text-white fw-bold mb-1" style="font-size: 0.9rem;">Customs Clearance Cert</h6>
                            <p class="text-secondary small mb-3" style="font-size: 0.75rem;">Federal customs tariff payment &amp; legal entry declaration.</p>
                            
                            <!-- Document Thumbnail -->
                            <div class="document-preview-box rounded-3 mb-3 border border-secondary border-opacity-10 bg-black bg-opacity-30 d-flex align-items-center justify-content-center" style="height: 140px; overflow: hidden; position: relative;">
                                <?php if (!empty($cf['customs_doc_path'])): ?>
                                    <?php if (strtolower(pathinfo($cf['customs_doc_path'], PATHINFO_EXTENSION)) === 'pdf'): ?>
                                        <div class="text-center text-danger">
                                            <i class="fa-solid fa-file-pdf fa-4x"></i>
                                            <div class="text-secondary font-monospace mt-1" style="font-size:0.75rem;"><?= htmlspecialchars(basename($cf['customs_doc_path'])) ?></div>
                                        </div>
                                    <?php else: ?>
                                        <img src="<?= BASE_URL . '/' . htmlspecialchars($cf['customs_doc_path']) ?>" alt="Customs Document" style="width: 100%; height: 100%; object-fit: cover; cursor: pointer;" class="view-dossier-doc" data-type="image" data-src="<?= BASE_URL . '/' . htmlspecialchars($cf['customs_doc_path']) ?>" data-title="Customs Clearance Certificate">
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="text-center text-secondary small">
                                        <i class="fa-solid fa-file-excel fa-3x mb-2 text-muted"></i>
                                        <div>Not Uploaded</div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (!empty($cf['customs_doc_path'])): ?>
                            <div class="d-flex gap-2">
                                <?php if (strtolower(pathinfo($cf['customs_doc_path'], PATHINFO_EXTENSION)) === 'pdf'): ?>
                                    <button class="btn btn-sm btn-outline-info flex-grow-1 view-dossier-doc" data-type="pdf" data-src="<?= BASE_URL . '/' . htmlspecialchars($cf['customs_doc_path']) ?>" data-title="Customs Clearance Certificate">
                                        <i class="fa-solid fa-eye me-1"></i>View PDF
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-info flex-grow-1 view-dossier-doc" data-type="image" data-src="<?= BASE_URL . '/' . htmlspecialchars($cf['customs_doc_path']) ?>" data-title="Customs Clearance Certificate">
                                        <i class="fa-solid fa-magnifying-glass me-1"></i>Preview
                                    </button>
                                <?php endif; ?>
                                <a href="<?= BASE_URL . '/' . htmlspecialchars($cf['customs_doc_path']) ?>" download class="btn btn-sm btn-success"><i class="fa-solid fa-download"></i></a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 2. Police Security Clearance -->
                <div class="col-md-6 col-lg-3">
                    <div class="card glass-panel border border-secondary border-opacity-10 p-3 h-100 hover-border-active d-flex flex-column justify-content-between" style="background: rgba(255,255,255,0.02);">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-warning text-dark text-xs">Police Force</span>
                                <?php if (!empty($cf['police_doc_path'])): ?>
                                    <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i>Verified</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark"><i class="fa-solid fa-triangle-exclamation me-1"></i>Missing</span>
                                <?php endif; ?>
                            </div>
                            <h6 class="text-white fw-bold mb-1" style="font-size: 0.9rem;">Police Clearance Report</h6>
                            <p class="text-secondary small mb-3" style="font-size: 0.75rem;">Official national police clearance and trace audit certificate.</p>
                            
                            <!-- Document Thumbnail -->
                            <div class="document-preview-box rounded-3 mb-3 border border-secondary border-opacity-10 bg-black bg-opacity-30 d-flex align-items-center justify-content-center" style="height: 140px; overflow: hidden; position: relative;">
                                <?php if (!empty($cf['police_doc_path'])): ?>
                                    <?php if (strtolower(pathinfo($cf['police_doc_path'], PATHINFO_EXTENSION)) === 'pdf'): ?>
                                        <div class="text-center text-danger">
                                            <i class="fa-solid fa-file-pdf fa-4x"></i>
                                            <div class="text-secondary font-monospace mt-1" style="font-size:0.75rem;"><?= htmlspecialchars(basename($cf['police_doc_path'])) ?></div>
                                        </div>
                                    <?php else: ?>
                                        <img src="<?= BASE_URL . '/' . htmlspecialchars($cf['police_doc_path']) ?>" alt="Police Clearance Document" style="width: 100%; height: 100%; object-fit: cover; cursor: pointer;" class="view-dossier-doc" data-type="image" data-src="<?= BASE_URL . '/' . htmlspecialchars($cf['police_doc_path']) ?>" data-title="Police Clearance Report">
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="text-center text-secondary small">
                                        <i class="fa-solid fa-file-excel fa-3x mb-2 text-muted"></i>
                                        <div>Not Uploaded</div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (!empty($cf['police_doc_path'])): ?>
                            <div class="d-flex gap-2">
                                <?php if (strtolower(pathinfo($cf['police_doc_path'], PATHINFO_EXTENSION)) === 'pdf'): ?>
                                    <button class="btn btn-sm btn-outline-info flex-grow-1 view-dossier-doc" data-type="pdf" data-src="<?= BASE_URL . '/' . htmlspecialchars($cf['police_doc_path']) ?>" data-title="Police Clearance Report">
                                        <i class="fa-solid fa-eye me-1"></i>View PDF
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-info flex-grow-1 view-dossier-doc" data-type="image" data-src="<?= BASE_URL . '/' . htmlspecialchars($cf['police_doc_path']) ?>" data-title="Police Clearance Report">
                                        <i class="fa-solid fa-magnifying-glass me-1"></i>Preview
                                    </button>
                                <?php endif; ?>
                                <a href="<?= BASE_URL . '/' . htmlspecialchars($cf['police_doc_path']) ?>" download class="btn btn-sm btn-success"><i class="fa-solid fa-download"></i></a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 3. Payment Receipt Evidence -->
                <div class="col-md-6 col-lg-3">
                    <div class="card glass-panel border border-secondary border-opacity-10 p-3 h-100 hover-border-active d-flex flex-column justify-content-between" style="background: rgba(255,255,255,0.02);">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-success">Registry Payments</span>
                                <?php if (!empty($payments[0]['receipt_file'])): ?>
                                    <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i>Logged</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark"><i class="fa-solid fa-triangle-exclamation me-1"></i>No File</span>
                                <?php endif; ?>
                            </div>
                            <h6 class="text-white fw-bold mb-1" style="font-size: 0.9rem;">Onboarding Fee Receipt</h6>
                            <p class="text-secondary small mb-3" style="font-size: 0.75rem;">Evidence of payment of government registry &amp; onboarding fees.</p>
                            
                            <!-- Document Thumbnail -->
                            <div class="document-preview-box rounded-3 mb-3 border border-secondary border-opacity-10 bg-black bg-opacity-30 d-flex align-items-center justify-content-center" style="height: 140px; overflow: hidden; position: relative;">
                                <?php if (!empty($payments[0]['receipt_file'])): ?>
                                    <?php if (strtolower(pathinfo($payments[0]['receipt_file'], PATHINFO_EXTENSION)) === 'pdf'): ?>
                                        <div class="text-center text-danger">
                                            <i class="fa-solid fa-file-pdf fa-4x"></i>
                                            <div class="text-secondary font-monospace mt-1" style="font-size:0.75rem;"><?= htmlspecialchars(basename($payments[0]['receipt_file'])) ?></div>
                                        </div>
                                    <?php else: ?>
                                        <img src="<?= BASE_URL . '/' . htmlspecialchars($payments[0]['receipt_file']) ?>" alt="Payment Receipt Document" style="width: 100%; height: 100%; object-fit: cover; cursor: pointer;" class="view-dossier-doc" data-type="image" data-src="<?= BASE_URL . '/' . htmlspecialchars($payments[0]['receipt_file']) ?>" data-title="Onboarding Fee Receipt">
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="text-center text-secondary small">
                                        <i class="fa-solid fa-file-excel fa-3x mb-2 text-muted"></i>
                                        <div>No File Logged</div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (!empty($payments[0]['receipt_file'])): ?>
                            <div class="d-flex gap-2">
                                <?php if (strtolower(pathinfo($payments[0]['receipt_file'], PATHINFO_EXTENSION)) === 'pdf'): ?>
                                    <button class="btn btn-sm btn-outline-info flex-grow-1 view-dossier-doc" data-type="pdf" data-src="<?= BASE_URL . '/' . htmlspecialchars($payments[0]['receipt_file']) ?>" data-title="Onboarding Fee Receipt">
                                        <i class="fa-solid fa-eye me-1"></i>View PDF
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-info flex-grow-1 view-dossier-doc" data-type="image" data-src="<?= BASE_URL . '/' . htmlspecialchars($payments[0]['receipt_file']) ?>" data-title="Onboarding Fee Receipt">
                                        <i class="fa-solid fa-magnifying-glass me-1"></i>Preview
                                    </button>
                                <?php endif; ?>
                                <a href="<?= BASE_URL . '/' . htmlspecialchars($payments[0]['receipt_file']) ?>" download class="btn btn-sm btn-success"><i class="fa-solid fa-download"></i></a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 4. Vehicle Physical Image -->
                <div class="col-md-6 col-lg-3">
                    <div class="card glass-panel border border-secondary border-opacity-10 p-3 h-100 hover-border-active d-flex flex-column justify-content-between" style="background: rgba(255,255,255,0.02);">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-secondary">Vehicle Registry</span>
                                <?php if (!empty($v['image_path'])): ?>
                                    <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i>Uploaded</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark"><i class="fa-solid fa-triangle-exclamation me-1"></i>No Image</span>
                                <?php endif; ?>
                            </div>
                            <h6 class="text-white fw-bold mb-1" style="font-size: 0.9rem;">Physical Vehicle Photo</h6>
                            <p class="text-secondary small mb-3" style="font-size: 0.75rem;">Onboarded photograph of the vehicle physical appearance.</p>
                            
                            <!-- Document Thumbnail -->
                            <div class="document-preview-box rounded-3 mb-3 border border-secondary border-opacity-10 bg-black bg-opacity-30 d-flex align-items-center justify-content-center" style="height: 140px; overflow: hidden; position: relative;">
                                <?php if (!empty($v['image_path'])): ?>
                                    <img src="<?= BASE_URL . '/' . htmlspecialchars($v['image_path']) ?>" alt="Vehicle Photo" style="width: 100%; height: 100%; object-fit: cover; cursor: pointer;" class="view-dossier-doc" data-type="image" data-src="<?= BASE_URL . '/' . htmlspecialchars($v['image_path']) ?>" data-title="Physical Vehicle Photo">
                                <?php else: ?>
                                    <div class="text-center text-secondary small">
                                        <i class="fa-solid fa-image fa-3x mb-2 text-muted"></i>
                                        <div>No Photo</div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (!empty($v['image_path'])): ?>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-info flex-grow-1 view-dossier-doc" data-type="image" data-src="<?= BASE_URL . '/' . htmlspecialchars($v['image_path']) ?>" data-title="Physical Vehicle Photo">
                                    <i class="fa-solid fa-magnifying-glass me-1"></i>Preview
                                </button>
                                <a href="<?= BASE_URL . '/' . htmlspecialchars($v['image_path']) ?>" download class="btn btn-sm btn-success"><i class="fa-solid fa-download"></i></a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        </div>
    </div>
</div>

<!-- Dossier Document Lightbox / Viewer Modal -->
<div class="modal fade" id="dossierDocModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark border-0 shadow-lg" style="border-radius:16px;">
            <div class="modal-header border-0 pb-0 d-flex align-items-center justify-content-between px-4 pt-4">
                <h6 class="modal-title text-white fw-bold d-flex align-items-center gap-2">
                    <i class="fa-solid fa-file-shield text-success"></i> <span id="dossierModalTitle">Document View</span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <!-- Image Viewer -->
                <img id="dossierModalImage" src="" alt="Document Preview" class="img-fluid rounded-3 shadow" style="max-height:70vh; display:none; border:1px solid rgba(255,255,255,0.1); margin: 0 auto;">
                <!-- PDF Viewer -->
                <div id="dossierModalPdfContainer" style="display:none; height:70vh; width:100%; border-radius:12px; overflow:hidden;">
                    <iframe id="dossierModalIframe" src="" style="width:100%; height:100%; border:none;"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Lightbox Modal -->
<?php if (!empty($vehicleImageUrl)): ?>
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title text-white"><i class="fa-solid fa-image me-2 text-info"></i>Vehicle Image — <?= htmlspecialchars($v['plate_number']) ?></h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <img src="<?= htmlspecialchars($vehicleImageUrl) ?>" alt="Vehicle Image Full" class="img-fluid rounded-3" style="max-height:75vh;">
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Image lightbox
    $('#vehicleHeroImg').on('click', function() {
        new bootstrap.Modal('#imageModal').show();
    });

    // Active tab styling
    $('#vehicleDetailTabs .nav-link').on('shown.bs.tab', function() {
        $('#vehicleDetailTabs .nav-link').removeClass('active-tab-glow');
        $(this).addClass('active-tab-glow');
    });

    // Completeness Audit Form Toggle
    $('#btnToggleUpdateForm').on('click', function() {
        $('#checklistSection').toggle();
        $('#updateFormSection').toggle();
        if ($(this).text().includes('Edit')) {
            $(this).html('<i class="fa-solid fa-arrow-left me-1"></i> View Checklist');
        } else {
            $(this).html('<i class="fa-solid fa-pen-to-square me-1"></i> Edit & Complete Details');
        }
    });

    $('#btnCancelUpdate').on('click', function() {
        $('#checklistSection').show();
        $('#updateFormSection').hide();
        $('#btnToggleUpdateForm').html('<i class="fa-solid fa-pen-to-square me-1"></i> Edit & Complete Details');
    });

    // Dossier Document Viewer trigger
    document.querySelectorAll('.view-dossier-doc').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const type = this.getAttribute('data-type');
            const src = this.getAttribute('data-src');
            const title = this.getAttribute('data-title');

            document.getElementById('dossierModalTitle').textContent = title;

            const modalImg = document.getElementById('dossierModalImage');
            const modalPdfContainer = document.getElementById('dossierModalPdfContainer');
            const modalIframe = document.getElementById('dossierModalIframe');

            if (type === 'pdf') {
                modalImg.style.display = 'none';
                modalIframe.src = src;
                modalPdfContainer.style.display = 'block';
            } else {
                modalPdfContainer.style.display = 'none';
                modalImg.src = src;
                modalImg.style.display = 'block';
            }

            const dossierModal = new bootstrap.Modal(document.getElementById('dossierDocModal'));
            dossierModal.show();
        });
    });

    // Reset iframe source on modal close to stop loading
    document.getElementById('dossierDocModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('dossierModalIframe').src = '';
    });

    // Edit form Customs document preview
    const editCustomsInput = document.getElementById('edit_customs_doc');
    if (editCustomsInput) {
        editCustomsInput.addEventListener('change', function() {
            const file = this.files[0];
            const zone = document.getElementById('editCustomsUploadZone');
            const container = zone.querySelector('.edit-upload-preview-container');
            const imgPreview = document.getElementById('editCustomsImagePreview');
            const pdfPreview = document.getElementById('editCustomsPdfPreview');
            const placeholder = document.getElementById('editCustomsPlaceholder');
            const nameEl = document.getElementById('editCustomsUploadFileName');

            if (file) {
                placeholder.style.display = 'none';
                container.style.display = 'block';
                nameEl.textContent = '📎 ' + file.name;
                nameEl.style.display = 'block';

                if (file.type === 'application/pdf') {
                    imgPreview.style.display = 'none';
                    pdfPreview.style.display = 'block';
                } else {
                    pdfPreview.style.display = 'none';
                    const reader = new FileReader();
                    reader.onload = e => {
                        imgPreview.src = e.target.result;
                        imgPreview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            } else {
                placeholder.style.display = 'block';
                container.style.display = 'none';
                nameEl.style.display = 'none';
            }
        });
    }

    // Edit form Police document preview
    const editPoliceInput = document.getElementById('edit_police_doc');
    if (editPoliceInput) {
        editPoliceInput.addEventListener('change', function() {
            const file = this.files[0];
            const zone = document.getElementById('editPoliceUploadZone');
            const container = zone.querySelector('.edit-upload-preview-container');
            const imgPreview = document.getElementById('editPoliceImagePreview');
            const pdfPreview = document.getElementById('editPolicePdfPreview');
            const placeholder = document.getElementById('editPolicePlaceholder');
            const nameEl = document.getElementById('editPoliceUploadFileName');

            if (file) {
                placeholder.style.display = 'none';
                container.style.display = 'block';
                nameEl.textContent = '📎 ' + file.name;
                nameEl.style.display = 'block';

                if (file.type === 'application/pdf') {
                    imgPreview.style.display = 'none';
                    pdfPreview.style.display = 'block';
                } else {
                    pdfPreview.style.display = 'none';
                    const reader = new FileReader();
                    reader.onload = e => {
                        imgPreview.src = e.target.result;
                        imgPreview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            } else {
                placeholder.style.display = 'block';
                container.style.display = 'none';
                nameEl.style.display = 'none';
            }
        });
    }
});
</script>
