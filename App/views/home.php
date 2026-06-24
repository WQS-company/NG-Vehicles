<?php 
$isLanding = true; 
// App/views/home.php
use App\Models\Setting;

$settingModel = new Setting();
$platformSettings = $settingModel->getAllSettings();

$platformLogo = !empty($platformSettings['platform_logo']) ? $platformSettings['platform_logo'] : '';
$footerContactAddress = !empty($platformSettings['footer_contact_address']) ? $platformSettings['footer_contact_address'] : 'Federal Secretariat Complex, Shehu Shagari Way, Central Business District, Abuja, Nigeria';
$footerContactPhone = !empty($platformSettings['footer_contact_phone']) ? $platformSettings['footer_contact_phone'] : '+234 (0) 9 461 6000';
$footerContactEmail = !empty($platformSettings['footer_contact_email']) ? $platformSettings['footer_contact_email'] : 'support@nvots.gov.ng';
$socialTwitter = !empty($platformSettings['social_twitter']) ? $platformSettings['social_twitter'] : '#';
$socialFacebook = !empty($platformSettings['social_facebook']) ? $platformSettings['social_facebook'] : '#';
$socialInstagram = !empty($platformSettings['social_instagram']) ? $platformSettings['social_instagram'] : '#';

// Safe fallbacks for database counters
$vehiclesCount = $vehiclesCount ?? 0;
$verificationsCount = $verificationsCount ?? 0;
$statesCount = $statesCount ?? 0;
$accuracy = $accuracy ?? 100.00;
?>
<div class="landing-page-wrapper">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg landing-nav py-3">
        <div class="container">
            <a class="navbar-brand text-white fw-bold d-flex align-items-center gap-2" href="<?= BASE_URL ?>/">
                <?php if (!empty($platformLogo)): ?>
                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($platformLogo) ?>" alt="Logo" style="max-height: 38px; width: auto; object-fit: contain;">
                <?php else: ?>
                    <i class="fa-solid fa-shield-halved text-success fa-lg"></i>
                <?php endif; ?>
                <span style="letter-spacing: 0.05em;">NVOTS <span class="text-success text-muted-small">NIGERIA</span></span>
            </a>
            
            <div class="d-flex align-items-center gap-2 ms-auto">
                <a href="<?= BASE_URL ?>/search" class="btn btn-outline-light btn-sm d-none d-sm-inline-flex align-items-center gap-2">
                    <i class="fa-solid fa-magnifying-glass"></i> Public Query
                </a>
                <a href="<?= BASE_URL ?>/auth/login" class="btn btn-success btn-sm px-4 fw-bold shadow-sm d-flex align-items-center gap-2">
                    <i class="fa-solid fa-right-to-bracket"></i> Officer Login
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero-section py-5 position-relative overflow-hidden" id="hero" data-scroll-reveal>
        <div class="container py-lg-4">
            <div class="row align-items-center g-5">
                <!-- Hero Left -->
                <div class="col-lg-6 text-center text-lg-start">
                    <div class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-30 mb-3 px-3 py-2 rounded-pill fs-7 fw-semibold">
                        🇳🇬 Federal Vehicle Traceability Platform
                    </div>
                    <h1 class="display-4 fw-extrabold text-white mb-3 lh-sm">
                        Securing Nigeria's Roads via <span class="text-gradient">Immutable Ledger Traceability</span>
                    </h1>
                    <p class="lead text-secondary mb-4 fs-6" style="max-width: 540px; margin: 0 auto; margin-left: 0;">
                        The National Vehicle Ownership &amp; Traceability System (NVOTS) is a cryptographically secured registry mapping biometric ownership, real-time plate authentication, and complete vehicle lifecycles.
                    </p>
                    
                    <!-- Quick Actions -->
                    <div class="d-flex flex-wrap justify-content-center justify-content-lg-start gap-3 mb-5" id="hero-cta">
                        <a href="<?= BASE_URL ?>/auth/login" class="btn btn-primary btn-lg px-4 fs-6 shadow d-flex align-items-center gap-2">
                            <i class="fa-solid fa-gauge"></i> Enter Registry Portal
                        </a>
                        <a href="#lookup-widget" class="btn btn-outline-light btn-lg px-4 fs-6 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-barcode"></i> Verify License / VIN
                        </a>
                    </div>

                    <!-- Small Badges -->
                    <div class="d-flex justify-content-center justify-content-lg-start align-items-center gap-4 text-muted small">
                        <div><i class="fa-solid fa-lock text-success me-1"></i> SSL Protected</div>
                        <div><i class="fa-solid fa-shield-halved text-success me-1"></i> Biometric Verified</div>
                        <div><i class="fa-solid fa-database text-success me-1"></i> Real-time Sync</div>
                    </div>
                </div>

                <!-- Hero Right (Illustration) -->
                <div class="col-lg-6">
                    <div class="illustration-container text-center">
                        <div class="floating-wrapper">
                            <img src="<?= BASE_URL ?>/public/images/landing_illustration.png" alt="NVOTS Security Hub" class="img-fluid hero-illustration rounded-4 shadow-lg border border-secondary border-opacity-20">
                            <div class="illustration-glow"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Stats Bar -->
    <section class="stats-section py-4 my-3" data-scroll-reveal>
        <div class="container">
            <div class="glass-panel p-4 py-3">
                <div class="row text-center g-4">
                    <div class="col-md-3 col-6 border-end border-secondary border-opacity-20">
                        <h3 class="fw-bold text-success mb-0" data-counter><?= number_format($vehiclesCount) ?></h3>
                        <div class="text-white-50 small uppercase mt-1">Vehicles Tracked</div>
                    </div>
                    <div class="col-md-3 col-6 border-md-end border-secondary border-opacity-20">
                        <h3 class="fw-bold text-success mb-0" data-counter><?= number_format($accuracy, 2) ?>%</h3>
                        <div class="text-white-50 small uppercase mt-1">Trace Accuracy</div>
                    </div>
                    <div class="col-md-3 col-6 border-end border-secondary border-opacity-20">
                        <h3 class="fw-bold text-success mb-0" data-counter><?= number_format($verificationsCount) ?></h3>
                        <div class="text-white-50 small uppercase mt-1">Daily Verifications</div>
                    </div>
                    <div class="col-md-3 col-6">
                        <h3 class="fw-bold text-success mb-0" data-counter><?= number_format($statesCount) ?></h3>
                        <div class="text-white-50 small uppercase mt-1">States Synchronized</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & Vision Section -->
    <?php if (!empty($missionText) || !empty($visionText)): ?>
    <section class="mission-vision-section py-5 my-2" data-scroll-reveal>
        <div class="container">
            <div class="row g-4 justify-content-center">
                <?php if (!empty($missionText)): ?>
                <div class="col-md-6">
                    <div class="card glass-panel border-0 p-4 h-100 position-relative overflow-hidden">
                        <div class="position-absolute top-0 end-0 p-3 opacity-10" style="pointer-events: none;">
                            <i class="fa-solid fa-bullseye fa-4x text-success"></i>
                        </div>
                        <h4 class="text-white fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-bullseye text-success"></i> Our Mission
                        </h4>
                        <p class="text-secondary small mb-0 lh-lg" style="text-align: justify;">
                            <?= nl2br(htmlspecialchars($missionText)) ?>
                        </p>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (!empty($visionText)): ?>
                <div class="col-md-6">
                    <div class="card glass-panel border-0 p-4 h-100 position-relative overflow-hidden">
                        <div class="position-absolute top-0 end-0 p-3 opacity-10" style="pointer-events: none;">
                            <i class="fa-solid fa-eye fa-4x text-success"></i>
                        </div>
                        <h4 class="text-white fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-eye text-success"></i> Our Vision
                        </h4>
                        <p class="text-secondary small mb-0 lh-lg" style="text-align: justify;">
                            <?= nl2br(htmlspecialchars($visionText)) ?>
                        </p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Quick Public Lookup Widget -->
    <section class="lookup-section py-5" id="lookup-widget" data-scroll-reveal>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="glass-panel p-4 p-md-5 text-center position-relative overflow-hidden">
                        <div class="bg-glow"></div>
                        <h3 class="text-white fw-bold mb-2">Instant Public Registry Lookup</h3>
                        <p class="text-secondary small mb-4">Conduct an audit trace queries immediately via Vehicle Plate or VIN code.</p>
                        
                        <form method="GET" action="<?= BASE_URL ?>/search" class="lookup-form">
                            <div class="input-group input-group-lg shadow-sm">
                                <input type="text" class="form-control bg-dark border-secondary border-opacity-50 text-white placeholder-muted font-monospace" name="q" placeholder="Enter VIN (17 chars) or Plate (e.g. LAG-234AA)..." required style="letter-spacing: 0.05em;">
                                <button type="submit" class="btn btn-success px-4"><i class="fa-solid fa-magnifying-glass me-2"></i>Trace</button>
                            </div>
                        </form>
                        <div class="mt-3 text-muted small text-start d-flex flex-wrap gap-3 justify-content-center">
                            <span><b>Example Plates:</b> LAG-123AB, FCT-999ZZ</span>
                            <span><b>Example VINs:</b> 1HGCM82633A004352</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Core Features Grid -->
    <section class="features-section py-5" data-scroll-reveal>
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="text-white fw-bold mb-2">State-of-the-Art Protection Services</h2>
                <p class="text-secondary small" style="max-width: 500px; margin: 0 auto;">A full suite of digital tracking assets keeping Nigerian vehicle lifecycles secure and authenticated.</p>
            </div>
            
            <div class="row g-4">
                <!-- Feature 1 -->
                <div class="col-md-6 col-lg-3">
                    <div class="card glass-panel border-0 p-4 h-100 text-center text-md-start">
                        <div class="feature-icon-box mb-3 text-success">
                            <i class="fa-solid fa-shield-halved fa-2x"></i>
                        </div>
                        <h5 class="text-white fw-bold mb-2">Biometric Verification</h5>
                        <p class="text-secondary small mb-0">Live webcam face captures and digital signature drawing pads mapped to legal owners profile records.</p>
                    </div>
                </div>

                <!-- Feature 2 -->
                <div class="col-md-6 col-lg-3">
                    <div class="card glass-panel border-0 p-4 h-100 text-center text-md-start">
                        <div class="feature-icon-box mb-3 text-success">
                            <i class="fa-solid fa-link fa-2x"></i>
                        </div>
                        <h5 class="text-white fw-bold mb-2">Ownership Timeline</h5>
                        <p class="text-secondary small mb-0">Every vehicle purchase, transfer, and registration logged inside an immutable ownership chain database ledger.</p>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="col-md-6 col-lg-3">
                    <div class="card glass-panel border-0 p-4 h-100 text-center text-md-start">
                        <div class="feature-icon-box mb-3 text-success">
                            <i class="fa-solid fa-qrcode fa-2x"></i>
                        </div>
                        <h5 class="text-white fw-bold mb-2">Printable Certificates</h5>
                        <p class="text-secondary small mb-0">High-resolution verification documents complete with dynamic validation QR codes pointing to public traces.</p>
                    </div>
                </div>

                <!-- Feature 4 -->
                <div class="col-md-6 col-lg-3">
                    <div class="card glass-panel border-0 p-4 h-100 text-center text-md-start">
                        <div class="feature-icon-box mb-3 text-success">
                            <i class="fa-solid fa-receipt fa-2x"></i>
                        </div>
                        <h5 class="text-white fw-bold mb-2">Audited Revenue</h5>
                        <p class="text-secondary small mb-0">Automated government onboarding fee tracking, receipt number generation, and receipt image/PDF storage.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works-section py-5" data-scroll-reveal>
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="text-white fw-bold mb-3">How It Works</h2>
                <p class="text-secondary small" style="max-width: 600px; margin: 0 auto;">Three simple steps to get started with NVOTS.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4 text-center">
                    <div class="feature-icon-box mb-3" style="background: var(--secondary-color);">
                        <i class="fa-solid fa-id-card fa-2x" style="color:#fff;"></i>
                    </div>
                    <h5 class="text-white fw-bold">Register Vehicle</h5>
                    <p class="text-secondary small">Provide ownership documents and biometric data to register a vehicle securely.</p>
                </div>
                <div class="col-md-4 text-center">
                    <div class="feature-icon-box mb-3" style="background: var(--secondary-color);">
                        <i class="fa-solid fa-magnifying-glass fa-2x" style="color:#fff;"></i>
                    </div>
                    <h5 class="text-white fw-bold">Verify &amp; Trace</h5>
                    <p class="text-secondary small">Instantly verify plates or VINs using the public lookup tool.</p>
                </div>
                <div class="col-md-4 text-center">
                    <div class="feature-icon-box mb-3" style="background: var(--secondary-color);">
                        <i class="fa-solid fa-receipt fa-2x" style="color:#fff;"></i>
                    </div>
                    <h5 class="text-white fw-bold">Audit &amp; Report</h5>
                    <p class="text-secondary small">Generate certified reports and audit trails for compliance and enforcement.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="landing-footer pt-5 pb-4 border-top border-secondary border-opacity-10 mt-5 bg-black bg-opacity-25">
        <div class="container">
            <div class="row g-4 text-start">
                <div class="col-lg-4 mb-3">
                    <a class="text-white fw-bold d-flex align-items-center gap-2 mb-3 text-decoration-none" href="<?= BASE_URL ?>/">
                        <?php if (!empty($platformLogo)): ?>
                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($platformLogo) ?>" alt="Logo" style="max-height: 32px; width: auto; object-fit: contain;">
                        <?php else: ?>
                            <i class="fa-solid fa-shield-halved text-success fa-lg"></i>
                        <?php endif; ?>
                        <span style="letter-spacing: 0.05em; font-size: 16px;">NVOTS <span class="text-success small">NIGERIA</span></span>
                    </a>
                    <p class="text-secondary small mb-0 lh-lg" style="font-size: 12px;">
                        <?= htmlspecialchars($title ?? (defined('APP_TITLE') ? APP_TITLE : 'NVOTS')) ?>. Immutably mapping biometric registries, vehicle histories, and plate details across all Nigerian federation states.
                    </p>
                </div>
                
                <div class="col-md-4 col-lg-2 mb-3">
                    <h6 class="text-white fw-bold mb-3 small text-uppercase" style="letter-spacing: 1px;">Quick Links</h6>
                    <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                        <li><a href="<?= BASE_URL ?>/search" class="text-secondary text-decoration-none hover-white"><i class="fa-solid fa-angle-right me-1 text-success"></i> Public Query</a></li>
                        <li><a href="<?= BASE_URL ?>/auth/login" class="text-secondary text-decoration-none hover-white"><i class="fa-solid fa-angle-right me-1 text-success"></i> Officer Login</a></li>
                        <li><a href="<?= BASE_URL ?>/home/privacy" class="text-secondary text-decoration-none hover-white"><i class="fa-solid fa-angle-right me-1 text-success"></i> Privacy Policy</a></li>
                        <li><a href="<?= BASE_URL ?>/home/terms" class="text-secondary text-decoration-none hover-white"><i class="fa-solid fa-angle-right me-1 text-success"></i> Terms &amp; Conditions</a></li>
                    </ul>
                </div>
                
                <div class="col-md-4 col-lg-3 mb-3">
                    <h6 class="text-white fw-bold mb-3 small text-uppercase" style="letter-spacing: 1px;">Contact Support</h6>
                    <ul class="list-unstyled small d-flex flex-column gap-2 mb-0 text-secondary">
                        <li class="d-flex align-items-start gap-2">
                            <i class="fa-solid fa-envelope text-success mt-1"></i>
                            <span style="font-size: 12px;"><?= htmlspecialchars($footerContactEmail) ?></span>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <i class="fa-solid fa-phone text-success mt-1"></i>
                            <span style="font-size: 12px;"><?= htmlspecialchars($footerContactPhone) ?></span>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <i class="fa-solid fa-location-dot text-success mt-1"></i>
                            <span style="font-size: 12px;"><?= htmlspecialchars($footerContactAddress) ?></span>
                        </li>
                    </ul>
                </div>
                
                <div class="col-md-4 col-lg-3 mb-3">
                    <h6 class="text-white fw-bold mb-3 small text-uppercase" style="letter-spacing: 1px;">Official Socials</h6>
                    <p class="text-secondary small mb-3" style="font-size: 12px;">Follow our communication channels for platform notices.</p>
                    <div class="d-flex gap-2">
                        <?php if (!empty($socialTwitter) && $socialTwitter !== '#'): ?>
                            <a href="<?= htmlspecialchars($socialTwitter) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="fa-brands fa-twitter text-white"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($socialFacebook) && $socialFacebook !== '#'): ?>
                            <a href="<?= htmlspecialchars($socialFacebook) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="fa-brands fa-facebook-f text-white"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($socialInstagram) && $socialInstagram !== '#'): ?>
                            <a href="<?= htmlspecialchars($socialInstagram) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="fa-brands fa-instagram text-white"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="border-top border-secondary border-opacity-10 pt-3 mt-4 text-center">
                <p class="text-muted small mb-1">&copy; <?= date('Y') ?> Federal Republic of Nigeria. National Vehicle Ownership &amp; Traceability Registry.</p>
                <p class="text-secondary small mb-0" style="font-size: 10px;">Powered by the Federal Ministry of Transportation &amp; NVOTS Security Taskforce. All rights reserved.</p>
            </div>
        </div>
    </footer>
</div>
