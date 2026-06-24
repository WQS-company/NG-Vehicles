<?php
// App/views/owners/register.php
?>
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card glass-panel border-0 p-4">
            <h4 class="text-white mb-4"><i class="fa-solid fa-user-plus text-success me-2"></i>Owner Biometric & Identity Onboarding</h4>

            <?php if (isset($error) && $error): ?>
                <div class="alert alert-danger bg-danger bg-opacity-25 border-0 text-white"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (isset($success) && $success): ?>
                <div class="alert alert-success bg-success bg-opacity-25 border-0 text-white"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/owner/register" enctype="multipart/form-data" id="ownerForm">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="webcam_image" id="webcam_image">
                <input type="hidden" name="signature_image" id="signature_image">

                <!-- Section 1: Demographics -->
                <h5 class="text-white border-bottom border-secondary border-opacity-25 pb-2 mb-3"><i class="fa-solid fa-circle-info me-2"></i>Personal Demographics</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="full_name" class="form-label text-secondary">Full Name (Surname First)</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" placeholder="e.g. Alabi Musa Okon" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="gender" class="form-label text-secondary">Gender</label>
                        <select class="form-select" id="gender" name="gender">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="phone" class="form-label text-secondary">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="e.g. 08031234567" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="email" class="form-label text-secondary">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="e.g. musa@example.com" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="date_of_birth" class="form-label text-secondary">Date of Birth</label>
                        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="nationality" class="form-label text-secondary">Nationality</label>
                        <input type="text" class="form-control" id="nationality" name="nationality" value="Nigerian" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="occupation" class="form-label text-secondary">Occupation</label>
                        <input type="text" class="form-control" id="occupation" name="occupation" placeholder="e.g. Business Analyst" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="nin" class="form-label text-secondary">National Identity Number (NIN)</label>
                        <input type="text" class="form-control" id="nin" name="nin" minlength="11" maxlength="11" placeholder="e.g. 12345678901" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="bvn" class="form-label text-secondary">Bank Verification Number (BVN) - Optional</label>
                    <input type="text" class="form-control" id="bvn" name="bvn" minlength="11" maxlength="11" placeholder="e.g. 22234567890">
                </div>

                <!-- Section 2: Address Details -->
                <h5 class="text-white border-bottom border-secondary border-opacity-25 pb-2 mb-3"><i class="fa-solid fa-map-location-dot me-2"></i>Contact Address Details</h5>
                <div class="mb-3">
                    <label for="address" class="form-label text-secondary">Street Address</label>
                    <textarea class="form-control" id="address" name="address" placeholder="e.g. 12 Herbert Macaulay Way" required></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="state" class="form-label text-secondary">State of Residence</label>
                        <input type="text" class="form-control" id="state" name="state" placeholder="e.g. Lagos" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="lga" class="form-label text-secondary">LGA (Local Government Area)</label>
                        <input type="text" class="form-control" id="lga" name="lga" placeholder="e.g. Yaba" required>
                    </div>
                </div>

                <!-- Section 3: Biometrics Section (Live Camera Capture & Signature) -->
                <h5 class="text-white border-bottom border-secondary border-opacity-25 pb-2 mb-3"><i class="fa-solid fa-fingerprint me-2"></i>Biometric Identification Capture</h5>
                <div class="row mb-4">
                    <!-- Webcam Column -->
                    <div class="col-md-6 mb-3 text-center">
                        <label class="form-label text-secondary d-block">Live Passport Camera Capture</label>
                        
                        <div class="d-flex flex-column align-items-center bg-dark bg-opacity-50 border border-secondary border-opacity-25 rounded p-3">
                            <video id="webcam-feed" width="320" height="240" autoplay class="rounded border mb-3 d-none"></video>
                            <canvas id="photo-canvas" width="320" height="240" class="rounded border mb-3 d-none"></canvas>
                            <img id="captured-preview" src="<?= BASE_URL ?>/public/images/no-avatar.png" width="320" height="240" alt="Capture Preview" class="rounded border mb-3">
                            
                            <div>
                                <button type="button" class="btn btn-secondary btn-sm" id="btn-start-camera">
                                    <i class="fa-solid fa-video me-1"></i> Start Camera
                                </button>
                                <button type="button" class="btn btn-success btn-sm d-none" id="btn-capture-photo">
                                    <i class="fa-solid fa-camera me-1"></i> Capture
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Signature Column -->
                    <div class="col-md-6 mb-3 text-center">
                        <label class="form-label text-secondary d-block">Digital Signature capture</label>
                        
                        <div class="d-flex flex-column align-items-center bg-dark bg-opacity-50 border border-secondary border-opacity-25 rounded p-3">
                            <canvas id="sig-canvas" width="320" height="240" class="rounded border mb-3 bg-white" style="cursor: crosshair;"></canvas>
                            
                            <div>
                                <button type="button" class="btn btn-secondary btn-sm" id="btn-clear-sig">
                                    <i class="fa-solid fa-eraser me-1"></i> Clear
                                </button>
                                <button type="button" class="btn btn-success btn-sm" id="btn-save-sig">
                                    <i class="fa-solid fa-floppy-disk me-1"></i> Lock Signature
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Render Owner Dynamic Fields -->
                <?php if (!empty($dynamicFields)): ?>
                    <h5 class="text-white border-bottom border-secondary border-opacity-25 pb-2 mb-3"><i class="fa-solid fa-folder-plus me-2"></i>Additional Profile Fields (Dynamic)</h5>
                    <div class="row">
                        <?php foreach ($dynamicFields as $field): ?>
                            <div class="col-md-6 mb-3">
                                <label for="custom_<?= $field['id'] ?>" class="form-label text-secondary"><?= htmlspecialchars($field['field_name']) ?><?= $field['is_required'] ? ' <span class="text-danger">*</span>' : '' ?></label>
                                
                                <?php if ($field['field_type'] === 'text'): ?>
                                    <input type="text" class="form-control" id="custom_<?= $field['id'] ?>" name="custom_<?= $field['id'] ?>" <?= $field['is_required'] ? 'required' : '' ?>>
                                
                                <?php elseif ($field['field_type'] === 'number'): ?>
                                    <input type="number" class="form-control" id="custom_<?= $field['id'] ?>" name="custom_<?= $field['id'] ?>" <?= $field['is_required'] ? 'required' : '' ?>>
                                
                                <?php elseif ($field['field_type'] === 'date'): ?>
                                    <input type="date" class="form-control" id="custom_<?= $field['id'] ?>" name="custom_<?= $field['id'] ?>" <?= $field['is_required'] ? 'required' : '' ?>>
                                
                                <?php elseif ($field['field_type'] === 'textarea'): ?>
                                    <textarea class="form-control" id="custom_<?= $field['id'] ?>" name="custom_<?= $field['id'] ?>" <?= $field['is_required'] ? 'required' : '' ?>></textarea>
                                
                                <?php elseif ($field['field_type'] === 'dropdown'): ?>
                                    <select class="form-select" id="custom_<?= $field['id'] ?>" name="custom_<?= $field['id'] ?>" <?= $field['is_required'] ? 'required' : '' ?>>
                                        <option value="">-- Select --</option>
                                        <?php 
                                        $opts = explode(',', $field['options']);
                                        foreach ($opts as $o): 
                                            $o = trim($o);
                                        ?>
                                            <option value="<?= htmlspecialchars($o) ?>"><?= htmlspecialchars($o) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                
                                <?php elseif ($field['field_type'] === 'radio'): ?>
                                    <div>
                                        <?php 
                                        $opts = explode(',', $field['options']);
                                        foreach ($opts as $idx => $o): 
                                            $o = trim($o);
                                        ?>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="custom_<?= $field['id'] ?>" id="radio_owner_<?= $field['id'] ?>_<?= $idx ?>" value="<?= htmlspecialchars($o) ?>" <?= $field['is_required'] && $idx === 0 ? 'checked' : '' ?>>
                                                <label class="form-check-label text-secondary" for="radio_owner_<?= $field['id'] ?>_<?= $idx ?>"><?= htmlspecialchars($o) ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                <?php elseif ($field['field_type'] === 'checkbox'): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="custom_<?= $field['id'] ?>" id="custom_owner_<?= $field['id'] ?>" value="Yes">
                                        <label class="form-check-label text-secondary" for="custom_owner_<?= $field['id'] ?>">Check to confirm</label>
                                    </div>

                                <?php elseif ($field['field_type'] === 'file'): ?>
                                    <input type="file" class="form-control" id="custom_<?= $field['id'] ?>" name="custom_<?= $field['id'] ?>" <?= $field['is_required'] ? 'required' : '' ?>>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-primary w-100 py-3 mt-4">
                    <i class="fa-solid fa-id-card me-2"></i> Register Owner Profile
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Camera Variables
        const video = document.getElementById('webcam-feed');
        const canvas = document.getElementById('photo-canvas');
        const preview = document.getElementById('captured-preview');
        const startCamBtn = document.getElementById('btn-start-camera');
        const captureBtn = document.getElementById('btn-capture-photo');
        const webcamImageHidden = document.getElementById('webcam_image');

        // Signature Pad Variables
        const sigCanvas = document.getElementById('sig-canvas');
        const clearSigBtn = document.getElementById('btn-clear-sig');
        const saveSigBtn = document.getElementById('btn-save-sig');
        const sigImageHidden = document.getElementById('signature_image');
        const sigCtx = sigCanvas.getContext('2d');
        let drawing = false;

        // Start Camera Logic
        startCamBtn.addEventListener('click', async function() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                video.srcObject = stream;
                video.classList.remove('d-none');
                preview.classList.add('d-none');
                startCamBtn.classList.add('d-none');
                captureBtn.classList.remove('d-none');
            } catch (err) {
                alert("Camera access denied or unavailable.");
            }
        });

        // Capture Photo Logic
        captureBtn.addEventListener('click', function() {
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            // Populate base64 string
            const base64Data = canvas.toDataURL('image/jpeg');
            webcamImageHidden.value = base64Data;
            
            // Stop video stream
            const stream = video.srcObject;
            const tracks = stream.getTracks();
            tracks.forEach(track => track.stop());

            preview.src = base64Data;
            preview.classList.remove('d-none');
            video.classList.add('d-none');
            captureBtn.classList.add('d-none');
            startCamBtn.classList.remove('d-none');
            startCamBtn.innerHTML = '<i class="fa-solid fa-arrows-rotate me-1"></i> Retake Photo';
        });

        // Signature Canvas Logic
        sigCtx.strokeStyle = "#000000";
        sigCtx.lineWidth = 3;

        sigCanvas.addEventListener('mousedown', (e) => {
            drawing = true;
            sigCtx.beginPath();
            sigCtx.moveTo(e.offsetX, e.offsetY);
        });

        sigCanvas.addEventListener('mousemove', (e) => {
            if (!drawing) return;
            sigCtx.lineTo(e.offsetX, e.offsetY);
            sigCtx.stroke();
        });

        sigCanvas.addEventListener('mouseup', () => drawing = false);
        sigCanvas.addEventListener('mouseleave', () => drawing = false);

        // Mobile touch support for signature pad
        sigCanvas.addEventListener('touchstart', (e) => {
            drawing = true;
            const touch = e.touches[0];
            const rect = sigCanvas.getBoundingClientRect();
            sigCtx.beginPath();
            sigCtx.moveTo(touch.clientX - rect.left, touch.clientY - rect.top);
            e.preventDefault();
        });

        sigCanvas.addEventListener('touchmove', (e) => {
            if (!drawing) return;
            const touch = e.touches[0];
            const rect = sigCanvas.getBoundingClientRect();
            sigCtx.lineTo(touch.clientX - rect.left, touch.clientY - rect.top);
            sigCtx.stroke();
            e.preventDefault();
        });

        sigCanvas.addEventListener('touchend', () => drawing = false);

        clearSigBtn.addEventListener('click', function() {
            sigCtx.clearRect(0, 0, sigCanvas.width, sigCanvas.height);
            sigImageHidden.value = "";
        });

        saveSigBtn.addEventListener('click', function() {
            const base64Data = sigCanvas.toDataURL('image/png');
            sigImageHidden.value = base64Data;
            Swal.fire({
                title: 'Signature Locked',
                text: 'Signature registered for owner profile.',
                icon: 'success',
                confirmButtonColor: '#10b981'
            });
        });
    });
</script>
