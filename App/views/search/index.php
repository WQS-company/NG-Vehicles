<?php
// App/views/search/index.php
?>
<div class="row justify-content-center mb-4">
    <div class="col-md-8 text-center">
        <h3 class="text-white fw-bold mb-3"><i class="fa-solid fa-magnifying-glass-chart text-success me-2"></i>Global Registry Search</h3>
        <p class="text-secondary small">Query vehicle history logs instantly via VIN, Engine Number, Chassis, Plate, RFID/QR code, Owner Phone, Name, NIN or BVN.</p>
        
        <form method="GET" action="<?= BASE_URL ?>/search" id="searchForm">
            <div class="input-group input-group-lg shadow-sm position-relative">
                <input type="text" class="form-control bg-dark border-secondary border-opacity-50 text-white" name="q" id="searchQueryInput" value="<?= htmlspecialchars($query) ?>" placeholder="Enter search parameters..." required style="padding-right: 52px;">
                <!-- QR Scan Icon (inside search input) -->
                <button type="button" class="qr-scan-trigger" id="scanQrBtn" title="Scan Ownership Certificate QR Code">
                    <i class="fa-solid fa-qrcode"></i>
                    <span class="qr-scan-pulse"></span>
                </button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass me-2"></i>Query</button>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════ QR SCANNER MODAL ═══════════════ -->
<div class="modal fade" id="qrScannerModal" tabindex="-1" aria-labelledby="qrScannerLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 bg-transparent">
            <div class="qr-scanner-card">
                <!-- Header -->
                <div class="qr-scanner-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="qr-header-icon">
                            <i class="fa-solid fa-qrcode"></i>
                        </div>
                        <div>
                            <h5 class="m-0 fw-bold text-white">Scan Certificate QR</h5>
                            <small class="text-white-50">Point your camera at the ownership document QR code</small>
                        </div>
                    </div>
                    <button type="button" class="qr-close-btn" data-bs-dismiss="modal" aria-label="Close" id="qrCloseBtn">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Scanner Viewport -->
                <div class="qr-scanner-body">
                    <div class="qr-viewport-container">
                        <div id="qr-reader" class="qr-reader-element"></div>
                        <!-- Corner Markers Overlay -->
                        <div class="qr-scan-overlay" id="qrScanOverlay">
                            <div class="scan-corner top-left"></div>
                            <div class="scan-corner top-right"></div>
                            <div class="scan-corner bottom-left"></div>
                            <div class="scan-corner bottom-right"></div>
                            <div class="scan-line"></div>
                        </div>
                    </div>
                    
                    <!-- Status Text -->
                    <div class="qr-status-bar" id="qrStatusBar">
                        <div class="qr-status-dot"></div>
                        <span>Camera initializing…</span>
                    </div>
                </div>

                <!-- Footer -->
                <div class="qr-scanner-footer">
                    <div class="d-flex align-items-center gap-2 text-white-50 small">
                        <i class="fa-solid fa-shield-halved text-success"></i>
                        <span>Secure verification scan — camera feed is not stored</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QR Scanner Styles -->
<style>
/* ── QR Scan Trigger Button (inside input) ── */
.qr-scan-trigger {
    position: absolute;
    right: 130px;
    top: 50%;
    transform: translateY(-50%);
    z-index: 10;
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 6px 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #10b981;
    font-size: 1.35rem;
    transition: all 0.3s cubic-bezier(.4,0,.2,1);
    border-radius: 8px;
}
.qr-scan-trigger:hover {
    color: #34d399;
    background: rgba(16, 185, 129, 0.1);
    transform: translateY(-50%) scale(1.12);
}
.qr-scan-trigger:active {
    transform: translateY(-50%) scale(0.95);
}
/* Pulsing ring behind QR icon */
.qr-scan-pulse {
    position: absolute;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 2px solid rgba(16, 185, 129, 0.35);
    animation: qrPulse 2.5s ease-in-out infinite;
    pointer-events: none;
}
@keyframes qrPulse {
    0%   { transform: scale(0.8); opacity: 0.7; }
    50%  { transform: scale(1.4); opacity: 0; }
    100% { transform: scale(0.8); opacity: 0; }
}

/* ── Scanner Modal Card ── */
.qr-scanner-card {
    background: linear-gradient(145deg, #0f172a 0%, #1e1b4b 100%);
    border-radius: 20px;
    border: 1px solid rgba(255,255,255,0.1);
    box-shadow: 0 25px 60px rgba(0,0,0,0.5), 0 0 40px rgba(16,185,129,0.08);
    overflow: hidden;
}
.qr-scanner-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    border-bottom: 1px solid rgba(255,255,255,0.07);
}
.qr-header-icon {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, #10b981, #059669);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 20px;
    box-shadow: 0 4px 15px rgba(16,185,129,0.35);
}
.qr-close-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 1px solid rgba(255,255,255,0.15);
    background: rgba(255,255,255,0.05);
    color: #94a3b8;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 14px;
}
.qr-close-btn:hover {
    background: rgba(239,68,68,0.15);
    border-color: rgba(239,68,68,0.3);
    color: #ef4444;
}

/* ── Scanner Body ── */
.qr-scanner-body {
    padding: 20px 24px;
}
.qr-viewport-container {
    position: relative;
    border-radius: 14px;
    overflow: hidden;
    background: #000;
    aspect-ratio: 1;
    max-height: 340px;
    margin: 0 auto;
}
.qr-reader-element {
    width: 100% !important;
    height: 100% !important;
}
.qr-reader-element video {
    object-fit: cover !important;
    border-radius: 14px;
}
/* Hide the default html5-qrcode UI controls */
#qr-reader__scan_region {
    min-height: unset !important;
}
#qr-reader__dashboard {
    display: none !important;
}
#qr-reader img[alt="Info icon"],
#qr-reader__header_message,
#qr-reader__dashboard_section,
#qr-reader__dashboard_section_csr,
#qr-reader__dashboard_section_swaplink {
    display: none !important;
}
#qr-reader {
    border: none !important;
    background: transparent !important;
}

/* ── Scan Overlay with Corner Markers ── */
.qr-scan-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 200px;
    height: 200px;
    transform: translate(-50%, -50%);
    pointer-events: none;
    z-index: 5;
}
.scan-corner {
    position: absolute;
    width: 28px;
    height: 28px;
    border-color: #10b981;
    border-style: solid;
    border-width: 0;
}
.scan-corner.top-left {
    top: 0; left: 0;
    border-top-width: 3px;
    border-left-width: 3px;
    border-radius: 6px 0 0 0;
}
.scan-corner.top-right {
    top: 0; right: 0;
    border-top-width: 3px;
    border-right-width: 3px;
    border-radius: 0 6px 0 0;
}
.scan-corner.bottom-left {
    bottom: 0; left: 0;
    border-bottom-width: 3px;
    border-left-width: 3px;
    border-radius: 0 0 0 6px;
}
.scan-corner.bottom-right {
    bottom: 0; right: 0;
    border-bottom-width: 3px;
    border-right-width: 3px;
    border-radius: 0 0 6px 0;
}
/* Scanning line animation */
.scan-line {
    position: absolute;
    left: 4px;
    right: 4px;
    height: 2px;
    background: linear-gradient(90deg, transparent, #10b981, transparent);
    box-shadow: 0 0 12px rgba(16,185,129,0.6);
    animation: scanLineMove 2.2s ease-in-out infinite;
}
@keyframes scanLineMove {
    0%   { top: 4px; }
    50%  { top: calc(100% - 6px); }
    100% { top: 4px; }
}

/* ── Status Bar ── */
.qr-status-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 0 0;
    color: #94a3b8;
    font-size: 0.82rem;
    justify-content: center;
}
.qr-status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #fbbf24;
    animation: statusBlink 1.2s ease-in-out infinite;
}
.qr-status-dot.active {
    background: #10b981;
    animation: none;
}
.qr-status-dot.error {
    background: #ef4444;
    animation: none;
}
@keyframes statusBlink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
}

/* ── Footer ── */
.qr-scanner-footer {
    padding: 14px 24px;
    border-top: 1px solid rgba(255,255,255,0.07);
    text-align: center;
    display: flex;
    justify-content: center;
}

/* ── Success flash ── */
.qr-scan-success {
    animation: successFlash 0.5s ease;
}
@keyframes successFlash {
    0%   { box-shadow: inset 0 0 0 0 rgba(16,185,129,0); }
    40%  { box-shadow: inset 0 0 60px rgba(16,185,129,0.25); }
    100% { box-shadow: inset 0 0 0 0 rgba(16,185,129,0); }
}
</style>

<!-- html5-qrcode Library -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const scanBtn       = document.getElementById('scanQrBtn');
    const searchInput   = document.getElementById('searchQueryInput');
    const searchForm    = document.getElementById('searchForm');
    const statusBar     = document.getElementById('qrStatusBar');
    const statusDot     = statusBar?.querySelector('.qr-status-dot');
    const statusText    = statusBar?.querySelector('span');
    const modalEl       = document.getElementById('qrScannerModal');
    const closeBtn      = document.getElementById('qrCloseBtn');
    const viewport      = document.querySelector('.qr-viewport-container');

    let html5QrCode     = null;
    let scanning        = false;
    const bsModal       = new bootstrap.Modal(modalEl);

    // ── Open Scanner ──
    scanBtn.addEventListener('click', function() {
        bsModal.show();
    });

    // ── When modal fully shown, start camera ──
    modalEl.addEventListener('shown.bs.modal', async function() {
        setStatus('Requesting camera access…', 'pending');

        try {
            html5QrCode = new Html5Qrcode("qr-reader");
            
            await html5QrCode.start(
                { facingMode: "environment" },
                {
                    fps: 12,
                    qrbox: { width: 200, height: 200 },
                    aspectRatio: 1,
                    disableFlip: false
                },
                onScanSuccess,
                onScanFailure
            );

            scanning = true;
            setStatus('Scanner active — position QR code inside the frame', 'active');
        } catch (err) {
            console.error('QR Scanner Error:', err);
            setStatus('Camera access denied or unavailable', 'error');
            
            Swal.fire({
                icon: 'error',
                title: 'Camera Unavailable',
                html: '<p style="color:#94a3b8;">Could not access your device camera. Please ensure:</p>' +
                      '<ul style="color:#cbd5e1; text-align:left; font-size:0.9rem;">' +
                      '<li>Camera permissions are granted</li>' +
                      '<li>No other app is using the camera</li>' +
                      '<li>You are using HTTPS or localhost</li></ul>',
                confirmButtonColor: '#10b981',
                background: '#1e293b',
                color: '#f8fafc'
            });
        }
    });

    // ── On successful QR decode ──
    function onScanSuccess(decodedText, decodedResult) {
        if (!scanning) return;
        scanning = false;

        // Success flash effect
        if (viewport) viewport.classList.add('qr-scan-success');
        setStatus('✓ QR Code decoded successfully!', 'active');

        // Vibration feedback (mobile)
        if (navigator.vibrate) navigator.vibrate(200);

        // Stop camera
        stopScanner();
        bsModal.hide();

        // Parse the scanned URL — extract the vehicle ID or search query
        let searchQuery = decodedText;

        // If it's a URL like /document/ownership/123 or /search?q=..., extract useful part
        try {
            const url = new URL(decodedText);
            const pathParts = url.pathname.split('/').filter(Boolean);
            
            // Pattern: /document/ownership/{id}
            const ownershipIdx = pathParts.indexOf('ownership');
            if (ownershipIdx !== -1 && pathParts[ownershipIdx + 1]) {
                searchQuery = decodedText; // Keep full URL — we'll redirect to it
                window.location.href = decodedText;
                return;
            }

            // Pattern: /search?q=...
            if (url.searchParams.has('q')) {
                searchQuery = url.searchParams.get('q');
            }
        } catch (e) {
            // Not a URL — use raw text as search query
        }

        // Fill search input and submit
        searchInput.value = searchQuery;
        searchInput.removeAttribute('required');

        Swal.fire({
            icon: 'success',
            title: 'QR Code Scanned!',
            text: 'Redirecting to verification results…',
            timer: 1500,
            timerProgressBar: true,
            showConfirmButton: false,
            background: '#1e293b',
            color: '#f8fafc'
        }).then(() => {
            searchForm.submit();
        });
    }

    function onScanFailure(error) {
        // Silent — continuous scanning, no need to log each frame miss
    }

    // ── Stop scanner helper ──
    function stopScanner() {
        if (html5QrCode && scanning) {
            scanning = false;
            html5QrCode.stop().catch(err => console.log('Stop error:', err));
        } else if (html5QrCode) {
            html5QrCode.stop().catch(err => console.log('Stop error:', err));
        }
    }

    // ── When modal closes, stop camera ──
    modalEl.addEventListener('hidden.bs.modal', function() {
        stopScanner();
        if (viewport) viewport.classList.remove('qr-scan-success');
        setStatus('Camera initializing…', 'pending');
    });

    // ── Status helpers ──
    function setStatus(text, state) {
        if (statusText) statusText.textContent = text;
        if (statusDot) {
            statusDot.className = 'qr-status-dot';
            if (state === 'active') statusDot.classList.add('active');
            else if (state === 'error') statusDot.classList.add('error');
        }
    }
});
</script>

<?php if (!empty($query)): ?>
    <!-- Multiple Search Results List -->
    <?php if (empty($results)): ?>
        <div class="alert alert-warning text-center bg-warning bg-opacity-10 border-warning border-opacity-25 text-warning p-4">
            <i class="fa-solid fa-triangle-exclamation fa-2x mb-2 d-block"></i> No registry matches found for query '<?= htmlspecialchars($query) ?>'
        </div>
    <?php elseif (!$vehicle): ?>
        <!-- Render Multiple Matches Table -->
        <div class="card glass-panel border-0 p-4">
            <h5 class="text-white mb-3">Matching Registry Entries (<?= count($results) ?> matches)</h5>
            <div class="table-responsive">
                <table class="table text-secondary table-hover">
                    <thead>
                        <tr>
                            <th>Plate Number</th>
                            <th>Manufacturer & Model</th>
                            <th>VIN Number</th>
                            <th>Current Owner</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $res): ?>
                            <tr>
                                <td><span class="badge bg-dark border border-secondary text-white"><?= htmlspecialchars($res['plate_number']) ?></span></td>
                                <td class="text-white"><?= htmlspecialchars($res['manufacturer'] . ' ' . $res['model']) ?></td>
                                <td><code><?= htmlspecialchars($res['vin']) ?></code></td>
                                <td><?= htmlspecialchars($res['current_owner_name'] ?? 'N/A') ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/search?q=<?= urlencode($res['vin']) ?>" class="btn btn-sm btn-success">
                                        <i class="fa-solid fa-eye me-1"></i> Trace
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <!-- Exact Trace Data View -->
        <div class="row g-4">
            <!-- Left Side: Vehicle Info & Payments -->
            <div class="col-lg-5">
                <!-- Vehicle Card -->
                <div class="card glass-panel border-0 p-4 mb-4 text-center">
                    <?php if ($vehicle['image_path']): ?>
                        <img src="<?= BASE_URL ?>/<?= $vehicle['image_path'] ?>" class="img-fluid rounded mb-3 border border-secondary border-opacity-25 shadow object-fit-cover" style="max-height: 200px;" alt="Vehicle photo">
                    <?php else: ?>
                        <div class="bg-dark bg-opacity-50 border border-secondary border-opacity-20 rounded mb-3 py-4">
                            <i class="fa-solid fa-car-rear fa-4x text-muted"></i>
                        </div>
                    <?php endif; ?>

                    <h4 class="text-white fw-bold mb-0"><?= htmlspecialchars($vehicle['manufacturer'] . ' ' . $vehicle['model']) ?></h4>
                    <span class="badge bg-dark border border-success text-success mt-2 mx-auto px-3 py-2"><i class="fa-solid fa-id-card me-1"></i><?= htmlspecialchars($vehicle['plate_number']) ?></span>

                    <div class="mt-3">
                        <a href="<?= BASE_URL ?>/document/ownership/<?= (int)$vehicle['id'] ?>" target="_blank" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-2">
                            <i class="fa-solid fa-file-pdf"></i> Print Ownership Document
                        </a>
                    </div>

                    <table class="table text-secondary text-start small mt-4">
                        <tr>
                            <td><b>VIN Number:</b></td>
                            <td><code><?= htmlspecialchars($vehicle['vin']) ?></code></td>
                        </tr>
                        <tr>
                            <td><b>Engine Number:</b></td>
                            <td><code><?= htmlspecialchars($vehicle['engine_number']) ?></code></td>
                        </tr>
                        <tr>
                            <td><b>Chassis Number:</b></td>
                            <td><code><?= htmlspecialchars($vehicle['chassis_number']) ?></code></td>
                        </tr>
                        <tr>
                            <td><b>Year / Color:</b></td>
                            <td><?= htmlspecialchars($vehicle['year'] . ' / ' . $vehicle['color']) ?></td>
                        </tr>
                        <tr>
                            <td><b>Vehicle Class:</b></td>
                            <td><?= htmlspecialchars($vehicle['class']) ?></td>
                        </tr>
                        <tr>
                            <td><b>Specs:</b></td>
                            <td><?= htmlspecialchars($vehicle['category'] . ' (' . $vehicle['transmission'] . ')') ?></td>
                        </tr>
                    </table>
                </div>

                <!-- Payments Card -->
                <div class="card glass-panel border-0 p-4 mb-4">
                    <h5 class="text-white mb-3"><i class="fa-solid fa-receipt text-success me-2"></i>Onboarding Payments</h5>
                    <div class="table-responsive">
                        <table class="table text-secondary small">
                            <thead>
                                <tr>
                                    <th>Receipt</th>
                                    <th>Method</th>
                                    <th>Amount</th>
                                    <th>Evidence</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $p): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($p['receipt_number']) ?></code></td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($p['payment_method']) ?></span></td>
                                        <td class="text-white">₦<?= number_format($p['amount'], 2) ?></td>
                                        <td>
                                            <?php if (!empty($p['receipt_file'])): ?>
                                                <a href="<?= BASE_URL ?>/<?= htmlspecialchars($p['receipt_file']) ?>" target="_blank" class="btn btn-xs btn-outline-info text-info border-info border-opacity-50 p-1 py-0" style="font-size:0.72rem;">
                                                    <i class="fa-solid fa-file-arrow-down"></i> View
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted small">None</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($p['payment_date']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Verification Records -->
                <div class="card glass-panel border-0 p-4">
                    <h5 class="text-white mb-3"><i class="fa-solid fa-shield-halved text-success me-2"></i>Audited Verification Status</h5>
                    <div class="table-responsive">
                        <table class="table text-secondary small">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Verifier</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($verifications)): ?>
                                    <tr><td colspan="4" class="text-center">No audits conducted yet.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($verifications as $v): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($v['verification_type']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $v['status'] === 'APPROVED' ? 'success' : ($v['status'] === 'PENDING' ? 'warning' : 'danger') ?>">
                                                    <?= htmlspecialchars($v['status']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($v['verifier_email']) ?></td>
                                            <td><?= htmlspecialchars($v['verified_at'] ?? 'Pending') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Side: Ownership trace / Chain timeline -->
            <div class="col-lg-7">
                <div class="card glass-panel border-0 p-4 h-100">
                    <h5 class="text-white mb-4"><i class="fa-solid fa-link text-success me-2"></i>Immutable Ownership Timeline</h5>

                    <div class="ownership-timeline">
                        <?php foreach ($history as $idx => $hist): 
                            $isCurrent = ($idx === count($history) - 1);
                        ?>
                            <div class="timeline-item">
                                <div class="timeline-marker">
                                    <i class="fa-solid <?= $isCurrent ? 'fa-user-shield text-success' : 'fa-clock text-secondary' ?>"></i>
                                </div>
                                <div class="card bg-dark bg-opacity-40 border border-secondary border-opacity-10 p-3">
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <?php if ($hist['passport_photo_path']): ?>
                                            <img src="<?= BASE_URL ?>/<?= $hist['passport_photo_path'] ?>" width="60" height="60" class="rounded-circle border border-2 border-secondary object-fit-cover shadow" alt="Photo">
                                        <?php else: ?>
                                            <img src="<?= BASE_URL ?>/public/images/no-avatar.png" width="60" height="60" class="rounded-circle border border-2 border-secondary" alt="No Photo">
                                        <?php endif; ?>
                                        
                                        <div>
                                            <h6 class="text-white fw-bold m-0"><?= htmlspecialchars($hist['full_name']) ?></h6>
                                            <small class="text-muted"><?= htmlspecialchars($hist['phone']) ?> | NIN: <?= htmlspecialchars($hist['nin'] ?? 'N/A') ?></small>
                                            <div>
                                                <?php if ($isCurrent): ?>
                                                    <span class="badge bg-success bg-opacity-25 text-success border border-success mt-1">Current Custodian</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary text-light mt-1">Previous Custodian</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <table class="table table-sm text-secondary small m-0">
                                        <tr>
                                            <td><b>Acquisition Date:</b></td>
                                            <td class="text-white"><?= htmlspecialchars($hist['purchase_date']) ?></td>
                                        </tr>
                                        <tr>
                                            <td><b>Valuation Price:</b></td>
                                            <td class="text-white">₦<?= number_format($hist['purchase_amount'], 2) ?></td>
                                        </tr>
                                        <tr>
                                            <td><b>Market/Dealership:</b></td>
                                            <td class="text-white"><?= htmlspecialchars($hist['market_name']) ?></td>
                                        </tr>
                                        <tr>
                                            <td><b>Witness Info:</b></td>
                                            <td class="text-white"><?= htmlspecialchars($hist['witness_name']) ?></td>
                                        </tr>
                                        <tr>
                                            <td><b>Intermediary / Agent:</b></td>
                                            <td class="text-white"><?= htmlspecialchars($hist['middleman_name'] ?? 'None') ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Professional Registration & Officer Audits Card (Full Width) -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card glass-panel border-0 p-4">
                    <h5 class="text-white mb-4"><i class="fa-solid fa-file-shield text-success me-2"></i>Official Registry Data & Security Audits</h5>
                    
                    <?php
                        // Helper to get custom field values safely
                        $cf = [];
                        if (!empty($vehicle['custom_fields'])) {
                            $cf = json_decode($vehicle['custom_fields'], true) ?: [];
                        }

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
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

