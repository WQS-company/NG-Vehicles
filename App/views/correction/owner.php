<?php
// App/views/correction/owner.php
// Conditional display based on latestRequest status
$isUnlocked = ($latestRequest && $latestRequest['status'] === 'VERIFIED' && !$latestRequest['is_corrected']);
$isPending = ($latestRequest && $latestRequest['status'] === 'PENDING');
?>
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card glass-panel border-0 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary border-opacity-25 pb-3">
                <h4 class="text-white m-0"><i class="fa-solid fa-user-pen text-success me-2"></i>Correct Owner Profile Details</h4>
                <a href="<?= BASE_URL ?>/correction" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to List
                </a>
            </div>

            <?php if (isset($error) && $error): ?>
                <div class="alert alert-danger bg-danger bg-opacity-25 border-0 text-white mb-4"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (isset($success) && $success): ?>
                <div class="alert alert-success bg-success bg-opacity-25 border-0 text-white mb-4"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <!-- STATUS ALERTS AND PAYMENTS -->
            <?php if (!$latestRequest || $latestRequest['status'] === 'REJECTED' || $latestRequest['is_corrected']): ?>
                <!-- No active request, or last was rejected/completed -->
                <?php if ($latestRequest && $latestRequest['status'] === 'REJECTED'): ?>
                    <div class="alert alert-danger bg-danger bg-opacity-15 border-danger border-opacity-30 text-white mb-4">
                        <i class="fa-solid fa-circle-xmark me-2"></i>
                        <strong>Your previous correction request (Ref: <?= htmlspecialchars($latestRequest['receipt_number']) ?>) was rejected by the Super Admin.</strong> Please submit a new proof of payment.
                    </div>
                <?php endif; ?>

                <div class="alert alert-warning bg-warning bg-opacity-10 border-warning border-opacity-25 text-white mb-4 d-flex align-items-start gap-3">
                    <i class="fa-solid fa-lock fs-4 text-warning"></i>
                    <div>
                        <h6 class="text-warning fw-bold mb-1">Data Correction Locked</h6>
                        <span class="small">Modifying registered owner fields requires a data correction fee of <strong class="text-success">₦<?= number_format($correctionFee, 2) ?></strong>. Please submit payment details to the Super Admin below to unlock the editing fields.</span>
                    </div>
                </div>

                <!-- Submit Payment Proof Form -->
                <div class="card bg-black bg-opacity-30 border border-secondary border-opacity-20 p-4 mb-5 rounded-3">
                    <h5 class="text-white mb-3"><i class="fa-solid fa-file-invoice-dollar text-success me-2"></i>Submit Correction Fee Receipt</h5>
                    <form method="POST" action="<?= BASE_URL ?>/correction/owner/<?= $owner['id'] ?>" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <input type="hidden" name="action" value="submit_request">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="payment_method" class="form-label text-secondary">Payment Method</label>
                                <select class="form-select" id="payment_method" name="payment_method" required>
                                    <option value="CASH">CASH</option>
                                    <option value="BANK_TRANSFER">BANK_TRANSFER</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="receipt_number" class="form-label text-secondary">Receipt / Reference Number</label>
                                <input type="text" class="form-control font-monospace" id="receipt_number" name="receipt_number" placeholder="Enter receipt number or bank ref">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="receipt_file" class="form-label text-secondary">Upload Payment Receipt Proof (PDF / Image)</label>
                            <input type="file" class="form-control" id="receipt_file" name="receipt_file" accept="image/*,application/pdf" required>
                            <small class="text-muted">Max size: 5MB. PDF, JPG, PNG only.</small>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2.5">
                            <i class="fa-solid fa-upload me-2"></i> Submit Payment Proof for Verification
                        </button>
                    </form>
                </div>

            <?php elseif ($isPending): ?>
                <!-- Request is pending verification -->
                <div class="alert alert-warning bg-warning bg-opacity-15 border border-warning border-opacity-35 text-white mb-4 p-4 rounded-3 d-flex align-items-start gap-3">
                    <i class="fa-solid fa-circle-notch fa-spin fs-4 text-warning mt-1"></i>
                    <div>
                        <h6 class="text-warning fw-bold mb-1">Awaiting Payment Verification</h6>
                        <span class="small">A correction request has been submitted with reference number <strong class="font-monospace text-success"><?= htmlspecialchars($latestRequest['receipt_number']) ?></strong>. The fields will become editable as soon as the Super Admin verifies the payment.</span>
                    </div>
                </div>

            <?php elseif ($isUnlocked): ?>
                <!-- Request is verified and ready for edits -->
                <div class="alert alert-success bg-success bg-opacity-10 border border-success border-opacity-25 text-white mb-4 p-4 rounded-3 d-flex align-items-start gap-3 animate-fade-in">
                    <i class="fa-solid fa-circle-check fs-4 text-success mt-1"></i>
                    <div>
                        <h6 class="text-success fw-bold mb-1">Payment Verified & Fields Unlocked!</h6>
                        <span class="small">The Super Admin approved payment reference <strong class="font-monospace text-info"><?= htmlspecialchars($latestRequest['receipt_number']) ?></strong>. You can now modify the owner fields below and click "Save & Apply Verified Corrections".</span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Main Owner Editing Form -->
            <form method="POST" action="<?= BASE_URL ?>/correction/owner/<?= $owner['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="action" value="apply_correction">

                <!-- Section 1: Demographics -->
                <h5 class="text-white border-bottom border-secondary border-opacity-10 pb-2 mb-3">Personal Demographics</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="full_name" class="form-label text-secondary">Full Name (Surname First)</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" value="<?= htmlspecialchars($owner['full_name']) ?>" <?= !$isUnlocked ? 'disabled' : 'required' ?>>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="gender" class="form-label text-secondary">Gender</label>
                        <select class="form-select" id="gender" name="gender" <?= !$isUnlocked ? 'disabled' : '' ?>>
                            <option value="Male" <?= $owner['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= $owner['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                            <option value="Other" <?= $owner['gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="phone" class="form-label text-secondary">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($owner['phone']) ?>" <?= !$isUnlocked ? 'disabled' : 'required' ?>>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="email" class="form-label text-secondary">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($owner['email']) ?>" <?= !$isUnlocked ? 'disabled' : 'required' ?>>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="date_of_birth" class="form-label text-secondary">Date of Birth</label>
                        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?= htmlspecialchars($owner['date_of_birth']) ?>" <?= !$isUnlocked ? 'disabled' : 'required' ?>>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="nationality" class="form-label text-secondary">Nationality</label>
                        <input type="text" class="form-control" id="nationality" name="nationality" value="<?= htmlspecialchars($owner['nationality'] ?? 'Nigerian') ?>" <?= !$isUnlocked ? 'disabled' : 'required' ?>>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="occupation" class="form-label text-secondary">Occupation</label>
                        <input type="text" class="form-control" id="occupation" name="occupation" value="<?= htmlspecialchars($owner['occupation']) ?>" <?= !$isUnlocked ? 'disabled' : 'required' ?>>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="nin" class="form-label text-secondary">National Identity Number (NIN)</label>
                        <input type="text" class="form-control font-monospace" id="nin" name="nin" minlength="11" maxlength="11" value="<?= htmlspecialchars($owner['nin']) ?>" <?= !$isUnlocked ? 'disabled' : 'required' ?>>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="bvn" class="form-label text-secondary">Bank Verification Number (BVN) - Optional</label>
                    <input type="text" class="form-control font-monospace" id="bvn" name="bvn" minlength="11" maxlength="11" value="<?= htmlspecialchars($owner['bvn']) ?>" <?= !$isUnlocked ? 'disabled' : '' ?>>
                </div>

                <!-- Section 2: Contact Address Details -->
                <h5 class="text-white border-bottom border-secondary border-opacity-10 pb-2 mb-3 mt-4">Contact Address Details</h5>
                <div class="mb-3">
                    <label for="address" class="form-label text-secondary">Street Address</label>
                    <textarea class="form-control" id="address" name="address" <?= !$isUnlocked ? 'disabled' : 'required' ?>><?= htmlspecialchars($owner['address']) ?></textarea>
                </div>
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label for="state" class="form-label text-secondary">State of Residence</label>
                        <input type="text" class="form-control" id="state" name="state" value="<?= htmlspecialchars($owner['state']) ?>" <?= !$isUnlocked ? 'disabled' : 'required' ?>>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="lga" class="form-label text-secondary">LGA (Local Government Area)</label>
                        <input type="text" class="form-control" id="lga" name="lga" value="<?= htmlspecialchars($owner['lga']) ?>" <?= !$isUnlocked ? 'disabled' : 'required' ?>>
                    </div>
                </div>

                <button type="submit" class="btn btn-success w-100 py-3 mt-3 fw-bold" <?= !$isUnlocked ? 'disabled style="opacity:0.4; pointer-events:none;"' : '' ?>>
                    <i class="fa-solid fa-circle-check me-2"></i> Save &amp; Apply Verified Corrections
                </button>
            </form>
        </div>
    </div>
</div>
