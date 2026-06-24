<?php
// App/views/transfers/create.php
?>
<div class="row">
    <!-- Left Column: Vehicle & Seller Verification -->
    <div class="col-lg-5 mb-4">
        <div class="card glass-panel border-0 p-4 h-100">
            <h5 class="text-white mb-4"><i class="fa-solid fa-car-tunnel text-success me-2"></i>Verify Current Ownership</h5>
            
            <div class="mb-4">
                <label for="vehicle_id" class="form-label text-secondary">Select Vehicle to Transfer</label>
                <select class="form-select" id="vehicle_id" name="vehicle_id" required>
                    <option value="">-- Choose Vehicle --</option>
                    <?php foreach ($vehicles as $v): ?>
                        <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['manufacturer'] . ' ' . $v['model']) ?> [<?= htmlspecialchars($v['plate_number']) ?>]</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Seller Details View -->
            <div class="d-none" id="seller-info-container">
                <div class="text-center mb-4">
                    <img id="seller-photo" src="<?= BASE_URL ?>/public/images/no-avatar.png" width="120" height="120" class="rounded-circle border border-3 border-success mb-3 object-fit-cover shadow" alt="Seller Photo">
                    <h5 class="text-white fw-bold m-0" id="seller-name">Musa Ibrahim</h5>
                    <span class="badge bg-danger mt-2"><i class="fa-solid fa-user-tag me-1"></i>Current Legal Owner</span>
                </div>

                <table class="table text-secondary small">
                    <tr>
                        <td><b>Phone:</b></td>
                        <td id="seller-phone">08031234567</td>
                    </tr>
                    <tr>
                        <td><b>Email:</b></td>
                        <td id="seller-email">musa@example.com</td>
                    </tr>
                    <tr>
                        <td><b>Purchase Date:</b></td>
                        <td id="seller-purchase-date">2026-01-10</td>
                    </tr>
                    <tr>
                        <td><b>Onboarding Cost:</b></td>
                        <td id="seller-purchase-amount">₦50,000.00</td>
                    </tr>
                </table>
            </div>
            
            <div class="text-center text-muted py-5" id="seller-placeholder">
                <i class="fa-solid fa-file-signature fa-3x mb-3 text-secondary border border-secondary border-opacity-10 p-3 rounded-circle"></i>
                <p class="small">Choose a vehicle to load current verified ownership credentials.</p>
            </div>
        </div>
    </div>

    <!-- Right Column: Buyer & Transaction details -->
    <div class="col-lg-7">
        <div class="card glass-panel border-0 p-4">
            <h5 class="text-white mb-4"><i class="fa-solid fa-right-left text-success me-2"></i>Execution & Witness Registration</h5>
            
            <?php if (isset($error) && $error): ?>
                <div class="alert alert-danger bg-danger bg-opacity-25 border-0 text-white"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (isset($success) && $success): ?>
                <div class="alert alert-success bg-success bg-opacity-25 border-0 text-white"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/transfer/create" id="transferForm">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="vehicle_id" id="hidden_vehicle_id">

                <!-- Buyer Info -->
                <h6 class="text-white border-bottom border-secondary border-opacity-25 pb-2 mb-3">Buyer (New Owner) Selection</h6>
                <div class="mb-4">
                    <label for="buyer_id" class="form-label text-secondary">Verify & Select Buyer Account</label>
                    <select class="form-select" id="buyer_id" name="buyer_id" required>
                        <option value="">-- Select Buyer --</option>
                        <?php foreach ($owners as $owner): ?>
                            <option value="<?= $owner['id'] ?>"><?= htmlspecialchars($owner['full_name']) ?> (NIN: <?= htmlspecialchars($owner['nin'] ?? 'N/A') ?>) - <?= htmlspecialchars($owner['phone']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Ensure the buyer has already completed biometric profile registration.</small>
                </div>

                <!-- Transaction Info -->
                <h6 class="text-white border-bottom border-secondary border-opacity-25 pb-2 mb-3">Transaction details</h6>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="sale_price" class="form-label text-secondary">Final Agreed Purchase Price (₦)</label>
                        <input type="number" step="0.01" class="form-control" id="sale_price" name="sale_price" placeholder="e.g. 2500000" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="market_name" class="form-label text-secondary">Market / Location Purchased From</label>
                        <input type="text" class="form-control" id="market_name" name="market_name" placeholder="e.g. Berger Auto Market, Lagos" required>
                    </div>
                </div>

                <!-- Witnesses & Middlemen -->
                <h6 class="text-white border-bottom border-secondary border-opacity-25 pb-2 mb-3">Witness & Intermediary Details</h6>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="witness_name" class="form-label text-secondary">Witness Full Name & Phone</label>
                        <input type="text" class="form-control" id="witness_name" name="witness_name" placeholder="e.g. John Doe - 08055555555" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="middleman_name" class="form-label text-secondary">Middleman / Agent Full Name (Optional)</label>
                        <input type="text" class="form-control" id="middleman_name" name="middleman_name" placeholder="e.g. Alao Agents Ltd">
                    </div>
                </div>

                <div class="alert alert-warning bg-warning bg-opacity-10 border-warning border-opacity-25 text-warning small mt-3">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> <b>IMPRIMATUR RULE:</b> Once submitted, this transfer creates an irreversible record in the national history chain. Current ownership privileges will migrate immediately.
                </div>

                <button type="submit" class="btn btn-primary w-100 py-3" id="btn-execute-transfer" disabled>
                    <i class="fa-solid fa-file-contract me-2"></i> Approve & Execute Transfer
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const vehicleSelect = document.getElementById('vehicle_id');
        const hiddenVehicleId = document.getElementById('hidden_vehicle_id');
        const sellerPlaceholder = document.getElementById('seller-placeholder');
        const sellerInfoContainer = document.getElementById('seller-info-container');
        const btnExecuteTransfer = document.getElementById('btn-execute-transfer');

        const sellerPhoto = document.getElementById('seller-photo');
        const sellerName = document.getElementById('seller-name');
        const sellerPhone = document.getElementById('seller-phone');
        const sellerEmail = document.getElementById('seller-email');
        const sellerPurchaseDate = document.getElementById('seller-purchase-date');
        const sellerPurchaseAmount = document.getElementById('seller-purchase-amount');

        vehicleSelect.addEventListener('change', function() {
            const vehicleId = this.value;
            hiddenVehicleId.value = vehicleId;

            if (!vehicleId) {
                sellerPlaceholder.classList.remove('d-none');
                sellerInfoContainer.classList.add('d-none');
                btnExecuteTransfer.disabled = true;
                return;
            }

            // AJAX call to fetch current owner details
            fetch('<?= BASE_URL ?>/transfer/getCurrentOwnerApi/' + vehicleId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        sellerPlaceholder.classList.add('d-none');
                        sellerInfoContainer.classList.remove('d-none');
                        
                        sellerName.innerText = data.owner.full_name;
                        sellerPhone.innerText = data.owner.phone;
                        sellerEmail.innerText = data.owner.email ? data.owner.email : 'N/A';
                        sellerPurchaseDate.innerText = data.owner.purchase_date;
                        sellerPurchaseAmount.innerText = '₦' + parseFloat(data.owner.purchase_amount).toLocaleString('en-US', { minimumFractionDigits: 2 });
                        
                        if (data.owner.passport_photo_path) {
                            sellerPhoto.src = '<?= BASE_URL ?>/' + data.owner.passport_photo_path;
                        } else {
                            sellerPhoto.src = '<?= BASE_URL ?>/public/images/no-avatar.png';
                        }
                        
                        btnExecuteTransfer.disabled = false;
                    } else {
                        sellerPlaceholder.classList.remove('d-none');
                        sellerInfoContainer.classList.add('d-none');
                        btnExecuteTransfer.disabled = true;
                        
                        Swal.fire({
                            title: 'Registry Alert',
                            text: data.message,
                            icon: 'warning',
                            confirmButtonColor: '#10b981'
                        });
                    }
                })
                .catch(err => {
                    console.error("Owner Fetch Error:", err);
                    alert("Failed to load vehicle owner details. Try again.");
                });
        });
    });
</script>
