<?php // App/views/auth/login.php ?>
<style>
/* ── Login Page ────────────────────────────────────────────── */
.login-bg {
    position: fixed; inset: 0; z-index: -1;
    background: linear-gradient(135deg, #050d1a 0%, #0a1628 40%, #0f1f3d 70%, #0d1527 100%);
    overflow: hidden;
}
.login-bg .orb {
    position: absolute; border-radius: 50%;
    filter: blur(80px); opacity: 0.18;
    animation: floatOrb 12s ease-in-out infinite alternate;
}
.orb-1 { width:520px;height:520px;background:#10b981;top:-120px;left:-140px;animation-delay:0s; }
.orb-2 { width:400px;height:400px;background:#3b82f6;bottom:-80px;right:-80px;animation-delay:4s; }
.orb-3 { width:280px;height:280px;background:#8b5cf6;top:40%;left:55%;animation-delay:8s; }
@keyframes floatOrb {
    from { transform: translate(0,0) scale(1); }
    to   { transform: translate(30px,20px) scale(1.08); }
}

.login-grid-overlay {
    position: fixed; inset: 0; z-index: -1;
    background-image:
        linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
    background-size: 60px 60px;
}

.login-card {
    background: rgba(255,255,255,0.06);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 24px;
    box-shadow: 0 32px 80px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.05) inset;
    padding: 2.75rem 2.5rem;
    width: 100%;
    max-width: 460px;
    animation: cardEntry 0.6s cubic-bezier(.4,0,.2,1) both;
}
@keyframes cardEntry {
    from { opacity:0; transform: translateY(30px) scale(0.97); }
    to   { opacity:1; transform: translateY(0) scale(1); }
}

.brand-shield {
    width: 76px; height: 76px;
    background: linear-gradient(135deg, #10b981, #059669);
    border-radius: 20px;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 1.25rem;
    box-shadow: 0 8px 30px rgba(16,185,129,0.35);
    position: relative;
    animation: pulse-glow 3s ease-in-out infinite;
}
@keyframes pulse-glow {
    0%,100% { box-shadow: 0 8px 30px rgba(16,185,129,0.35); }
    50%      { box-shadow: 0 8px 45px rgba(16,185,129,0.6); }
}
.brand-shield i { font-size: 2rem; color: #fff; }

.brand-title {
    font-size: 1.85rem; font-weight: 800;
    background: linear-gradient(135deg, #f8fafc, #cbd5e1);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    letter-spacing: -0.02em;
}
.brand-subtitle { font-size: 0.82rem; color: #64748b; letter-spacing: 0.03em; }

.gov-badge {
    display: inline-flex; align-items: center; gap: 0.4rem;
    background: rgba(16,185,129,0.1);
    border: 1px solid rgba(16,185,129,0.25);
    color: #10b981;
    font-size: 0.7rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.1em;
    border-radius: 20px;
    padding: 0.3rem 0.85rem;
    margin-top: 0.5rem;
}

/* Input Fields */
.login-field { position: relative; margin-bottom: 1.2rem; }

.login-field .f-icon {
    position: absolute; left: 1rem; top: 50%; transform: translateY(-50%);
    color: #475569; font-size: 0.9rem; pointer-events: none; transition: color .3s;
    z-index: 2;
}
.login-field input:focus ~ .f-icon,
.login-field input:focus + .f-icon { color: #10b981; }

.login-field input {
    width: 100%;
    background: rgba(15, 23, 42, 0.6);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px;
    padding: 0.85rem 1rem 0.85rem 2.85rem;
    color: #f8fafc;
    font-size: 0.92rem;
    font-family: 'Outfit', sans-serif;
    transition: all .3s ease;
    outline: none;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);
}
.login-field input:focus {
    background: rgba(15, 23, 42, 0.9);
    border-color: #10b981;
    box-shadow: 0 0 0 4px rgba(16,185,129,0.15), inset 0 2px 4px rgba(0,0,0,0.2);
}
.login-field input::placeholder { color: #64748b; }

/* Fix WebKit Autofill text visibility */
.login-field input:-webkit-autofill,
.login-field input:-webkit-autofill:hover, 
.login-field input:-webkit-autofill:focus, 
.login-field input:-webkit-autofill:active {
    -webkit-box-shadow: 0 0 0 30px #0f172a inset !important;
    -webkit-text-fill-color: #f8fafc !important;
    transition: background-color 5000s ease-in-out 0s;
}

.login-field label {
    display: block;
    font-size: 0.74rem; font-weight: 600;
    text-transform: uppercase; letter-spacing: 0.07em;
    color: #64748b; margin-bottom: 0.4rem;
}

/* Password toggle */
.pw-toggle {
    position: absolute; right: 1rem; top: 50%; transform: translateY(-50%);
    color: #475569; cursor: pointer; font-size: 0.9rem;
    transition: color .3s; z-index: 2;
}
.pw-toggle:hover { color: #10b981; }

/* Submit button */
.btn-signin {
    width: 100%;
    background: linear-gradient(135deg, #10b981, #059669);
    border: none; border-radius: 12px;
    color: #fff; font-weight: 700;
    font-size: 0.95rem; letter-spacing: 0.02em;
    padding: 0.9rem;
    cursor: pointer;
    transition: all .3s ease;
    box-shadow: 0 4px 20px rgba(16,185,129,0.35);
    display: flex; align-items: center; justify-content: center; gap: 0.6rem;
    font-family: 'Outfit', sans-serif;
}
.btn-signin:hover {
    background: linear-gradient(135deg, #059669, #047857);
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(16,185,129,0.5);
}
.btn-signin:active { transform: translateY(0); }

.error-alert {
    background: rgba(239,68,68,0.1);
    border: 1px solid rgba(239,68,68,0.25);
    border-radius: 10px;
    padding: 0.75rem 1rem;
    font-size: 0.84rem;
    color: #fca5a5;
    display: flex; align-items: center; gap: 0.6rem;
    margin-bottom: 1.25rem;
}

.divider {
    display: flex; align-items: center; gap: 1rem;
    margin: 1.25rem 0;
}
.divider::before, .divider::after {
    content: ''; flex: 1; height: 1px;
    background: rgba(255,255,255,0.08);
}
.divider span { font-size: 0.72rem; color: #475569; white-space: nowrap; }

.security-strip {
    display: flex; align-items: center; justify-content: center;
    gap: 1.5rem;
    margin-top: 1.5rem;
    padding-top: 1.25rem;
    border-top: 1px solid rgba(255,255,255,0.06);
}
.security-strip .s-item {
    display: flex; align-items: center; gap: 0.4rem;
    font-size: 0.68rem; color: #475569; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em;
}
.security-strip .s-item i { color: #10b981; font-size: 0.75rem; }

.remember-row {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 1.25rem;
}
.remember-row label { font-size: 0.82rem; color: #64748b; cursor: pointer; }
.remember-row a { font-size: 0.82rem; color: #10b981; text-decoration: none; font-weight: 600; }
.remember-row a:hover { text-decoration: underline; }

.login-footer { text-align: center; margin-top: 1.5rem; font-size: 0.72rem; color: #334155; }

.back-to-home {
    position: absolute;
    top: 2rem;
    left: 2rem;
    color: #94a3b8;
    text-decoration: none;
    font-size: 0.95rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    z-index: 10;
}
.back-to-home:hover {
    color: #10b981;
    transform: translateX(-4px);
}
</style>

<!-- Animated Background -->
<div class="login-bg">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
</div>
<div class="login-grid-overlay"></div>

<a href="<?= BASE_URL ?>/" class="back-to-home">
    <i class="fa-solid fa-arrow-left"></i> Back to Home
</a>

<div class="login-card">
    <!-- Brand Header -->
    <div class="text-center mb-4">
        <div class="brand-shield">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <div class="brand-title">NVOTS</div>
        <div class="brand-subtitle mt-1">National Vehicle Ownership &amp; Traceability System</div>
        <div class="gov-badge">
            <i class="fa-solid fa-landmark"></i>
            Federal Republic of Nigeria
        </div>
    </div>

    <!-- Error Alert -->
    <?php if (isset($error) && $error): ?>
        <div class="error-alert">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- Login Form -->
    <form method="POST" action="<?= BASE_URL ?>/auth/login" id="loginForm" novalidate>
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

        <div class="login-field">
            <label for="username">Email Address or Phone Number</label>
            <input type="text" id="username" name="username"
                   placeholder="e.g. admin@nvots.gov.ng or 08000000001"
                   required autocomplete="username">
            <i class="f-icon fa-solid fa-envelope"></i>
        </div>

        <div class="login-field" style="position:relative;">
            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   placeholder="••••••••••••"
                   required autocomplete="current-password">
            <i class="f-icon fa-solid fa-lock"></i>
            <span class="pw-toggle" id="pwToggle" onclick="togglePassword()">
                <i class="fa-solid fa-eye" id="pwEyeIcon"></i>
            </span>
        </div>

        <div class="remember-row">
            <label style="display:flex;align-items:center;gap:0.5rem;">
                <input type="checkbox" name="remember" id="remember"
                       style="width:15px;height:15px;accent-color:#10b981;">
                Remember me for 30 days
            </label>
            <a href="<?= BASE_URL ?>/auth/forgot">Forgot Password?</a>
        </div>

        <button type="submit" class="btn-signin" id="signinBtn">
            <i class="fa-solid fa-right-to-bracket"></i>
            Secure Sign In
        </button>
    </form>

    <!-- Security Strip -->
    <div class="security-strip">
        <div class="s-item"><i class="fa-solid fa-lock"></i> 256-bit SSL</div>
        <div class="s-item"><i class="fa-solid fa-shield-halved"></i> Encrypted</div>
        <div class="s-item"><i class="fa-solid fa-eye-slash"></i> Audited</div>
    </div>

    <div class="login-footer">
        &copy; <?= date('Y') ?> Federal Government of Nigeria · NVOTS v2.0 &nbsp;·&nbsp;
        Authorized Personnel Only
    </div>
</div>

<script>
function togglePassword() {
    const pw = document.getElementById('password');
    const icon = document.getElementById('pwEyeIcon');
    if (pw.type === 'password') {
        pw.type = 'text';
        icon.className = 'fa-solid fa-eye-slash';
    } else {
        pw.type = 'password';
        icon.className = 'fa-solid fa-eye';
    }
}

document.getElementById('loginForm').addEventListener('submit', function(e) {
    const btn = document.getElementById('signinBtn');
    const user = document.getElementById('username').value.trim();
    const pass = document.getElementById('password').value;
    if (!user || !pass) {
        e.preventDefault();
        btn.style.background = 'rgba(239,68,68,0.7)';
        btn.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Please fill all fields';
        setTimeout(() => {
            btn.style.background = '';
            btn.innerHTML = '<i class="fa-solid fa-right-to-bracket"></i> Secure Sign In';
        }, 2000);
        return;
    }
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Authenticating…';
});
</script>
