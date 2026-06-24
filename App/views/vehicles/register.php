<?php
// App/views/vehicles/register.php
?>

<style>
/* ─── Multi-step Wizard Styles ──────────────────────────────────── */
.wizard-wrapper {
    max-width: 960px;
    margin: 0 auto;
}

/* Progress Bar */
.wizard-progress {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 2.5rem;
    position: relative;
}

.wizard-progress::before {
    content: '';
    position: absolute;
    top: 22px;
    left: 0;
    right: 0;
    height: 2px;
    background: rgba(255,255,255,0.1);
    z-index: 0;
}

.progress-line {
    position: absolute;
    top: 22px;
    left: 0;
    height: 2px;
    background: linear-gradient(90deg, #10b981, #3b82f6);
    z-index: 1;
    transition: width 0.5s cubic-bezier(.4,0,.2,1);
}

.wizard-step-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    z-index: 2;
    flex: 1;
    cursor: pointer;
}

.step-circle {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    font-weight: 700;
    background: rgba(255,255,255,0.07);
    border: 2px solid rgba(255,255,255,0.15);
    color: #64748b;
    transition: all 0.4s ease;
    position: relative;
}

.wizard-step-item.active .step-circle {
    background: linear-gradient(135deg, #10b981, #059669);
    border-color: #10b981;
    color: #fff;
    box-shadow: 0 0 0 5px rgba(16,185,129,0.2);
}

.wizard-step-item.completed .step-circle {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    border-color: #3b82f6;
    color: #fff;
    box-shadow: 0 0 0 5px rgba(59,130,246,0.15);
}

.step-label {
    font-size: 0.7rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    text-align: center;
    max-width: 80px;
    transition: color 0.3s;
    white-space: nowrap;
}

.wizard-step-item.active .step-label { color: #10b981; }
.wizard-step-item.completed .step-label { color: #3b82f6; }

/* Form Panels */
.wizard-panel {
    display: none;
    animation: fadeSlideIn 0.4s ease;
}
.wizard-panel.active { display: block; }

@keyframes fadeSlideIn {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* Section Headers */
.section-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    background: rgba(16,185,129,0.08);
    border-left: 4px solid #10b981;
    border-radius: 8px;
    margin-bottom: 1.75rem;
}

.section-header .icon-box {
    width: 38px;
    height: 38px;
    border-radius: 8px;
    background: rgba(16,185,129,0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #10b981;
    font-size: 1rem;
    flex-shrink: 0;
}

.section-header h5 {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    color: #f8fafc;
    letter-spacing: 0.02em;
}

.section-header p {
    margin: 0;
    font-size: 0.78rem;
    color: #64748b;
}

/* Enhanced Form Fields */
.field-group {
    margin-bottom: 1.25rem;
}

.field-group label {
    font-size: 0.82rem;
    font-weight: 600;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 0.45rem;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.field-group label .req { color: #ef4444; font-size: 0.9rem; }

.input-with-icon {
    position: relative;
}

.input-with-icon .field-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #64748b;
    font-size: 0.9rem;
    pointer-events: none;
    transition: color 0.3s;
}

.input-with-icon .form-control,
.input-with-icon .form-select {
    padding-left: 2.75rem !important;
}

.input-with-icon .form-control:focus ~ .field-icon,
.input-with-icon .form-control:focus + .field-icon {
    color: #10b981;
}

.field-validate-icon {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: 0.9rem;
    display: none;
}

.field-validate-icon.valid { color: #10b981; display: block; }
.field-validate-icon.invalid { color: #ef4444; display: block; }

/* VIN Lookup Box */
.vin-lookup-box {
    background: rgba(59,130,246,0.06);
    border: 1px dashed rgba(59,130,246,0.25);
    border-radius: 10px;
    padding: 0.75rem 1rem;
    margin-top: 0.5rem;
    font-size: 0.82rem;
    color: #94a3b8;
    display: none;
}

.vin-lookup-box.visible { display: block; animation: fadeSlideIn 0.3s ease; }

/* Payment Card */
.fee-display-card {
    background: linear-gradient(135deg, rgba(16,185,129,0.12), rgba(59,130,246,0.08));
    border: 1px solid rgba(16,185,129,0.25);
    border-radius: 14px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.fee-amount {
    font-size: 2.25rem;
    font-weight: 800;
    background: linear-gradient(135deg, #10b981, #3b82f6);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    line-height: 1.1;
}

.fee-label {
    font-size: 0.78rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

/* Payment Method Selector */
.payment-method-cards {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
    margin-bottom: 1.25rem;
}

.payment-card-option {
    background: rgba(255,255,255,0.04);
    border: 2px solid rgba(255,255,255,0.1);
    border-radius: 12px;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
    position: relative;
}

.payment-card-option:hover {
    border-color: rgba(16,185,129,0.4);
    background: rgba(16,185,129,0.06);
}

.payment-card-option.selected {
    border-color: #10b981;
    background: rgba(16,185,129,0.1);
    box-shadow: 0 0 0 3px rgba(16,185,129,0.15);
}

.payment-card-option input[type="radio"] {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.payment-card-option .pm-icon {
    font-size: 1.5rem;
    margin-bottom: 0.4rem;
    display: block;
}

.payment-card-option .pm-label {
    font-size: 0.8rem;
    font-weight: 600;
    color: #94a3b8;
}

.payment-card-option.selected .pm-label { color: #10b981; }

.payment-card-option .pm-check {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #10b981;
    color: #fff;
    font-size: 0.55rem;
    display: none;
    align-items: center;
    justify-content: center;
}

.payment-card-option.selected .pm-check { display: flex; }

/* Image Upload Zone */
.upload-zone {
    border: 2px dashed rgba(255,255,255,0.15);
    border-radius: 14px;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    background: rgba(255,255,255,0.02);
}

.upload-zone:hover, .upload-zone.dragover {
    border-color: rgba(16,185,129,0.5);
    background: rgba(16,185,129,0.05);
}

.upload-zone input[type="file"] {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
    width: 100%;
    height: 100%;
}

.upload-zone .upload-preview {
    display: none;
    width: 120px;
    height: 90px;
    object-fit: cover;
    border-radius: 10px;
    border: 2px solid #10b981;
    margin: 0 auto 0.75rem;
}

.upload-zone .upload-placeholder { pointer-events: none; }
.upload-zone.has-file .upload-placeholder { display: none; }
.upload-zone.has-file #imagePreview { display: block; }

/* Summary Panel */
.summary-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}

.summary-item {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 10px;
    padding: 0.75rem 1rem;
}

.summary-item .s-label {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: #64748b;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.summary-item .s-value {
    font-size: 0.88rem;
    font-weight: 600;
    color: #f8fafc;
    word-break: break-word;
}

/* Alert Banners */
.alert-glass {
    background: rgba(255,255,255,0.05);
    border-radius: 10px;
    padding: 0.85rem 1.1rem;
    border-left: 4px solid;
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    font-size: 0.84rem;
}

.alert-glass.alert-danger  { border-color: #ef4444; color: #fca5a5; }
.alert-glass.alert-success { border-color: #10b981; color: #6ee7b7; }
.alert-glass.alert-info    { border-color: #3b82f6; color: #93c5fd; }

/* Navigation Buttons */
.wizard-nav {
    display: flex;
    gap: 0.75rem;
    justify-content: space-between;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid rgba(255,255,255,0.07);
}

.btn-wizard-prev {
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,255,255,0.12);
    color: #94a3b8;
    padding: 0.7rem 1.75rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.88rem;
    transition: all 0.3s ease;
    cursor: pointer;
}

.btn-wizard-prev:hover {
    background: rgba(255,255,255,0.12);
    color: #f8fafc;
    transform: translateX(-2px);
}

.btn-wizard-next {
    background: linear-gradient(135deg, #10b981, #059669);
    border: none;
    color: #fff;
    padding: 0.7rem 1.75rem;
    border-radius: 10px;
    font-weight: 700;
    font-size: 0.88rem;
    transition: all 0.3s ease;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 4px 15px rgba(16,185,129,0.3);
}

.btn-wizard-next:hover {
    background: linear-gradient(135deg, #059669, #047857);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(16,185,129,0.4);
}

.btn-wizard-submit {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    border: none;
    color: #fff;
    padding: 0.85rem 2.5rem;
    border-radius: 10px;
    font-weight: 700;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.6rem;
    box-shadow: 0 4px 15px rgba(59,130,246,0.3);
}

.btn-wizard-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(59,130,246,0.45);
}

/* Step Counter Badge */
.step-counter {
    font-size: 0.75rem;
    background: rgba(255,255,255,0.07);
    border-radius: 20px;
    padding: 0.25rem 0.75rem;
    color: #64748b;
    font-weight: 600;
}
</style>

<div class="wizard-wrapper">
    <!-- Page Title -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="text-white fw-bold mb-1">
                <i class="fa-solid fa-car-on text-success me-2"></i>
                Vehicle Onboarding Registration
            </h3>
            <p class="text-secondary mb-0" style="font-size:0.85rem;">
                Complete all sections to register a new vehicle on the NVOTS national registry.
            </p>
        </div>
        <a href="<?= BASE_URL ?>/dashboard" class="btn btn-sm" style="background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,0.12);color:#94a3b8;border-radius:8px;">
            <i class="fa-solid fa-arrow-left me-2"></i>Back
        </a>
    </div>

    <?php if (isset($error) && $error): ?>
        <div class="alert-glass alert-danger mb-4">
            <i class="fa-solid fa-circle-xmark fa-lg mt-1"></i>
            <div><strong>Registration Error</strong><br><?= htmlspecialchars($error) ?></div>
        </div>
    <?php endif; ?>

    <?php if (isset($success) && $success): ?>
        <div class="alert-glass alert-success mb-4">
            <i class="fa-solid fa-circle-check fa-lg mt-1"></i>
            <div>
                <strong>Vehicle Successfully Registered!</strong><br>
                <?= htmlspecialchars($success) ?>
                
                <?php if (!empty($registeredVehicleId)): ?>
                <div class="mt-3 mb-2">
                    <a href="<?= BASE_URL ?>/document/ownership/<?= (int)$registeredVehicleId ?>" target="_blank" class="btn btn-primary d-inline-flex align-items-center gap-2">
                        <i class="fa-solid fa-print"></i> Print Ownership Document
                    </a>
                </div>
                <?php endif; ?>

                <div class="mt-2">
                    <a href="<?= BASE_URL ?>/vehicle/register" class="text-success fw-bold">Register Another</a> |
                    <a href="<?= BASE_URL ?>/dashboard" class="text-success fw-bold">Go to Dashboard</a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Card -->
    <div class="glass-panel p-4">

        <!-- Wizard Progress -->
        <div class="wizard-progress" id="wizardProgress">
            <div class="progress-line" id="progressLine" style="width:0%"></div>

            <div class="wizard-step-item active" data-step="1">
                <div class="step-circle"><i class="fa-solid fa-user-shield"></i></div>
                <span class="step-label">Owner</span>
            </div>
            <div class="wizard-step-item" data-step="2">
                <div class="step-circle"><i class="fa-solid fa-id-card"></i></div>
                <span class="step-label">Identity</span>
            </div>
            <div class="wizard-step-item" data-step="3">
                <div class="step-circle"><i class="fa-solid fa-sliders"></i></div>
                <span class="step-label">Specs</span>
            </div>
            <div class="wizard-step-item" data-step="4">
                <div class="step-circle"><i class="fa-solid fa-receipt"></i></div>
                <span class="step-label">Payment</span>
            </div>
            <div class="wizard-step-item" data-step="5">
                <div class="step-circle"><i class="fa-solid fa-check-double"></i></div>
                <span class="step-label">Confirm</span>
            </div>
        </div>

        <!-- FORM -->
        <form method="POST" action="<?= BASE_URL ?>/vehicle/register" enctype="multipart/form-data" id="vehicleRegForm" novalidate>
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

            <!-- ═══════════════════════════════════════════════════ -->
            <!-- STEP 1: OWNER ASSOCIATION                          -->
            <!-- ═══════════════════════════════════════════════════ -->
            <div class="wizard-panel active" id="step1">
                <div class="section-header">
                    <div class="icon-box"><i class="fa-solid fa-user-shield"></i></div>
                    <div>
                        <h5>Owner Association</h5>
                        <p>Link this vehicle to a registered owner in the NVOTS database</p>
                    </div>
                </div>

                <div class="alert-glass alert-info mb-4">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>If the owner is not yet registered, please <a href="<?= BASE_URL ?>/owner/register" class="text-info fw-bold text-decoration-none">Register Owner First</a> before proceeding with vehicle onboarding.</span>
                </div>

                <div class="field-group">
                    <label for="owner_id">
                        <i class="fa-solid fa-user me-1" style="color:#10b981;font-size:0.75rem;"></i>
                        Primary Vehicle Owner <span class="req">*</span>
                    </label>
                    <div class="input-with-icon">
                        <i class="field-icon fa-solid fa-magnifying-glass"></i>
                        <select class="form-select" id="owner_id" name="owner_id" required>
                            <option value="">— Search &amp; Select Owner —</option>
                            <?php foreach ($owners as $owner): ?>
                                <option value="<?= $owner['id'] ?>">
                                    <?= htmlspecialchars($owner['full_name']) ?> &nbsp;|&nbsp;
                                    NIN: <?= htmlspecialchars($owner['nin'] ?? 'N/A') ?> &nbsp;|&nbsp;
                                    <?= htmlspecialchars($owner['phone']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Owner Preview Card -->
                <div id="ownerPreviewCard" class="mt-3" style="display:none;">
                    <div style="background:rgba(16,185,129,0.07);border:1px solid rgba(16,185,129,0.2);border-radius:12px;padding:1rem 1.25rem;">
                        <div class="d-flex align-items-center gap-3">
                            <div style="width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,#10b981,#3b82f6);display:flex;align-items:center;justify-content:center;font-size:1.25rem;color:#fff;font-weight:800;" id="ownerInitial">—</div>
                            <div>
                                <div class="fw-bold text-white" id="ownerPreviewName">—</div>
                                <div class="text-secondary" style="font-size:0.8rem;" id="ownerPreviewDetail">—</div>
                            </div>
                            <div class="ms-auto">
                                <span class="badge" style="background:rgba(16,185,129,0.15);color:#10b981;border:1px solid rgba(16,185,129,0.3);font-size:0.7rem;padding:0.4rem 0.75rem;">
                                    <i class="fa-solid fa-circle-check me-1"></i>Verified Owner
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($dynamicFields)): ?>
                    <!-- Dynamic Fields -->
                    <div class="mt-4">
                        <div class="section-header" style="border-left-color:#3b82f6;background:rgba(59,130,246,0.06);">
                            <div class="icon-box" style="background:rgba(59,130,246,0.15);color:#3b82f6;"><i class="fa-solid fa-folder-plus"></i></div>
                            <div>
                                <h5>Additional Custom Fields</h5>
                                <p>Administrator-defined fields for extended vehicle data</p>
                            </div>
                        </div>
                        <div class="row">
                            <?php foreach ($dynamicFields as $field): ?>
                                <div class="col-md-6">
                                    <div class="field-group">
                                        <label for="custom_<?= $field['id'] ?>">
                                            <?= htmlspecialchars($field['field_name']) ?>
                                            <?= $field['is_required'] ? '<span class="req">*</span>' : '' ?>
                                        </label>
                                        <?php if ($field['field_type'] === 'text'): ?>
                                            <input type="text" class="form-control" id="custom_<?= $field['id'] ?>" name="custom_<?= $field['id'] ?>" <?= $field['is_required'] ? 'required' : '' ?>>
                                        <?php elseif ($field['field_type'] === 'number'): ?>
                                            <input type="number" class="form-control" id="custom_<?= $field['id'] ?>" name="custom_<?= $field['id'] ?>" <?= $field['is_required'] ? 'required' : '' ?>>
                                        <?php elseif ($field['field_type'] === 'date'): ?>
                                            <input type="date" class="form-control" id="custom_<?= $field['id'] ?>" name="custom_<?= $field['id'] ?>" <?= $field['is_required'] ? 'required' : '' ?>>
                                        <?php elseif ($field['field_type'] === 'textarea'): ?>
                                            <textarea class="form-control" id="custom_<?= $field['id'] ?>" name="custom_<?= $field['id'] ?>" rows="3" <?= $field['is_required'] ? 'required' : '' ?>></textarea>
                                        <?php elseif ($field['field_type'] === 'dropdown'): ?>
                                            <select class="form-select" id="custom_<?= $field['id'] ?>" name="custom_<?= $field['id'] ?>" <?= $field['is_required'] ? 'required' : '' ?>>
                                                <option value="">— Select —</option>
                                                <?php foreach (explode(',', $field['options']) as $o): $o = trim($o); ?>
                                                    <option value="<?= htmlspecialchars($o) ?>"><?= htmlspecialchars($o) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php elseif ($field['field_type'] === 'radio'): ?>
                                            <div class="d-flex gap-3 pt-1">
                                                <?php foreach (explode(',', $field['options']) as $idx => $o): $o = trim($o); ?>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="custom_<?= $field['id'] ?>" id="radio_<?= $field['id'] ?>_<?= $idx ?>" value="<?= htmlspecialchars($o) ?>">
                                                        <label class="form-check-label text-secondary" for="radio_<?= $field['id'] ?>_<?= $idx ?>"><?= htmlspecialchars($o) ?></label>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php elseif ($field['field_type'] === 'checkbox'): ?>
                                            <div class="form-check pt-1">
                                                <input class="form-check-input" type="checkbox" name="custom_<?= $field['id'] ?>" id="custom_<?= $field['id'] ?>" value="Yes">
                                                <label class="form-check-label text-secondary" for="custom_<?= $field['id'] ?>">Yes, confirm</label>
                                            </div>
                                        <?php elseif ($field['field_type'] === 'file'): ?>
                                            <input type="file" class="form-control" id="custom_<?= $field['id'] ?>" name="custom_<?= $field['id'] ?>" <?= $field['is_required'] ? 'required' : '' ?>>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="wizard-nav">
                    <div></div>
                    <button type="button" class="btn-wizard-next" onclick="goStep(2)">
                        Vehicle Identity <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════ -->
            <!-- STEP 2: PRIMARY IDENTIFICATION                     -->
            <!-- ═══════════════════════════════════════════════════ -->
            <div class="wizard-panel" id="step2">
                <div class="section-header">
                    <div class="icon-box"><i class="fa-solid fa-id-card"></i></div>
                    <div>
                        <h5>Vehicle Primary Identification</h5>
                        <p>Enter the unique government-issued identification numbers for this vehicle</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="field-group">
                            <label for="vin">
                                <i class="fa-solid fa-barcode me-1" style="color:#10b981;font-size:0.75rem;"></i>
                                VIN Number <span class="req">*</span>
                            </label>
                            <div class="input-with-icon" style="position:relative;">
                                <i class="field-icon fa-solid fa-barcode"></i>
                                <input type="text" class="form-control text-uppercase font-monospace" id="vin" name="vin"
                                       placeholder="e.g. 1HGCM82633A004352" maxlength="17" required
                                       style="letter-spacing:0.1em;">
                                <span class="field-validate-icon" id="vinValidIcon"></span>
                            </div>
                            <div class="vin-lookup-box" id="vinInfo">
                                <i class="fa-solid fa-circle-info me-1 text-info"></i>
                                VIN must be 17 alphanumeric characters. No I, O, or Q allowed.
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="field-group">
                            <label for="plate_number">
                                <i class="fa-solid fa-rectangle-list me-1" style="color:#10b981;font-size:0.75rem;"></i>
                                Plate Number <span class="req">*</span>
                            </label>
                            <div class="input-with-icon">
                                <i class="field-icon fa-solid fa-hashtag"></i>
                                <input type="text" class="form-control text-uppercase font-monospace" id="plate_number" name="plate_number"
                                       placeholder="e.g. LAG-234AA" required style="letter-spacing:0.1em;">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="field-group">
                            <label for="engine_number">
                                <i class="fa-solid fa-gear me-1" style="color:#10b981;font-size:0.75rem;"></i>
                                Engine Number <span class="req">*</span>
                            </label>
                            <div class="input-with-icon">
                                <i class="field-icon fa-solid fa-gear"></i>
                                <input type="text" class="form-control text-uppercase font-monospace" id="engine_number" name="engine_number"
                                       placeholder="e.g. K24Z7-1234567" required style="letter-spacing:0.08em;">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="field-group">
                            <label for="chassis_number">
                                <i class="fa-solid fa-car-side me-1" style="color:#10b981;font-size:0.75rem;"></i>
                                Chassis Number <span class="req">*</span>
                            </label>
                            <div class="input-with-icon">
                                <i class="field-icon fa-solid fa-car-side"></i>
                                <input type="text" class="form-control text-uppercase font-monospace" id="chassis_number" name="chassis_number"
                                       placeholder="e.g. ZA911000000000000" required style="letter-spacing:0.08em;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ID Authenticity Notice -->
                <div class="alert-glass alert-info mt-2">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>All identification numbers will be cross-referenced against the national fraud database. Ensure accuracy of all entered values.</span>
                </div>

                <div class="wizard-nav">
                    <button type="button" class="btn-wizard-prev" onclick="goStep(1)">
                        <i class="fa-solid fa-arrow-left me-2"></i>Back
                    </button>
                    <button type="button" class="btn-wizard-next" onclick="goStep(3)">
                        Vehicle Specs <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════ -->
            <!-- STEP 3: VEHICLE SPECIFICATIONS + PHOTO             -->
            <!-- ═══════════════════════════════════════════════════ -->
            <div class="wizard-panel" id="step3">
                <div class="section-header">
                    <div class="icon-box"><i class="fa-solid fa-sliders"></i></div>
                    <div>
                        <h5>Vehicle Specifications</h5>
                        <p>Enter manufacturer details, physical attributes, and upload a vehicle photo</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="manufacturer">Manufacturer <span class="req">*</span></label>
                            <div class="input-with-icon">
                                <i class="field-icon fa-solid fa-industry"></i>
                                <input type="text" class="form-control" id="manufacturer" name="manufacturer" placeholder="e.g. Toyota" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="model">Model <span class="req">*</span></label>
                            <div class="input-with-icon">
                                <i class="field-icon fa-solid fa-tag"></i>
                                <input type="text" class="form-control" id="model" name="model" placeholder="e.g. Corolla" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="year">Year of Manufacture <span class="req">*</span></label>
                            <div class="input-with-icon">
                                <i class="field-icon fa-solid fa-calendar-days"></i>
                                <input type="number" class="form-control" id="year" name="year"
                                       min="1950" max="<?= date('Y') + 1 ?>" placeholder="<?= date('Y') ?>" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="field-group">
                            <label for="color">Color <span class="req">*</span></label>
                            <div class="input-with-icon">
                                <i class="field-icon fa-solid fa-palette"></i>
                                <input type="text" class="form-control" id="color" name="color" placeholder="e.g. Pearl White" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="field-group">
                            <label for="fuel_type">Fuel Type</label>
                            <div class="input-with-icon">
                                <i class="field-icon fa-solid fa-gas-pump"></i>
                                <select class="form-select" id="fuel_type" name="fuel_type">
                                    <option value="Petrol">⛽ Petrol</option>
                                    <option value="Diesel">🛢 Diesel</option>
                                    <option value="Hybrid">⚡ Hybrid</option>
                                    <option value="Electric">🔋 Electric</option>
                                    <option value="Gas (CNG/LPG)">🪣 Gas (CNG/LPG)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="field-group">
                            <label for="transmission">Transmission</label>
                            <div class="input-with-icon">
                                <i class="field-icon fa-solid fa-gears"></i>
                                <select class="form-select" id="transmission" name="transmission">
                                    <option value="Automatic">🔄 Automatic</option>
                                    <option value="Manual">🕹 Manual</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="field-group">
                            <label for="category">Category</label>
                            <div class="input-with-icon">
                                <i class="field-icon fa-solid fa-car"></i>
                                <select class="form-select" id="category" name="category">
                                    <option value="Sedan">🚗 Sedan</option>
                                    <option value="SUV">🚙 SUV / Crossover</option>
                                    <option value="Hatchback">🚘 Hatchback</option>
                                    <option value="Coupe">🏎 Coupe</option>
                                    <option value="Truck">🚚 Truck / Pickup</option>
                                    <option value="Bus">🚌 Bus / Minibus</option>
                                    <option value="Motorcycle">🏍 Motorcycle</option>
                                    <option value="Van">🚐 Van</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="field-group">
                            <label for="class">Vehicle Class</label>
                            <div class="input-with-icon">
                                <i class="field-icon fa-solid fa-layer-group"></i>
                                <select class="form-select" id="class" name="class">
                                    <option value="Private">🏠 Private Passenger</option>
                                    <option value="Commercial">🏪 Commercial Transport</option>
                                    <option value="Government">🏛 Government / Diplomatic</option>
                                    <option value="Agricultural">🚜 Agricultural</option>
                                    <option value="Emergency">🚨 Emergency Services</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6"></div>
                </div>

                <div class="section-header" style="border-left-color:#2563eb;background:rgba(37,99,235,0.08);">
                    <div class="icon-box" style="background:rgba(37,99,235,0.15);color:#2563eb;"><i class="fa-solid fa-truck-fast"></i></div>
                    <div>
                        <h5>Import & Customs Details</h5>
                        <p>Capture all vehicle importation, clearance, and security verification details.</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="country_of_origin">Country of Origin</label>
                            <input type="text" class="form-control" id="country_of_origin" name="country_of_origin" placeholder="e.g. Nigeria">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="country_of_manufacture">Country of Manufacture</label>
                            <input type="text" class="form-control" id="country_of_manufacture" name="country_of_manufacture" placeholder="e.g. Japan">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="model">Model</label>
                            <input type="text" class="form-control" id="model" name="model" placeholder="e.g. Corolla" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="ship_year">Year of Manufacture</label>
                            <input type="number" class="form-control" id="ship_year" name="ship_year" min="1950" max="<?= date('Y') + 1 ?>" placeholder="2025">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="importer_name">Importer Name</label>
                            <input type="text" class="form-control" id="importer_name" name="importer_name" placeholder="Importer full name">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="importer_company">Importer Company</label>
                            <input type="text" class="form-control" id="importer_company" name="importer_company" placeholder="Company name">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="field-group">
                            <label for="importer_address">Importer Office Address</label>
                            <input type="text" class="form-control" id="importer_address" name="importer_address" placeholder="Office address">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="field-group">
                            <label for="importer_email">Importer Email</label>
                            <input type="email" class="form-control" id="importer_email" name="importer_email" placeholder="name@domain.com">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="field-group">
                            <label for="importer_tel">Importer Tel</label>
                            <input type="text" class="form-control" id="importer_tel" name="importer_tel" placeholder="08012345678">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="field-group">
                            <label for="clearing_agent_name">Clearing Agent Name</label>
                            <input type="text" class="form-control" id="clearing_agent_name" name="clearing_agent_name" placeholder="Name">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="field-group">
                            <label for="clearing_agent_company">Clearing Agent Company</label>
                            <input type="text" class="form-control" id="clearing_agent_company" name="clearing_agent_company" placeholder="Company">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="field-group">
                            <label for="clearing_agent_address">Clearing Agent Address</label>
                            <input type="text" class="form-control" id="clearing_agent_address" name="clearing_agent_address" placeholder="Office Address">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="field-group">
                            <label for="clearing_agent_email">Clearing Agent Email</label>
                            <input type="email" class="form-control" id="clearing_agent_email" name="clearing_agent_email" placeholder="email@example.com">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="field-group">
                            <label for="clearing_agent_tel">Clearing Agent Tel</label>
                            <input type="text" class="form-control" id="clearing_agent_tel" name="clearing_agent_tel" placeholder="Phone number">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="field-group">
                            <label for="foreign_office_name">Foreign Office Name</label>
                            <input type="text" class="form-control" id="foreign_office_name" name="foreign_office_name" placeholder="Name">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="field-group">
                            <label for="foreign_office_company">Foreign Office Company</label>
                            <input type="text" class="form-control" id="foreign_office_company" name="foreign_office_company" placeholder="Company">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="field-group">
                            <label for="foreign_office_address">Foreign Office Address</label>
                            <input type="text" class="form-control" id="foreign_office_address" name="foreign_office_address" placeholder="Office Address">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="field-group">
                            <label for="foreign_office_email">Foreign Office Email</label>
                            <input type="email" class="form-control" id="foreign_office_email" name="foreign_office_email" placeholder="email@example.com">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="field-group">
                            <label for="foreign_office_tel">Foreign Office Tel</label>
                            <input type="text" class="form-control" id="foreign_office_tel" name="foreign_office_tel" placeholder="Phone number">
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <h6 class="text-white fw-bold mb-2">Port of Landing</h6>
                    </div>
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="port_name">Port Name</label>
                            <input type="text" class="form-control" id="port_name" name="port_name" placeholder="Port name">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="port_company">Company</label>
                            <input type="text" class="form-control" id="port_company" name="port_company" placeholder="Company">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="port_address">Address</label>
                            <input type="text" class="form-control" id="port_address" name="port_address" placeholder="Address">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="port_email">Port Email</label>
                            <input type="email" class="form-control" id="port_email" name="port_email" placeholder="Email">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="port_tel">Port Tel</label>
                            <input type="text" class="form-control" id="port_tel" name="port_tel" placeholder="Phone number">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="ship_departure_port">Departure Port</label>
                            <input type="text" class="form-control" id="ship_departure_port" name="ship_departure_port" placeholder="Departure port">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="ship_departure_date">Date of Departure</label>
                            <input type="date" class="form-control" id="ship_departure_date" name="ship_departure_date">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="ship_landing_date">Date of Landing</label>
                            <input type="date" class="form-control" id="ship_landing_date" name="ship_landing_date">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="custom_papers_status">Custom Papers</label>
                            <select class="form-select" id="custom_papers_status" name="custom_papers_status">
                                <option value="Complete">Complete</option>
                                <option value="Not Complete">Not Complete</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Customs Clearance Document Upload -->
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="field-group">
                            <label>Customs Clearance Certificate (Image / PDF)</label>
                            <div class="upload-zone" id="customsUploadZone">
                                <input type="file" name="customs_doc" id="customs_doc" accept="image/jpeg,image/png,image/jpg,application/pdf">
                                <div class="upload-preview-container" style="display:none;margin-bottom:0.75rem;">
                                    <img class="upload-preview" id="customsImagePreview" src="#" alt="Preview" style="display:none;margin: 0 auto;width: 120px;height: 90px;object-fit: cover;border-radius: 10px;border: 2px solid #10b981;">
                                    <div id="customsPdfPreview" style="display:none;color:#ef4444;font-size:2.5rem;margin: 0 auto 0.5rem;"><i class="fa-solid fa-file-pdf"></i></div>
                                </div>
                                <div class="upload-placeholder" id="customsPlaceholder">
                                    <i class="fa-solid fa-file-arrow-up fa-2x text-secondary mb-2 d-block"></i>
                                    <div class="fw-semibold text-secondary" style="font-size:0.9rem;">Click or drag &amp; drop to upload Customs document</div>
                                    <div class="text-muted mt-1" style="font-size:0.75rem;">JPG / PNG / PDF — max 5MB</div>
                                </div>
                                <div id="customsUploadFileName" class="mt-2 text-success fw-semibold" style="font-size:0.82rem;display:none;pointer-events:none;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <h6 class="text-white fw-bold mb-2">Custom Officers</h6>
                    </div>
                    <?php for ($i = 1; $i <= 3; $i++): ?>
                        <div class="col-md-4">
                            <div class="p-3" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:12px;">
                                <div class="fw-semibold mb-2">Custom Officer <?= $i ?></div>
                                <div class="field-group mb-2">
                                    <label for="custom_officer_<?= $i ?>_name">Name</label>
                                    <input type="text" class="form-control" id="custom_officer_<?= $i ?>_name" name="custom_officer_<?= $i ?>_name" placeholder="Name">
                                </div>
                                <div class="field-group mb-2">
                                    <label for="custom_officer_<?= $i ?>_rank">Rank</label>
                                    <input type="text" class="form-control" id="custom_officer_<?= $i ?>_rank" name="custom_officer_<?= $i ?>_rank" placeholder="Rank">
                                </div>
                                <div class="field-group mb-2">
                                    <label for="custom_officer_<?= $i ?>_address">Address</label>
                                    <input type="text" class="form-control" id="custom_officer_<?= $i ?>_address" name="custom_officer_<?= $i ?>_address" placeholder="Address">
                                </div>
                                <div class="field-group mb-2">
                                    <label for="custom_officer_<?= $i ?>_tel">Tel</label>
                                    <input type="text" class="form-control" id="custom_officer_<?= $i ?>_tel" name="custom_officer_<?= $i ?>_tel" placeholder="Phone">
                                </div>
                                <div class="field-group">
                                    <label for="custom_officer_<?= $i ?>_email">Email</label>
                                    <input type="email" class="form-control" id="custom_officer_<?= $i ?>_email" name="custom_officer_<?= $i ?>_email" placeholder="Email">
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>

                <div class="section-header" style="border-left-color:#0ea5e9;background:rgba(14,165,233,0.08);">
                    <div class="icon-box" style="background:rgba(14,165,233,0.15);color:#0ea5e9;"><i class="fa-solid fa-shield-halved"></i></div>
                    <div>
                        <h5>Security Verification</h5>
                        <p>Record police, DSS, and NIA verification officer details.</p>
                    </div>
                </div>

                <?php $securityGroups = ['police' => 3, 'dss' => 3, 'nia' => 3]; ?>
                <?php foreach ($securityGroups as $group => $count): ?>
                    <div class="row mt-3">
                        <div class="col-12 mb-2"><h6 class="text-white fw-semibold text-capitalize"><?= strtoupper($group) ?> Officers</h6></div>
                        <?php for ($i = 1; $i <= $count; $i++): ?>
                            <div class="col-md-4">
                                <div class="p-3" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);border-radius:12px;">
                                    <div class="fw-semibold mb-2"><?= strtoupper($group) ?> Officer <?= $i ?></div>
                                    <div class="field-group mb-2">
                                        <label for="<?= $group ?>_officer_<?= $i ?>_name">Name</label>
                                        <input type="text" class="form-control" id="<?= $group ?>_officer_<?= $i ?>_name" name="<?= $group ?>_officer_<?= $i ?>_name" placeholder="Name">
                                    </div>
                                    <div class="field-group mb-2">
                                        <label for="<?= $group ?>_officer_<?= $i ?>_rank">Rank</label>
                                        <input type="text" class="form-control" id="<?= $group ?>_officer_<?= $i ?>_rank" name="<?= $group ?>_officer_<?= $i ?>_rank" placeholder="Rank">
                                    </div>
                                    <div class="field-group mb-2">
                                        <label for="<?= $group ?>_officer_<?= $i ?>_address">Address</label>
                                        <input type="text" class="form-control" id="<?= $group ?>_officer_<?= $i ?>_address" name="<?= $group ?>_officer_<?= $i ?>_address" placeholder="Address">
                                    </div>
                                    <div class="field-group mb-2">
                                        <label for="<?= $group ?>_officer_<?= $i ?>_tel">Tel</label>
                                        <input type="text" class="form-control" id="<?= $group ?>_officer_<?= $i ?>_tel" name="<?= $group ?>_officer_<?= $i ?>_tel" placeholder="Phone">
                                    </div>
                                    <div class="field-group">
                                        <label for="<?= $group ?>_officer_<?= $i ?>_email">Email</label>
                                        <input type="email" class="form-control" id="<?= $group ?>_officer_<?= $i ?>_email" name="<?= $group ?>_officer_<?= $i ?>_email" placeholder="Email">
                                    </div>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                <?php endforeach; ?>

                <div class="section-header" style="border-left-color:#22c55e;background:rgba(34,197,94,0.08);">
                    <div class="icon-box" style="background:rgba(34,197,94,0.15);color:#22c55e;"><i class="fa-solid fa-money-bill-wave"></i></div>
                    <div>
                        <h5>Purchase & Identification</h5>
                        <p>Enter purchase details, insurance, identification, tax and clearance information.</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="purchase_date">Date of Purchase</label>
                            <input type="date" class="form-control" id="purchase_date" name="purchase_date">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="purchase_amount">Amount</label>
                            <input type="number" step="0.01" class="form-control" id="purchase_amount" name="purchase_amount" placeholder="0.00">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="means_of_identification">Means of Identification</label>
                            <select class="form-select" id="means_of_identification" name="means_of_identification[]" multiple>
                                <option value="National ID Card">National ID Card</option>
                                <option value="Voter Card">Voter Card</option>
                                <option value="Driving License">Driving License</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="insurance_cover">Insurance Cover</label>
                            <select class="form-select" id="insurance_cover" name="insurance_cover">
                                <option value="Comprehensive">Comprehensive</option>
                                <option value="Third Party">Third Party</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="number_plate_state">Number Plate State</label>
                            <input type="text" class="form-control" id="number_plate_state" name="number_plate_state" placeholder="State">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="number_plate_lga">Local Government</label>
                            <input type="text" class="form-control" id="number_plate_lga" name="number_plate_lga" placeholder="Local Government">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="field-group">
                            <label for="agent_name">Agent Name</label>
                            <input type="text" class="form-control" id="agent_name" name="agent_name" placeholder="Agent name">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="field-group">
                            <label for="agent_address">Agent Address</label>
                            <input type="text" class="form-control" id="agent_address" name="agent_address" placeholder="Agent address">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="agent_tel">Agent Tel</label>
                            <input type="text" class="form-control" id="agent_tel" name="agent_tel" placeholder="Phone number">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="agent_email">Agent Email</label>
                            <input type="email" class="form-control" id="agent_email" name="agent_email" placeholder="Email">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="tax_number">Tax Number</label>
                            <input type="text" class="form-control" id="tax_number" name="tax_number" placeholder="Tax number">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="vehicle_particulars_number">Vehicle Particulars Number</label>
                            <input type="text" class="form-control" id="vehicle_particulars_number" name="vehicle_particulars_number" placeholder="Particulars number">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="vehicle_particulars_purchase_date">Purchase Date</label>
                            <input type="date" class="form-control" id="vehicle_particulars_purchase_date" name="vehicle_particulars_purchase_date">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="vehicle_particulars_amount">Amount</label>
                            <input type="number" step="0.01" class="form-control" id="vehicle_particulars_amount" name="vehicle_particulars_amount" placeholder="Amount">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="vehicle_particulars_expiry_date">Expiry Date</label>
                            <input type="date" class="form-control" id="vehicle_particulars_expiry_date" name="vehicle_particulars_expiry_date">
                        </div>
                    </div>
                </div>

                <div class="section-header" style="border-left-color:#f97316;background:rgba(249,115,22,0.08);">
                    <div class="icon-box" style="background:rgba(249,115,22,0.15);color:#f97316;"><i class="fa-solid fa-gavel"></i></div>
                    <div>
                        <h5>Police Clearance & Driving License</h5>
                        <p>Capture the policing and license review details.</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="pol_clearance_name">Police Clearance Name</label>
                            <input type="text" class="form-control" id="pol_clearance_name" name="pol_clearance_name" placeholder="Name">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="pol_clearance_rank">Rank</label>
                            <input type="text" class="form-control" id="pol_clearance_rank" name="pol_clearance_rank" placeholder="Rank">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="pol_clearance_office_address">Office Address</label>
                            <input type="text" class="form-control" id="pol_clearance_office_address" name="pol_clearance_office_address" placeholder="Office Address">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="pol_clearance_local_govt">Local Government</label>
                            <input type="text" class="form-control" id="pol_clearance_local_govt" name="pol_clearance_local_govt" placeholder="Local Government">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="pol_clearance_state">State</label>
                            <input type="text" class="form-control" id="pol_clearance_state" name="pol_clearance_state" placeholder="State">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="pol_clearance_tel">Tel</label>
                            <input type="text" class="form-control" id="pol_clearance_tel" name="pol_clearance_tel" placeholder="Phone">
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="field-group">
                            <label for="pol_clearance_email">Email</label>
                            <input type="email" class="form-control" id="pol_clearance_email" name="pol_clearance_email" placeholder="Email">
                        </div>
                    </div>
                </div>

                <!-- Police Clearance Document Upload -->
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="field-group">
                            <label>Police Clearance Certificate (Image / PDF)</label>
                            <div class="upload-zone" id="policeUploadZone">
                                <input type="file" name="police_doc" id="police_doc" accept="image/jpeg,image/png,image/jpg,application/pdf">
                                <div class="upload-preview-container" style="display:none;margin-bottom:0.75rem;">
                                    <img class="upload-preview" id="policeImagePreview" src="#" alt="Preview" style="display:none;margin: 0 auto;width: 120px;height: 90px;object-fit: cover;border-radius: 10px;border: 2px solid #10b981;">
                                    <div id="policePdfPreview" style="display:none;color:#ef4444;font-size:2.5rem;margin: 0 auto 0.5rem;"><i class="fa-solid fa-file-pdf"></i></div>
                                </div>
                                <div class="upload-placeholder" id="policePlaceholder">
                                    <i class="fa-solid fa-file-arrow-up fa-2x text-secondary mb-2 d-block"></i>
                                    <div class="fw-semibold text-secondary" style="font-size:0.9rem;">Click or drag &amp; drop to upload Police document</div>
                                    <div class="text-muted mt-1" style="font-size:0.75rem;">JPG / PNG / PDF — max 5MB</div>
                                </div>
                                <div id="policeUploadFileName" class="mt-2 text-success fw-semibold" style="font-size:0.82rem;display:none;pointer-events:none;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="dl_name">Driving License Name</label>
                            <input type="text" class="form-control" id="dl_name" name="dl_name" placeholder="Name">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="dl_rank">Rank</label>
                            <input type="text" class="form-control" id="dl_rank" name="dl_rank" placeholder="Rank">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="dl_address">Address</label>
                            <input type="text" class="form-control" id="dl_address" name="dl_address" placeholder="Address">
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="dl_tel">Tel</label>
                            <input type="text" class="form-control" id="dl_tel" name="dl_tel" placeholder="Phone">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-group">
                            <label for="dl_email">Email</label>
                            <input type="email" class="form-control" id="dl_email" name="dl_email" placeholder="Email">
                        </div>
                    </div>
                </div>

                <!-- Photo Upload -->
                <div class="field-group mt-2">
                    <label>Vehicle Photo</label>
                    <div class="upload-zone" id="uploadZone">
                        <input type="file" name="vehicle_image" id="vehicle_image" accept="image/jpeg,image/png,image/jpg">
                        <img class="upload-preview" id="imagePreview" src="#" alt="Preview">
                        <div class="upload-placeholder">
                            <i class="fa-solid fa-cloud-arrow-up fa-2x text-secondary mb-2 d-block"></i>
                            <div class="fw-semibold text-secondary" style="font-size:0.9rem;">Click or drag &amp; drop to upload</div>
                            <div class="text-muted mt-1" style="font-size:0.75rem;">JPG / PNG — max 5MB</div>
                        </div>
                        <div id="uploadFileName" class="mt-2 text-success fw-semibold" style="font-size:0.82rem;display:none;pointer-events:none;"></div>
                    </div>
                </div>

                <div class="wizard-nav">
                    <button type="button" class="btn-wizard-prev" onclick="goStep(2)">
                        <i class="fa-solid fa-arrow-left me-2"></i>Back
                    </button>
                    <button type="button" class="btn-wizard-next" onclick="goStep(4)">
                        Payment <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════ -->
            <!-- STEP 4: PAYMENT                                    -->
            <!-- ═══════════════════════════════════════════════════ -->
            <div class="wizard-panel" id="step4">
                <div class="section-header">
                    <div class="icon-box"><i class="fa-solid fa-receipt"></i></div>
                    <div>
                        <h5>Onboarding Service Payment</h5>
                        <p>Collect and log the government vehicle registration service fee</p>
                    </div>
                </div>

                <!-- Fee Display -->
                <div class="fee-display-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fee-label">Registration Service Fee</div>
                            <div class="fee-amount">₦<?= number_format($onboardingFee, 2) ?></div>
                            <div class="text-secondary mt-1" style="font-size:0.78rem;">
                                <i class="fa-solid fa-circle-info me-1"></i>
                                Non-refundable government processing fee
                            </div>
                        </div>
                        <div class="text-end">
                            <div style="width:72px;height:72px;border-radius:16px;background:linear-gradient(135deg,rgba(16,185,129,0.2),rgba(59,130,246,0.1));display:flex;align-items:center;justify-content:center;">
                                <i class="fa-solid fa-naira-sign fa-2x text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="amount" id="paymentAmount" value="<?= htmlspecialchars($onboardingFee) ?>">

                <!-- Payment Method -->
                <div class="field-group">
                    <label>Select Payment Method <span class="req">*</span></label>
                    <div class="payment-method-cards">
                        <label class="payment-card-option selected" for="pay_cash">
                            <input type="radio" name="payment_method" id="pay_cash" value="CASH" checked>
                            <div class="pm-check"><i class="fa-solid fa-check" style="font-size:0.6rem;"></i></div>
                            <span class="pm-icon">💵</span>
                            <div class="pm-label">Cash Collection</div>
                        </label>
                        <label class="payment-card-option" for="pay_bank">
                            <input type="radio" name="payment_method" id="pay_bank" value="BANK_TRANSFER">
                            <div class="pm-check"><i class="fa-solid fa-check" style="font-size:0.6rem;"></i></div>
                            <span class="pm-icon">🏦</span>
                            <div class="pm-label">Bank Transfer</div>
                        </label>
                        <label class="payment-card-option<?= empty($paystackEnabled) ? ' disabled' : '' ?>" for="pay_paystack">
                            <input type="radio" name="payment_method" id="pay_paystack" value="PAYSTACK" <?= empty($paystackEnabled) ? 'disabled' : '' ?>>
                            <div class="pm-check"><i class="fa-solid fa-check" style="font-size:0.6rem;"></i></div>
                            <span class="pm-icon">⚡</span>
                            <div class="pm-label">Paystack Online</div>
                        </label>
                    </div>
                    <?php if (empty($paystackEnabled)): ?>
                        <div class="alert alert-warning bg-warning bg-opacity-10 text-white border-warning border-opacity-15 mt-3">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i>
                            Paystack is not configured. Please set Paystack Public and Secret keys in the admin settings to enable online payments.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Bank Transfer Info (hidden by default) -->
                <div id="paystackInfoBox" class="alert-glass alert-success mb-3" style="display:none;">
                    <i class="fa-solid fa-shield-halved fa-lg"></i>
                    <div>
                        <strong>Paystack Online Payment</strong><br>
                        <span style="font-size:0.82rem;">
                            Customers will be redirected to Paystack to complete payment securely. Once the transaction is confirmed, onboarding continues automatically.
                        </span>
                    </div>
                </div>
                <div id="bankInfoBox" class="alert-glass alert-info mb-3" style="display:none;">
                    <i class="fa-solid fa-building-columns fa-lg"></i>
                    <div>
                        <strong>Bank Transfer Details</strong><br>
                        <span style="font-size:0.82rem;">
                            Instruct the client to transfer to:<br>
                            <strong>Bank:</strong> <?= htmlspecialchars($settingModel->get('bank_name', 'Central Bank of Nigeria')) ?> &nbsp;|&nbsp;
                            <strong>Acct:</strong> <?= htmlspecialchars($settingModel->get('account_number', '1000000000')) ?> &nbsp;|&nbsp;
                            <strong>Name:</strong> <?= htmlspecialchars($settingModel->get('account_name', 'FGN Vehicle Registry')) ?>
                        </span>
                    </div>
                </div>

                <div class="field-group">
                    <label id="receiptLabel" for="receipt_number">
                        Cash Receipt / Reference Number
                    </label>
                    <div class="input-with-icon">
                        <i class="field-icon fa-solid fa-file-invoice"></i>
                        <input type="text" class="form-control" id="receipt_number" name="receipt_number"
                               placeholder="Enter receipt or reference number (optional — will be auto-generated if blank)">
                    </div>
                    <div class="mt-1" style="font-size:0.75rem;color:#64748b;">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        Leave blank to auto-generate a receipt number for this transaction.
                    </div>
                </div>

                <!-- Receipt Upload -->
                <div class="field-group mt-3">
                    <label>Payment Evidence Receipt (Image / PDF)</label>
                    <div class="upload-zone" id="receiptUploadZone">
                        <input type="file" name="receipt_file" id="receipt_file" accept="image/jpeg,image/png,image/jpg,application/pdf">
                        <div class="upload-preview-container" style="display:none;margin-bottom:0.75rem;">
                            <img class="upload-preview" id="receiptImagePreview" src="#" alt="Preview" style="display:none;margin: 0 auto;width: 120px;height: 90px;object-fit: cover;border-radius: 10px;border: 2px solid #10b981;">
                            <div id="receiptPdfPreview" style="display:none;color:#ef4444;font-size:2.5rem;margin: 0 auto 0.5rem;"><i class="fa-solid fa-file-pdf"></i></div>
                        </div>
                        <div class="upload-placeholder" id="receiptPlaceholder">
                            <i class="fa-solid fa-file-arrow-up fa-2x text-secondary mb-2 d-block"></i>
                            <div class="fw-semibold text-secondary" style="font-size:0.9rem;">Click or drag &amp; drop to upload receipt</div>
                            <div class="text-muted mt-1" style="font-size:0.75rem;">JPG / PNG / PDF — max 5MB</div>
                        </div>
                        <div id="receiptUploadFileName" class="mt-2 text-success fw-semibold" style="font-size:0.82rem;display:none;pointer-events:none;"></div>
                    </div>
                </div>

                <div class="wizard-nav">
                    <button type="button" class="btn-wizard-prev" onclick="goStep(3)">
                        <i class="fa-solid fa-arrow-left me-2"></i>Back
                    </button>
                    <button type="button" class="btn-wizard-next" onclick="goStep(5)">
                        Review &amp; Confirm <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════ -->
            <!-- STEP 5: REVIEW & SUBMIT                            -->
            <!-- ═══════════════════════════════════════════════════ -->
            <div class="wizard-panel" id="step5">
                <div class="section-header" style="border-left-color:#3b82f6;background:rgba(59,130,246,0.06);">
                    <div class="icon-box" style="background:rgba(59,130,246,0.15);color:#3b82f6;"><i class="fa-solid fa-check-double"></i></div>
                    <div>
                        <h5>Review &amp; Confirm Registration</h5>
                        <p>Verify all details before final submission to the national registry</p>
                    </div>
                </div>

                <div class="alert-glass alert-info mb-4">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>Please carefully review the information below. Once submitted, corrections require an administrative amendment request.</span>
                </div>

                <!-- Summary -->
                <div class="mb-4">
                    <div class="text-secondary fw-bold mb-2" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.07em;">
                        <i class="fa-solid fa-id-card me-1 text-success"></i>Vehicle Identification
                    </div>
                    <div class="summary-grid" id="summaryIdSection"></div>
                </div>

                <div class="mb-4">
                    <div class="text-secondary fw-bold mb-2" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.07em;">
                        <i class="fa-solid fa-sliders me-1 text-success"></i>Specifications
                    </div>
                    <div class="summary-grid" id="summarySpecSection"></div>
                </div>

                <div class="mb-4">
                    <div class="text-secondary fw-bold mb-2" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.07em;">
                        <i class="fa-solid fa-receipt me-1 text-success"></i>Payment
                    </div>
                    <div class="summary-grid" id="summaryPaySection"></div>
                </div>

                <!-- Declaration -->
                <div class="mb-4 p-3" style="background:rgba(239,68,68,0.05);border:1px solid rgba(239,68,68,0.15);border-radius:10px;">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="declarationCheck" required>
                        <label class="form-check-label text-secondary" for="declarationCheck" style="font-size:0.85rem;">
                            I, the registering officer, hereby certify that all information entered is accurate and has been verified against original documents presented by the vehicle owner. I understand that false declarations are punishable under Nigerian law.
                        </label>
                    </div>
                </div>

                <div class="wizard-nav">
                    <button type="button" class="btn-wizard-prev" onclick="goStep(4)">
                        <i class="fa-solid fa-arrow-left me-2"></i>Back
                    </button>
                    <button type="submit" class="btn-wizard-submit" id="submitBtn">
                        <i class="fa-solid fa-shield-halved"></i>
                        Submit to National Registry
                    </button>
                </div>
            </div>

        </form><!-- /form -->
    </div><!-- /glass-panel -->
</div><!-- /wizard-wrapper -->

<script>
/* ─── Wizard State ───────────────────────────────────────────────── */
let currentStep = 1;
const TOTAL_STEPS = 5;
const BASE_URL = '<?= rtrim(BASE_URL, '/') ?>';

// Owner data map built from PHP
const ownerData = {
    <?php foreach ($owners as $owner): ?>
    "<?= $owner['id'] ?>": {
        name: "<?= addslashes($owner['full_name']) ?>",
        nin:  "<?= addslashes($owner['nin'] ?? 'N/A') ?>",
        phone: "<?= addslashes($owner['phone']) ?>",
        email: "<?= addslashes($owner['email'] ?? '') ?>"
    },
    <?php endforeach; ?>
};

function goStep(step) {
    // Validate current step before advancing
    if (step > currentStep && !validateStep(currentStep)) return;

    document.getElementById('step' + currentStep).classList.remove('active');
    currentStep = step;
    document.getElementById('step' + currentStep).classList.add('active');

    // Update progress indicators
    updateProgress();

    // Build summary when reaching step 5
    if (step === 5) buildSummary();

    // Scroll to top of form
    document.querySelector('.wizard-wrapper').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function validateStep(step) {
    let valid = true;

    if (step === 1) {
        const owner = document.getElementById('owner_id').value;
        if (!owner) {
            shakeField('owner_id', 'Please select a vehicle owner before proceeding.');
            valid = false;
        }
    }

    if (step === 2) {
        const fields = ['vin', 'plate_number', 'engine_number', 'chassis_number'];
        for (const f of fields) {
            const el = document.getElementById(f);
            if (!el.value.trim()) {
                shakeField(f, `${el.previousElementSibling?.innerText || 'This field'} is required.`);
                valid = false;
                break;
            }
        }
        // VIN length check
        if (valid && document.getElementById('vin').value.trim().length !== 17) {
            shakeField('vin', 'VIN must be exactly 17 characters.');
            valid = false;
        }
    }

    if (step === 3) {
        const fields = ['manufacturer', 'model', 'year', 'color'];
        for (const f of fields) {
            const el = document.getElementById(f);
            if (!el.value.trim()) {
                shakeField(f, 'This field is required.');
                valid = false;
                break;
            }
        }
    }

    return valid;
}

function shakeField(id, msg) {
    const el = document.getElementById(id);
    el.style.borderColor = '#ef4444';
    el.style.boxShadow = '0 0 0 3px rgba(239,68,68,0.2)';
    el.focus();

    Swal.fire({
        toast: true, position: 'top-end', icon: 'warning',
        title: msg, showConfirmButton: false, timer: 3000,
        background: 'rgba(15,23,42,0.97)', color: '#f8fafc',
        timerProgressBar: true
    });

    setTimeout(() => {
        el.style.borderColor = '';
        el.style.boxShadow = '';
    }, 2500);
}

function updateProgress() {
    const steps = document.querySelectorAll('.wizard-step-item');
    steps.forEach((s, i) => {
        s.classList.remove('active', 'completed');
        const n = i + 1;
        if (n < currentStep)       s.classList.add('completed');
        else if (n === currentStep) s.classList.add('active');
    });

    // Update progress line
    const pct = ((currentStep - 1) / (TOTAL_STEPS - 1)) * 100;
    document.getElementById('progressLine').style.width = pct + '%';
}

/* ─── Owner Preview ──────────────────────────────────────────────── */
document.getElementById('owner_id').addEventListener('change', function () {
    const id = this.value;
    const card = document.getElementById('ownerPreviewCard');
    if (id && ownerData[id]) {
        const o = ownerData[id];
        document.getElementById('ownerInitial').textContent = o.name.charAt(0).toUpperCase();
        document.getElementById('ownerPreviewName').textContent = o.name;
        document.getElementById('ownerPreviewDetail').textContent = `NIN: ${o.nin}  ·  ${o.phone}`;
        card.style.display = 'block';
    } else {
        card.style.display = 'none';
    }
});

/* ─── VIN Validation ─────────────────────────────────────────────── */
document.getElementById('vin').addEventListener('input', function () {
    const val = this.value.trim().toUpperCase();
    const icon = document.getElementById('vinValidIcon');
    const box  = document.getElementById('vinInfo');
    box.classList.add('visible');

    if (val.length === 0) {
        icon.className = 'field-validate-icon';
    } else if (val.length === 17 && /^[A-HJ-NPR-Z0-9]{17}$/.test(val)) {
        icon.innerHTML  = '<i class="fa-solid fa-circle-check"></i>';
        icon.className  = 'field-validate-icon valid';
        box.innerHTML   = '<i class="fa-solid fa-circle-check me-1 text-success"></i><span class="text-success">Valid VIN format confirmed.</span>';
    } else {
        icon.innerHTML = '<i class="fa-solid fa-circle-xmark"></i>';
        icon.className = 'field-validate-icon invalid';
        box.innerHTML  = `<i class="fa-solid fa-circle-info me-1 text-info"></i>VIN must be 17 alphanumeric chars (no I, O, Q). Currently: <strong>${val.length}</strong>/17`;
    }
});

/* ─── Image Upload Preview ───────────────────────────────────────── */
document.getElementById('vehicle_image').addEventListener('change', function () {
    const file = this.files[0];
    const zone = document.getElementById('uploadZone');
    const preview = document.getElementById('imagePreview');
    const nameEl  = document.getElementById('uploadFileName');

    if (file) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            zone.classList.add('has-file');
            nameEl.textContent = '📎 ' + file.name;
            nameEl.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

// Drag & drop
const uploadZone = document.getElementById('uploadZone');
uploadZone.addEventListener('dragover', e => { e.preventDefault(); uploadZone.classList.add('dragover'); });
uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('dragover'));
uploadZone.addEventListener('drop', e => {
    e.preventDefault();
    uploadZone.classList.remove('dragover');
    const dt = e.dataTransfer;
    document.getElementById('vehicle_image').files = dt.files;
    document.getElementById('vehicle_image').dispatchEvent(new Event('change'));
});

/* ─── Receipt Upload Preview ───────────────────────────────────────── */
document.getElementById('receipt_file').addEventListener('change', function () {
    const file = this.files[0];
    const zone = document.getElementById('receiptUploadZone');
    const previewContainer = zone.querySelector('.upload-preview-container');
    const imgPreview = document.getElementById('receiptImagePreview');
    const pdfPreview = document.getElementById('receiptPdfPreview');
    const nameEl  = document.getElementById('receiptUploadFileName');

    if (file) {
        zone.classList.add('has-file');
        previewContainer.style.display = 'block';
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
        zone.classList.remove('has-file');
        previewContainer.style.display = 'none';
        nameEl.style.display = 'none';
    }
});

// Drag & drop for receipt
const receiptUploadZone = document.getElementById('receiptUploadZone');
receiptUploadZone.addEventListener('dragover', e => { e.preventDefault(); receiptUploadZone.classList.add('dragover'); });
receiptUploadZone.addEventListener('dragleave', () => receiptUploadZone.classList.remove('dragover'));
receiptUploadZone.addEventListener('drop', e => {
    e.preventDefault();
    receiptUploadZone.classList.remove('dragover');
    const dt = e.dataTransfer;
    document.getElementById('receipt_file').files = dt.files;
    document.getElementById('receipt_file').dispatchEvent(new Event('change'));
});

/* ─── Customs Upload Preview ───────────────────────────────────────── */
document.getElementById('customs_doc').addEventListener('change', function () {
    const file = this.files[0];
    const zone = document.getElementById('customsUploadZone');
    const previewContainer = zone.querySelector('.upload-preview-container');
    const imgPreview = document.getElementById('customsImagePreview');
    const pdfPreview = document.getElementById('customsPdfPreview');
    const nameEl  = document.getElementById('customsUploadFileName');

    if (file) {
        zone.classList.add('has-file');
        previewContainer.style.display = 'block';
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
        zone.classList.remove('has-file');
        previewContainer.style.display = 'none';
        nameEl.style.display = 'none';
    }
});

const customsUploadZone = document.getElementById('customsUploadZone');
customsUploadZone.addEventListener('dragover', e => { e.preventDefault(); customsUploadZone.classList.add('dragover'); });
customsUploadZone.addEventListener('dragleave', () => customsUploadZone.classList.remove('dragover'));
customsUploadZone.addEventListener('drop', e => {
    e.preventDefault();
    customsUploadZone.classList.remove('dragover');
    const dt = e.dataTransfer;
    document.getElementById('customs_doc').files = dt.files;
    document.getElementById('customs_doc').dispatchEvent(new Event('change'));
});

/* ─── Police Upload Preview ───────────────────────────────────────── */
document.getElementById('police_doc').addEventListener('change', function () {
    const file = this.files[0];
    const zone = document.getElementById('policeUploadZone');
    const previewContainer = zone.querySelector('.upload-preview-container');
    const imgPreview = document.getElementById('policeImagePreview');
    const pdfPreview = document.getElementById('policePdfPreview');
    const nameEl  = document.getElementById('policeUploadFileName');

    if (file) {
        zone.classList.add('has-file');
        previewContainer.style.display = 'block';
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
        zone.classList.remove('has-file');
        previewContainer.style.display = 'none';
        nameEl.style.display = 'none';
    }
});

const policeUploadZone = document.getElementById('policeUploadZone');
policeUploadZone.addEventListener('dragover', e => { e.preventDefault(); policeUploadZone.classList.add('dragover'); });
policeUploadZone.addEventListener('dragleave', () => policeUploadZone.classList.remove('dragover'));
policeUploadZone.addEventListener('drop', e => {
    e.preventDefault();
    policeUploadZone.classList.remove('dragover');
    const dt = e.dataTransfer;
    document.getElementById('police_doc').files = dt.files;
    document.getElementById('police_doc').dispatchEvent(new Event('change'));
});

/* ─── Payment Method Toggle ──────────────────────────────────────── */
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function () {
        document.querySelectorAll('.payment-card-option').forEach(c => c.classList.remove('selected'));
        this.closest('.payment-card-option').classList.add('selected');

        const bankBox = document.getElementById('bankInfoBox');
        const paystackBox = document.getElementById('paystackInfoBox');
        const recLabel = document.getElementById('receiptLabel');
        const recInput = document.getElementById('receipt_number');

        if (this.value === 'BANK_TRANSFER') {
            bankBox.style.display = 'flex';
            paystackBox.style.display = 'none';
            recLabel.textContent = 'Bank Transfer Reference Number';
            recInput.placeholder = 'Enter bank teller / transaction reference';
        } else if (this.value === 'PAYSTACK') {
            bankBox.style.display = 'none';
            paystackBox.style.display = 'flex';
            recLabel.textContent = 'Paystack transaction reference';
            recInput.placeholder = 'Auto-generated when Paystack payment completes';
        } else {
            bankBox.style.display = 'none';
            paystackBox.style.display = 'none';
            recLabel.textContent = 'Cash Receipt / Reference Number';
            recInput.placeholder = 'Enter receipt or reference number (optional)';
        }
    });
});

const vehicleRegForm = document.getElementById('vehicleRegForm');
if (vehicleRegForm) {
    vehicleRegForm.addEventListener('submit', function (event) {
        const selectedPayment = document.querySelector('input[name="payment_method"]:checked')?.value;
        if (selectedPayment === 'PAYSTACK') {
            event.preventDefault();
            startPaystackPayment();
        }
    });
}

async function startPaystackPayment() {
    const form = document.getElementById('vehicleRegForm');
    const formData = new FormData(form);
    formData.set('payment_method', 'PAYSTACK');

    if (!formData.get('csrf_token')) {
        Swal.fire({icon:'error',title:'Security token missing.',toast:true,position:'top-end',showConfirmButton:false,timer:3500});
        return;
    }

    const loading = Swal.fire({
        title: 'Preparing Paystack payment...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    try {
        const response = await fetch(BASE_URL + '/paystack/initialize', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (!result.status) {
            Swal.fire({icon:'error',title:'Paystack error',text:result.message || 'Unable to start payment.',toast:true,position:'top-end',showConfirmButton:false,timer:4000});
            return;
        }

        if (result.authorization_url) {
            window.location.href = result.authorization_url;
            return;
        }

        Swal.fire({icon:'error',title:'Paystack initialization failed',text:'Invalid response from payment provider.',toast:true,position:'top-end',showConfirmButton:false,timer:4000});
    } catch (err) {
        Swal.fire({icon:'error',title:'Network error',text:'Unable to connect to Paystack. Please try again.',toast:true,position:'top-end',showConfirmButton:false,timer:4000});
    }
}

/* ─── Summary Builder ────────────────────────────────────────────── */
function buildSummary() {
    const g = id => document.getElementById(id)?.value?.trim() || '—';
    const gSel = id => { const el = document.getElementById(id); return el?.options[el.selectedIndex]?.text || '—'; };

    const idData = [
        { label: 'VIN Number',       value: g('vin') },
        { label: 'Plate Number',     value: g('plate_number') },
        { label: 'Engine Number',    value: g('engine_number') },
        { label: 'Chassis Number',   value: g('chassis_number') },
    ];
    const specData = [
        { label: 'Manufacturer',  value: g('manufacturer') },
        { label: 'Model',         value: g('model') },
        { label: 'Year',          value: g('year') },
        { label: 'Color',         value: g('color') },
        { label: 'Fuel Type',     value: gSel('fuel_type') },
        { label: 'Transmission',  value: gSel('transmission') },
        { label: 'Category',      value: gSel('category') },
        { label: 'Class',         value: gSel('class') },
    ];
    const payData = [
        { label: 'Fee Amount',       value: '₦<?= number_format($onboardingFee, 2) ?>' },
        { label: 'Payment Method',   value: document.querySelector('input[name="payment_method"]:checked')?.value || '—' },
        { label: 'Receipt / Ref',    value: g('receipt_number') || '(Auto-generated)' },
        { label: 'Receipt File',     value: document.getElementById('receipt_file').files[0] ? document.getElementById('receipt_file').files[0].name : '(None)' },
        { label: 'Owner',            value: document.getElementById('owner_id').options[document.getElementById('owner_id').selectedIndex]?.text || '—' },
    ];

    renderSummary('summaryIdSection',   idData);
    renderSummary('summarySpecSection', specData);
    renderSummary('summaryPaySection',  payData);
}

function renderSummary(containerId, items) {
    const container = document.getElementById(containerId);
    container.innerHTML = items.map(item => `
        <div class="summary-item">
            <div class="s-label">${item.label}</div>
            <div class="s-value">${item.value}</div>
        </div>
    `).join('');
}

/* ─── Form Submission Guard ──────────────────────────────────────── */
document.getElementById('vehicleRegForm').addEventListener('submit', function (e) {
    const decl = document.getElementById('declarationCheck');
    if (!decl.checked) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning', title: 'Declaration Required',
            text: 'You must confirm the declaration before submitting.',
            background: 'rgba(15,23,42,0.97)', color: '#f8fafc',
            confirmButtonColor: '#10b981'
        });
        return;
    }
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Submitting to Registry…';
});

/* ─── Make step circles clickable to navigate back ──────────────── */
document.querySelectorAll('.wizard-step-item').forEach(item => {
    item.addEventListener('click', function () {
        const target = parseInt(this.dataset.step);
        if (target < currentStep) goStep(target);
    });
});

// Init
updateProgress();

/* ─── Real-Time Duplicate Vehicle Detection ──────────────────────── */
(function() {
    const BASE = '<?= BASE_URL ?>';
    const fieldsToCheck = [
        { input: 'vin',            field: 'vin',            label: 'VIN Number' },
        { input: 'plate_number',   field: 'plate_number',   label: 'Plate Number' },
        { input: 'engine_number',  field: 'engine_number',  label: 'Engine Number' },
        { input: 'chassis_number', field: 'chassis_number', label: 'Chassis Number' }
    ];

    let debounceTimers = {};

    fieldsToCheck.forEach(function(cfg) {
        const inputEl = document.getElementById(cfg.input);
        if (!inputEl) return;

        // Create warning element
        const warnEl = document.createElement('div');
        warnEl.className = 'duplicate-warn';
        warnEl.style.cssText = 'display:none; margin-top:6px; padding:8px 12px; border-radius:8px; font-size:0.8rem; font-weight:500; border-left:3px solid #ef4444; background:rgba(239,68,68,0.08); color:#fca5a5; animation: fadeSlideIn 0.3s ease;';
        inputEl.closest('.field-group')?.appendChild(warnEl);

        inputEl.addEventListener('input', function() {
            const val = inputEl.value.trim().toUpperCase();
            clearTimeout(debounceTimers[cfg.input]);

            if (val.length < 3) {
                warnEl.style.display = 'none';
                inputEl.style.borderColor = '';
                return;
            }

            debounceTimers[cfg.input] = setTimeout(function() {
                fetch(BASE + '/vehicle/checkDuplicate?field=' + cfg.field + '&value=' + encodeURIComponent(val))
                    .then(r => r.json())
                    .then(data => {
                        if (data.exists) {
                            warnEl.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-1" style="color:#ef4444;"></i> ' +
                                '<strong>Duplicate Found!</strong> A vehicle with this ' + cfg.label +
                                ' is already registered: <strong style="color:#f8fafc;">' +
                                data.vehicle.name + ' [' + data.vehicle.plate_number + ']</strong>';
                            warnEl.style.display = 'block';
                            inputEl.style.borderColor = '#ef4444';
                            inputEl.style.boxShadow = '0 0 0 0.2rem rgba(239,68,68,0.25)';
                        } else {
                            warnEl.style.display = 'none';
                            inputEl.style.borderColor = '#10b981';
                            inputEl.style.boxShadow = '0 0 0 0.2rem rgba(16,185,129,0.15)';
                        }
                    })
                    .catch(() => {
                        warnEl.style.display = 'none';
                    });
            }, 500);
        });

        // Reset styling on focus out
        inputEl.addEventListener('blur', function() {
            setTimeout(() => {
                if (warnEl.style.display === 'none') {
                    inputEl.style.borderColor = '';
                    inputEl.style.boxShadow = '';
                }
            }, 200);
        });
    });
})();
</script>
