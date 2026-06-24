<?php
// Simple professional beneficiary login page
?>
<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card p-4">
            <h3 class="mb-3">Beneficiary Login</h3>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/beneficiary/login">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="position-relative">
                        <input type="password" name="password" id="login_password" class="form-control pe-5" required>
                        <span class="position-absolute end-0 top-50 translate-middle-y pe-3" style="cursor:pointer;" onclick="togglePasswordInput('login_password', 'login_password_icon')">
                            <i class="fa-solid fa-eye text-secondary" id="login_password_icon"></i>
                        </span>
                    </div>
                </div>
                <div class="d-grid">
                    <button class="btn btn-primary">Sign in</button>
                </div>
            </form>

            <script>
            function togglePasswordInput(inputId, iconId) {
                const field = document.getElementById(inputId);
                const icon = document.getElementById(iconId);
                if (field.type === 'password') {
                    field.type = 'text';
                    icon.className = 'fa-solid fa-eye-slash text-secondary';
                } else {
                    field.type = 'password';
                    icon.className = 'fa-solid fa-eye text-secondary';
                }
            }
            </script>
        </div>
    </div>
</div>
