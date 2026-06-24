<?php
// App/views/verifications/manage.php
?>
<div class="row">
    <!-- Left Column: Initiate Audit -->
    <div class="col-lg-4 mb-4">
        <div class="card glass-panel border-0 p-4">
            <h5 class="text-white mb-4"><i class="fa-solid fa-file-signature text-success me-2"></i>Initiate Vehicle Audit</h5>
            
            <?php if (isset($error) && $error): ?>
                <div class="alert alert-danger bg-danger bg-opacity-25 border-0 text-white"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (isset($success) && $success): ?>
                <div class="alert alert-success bg-success bg-opacity-25 border-0 text-white">
                    <i class="fa-solid fa-circle-check me-2"></i><?= htmlspecialchars($success) ?>
                    <?php if (!empty($verifiedVehicleId)): ?>
                    <div class="mt-3 text-center">
                        <a href="<?= BASE_URL ?>/document/ownership/<?= (int)$verifiedVehicleId ?>" target="_blank" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-2">
                            <i class="fa-solid fa-print"></i> Print Ownership Document
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- QR Scan Quick Access -->
            <div class="mb-3">
                <button type="button" class="btn btn-outline-success w-100 d-flex align-items-center justify-content-center gap-2 py-2" id="verScanQrBtn">
                    <i class="fa-solid fa-qrcode fs-5"></i>
                    <span>Scan Certificate QR Code</span>
                    <span class="qr-scan-pulse-sm"></span>
                </button>
            </div>

            <div class="text-center text-secondary small mb-3" style="font-size:0.72rem;">
                <i class="fa-solid fa-arrows-left-right me-1"></i> or select vehicle manually below
            </div>

            <form method="POST" action="<?= BASE_URL ?>/verification/manage">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="create_audit" value="1">

                <div class="mb-3">
                    <label for="vehicle_id" class="form-label text-secondary">Select Vehicle to Audit</label>
                    <select class="form-select select2" id="vehicle_id" name="vehicle_id" required>
                        <option value="">-- Choose Vehicle --</option>
                        <?php foreach ($vehicles as $v): ?>
                            <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['manufacturer'] . ' ' . $v['model']) ?> [<?= htmlspecialchars($v['plate_number']) ?>]</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="verification_type" class="form-label text-secondary">Verification Audit Type</label>
                    <select class="form-select" id="verification_type" name="verification_type">
                        <option value="VEHICLE">Physical Vehicle Check</option>
                        <option value="OWNERSHIP">Ownership History Audit</option>
                        <option value="DOCUMENT">Customs & License Papers Audit</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="fa-solid fa-square-plus me-1"></i> Launch Audit Request
                </button>
            </form>
        </div>
    </div>

    <!-- Right Column: Verification Requests Ledger -->
    <div class="col-lg-8">
        <div class="card glass-panel border-0 p-4">
            <h5 class="text-white mb-3"><i class="fa-solid fa-list-check text-success me-2"></i>Verification Requests Ledger</h5>
            <div class="table-responsive">
                <table class="table text-secondary w-100" id="verTable">
                    <thead>
                        <tr>
                            <th>Plate Number</th>
                            <th>Vehicle</th>
                            <th>Audit Type</th>
                            <th>Status</th>
                            <th>Auditor</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $r): ?>
                            <tr>
                                <td><span class="badge bg-dark border border-secondary text-white"><?= htmlspecialchars($r['plate_number']) ?></span></td>
                                <td class="text-white"><?= htmlspecialchars($r['manufacturer'] . ' ' . $r['model']) ?></td>
                                <td><?= htmlspecialchars($r['verification_type']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $r['status'] === 'APPROVED' ? 'success' : ($r['status'] === 'PENDING' ? 'warning' : 'danger') ?>">
                                        <?= htmlspecialchars($r['status']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($r['verifier_email'] ?? 'Awaiting Audit') ?></td>
                                <td>
                                    <?php if ($r['status'] === 'PENDING'): ?>
                                        <button class="btn btn-sm btn-success btn-audit-action" 
                                                data-id="<?= $r['id'] ?>" 
                                                data-plate="<?= htmlspecialchars($r['plate_number']) ?>"
                                                data-type="<?= htmlspecialchars($r['verification_type']) ?>">
                                            <i class="fa-solid fa-signature"></i> Audit
                                        </button>
                                    <?php elseif ($r['status'] === 'APPROVED'): ?>
                                        <a href="<?= BASE_URL ?>/verification/certificate/<?= $r['id'] ?>" target="_blank" class="btn btn-sm btn-secondary">
                                            <i class="fa-solid fa-certificate text-warning"></i> View Cert
                                        </a>
                                    <?php else: ?>
                                        <span class="text-danger small"><i class="fa-solid fa-ban"></i> Rejected</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Audit Modal -->
<div class="modal fade" id="auditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 text-white">
            <div class="modal-header border-bottom border-secondary border-opacity-25">
                <h5 class="modal-title">Evaluate Audit Request</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/verification/manage">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="record_id" id="modal_record_id">
                
                <div class="modal-body">
                    <p class="small text-secondary">Evaluating audit for Vehicle Plate: <b class="text-white" id="modal_plate_number"></b></p>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label text-secondary">Set Resolution Status</label>
                        <select class="form-select" id="modal_status" name="status">
                            <option value="APPROVED">Approve and Verify Credentials</option>
                            <option value="REJECTED">Reject / Flags Discrepancies</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label text-secondary">Audit Evaluator Notes</label>
                        <textarea class="form-control" id="modal_notes" name="notes" placeholder="Enter findings or discrepancy alerts..." required></textarea>
                    </div>
                </div>
                
                <div class="modal-footer border-top border-secondary border-opacity-25">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fa-solid fa-circle-check me-1"></i> Save Resolution</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ═══════════════ QR SCANNER MODAL (Verification) ═══════════════ -->
<div class="modal fade" id="verQrScannerModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 bg-transparent">
            <div class="qr-scanner-card">
                <div class="qr-scanner-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="qr-header-icon">
                            <i class="fa-solid fa-qrcode"></i>
                        </div>
                        <div>
                            <h5 class="m-0 fw-bold text-white">Scan for Verification</h5>
                            <small class="text-white-50">Point camera at ownership document QR code</small>
                        </div>
                    </div>
                    <button type="button" class="qr-close-btn" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="qr-scanner-body">
                    <div class="qr-viewport-container">
                        <div id="ver-qr-reader" class="qr-reader-element"></div>
                        <div class="qr-scan-overlay">
                            <div class="scan-corner top-left"></div>
                            <div class="scan-corner top-right"></div>
                            <div class="scan-corner bottom-left"></div>
                            <div class="scan-corner bottom-right"></div>
                            <div class="scan-line"></div>
                        </div>
                    </div>
                    <div class="qr-status-bar" id="verQrStatusBar">
                        <div class="qr-status-dot"></div>
                        <span>Camera initializing…</span>
                    </div>
                </div>
                <div class="qr-scanner-footer">
                    <div class="d-flex align-items-center gap-2 text-white-50 small">
                        <i class="fa-solid fa-shield-halved text-success"></i>
                        <span>Secure scan — auto-selects vehicle for audit</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QR Scanner Styles (shared with search) -->
<style>
.qr-scan-pulse-sm {
    display: inline-block;
    width: 10px; height: 10px;
    border-radius: 50%;
    background: #10b981;
    animation: qrPulseSm 2s ease-in-out infinite;
    margin-left: 4px;
}
@keyframes qrPulseSm {
    0%,100% { opacity:1; box-shadow:0 0 0 0 rgba(16,185,129,0.4); }
    50% { opacity:0.7; box-shadow:0 0 0 8px rgba(16,185,129,0); }
}
.qr-scanner-card { background:linear-gradient(145deg,#0f172a 0%,#1e1b4b 100%); border-radius:20px; border:1px solid rgba(255,255,255,0.1); box-shadow:0 25px 60px rgba(0,0,0,0.5),0 0 40px rgba(16,185,129,0.08); overflow:hidden; }
.qr-scanner-header { display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid rgba(255,255,255,0.07); }
.qr-header-icon { width:44px; height:44px; background:linear-gradient(135deg,#10b981,#059669); border-radius:12px; display:flex; align-items:center; justify-content:center; color:#fff; font-size:20px; box-shadow:0 4px 15px rgba(16,185,129,0.35); }
.qr-close-btn { width:36px; height:36px; border-radius:50%; border:1px solid rgba(255,255,255,0.15); background:rgba(255,255,255,0.05); color:#94a3b8; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:all 0.2s; font-size:14px; }
.qr-close-btn:hover { background:rgba(239,68,68,0.15); border-color:rgba(239,68,68,0.3); color:#ef4444; }
.qr-scanner-body { padding:20px 24px; }
.qr-viewport-container { position:relative; border-radius:14px; overflow:hidden; background:#000; aspect-ratio:1; max-height:340px; margin:0 auto; }
.qr-reader-element { width:100%!important; height:100%!important; }
.qr-reader-element video { object-fit:cover!important; border-radius:14px; }
#ver-qr-reader__dashboard, #ver-qr-reader img[alt="Info icon"], #ver-qr-reader__header_message { display:none!important; }
#ver-qr-reader { border:none!important; background:transparent!important; }
.qr-scan-overlay { position:absolute; top:50%; left:50%; width:200px; height:200px; transform:translate(-50%,-50%); pointer-events:none; z-index:5; }
.scan-corner { position:absolute; width:28px; height:28px; border-color:#10b981; border-style:solid; border-width:0; }
.scan-corner.top-left { top:0;left:0; border-top-width:3px; border-left-width:3px; border-radius:6px 0 0 0; }
.scan-corner.top-right { top:0;right:0; border-top-width:3px; border-right-width:3px; border-radius:0 6px 0 0; }
.scan-corner.bottom-left { bottom:0;left:0; border-bottom-width:3px; border-left-width:3px; border-radius:0 0 0 6px; }
.scan-corner.bottom-right { bottom:0;right:0; border-bottom-width:3px; border-right-width:3px; border-radius:0 0 6px 0; }
.scan-line { position:absolute; left:4px; right:4px; height:2px; background:linear-gradient(90deg,transparent,#10b981,transparent); box-shadow:0 0 12px rgba(16,185,129,0.6); animation:scanLineMove 2.2s ease-in-out infinite; }
@keyframes scanLineMove { 0%{top:4px;} 50%{top:calc(100% - 6px);} 100%{top:4px;} }
.qr-status-bar { display:flex; align-items:center; gap:8px; padding:12px 0 0; color:#94a3b8; font-size:0.82rem; justify-content:center; }
.qr-status-dot { width:8px; height:8px; border-radius:50%; background:#fbbf24; animation:statusBlink 1.2s ease-in-out infinite; }
.qr-status-dot.active { background:#10b981; animation:none; }
.qr-status-dot.error { background:#ef4444; animation:none; }
@keyframes statusBlink { 0%,100%{opacity:1;} 50%{opacity:0.3;} }
.qr-scanner-footer { padding:14px 24px; border-top:1px solid rgba(255,255,255,0.07); text-align:center; display:flex; justify-content:center; }
</style>

<!-- html5-qrcode Library -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // ── DataTable ──
        $('#verTable').DataTable({ order: [[3, 'asc']] });

        // ── Audit Modal ──
        $('.btn-audit-action').on('click', function() {
            const data = $(this).data();
            $('#modal_record_id').val(data.id);
            $('#modal_plate_number').text(data.plate);
            const myModal = new bootstrap.Modal(document.getElementById('auditModal'));
            myModal.show();
        });

        // ═══════════════ QR SCANNER FOR VERIFICATION ═══════════════
        const verScanBtn  = document.getElementById('verScanQrBtn');
        const verModalEl  = document.getElementById('verQrScannerModal');
        const verStatusBar = document.getElementById('verQrStatusBar');
        const verStatusDot = verStatusBar?.querySelector('.qr-status-dot');
        const verStatusTxt = verStatusBar?.querySelector('span');
        const vehicleSelect = document.getElementById('vehicle_id');

        let verQrScanner = null;
        let verScanning  = false;
        const verBsModal = new bootstrap.Modal(verModalEl);

        verScanBtn.addEventListener('click', function() { verBsModal.show(); });

        verModalEl.addEventListener('shown.bs.modal', async function() {
            setVerStatus('Requesting camera access…', 'pending');
            try {
                verQrScanner = new Html5Qrcode("ver-qr-reader");
                await verQrScanner.start(
                    { facingMode: "environment" },
                    { fps: 12, qrbox: { width: 200, height: 200 }, aspectRatio: 1 },
                    onVerScanSuccess,
                    () => {}
                );
                verScanning = true;
                setVerStatus('Scanner active — position QR code inside the frame', 'active');
            } catch (err) {
                setVerStatus('Camera unavailable', 'error');
                Swal.fire({
                    icon: 'error', title: 'Camera Unavailable',
                    text: 'Could not access camera. Please check permissions.',
                    confirmButtonColor: '#10b981', background: '#1e293b', color: '#f8fafc'
                });
            }
        });

        function onVerScanSuccess(decodedText) {
            if (!verScanning) return;
            verScanning = false;
            if (navigator.vibrate) navigator.vibrate(200);
            stopVerScanner();
            verBsModal.hide();

            // Parse scanned data — look for vehicle plate or ownership URL
            let searchTerm = decodedText;
            try {
                const url = new URL(decodedText);
                const pathParts = url.pathname.split('/').filter(Boolean);
                // If it's an ownership doc URL, redirect to search
                const ownershipIdx = pathParts.indexOf('ownership');
                if (ownershipIdx !== -1 && pathParts[ownershipIdx + 1]) {
                    window.location.href = decodedText;
                    return;
                }
            } catch(e) {}

            // Try to match plate number in dropdown
            let matched = false;
            for (let i = 0; i < vehicleSelect.options.length; i++) {
                const optText = vehicleSelect.options[i].text.toUpperCase();
                if (optText.includes(searchTerm.toUpperCase())) {
                    vehicleSelect.selectedIndex = i;
                    matched = true;
                    break;
                }
            }

            if (matched) {
                Swal.fire({
                    icon: 'success', title: 'Vehicle Found!',
                    text: 'Vehicle auto-selected. You can now launch the audit.',
                    timer: 2000, timerProgressBar: true, showConfirmButton: false,
                    background: '#1e293b', color: '#f8fafc'
                });
            } else {
                Swal.fire({
                    icon: 'info', title: 'QR Scanned',
                    html: '<p style="color:#94a3b8;">Scanned: <code style="color:#10b981;">' + decodedText + '</code></p>' +
                          '<p style="color:#94a3b8;">No matching vehicle found in dropdown. Try searching manually.</p>',
                    confirmButtonColor: '#10b981', background: '#1e293b', color: '#f8fafc'
                });
            }
        }

        function stopVerScanner() {
            if (verQrScanner) {
                verQrScanner.stop().catch(() => {});
            }
        }

        verModalEl.addEventListener('hidden.bs.modal', function() {
            stopVerScanner();
            setVerStatus('Camera initializing…', 'pending');
        });

        function setVerStatus(text, state) {
            if (verStatusTxt) verStatusTxt.textContent = text;
            if (verStatusDot) {
                verStatusDot.className = 'qr-status-dot';
                if (state === 'active') verStatusDot.classList.add('active');
                else if (state === 'error') verStatusDot.classList.add('error');
            }
        }
    });
</script>

