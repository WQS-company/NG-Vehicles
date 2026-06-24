<?php
// App/views/layout/main.php
use Core\Auth;
use App\Models\Setting;

$user = Auth::user();
if ($user) {
    $firstName = !empty($user['first_name']) ? $user['first_name'] : ucfirst(explode('@', $user['email'])[0]);
    // Query notifications
    try {
        $notifModel = new \App\Models\Notification();
        $userNotifications = $notifModel->getNotificationsForUser($user['id']);
        $unreadNotifications = array_filter($userNotifications, function($n) { return !$n['is_read']; });
        $unreadCount = count($unreadNotifications);
    } catch (\Exception $e) {
        $userNotifications = [];
        $unreadCount = 0;
    }
}

$pendingCorrectionCount = 0;
if (Auth::check() && Auth::role() === ROLE_SUPER_ADMIN) {
    try {
        $db = \Core\Database::getInstance();
        $pendingCorrectionCount = (int)$db->fetch("SELECT COUNT(*) as count FROM correction_requests WHERE status = 'PENDING'")['count'];
    } catch (\Exception $e) {}
}

// Load global configuration settings
$settingModel = new Setting();
$platformSettings = $settingModel->getAllSettings();

$platformTitle = !empty($platformSettings['platform_title']) ? $platformSettings['platform_title'] : APP_TITLE;
$platformLogo = !empty($platformSettings['platform_logo']) ? $platformSettings['platform_logo'] : '';
$platformFavicon = !empty($platformSettings['platform_favicon']) ? $platformSettings['platform_favicon'] : '';
$footerContactAddress = !empty($platformSettings['footer_contact_address']) ? $platformSettings['footer_contact_address'] : 'Federal Secretariat Complex, Shehu Shagari Way, Central Business District, Abuja, Nigeria';
$footerContactPhone = !empty($platformSettings['footer_contact_phone']) ? $platformSettings['footer_contact_phone'] : '+234 (0) 9 461 6000';
$footerContactEmail = !empty($platformSettings['footer_contact_email']) ? $platformSettings['footer_contact_email'] : 'support@nvots.gov.ng';
$socialTwitter = !empty($platformSettings['social_twitter']) ? $platformSettings['social_twitter'] : '#';
$socialFacebook = !empty($platformSettings['social_facebook']) ? $platformSettings['social_facebook'] : '#';
$socialInstagram = !empty($platformSettings['social_instagram']) ? $platformSettings['social_instagram'] : '#';
$missionText = !empty($platformSettings['mission']) ? $platformSettings['mission'] : '';
$visionText = !empty($platformSettings['vision']) ? $platformSettings['vision'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? htmlspecialchars($title) . ' - ' : '' ?><?= htmlspecialchars($platformTitle) ?></title>
    <!-- Dynamic Favicon -->
    <?php if (!empty($platformFavicon)): ?>
        <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/<?= htmlspecialchars($platformFavicon) ?>">
    <?php else: ?>
        <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/public/favicon.ico">
    <?php endif; ?>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
    <!-- Custom Style CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css?v=<?= time() ?>">
    <!-- CSRF Token Meta -->
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <style>
        /* ─── Global Preloader & Process Overlay ────────────────────────── */
        #global-preloader, #process-overlay {
            position: fixed; inset: 0; z-index: 99999;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            background: #050d1a;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }

        #process-overlay {
            background: rgba(5, 13, 26, 0.85);
            backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
            visibility: hidden; opacity: 0;
        }
        #process-overlay.active {
            visibility: visible; opacity: 1;
        }

        .loader-shield {
            width: 70px; height: 70px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 1.8rem;
            box-shadow: 0 0 30px rgba(16,185,129,0.4);
            animation: pulseLoader 1.5s ease-in-out infinite;
            position: relative; margin-bottom: 1.5rem;
        }
        .loader-shield::before {
            content: ''; position: absolute; inset: -6px;
            border: 2px solid rgba(16,185,129,0.5); border-radius: 22px;
            border-top-color: transparent; border-bottom-color: transparent;
            animation: spinLoader 2s linear infinite;
        }

        .loader-text {
            color: #f8fafc; font-size: 1rem; font-weight: 600;
            letter-spacing: 0.1em; text-transform: uppercase;
            animation: fadeText 1.5s ease-in-out infinite alternate;
        }

        @keyframes pulseLoader {
            0%, 100% { transform: scale(1); box-shadow: 0 0 30px rgba(16,185,129,0.4); }
            50% { transform: scale(1.05); box-shadow: 0 0 50px rgba(16,185,129,0.7); }
        }
        @keyframes spinLoader {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @keyframes fadeText {
            from { opacity: 0.5; }
            to { opacity: 1; }
        }
    </style>

</head>
<body>
    <!-- Global Preloader -->
    <div id="global-preloader">
        <div class="loader-shield">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <div class="loader-text">Loading NVOTS...</div>
    </div>

    <!-- Process Action Overlay -->
    <div id="process-overlay">
        <div class="loader-shield">
            <i class="fa-solid fa-server"></i>
        </div>
        <div class="loader-text" id="process-overlay-text">Processing Request...</div>
    </div>

    <?php if (Auth::check()): ?>
        <!-- Sidebar Navigation -->
        <div class="sidebar" id="sidebar">
            <a href="<?= $user['role'] === 'BENEFICIARY' ? BASE_URL . '/beneficiary/dashboard' : BASE_URL . '/dashboard' ?>" class="sidebar-logo">
                <?php if (!empty($platformLogo)): ?>
                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($platformLogo) ?>" alt="Logo" class="sidebar-logo-img me-2" style="max-height: 32px; width: auto; object-fit: contain;">
                <?php else: ?>
                    <i class="fa-solid fa-shield-halved text-success"></i>
                <?php endif; ?>
                <span>NVOTS</span>
            </a>
            
            <ul class="sidebar-menu">
                <?php if ($user['role'] === 'BENEFICIARY'): ?>
                    <li>
                        <a href="<?= BASE_URL ?>/beneficiary/dashboard" class="sidebar-link <?= ($activePage ?? '') === 'beneficiary_dashboard' ? 'active' : '' ?>">
                            <i class="fa-solid fa-chart-pie"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/beneficiary/payroll" class="sidebar-link <?= ($activePage ?? '') === 'beneficiary_payroll' ? 'active' : '' ?>">
                            <i class="fa-solid fa-money-bill-transfer"></i> Commission Payroll
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/beneficiary/profile" class="sidebar-link <?= ($activePage ?? '') === 'beneficiary_profile' ? 'active' : '' ?>">
                            <i class="fa-solid fa-user-gear"></i> My Profile
                        </a>
                    </li>
                <?php else: ?>
                    <li>
                        <a href="<?= BASE_URL ?>/dashboard" class="sidebar-link <?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">
                            <i class="fa-solid fa-chart-line"></i> Dashboard
                        </a>
                    </li>
                    
                    <li>
                        <a href="<?= BASE_URL ?>/search" class="sidebar-link <?= ($activePage ?? '') === 'search' ? 'active' : '' ?>">
                            <i class="fa-solid fa-magnifying-glass-chart"></i> Search & Trace
                        </a>
                    </li>

                    <li>
                        <a href="<?= BASE_URL ?>/vehicle/list" class="sidebar-link <?= ($activePage ?? '') === 'vehicles' ? 'active' : '' ?>">
                            <i class="fa-solid fa-car-side"></i> Vehicles
                        </a>
                    </li>

                <?php if ($user['role'] === ROLE_SUPER_ADMIN || Auth::hasFeature('registration')): ?>
                <li>
                    <a href="<?= BASE_URL ?>/vehicle/register" class="sidebar-link <?= ($activePage ?? '') === 'vehicle_reg' ? 'active' : '' ?>">
                        <i class="fa-solid fa-car-rear"></i> Register Vehicle
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/owner/register" class="sidebar-link <?= ($activePage ?? '') === 'owner_reg' ? 'active' : '' ?>">
                        <i class="fa-solid fa-user-plus"></i> Register Owner
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/transfer/create" class="sidebar-link <?= ($activePage ?? '') === 'transfer' ? 'active' : '' ?>">
                        <i class="fa-solid fa-right-left"></i> Owner Transfer
                    </a>
                </li>
                <?php endif; ?>

                <?php if ($user['role'] === ROLE_SUPER_ADMIN || Auth::hasFeature('verification')): ?>
                <li>
                    <a href="<?= BASE_URL ?>/verification/manage" class="sidebar-link <?= ($activePage ?? '') === 'verification' ? 'active' : '' ?>">
                        <i class="fa-solid fa-file-signature"></i> Verifications
                    </a>
                </li>
                <?php endif; ?>

                <?php if ($user['role'] === ROLE_SUPER_ADMIN || Auth::hasFeature('payments')): ?>
                <li>
                    <a href="<?= BASE_URL ?>/payment/manage" class="sidebar-link <?= ($activePage ?? '') === 'payments' ? 'active' : '' ?>">
                        <i class="fa-solid fa-receipt"></i> Payments
                    </a>
                </li>
                <?php endif; ?>

                <?php if ($user['role'] === ROLE_SUPER_ADMIN): ?>
                <li>
                    <a href="<?= BASE_URL ?>/commission" class="sidebar-link <?= ($activePage ?? '') === 'commission' ? 'active' : '' ?>">
                        <i class="fa-solid fa-hand-holding-dollar"></i> Commission Board
                    </a>
                </li>
                <?php endif; ?>

                <?php if ($user['role'] === ROLE_SUPER_ADMIN || Auth::hasFeature('reports')): ?>
                <li>
                    <a href="<?= BASE_URL ?>/report/manage" class="sidebar-link <?= ($activePage ?? '') === 'reports' ? 'active' : '' ?>">
                        <i class="fa-solid fa-file-invoice-dollar"></i> Reports
                    </a>
                </li>
                <?php endif; ?>

                <?php if ($user['role'] === ROLE_SUPER_ADMIN || Auth::hasFeature('correction')): ?>
                <li>
                    <a href="<?= BASE_URL ?>/correction" class="sidebar-link <?= ($activePage ?? '') === 'correction' ? 'active' : '' ?>">
                        <i class="fa-solid fa-file-pen"></i> Data Correction
                    </a>
                </li>
                <?php endif; ?>

                <?php if ($user['role'] === ROLE_SUPER_ADMIN): ?>
                <li>
                    <a href="<?= BASE_URL ?>/admin/admins" class="sidebar-link <?= ($activePage ?? '') === 'admins' ? 'active' : '' ?>">
                        <i class="fa-solid fa-users-gear"></i> Manage Admins
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/admin/beneficiaries" class="sidebar-link <?= ($activePage ?? '') === 'beneficiaries' ? 'active' : '' ?>">
                        <i class="fa-solid fa-handshake-simple"></i> Manage Beneficiaries
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/correction/requests" class="sidebar-link <?= ($activePage ?? '') === 'correction_requests' ? 'active' : '' ?>">
                        <i class="fa-solid fa-list-check"></i> Correction Requests
                        <?php if ($pendingCorrectionCount > 0): ?>
                            <span class="badge bg-danger ms-auto"><?= $pendingCorrectionCount ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/admin/settings" class="sidebar-link <?= ($activePage ?? '') === 'settings' ? 'active' : '' ?>">
                        <i class="fa-solid fa-gears"></i> Platform Settings
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/admin/fields" class="sidebar-link <?= ($activePage ?? '') === 'fields' ? 'active' : '' ?>">
                        <i class="fa-solid fa-list-check"></i> Form Manager
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/admin/logs" class="sidebar-link <?= ($activePage ?? '') === 'logs' ? 'active' : '' ?>">
                        <i class="fa-solid fa-clock-rotate-left"></i> System Logs
                    </a>
                </li>
                <?php endif; ?>
                <?php endif; ?>

                <li class="mt-auto">
                    <a href="<?= BASE_URL ?>/auth/logout" class="sidebar-link text-danger">
                        <i class="fa-solid fa-right-from-bracket"></i> Log Out
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Workspace -->
        <div class="main-content">
            <!-- Header bar -->
            <div class="d-flex justify-content-between align-items-center mb-4 glass-panel p-3 position-relative" style="z-index: 50;">
                <button class="btn btn-outline-light d-lg-none" id="sidebar-toggle">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h4 class="m-0 text-white font-weight-bold d-none d-md-block"><?= isset($title) ? htmlspecialchars($title) : 'NVOTS Nigeria' ?></h4>
                <div class="d-flex align-items-center gap-3 ms-auto ms-lg-0">
                    <span class="text-secondary d-none d-sm-inline">Hi, <strong class="text-white"><?= htmlspecialchars($firstName) ?></strong></span>
                    <span class="badge bg-success p-2 d-none d-md-inline-block">
                        <i class="fa-solid fa-user-shield me-1"></i>
                        <?= $user['role'] === 'BENEFICIARY' ? 'Beneficiary' : htmlspecialchars($user['role']) ?>
                    </span>
                    
                    
                    <!-- Notifications Dropdown -->
                    <div class="dropdown me-1">
                        <div class="position-relative dropdown-toggle no-caret" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                            <i class="fa-solid fa-bell text-secondary fs-5 hover-glow-bell" style="transition: color 0.3s ease;"></i>
                            <?php if ($unreadCount > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-dark" style="font-size: 0.6rem; padding: 0.25em 0.5em;">
                                    <?= $unreadCount ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end bg-dark border-secondary border-opacity-30 mt-2 shadow-lg glass-dropdown" style="width: 320px; max-height: 400px; overflow-y: auto;">
                            <li>
                                <div class="dropdown-header text-secondary fw-bold d-flex justify-content-between align-items-center" style="font-size: 0.75rem; text-transform: uppercase;">
                                    <span>Notifications</span>
                                    <?php if ($unreadCount > 0): ?>
                                        <span class="badge bg-danger"><?= $unreadCount ?> New</span>
                                    <?php endif; ?>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider border-secondary border-opacity-20"></li>
                            <?php if (empty($userNotifications)): ?>
                                <li class="text-center py-3 text-muted small">No notifications found.</li>
                            <?php else: ?>
                                <?php foreach (array_slice($userNotifications, 0, 8) as $n): ?>
                                    <li class="notification-item px-3 py-2 <?= $n['is_read'] ? 'opacity-60' : 'bg-dark bg-opacity-40' ?> position-relative border-bottom border-secondary border-opacity-10">
                                        <div class="d-flex justify-content-between align-items-start gap-2">
                                            <span class="badge <?= $n['type'] === 'SMS' ? 'bg-info' : ($n['type'] === 'EMAIL' ? 'bg-primary' : 'bg-success') ?> fs-8 mb-1" style="font-size: 0.62rem;">
                                                <?= htmlspecialchars($n['type']) ?>
                                            </span>
                                            <span class="text-muted text-nowrap" style="font-size: 0.65rem;"><?= date('M d, h:i A', strtotime($n['created_at'])) ?></span>
                                        </div>
                                        <p class="m-0 text-white-50 small mt-1 text-wrap" style="line-height: 1.3; font-size: 0.82rem; word-break: break-word;">
                                            <?= htmlspecialchars($n['payload']) ?>
                                        </p>
                                        <?php if (!$n['is_read']): ?>
                                            <div class="text-end mt-1">
                                                <a href="#" class="mark-read-link text-emerald small font-weight-bold text-decoration-none" style="font-size: 0.72rem; color: #10b981;" data-id="<?= $n['id'] ?>">
                                                    <i class="fa-solid fa-check me-1"></i>Mark Read
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <!-- Profile Avatar Dropdown -->
                    <div class="dropdown">
                        <div class="position-relative user-avatar-container dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                            <img src="<?= !empty($user['avatar']) ? BASE_URL . '/' . htmlspecialchars($user['avatar']) : BASE_URL . '/public/images/no-avatar.png' ?>" 
                                 alt="Avatar" 
                                 class="rounded-circle border border-2 border-success object-fit-cover header-profile-img shadow" 
                                 width="40" height="40" style="object-fit: cover;">
                            <span class="avatar-status-dot"></span>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end bg-dark border-secondary border-opacity-30 mt-2 shadow-lg glass-dropdown">
                            <li>
                                <div class="dropdown-header text-secondary fw-bold" style="font-size: 0.72rem; text-transform: uppercase;">
                                    <?= $user['role'] === 'BENEFICIARY' ? 'Beneficiary' : htmlspecialchars($user['role']) ?>
                                </div>
                            </li>
                            <li>
                                <div class="dropdown-item text-white-50 small" style="pointer-events: none; background: transparent;">
                                    <?= htmlspecialchars($user['email']) ?>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider border-secondary border-opacity-20"></li>
                            <?php if ($user['role'] === ROLE_SUPER_ADMIN): ?>
                            <li>
                                <a class="dropdown-item text-white d-flex align-items-center gap-2" href="<?= BASE_URL ?>/admin/profile">
                                    <i class="fa-solid fa-user-gear"></i> Edit Profile
                                </a>
                            </li>
                            <li><hr class="dropdown-divider border-secondary border-opacity-20"></li>
                            <?php elseif ($user['role'] === 'BENEFICIARY'): ?>
                            <li>
                                <a class="dropdown-item text-white d-flex align-items-center gap-2" href="<?= BASE_URL ?>/beneficiary/profile">
                                    <i class="fa-solid fa-user-gear"></i> My Profile
                                </a>
                            </li>
                            <li><hr class="dropdown-divider border-secondary border-opacity-20"></li>
                            <?php endif; ?>
                            <li>
                                <a class="dropdown-item text-danger d-flex align-items-center gap-2" href="<?= BASE_URL ?>/auth/logout">
                                    <i class="fa-solid fa-right-from-bracket"></i> Log Out
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Page Content -->
            <?= $content ?>
        </div>

        <!-- Mobile Bottom Tabs Navigation -->
        <div class="bottom-tabs d-lg-none">
            <?php if ($user['role'] === 'BENEFICIARY'): ?>
                <a href="<?= BASE_URL ?>/beneficiary/dashboard" class="tab-item <?= ($activePage ?? '') === 'beneficiary_dashboard' ? 'active' : '' ?>">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>
                <a href="<?= BASE_URL ?>/beneficiary/payroll" class="tab-item <?= ($activePage ?? '') === 'beneficiary_payroll' ? 'active' : '' ?>">
                    <i class="fa-solid fa-money-bill-transfer"></i>
                    <span>Payroll</span>
                </a>
                <a href="<?= BASE_URL ?>/beneficiary/profile" class="tab-item <?= ($activePage ?? '') === 'beneficiary_profile' ? 'active' : '' ?>">
                    <i class="fa-solid fa-user-gear"></i>
                    <span>Profile</span>
                </a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/dashboard" class="tab-item <?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
                <a href="<?= BASE_URL ?>/search" class="tab-item <?= ($activePage ?? '') === 'search' ? 'active' : '' ?>">
                    <i class="fa-solid fa-magnifying-glass-chart"></i>
                    <span>Search</span>
                </a>
                <?php if ($user['role'] === ROLE_SUPER_ADMIN || $user['role'] === ROLE_REGISTRATION_ADMIN): ?>
                    <a href="<?= BASE_URL ?>/vehicle/register" class="tab-item <?= ($activePage ?? '') === 'vehicle_reg' ? 'active' : '' ?>">
                        <i class="fa-solid fa-car-rear"></i>
                        <span>Reg Vehicle</span>
                    </a>
                    <a href="<?= BASE_URL ?>/owner/register" class="tab-item <?= ($activePage ?? '') === 'owner_reg' ? 'active' : '' ?>">
                        <i class="fa-solid fa-user-plus"></i>
                        <span>Reg Owner</span>
                    </a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/verification/manage" class="tab-item <?= ($activePage ?? '') === 'verification' ? 'active' : '' ?>">
                    <i class="fa-solid fa-file-signature"></i>
                    <span>Audits</span>
                </a>
            <?php endif; ?>
        </div>

    <?php else: ?>
        <!-- Guest View (Login / Registration views) -->
        <?php if (isset($isLanding) && $isLanding): ?>
            <?= $content ?>
        <?php else: ?>
            <div class="container d-flex align-items-center justify-content-center min-vh-100">
                <?= $content ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- JQuery, Bootstrap, SweetAlert2, Chart.js, DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom JS logic -->
    <script src="<?= BASE_URL ?>/public/js/app.js"></script>
<?php if (isset($isLanding) && $isLanding): ?>
    <script src="<?= BASE_URL ?>/public/js/landing.js"></script>
<?php endif; ?>
    <script>
        $('#sidebar-toggle').on('click', function(e) {
            e.stopPropagation();
            $('#sidebar').toggleClass('open');
            if ($('#sidebar').hasClass('open')) {
                $('body').addClass('sidebar-open');
            } else {
                $('body').removeClass('sidebar-open');
            }
        });

        // Close sidebar when clicking outside on mobile screens
        $(document).on('click', function(e) {
            const sidebar = $('#sidebar');
            const toggle = $('#sidebar-toggle');
            if (sidebar.hasClass('open') && 
                !sidebar.is(e.target) && sidebar.has(e.target).length === 0 && 
                !toggle.is(e.target) && toggle.has(e.target).length === 0) {
                sidebar.removeClass('open');
                $('body').removeClass('sidebar-open');
            }
        });
        // Global Preloader
        $(window).on('load', function() {
            $('#global-preloader').css('opacity', '0');
            setTimeout(() => {
                $('#global-preloader').css('visibility', 'hidden');
            }, 500);
        });

        // Global Process Overlay API
        window.showProcessOverlay = function(text = 'Processing Request...') {
            $('#process-overlay-text').text(text);
            $('#process-overlay').addClass('active');
        };

        window.hideProcessOverlay = function() {
            $('#process-overlay').removeClass('active');
        };

        // Auto-show process overlay on forms that have the .show-process class
        $('form.show-process').on('submit', function() {
            if (this.checkValidity()) {
                window.showProcessOverlay($(this).data('process-msg') || 'Processing...');
            }
        });

        // Mark notification read via AJAX
        $(document).on('click', '.mark-read-link', function(e) {
            e.preventDefault();
            const link = $(this);
            const id = link.data('id');
            const item = link.closest('.notification-item');
            
            $.post('<?= BASE_URL ?>/auth/markNotificationRead/' + id, function(resp) {
                if (resp.status) {
                    item.removeClass('bg-dark bg-opacity-40').addClass('opacity-60');
                    link.remove();
                    
                    // Decrement badge count
                    const badge = $('.dropdown-toggle .badge');
                    if (badge.length) {
                        let count = parseInt(badge.first().text().trim()) - 1;
                        if (count <= 0) {
                            $('.dropdown-toggle .badge').remove();
                            $('.dropdown-header .badge').remove();
                        } else {
                            $('.dropdown-toggle .badge').text(count);
                            $('.dropdown-header .badge').text(count + ' New');
                        }
                    }
                }
            }, 'json');
        });
    </script>
</body>
</html>
