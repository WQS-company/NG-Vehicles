<?php
// App/views/admin/profile.php
?>
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card glass-panel border-0 p-4">
            <h4 class="text-white mb-4"><i class="fa-solid fa-user-gear text-success me-2"></i>My Profile Settings</h4>

            <?php if (isset($error) && $error): ?>
                <div class="alert alert-danger bg-danger bg-opacity-25 border-0 text-white"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (isset($success) && $success): ?>
                <div class="alert alert-success bg-success bg-opacity-25 border-0 text-white"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/admin/profile" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                <div class="text-center mb-4">
                    <img src="<?= !empty($user['avatar']) ? BASE_URL . '/' . htmlspecialchars($user['avatar']) : BASE_URL . '/public/images/no-avatar.png' ?>" 
                         alt="Avatar" 
                         class="rounded-circle border border-3 border-success object-fit-cover shadow" 
                         width="120" height="120" style="object-fit: cover;">
                    <div class="mt-3">
                        <label for="avatar" class="btn btn-secondary btn-sm">
                            <i class="fa-solid fa-camera me-1"></i> Change Avatar
                        </label>
                        <input type="file" id="avatar" name="avatar" class="d-none" accept="image/jpeg,image/png">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="first_name" class="form-label text-secondary">First Name / Display Name</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label text-secondary">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label text-secondary">Phone Number</label>
                    <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label text-secondary">New Password (leave blank to keep current)</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="••••••••">
                </div>

                <button type="submit" class="btn btn-primary w-100 py-3 mt-4">
                    <i class="fa-solid fa-circle-check me-2"></i> Update Profile
                </button>
            </form>
        </div>
    </div>
</div>
