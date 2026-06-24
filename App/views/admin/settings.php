<?php
// App/views/admin/settings.php
?>
<div class="row justify-content-center">
    <div class="col-lg-11">
        <div class="card glass-panel border-0 p-4">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
                <div>
                    <h4 class="text-white mb-1"><i class="fa-solid fa-gears text-success me-2"></i>Configure Platform Settings</h4>
                    <p class="text-secondary mb-0">Super Admins can manage the registration and owner form fields from the Form Manager.</p>
                </div>
                <a href="<?= BASE_URL ?>/admin/fields" class="btn btn-outline-light btn-sm">
                    <i class="fa-solid fa-list-check me-1"></i> Open Form Manager
                </a>
            </div>

            <?php if (isset($error) && $error): ?>
                <div class="alert alert-danger bg-danger bg-opacity-25 border-0 text-white mb-4"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (isset($success) && $success): ?>
                <div class="alert alert-success bg-success bg-opacity-25 border-0 text-white mb-4"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/admin/settings" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                <!-- Bootstrap Tabs Header -->
                <ul class="nav nav-tabs border-secondary border-opacity-20 mb-4" id="settingsTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-white-50 active fw-semibold" id="fees-tab" data-bs-toggle="tab" data-bs-target="#fees" type="button" role="tab" aria-controls="fees" aria-selected="true">
                            <i class="fa-solid fa-receipt me-1 text-success"></i> Fees &amp; Banking
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-white-50 fw-semibold" id="brand-tab" data-bs-toggle="tab" data-bs-target="#brand" type="button" role="tab" aria-controls="brand" aria-selected="false">
                            <i class="fa-solid fa-palette me-1 text-success"></i> Brand Identity
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-white-50 fw-semibold" id="mission-tab" data-bs-toggle="tab" data-bs-target="#mission" type="button" role="tab" aria-controls="mission" aria-selected="false">
                            <i class="fa-solid fa-bullseye me-1 text-success"></i> Mission &amp; Vision
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-white-50 fw-semibold" id="contacts-tab" data-bs-toggle="tab" data-bs-target="#contacts" type="button" role="tab" aria-controls="contacts" aria-selected="false">
                            <i class="fa-solid fa-envelope me-1 text-success"></i> Socials &amp; Support
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-white-50 fw-semibold" id="policies-tab" data-bs-toggle="tab" data-bs-target="#policies" type="button" role="tab" aria-controls="policies" aria-selected="false">
                            <i class="fa-solid fa-file-shield me-1 text-success"></i> Policies &amp; Terms
                        </button>
                    </li>
                </ul>

                <!-- Tabs Content -->
                <div class="tab-content text-start" id="settingsTabContent">
                    
                    <!-- TAB 1: FEES & BANKING -->
                    <div class="tab-pane fade show active" id="fees" role="tabpanel" aria-labelledby="fees-tab">
                        <h5 class="text-white mb-3">Service Charges</h5>
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label for="onboarding_fee" class="form-label text-secondary">Vehicle Onboarding Fee (₦)</label>
                                <input type="number" step="0.01" class="form-control font-monospace" id="onboarding_fee" name="onboarding_fee" value="<?= htmlspecialchars($settings['onboarding_fee'] ?? '0.00') ?>" required>
                                <small class="text-muted">Federal service charge required for registering a new vehicle.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="correction_fee" class="form-label text-secondary">Data Correction Fee (₦)</label>
                                <input type="number" step="0.01" class="form-control font-monospace" id="correction_fee" name="correction_fee" value="<?= htmlspecialchars($settings['correction_fee'] ?? '0.00') ?>" required>
                                <small class="text-muted">Service charge for correcting owner or vehicle information.</small>
                            </div>
                        </div>

                        <h5 class="text-white border-top border-secondary border-opacity-10 pt-3 mb-3">Bank Escrow Account Configurations</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="bank_name" class="form-label text-secondary">Bank Name</label>
                                <input type="text" class="form-control" id="bank_name" name="bank_name" value="<?= htmlspecialchars($settings['bank_name'] ?? '') ?>" placeholder="e.g. Central Bank of Nigeria" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="account_number" class="form-label text-secondary">Account Number</label>
                                <input type="text" class="form-control font-monospace" id="account_number" name="account_number" value="<?= htmlspecialchars($settings['account_number'] ?? '') ?>" placeholder="e.g. 1012345678" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="account_name" class="form-label text-secondary">Account Name</label>
                            <input type="text" class="form-control" id="account_name" name="account_name" value="<?= htmlspecialchars($settings['account_name'] ?? '') ?>" placeholder="e.g. FGN Vehicle Registry Escrow Account" required>
                        </div>

                        <h5 class="text-white border-top border-secondary border-opacity-10 pt-3 mb-3">Paystack Gateway</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="paystack_public_key" class="form-label text-secondary">Paystack Public Key</label>
                                <input type="text" class="form-control font-monospace" id="paystack_public_key" name="paystack_public_key" value="<?= htmlspecialchars($settings['paystack_public_key'] ?? '') ?>" placeholder="Enter Paystack public key">
                                <small class="text-muted">Public key is used by front-end checkout and can be safely stored for registration flows.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="paystack_secret_key" class="form-label text-secondary">Paystack Secret Key</label>
                                <input type="password" class="form-control font-monospace" id="paystack_secret_key" name="paystack_secret_key" value="" placeholder="Enter Paystack secret key">
                                <small class="text-muted">Secret key is kept secure and only used server-side. Leave blank if you do not wish to change the stored value.</small>
                            </div>
                        </div>
                        <?php if (!empty($settings['paystack_secret_key'])): ?>
                            <div class="alert alert-info bg-info bg-opacity-10 border-info border-opacity-20 text-white mb-4">
                                <strong>Paystack is configured.</strong> Online payments can now be accepted through the registration workflow.
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- TAB 2: BRAND IDENTITY -->
                    <div class="tab-pane fade" id="brand" role="tabpanel" aria-labelledby="brand-tab">
                        <h5 class="text-white mb-3">Platform Identity &amp; Assets</h5>
                        <div class="mb-4">
                            <label for="platform_title" class="form-label text-secondary">Platform Display Title</label>
                            <input type="text" class="form-control" id="platform_title" name="platform_title" value="<?= htmlspecialchars($settings['platform_title'] ?? 'National Vehicle Ownership & Traceability System') ?>" required>
                            <small class="text-muted">Will be shown in browser tabs, titles, and legal certificates.</small>
                        </div>

                        <div class="row">
                            <!-- Logo Upload -->
                            <div class="col-md-6 mb-3">
                                <label for="platform_logo" class="form-label text-secondary">Upload Custom Logo (PNG/JPG/SVG)</label>
                                <input type="file" class="form-control" id="platform_logo" name="platform_logo" accept="image/png,image/jpeg,image/svg+xml">
                                <small class="text-muted">Replaces the default NVOTS shield icon in headers. Recommended: transparent PNG.</small>
                                
                                <?php if (!empty($settings['platform_logo'])): ?>
                                    <div class="mt-3 p-3 bg-black bg-opacity-20 rounded border border-secondary border-opacity-10 d-inline-block">
                                        <div class="text-secondary small mb-2">Current Logo:</div>
                                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($settings['platform_logo']) ?>" alt="Logo Preview" style="max-height: 48px; object-fit: contain;">
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Favicon Upload -->
                            <div class="col-md-6 mb-3">
                                <label for="platform_favicon" class="form-label text-secondary">Upload Custom Favicon (ICO/PNG)</label>
                                <input type="file" class="form-control" id="platform_favicon" name="platform_favicon" accept="image/x-icon,image/png">
                                <small class="text-muted">Replaces the browser tab icon. Recommended: 32x32px or 16x16px size.</small>
                                
                                <?php if (!empty($settings['platform_favicon'])): ?>
                                    <div class="mt-3 p-3 bg-black bg-opacity-20 rounded border border-secondary border-opacity-10 d-inline-block">
                                        <div class="text-secondary small mb-2">Current Favicon:</div>
                                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($settings['platform_favicon']) ?>" alt="Favicon Preview" style="max-height: 32px; width: 32px; object-fit: contain;">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: MISSION & VISION -->
                    <div class="tab-pane fade" id="mission" role="tabpanel" aria-labelledby="mission-tab">
                        <h5 class="text-white mb-3">Strategic Platform Goals</h5>
                        <div class="mb-3">
                            <label for="mission_text" class="form-label text-secondary">Mission Statement</label>
                            <textarea class="form-control" id="mission_text" name="mission" rows="4" placeholder="To secure Nigeria's roads and ensure vehicle traceability through state-of-the-art cryptography..."><?= htmlspecialchars($settings['mission'] ?? '') ?></textarea>
                            <small class="text-muted">Displayed prominently on the platform public landing page.</small>
                        </div>
                        <div class="mb-3">
                            <label for="vision_text" class="form-label text-secondary">Vision Statement</label>
                            <textarea class="form-control" id="vision_text" name="vision" rows="4" placeholder="A safe, fully digitalised national transportation ecosystem with real-time tracking transparency..."><?= htmlspecialchars($settings['vision'] ?? '') ?></textarea>
                            <small class="text-muted">Displayed prominently on the platform public landing page.</small>
                        </div>
                    </div>

                    <!-- TAB 4: SOCIALS & SUPPORT -->
                    <div class="tab-pane fade" id="contacts" role="tabpanel" aria-labelledby="contacts-tab">
                        <h5 class="text-white mb-3">Support Contacts</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="contact_email" class="form-label text-secondary">Support Email Address</label>
                                <input type="email" class="form-control" id="contact_email" name="footer_contact_email" value="<?= htmlspecialchars($settings['footer_contact_email'] ?? 'support@nvots.gov.ng') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="contact_phone" class="form-label text-secondary">Support Phone Number</label>
                                <input type="text" class="form-control" id="contact_phone" name="footer_contact_phone" value="<?= htmlspecialchars($settings['footer_contact_phone'] ?? '+234 (0) 9 461 6000') ?>" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="contact_address" class="form-label text-secondary">Physical Office Address</label>
                            <textarea class="form-control" id="contact_address" name="footer_contact_address" rows="2" required><?= htmlspecialchars($settings['footer_contact_address'] ?? 'Federal Secretariat Complex, Shehu Shagari Way, Abuja') ?></textarea>
                        </div>

                        <h5 class="text-white border-top border-secondary border-opacity-10 pt-3 mb-3">Official Communication Handles</h5>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="social_twitter" class="form-label text-secondary"><i class="fa-brands fa-twitter text-info me-1"></i> Twitter / X Link</label>
                                <input type="text" class="form-control font-monospace" id="social_twitter" name="social_twitter" value="<?= htmlspecialchars($settings['social_twitter'] ?? '#') ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="social_facebook" class="form-label text-secondary"><i class="fa-brands fa-facebook text-primary me-1"></i> Facebook Page Link</label>
                                <input type="text" class="form-control font-monospace" id="social_facebook" name="social_facebook" value="<?= htmlspecialchars($settings['social_facebook'] ?? '#') ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="social_instagram" class="form-label text-secondary"><i class="fa-brands fa-instagram text-danger me-1"></i> Instagram Profile Link</label>
                                <input type="text" class="form-control font-monospace" id="social_instagram" name="social_instagram" value="<?= htmlspecialchars($settings['social_instagram'] ?? '#') ?>">
                            </div>
                        </div>
                    </div>

                    <!-- TAB 5: POLICIES & TERMS -->
                    <div class="tab-pane fade" id="policies" role="tabpanel" aria-labelledby="policies-tab">
                        <h5 class="text-white mb-3">Legal &amp; Policy Agreements</h5>
                        <div class="mb-4">
                            <label for="privacy_policy" class="form-label text-secondary fw-semibold">Privacy Policy Statement</label>
                            <textarea class="form-control font-monospace text-white-50" id="privacy_policy" name="privacy_policy" rows="12" style="font-size: 13px; line-height: 1.6; background: rgba(0,0,0,0.25);" placeholder="Enter Privacy Policy terms..."><?= htmlspecialchars($settings['privacy_policy'] ?? '') ?></textarea>
                            <small class="text-muted">Enter the complete Privacy Policy text. Will be formatted and visible to the public.</small>
                        </div>
                        <div class="mb-3">
                            <label for="terms_conditions" class="form-label text-secondary fw-semibold">Terms &amp; Conditions Statement</label>
                            <textarea class="form-control font-monospace text-white-50" id="terms_conditions" name="terms_conditions" rows="12" style="font-size: 13px; line-height: 1.6; background: rgba(0,0,0,0.25);" placeholder="Enter Terms & Conditions..."><?= htmlspecialchars($settings['terms_conditions'] ?? '') ?></textarea>
                            <small class="text-muted">Enter the complete Terms &amp; Conditions text. Will be formatted and visible to the public.</small>
                        </div>
                    </div>

                </div>

                <button type="submit" class="btn btn-primary w-100 py-3 mt-4">
                    <i class="fa-solid fa-circle-check me-2"></i> Save Configurations
                </button>
            </form>
        </div>
    </div>
</div>

<style>
/* CSS adjustment for tabs layout */
.nav-tabs .nav-link {
    border: none;
    border-bottom: 2px solid transparent;
    transition: all 0.2s ease;
}
.nav-tabs .nav-link:hover {
    color: #fff !important;
    border-bottom-color: rgba(25, 135, 84, 0.4);
}
.nav-tabs .nav-link.active {
    background: transparent !important;
    color: #198754 !important;
    border-bottom: 2px solid #198754;
}
</style>
