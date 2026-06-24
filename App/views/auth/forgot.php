<?php
// App/views/auth/forgot.php
?>
<div class="row w-100 justify-content-center">
    <div class="col-md-5">
        <div class="card glass-panel p-4 shadow border-0">
            <div class="card-body">
                <div class="text-center mb-4">
                    <i class="fa-solid fa-key fa-3x text-success mb-3"></i>
                    <h2 class="text-white font-weight-bold">Reset Password</h2>
                    <p class="text-secondary">Enter your registered email to request link</p>
                </div>

                <?php if (isset($error) && $error): ?>
                    <div class="alert alert-danger border-0 bg-danger bg-opacity-25 text-white" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($message) && $message): ?>
                    <div class="alert alert-success border-0 bg-success bg-opacity-25 text-white" role="alert">
                        <i class="fa-solid fa-circle-check me-2"></i> <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL ?>/auth/forgot">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    
                    <div class="mb-4">
                        <label for="email" class="form-label text-secondary">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0 border-secondary-subtle text-muted">
                                <i class="fa-solid fa-envelope"></i>
                            </span>
                            <input type="email" class="form-control border-start-0" id="email" name="email" placeholder="e.g. admin@example.com" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-3 text-uppercase mb-3">
                        <i class="fa-solid fa-paper-plane me-2"></i> Send Reset Link
                    </button>
                    
                    <div class="text-center">
                        <a href="<?= BASE_URL ?>/auth/login" class="text-success small text-decoration-none">
                            <i class="fa-solid fa-arrow-left me-1"></i> Back to Login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
