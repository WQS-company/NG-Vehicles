<?php // App/views/dashboard.php
use Core\Auth;
?>

<style>
/* ── Dashboard Specific Styles ──────────────────────────────────── */
.stat-card-v2 {
    position: relative;
    overflow: hidden;
    border-radius: 18px;
    padding: 1.5rem;
    background: rgba(255,255,255,0.06);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255,255,255,0.1);
    transition: all 0.35s ease;
    cursor: default;
}
.stat-card-v2:hover {
    transform: translateY(-4px);
    border-color: rgba(255,255,255,0.18);
    box-shadow: 0 16px 40px rgba(0,0,0,0.3);
}
.stat-card-v2 .glow-orb {
    position: absolute;
    width: 140px; height: 140px;
    border-radius: 50%;
    top: -40px; right: -40px;
    filter: blur(40px);
    opacity: 0.25;
}
.stat-card-v2 .stat-label {
    font-size: 0.7rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.1em;
    color: #64748b; margin-bottom: 0.6rem;
}
.stat-card-v2 .stat-value {
    font-size: 2rem; font-weight: 800;
    color: #f8fafc; line-height: 1;
    margin-bottom: 0.5rem;
    font-variant-numeric: tabular-nums;
}
.stat-card-v2 .stat-footer {
    font-size: 0.75rem; color: #475569;
    display: flex; align-items: center; gap: 0.4rem;
}
.stat-card-v2 .stat-icon-box {
    width: 46px; height: 46px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; margin-bottom: 1rem;
}

/* Mini Stat Chips */
.mini-stat-chips { display: grid; grid-template-columns: repeat(4,1fr); gap: 0.75rem; margin-bottom: 1.5rem; }
.mini-chip {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 12px;
    padding: 0.85rem 1rem;
    text-align: center;
    transition: all 0.3s;
}
.mini-chip:hover { background: rgba(255,255,255,0.07); transform: translateY(-2px); }
.mini-chip .chip-value { font-size: 1.35rem; font-weight: 800; line-height: 1.1; }
.mini-chip .chip-label { font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; color: #475569; margin-top: 0.25rem; }

/* Panel Headers */
.panel-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 1.25rem;
}
.panel-header h5 {
    font-size: 0.9rem; font-weight: 700; color: #f8fafc;
    text-transform: uppercase; letter-spacing: 0.04em; margin: 0;
    display: flex; align-items: center; gap: 0.6rem;
}
.panel-header h5 i { color: #10b981; }
.panel-header a {
    font-size: 0.75rem; color: #10b981; text-decoration: none; font-weight: 600;
    display: flex; align-items: center; gap: 0.3rem;
}
.panel-header a:hover { text-decoration: underline; }

/* Chart Panel */
.chart-panel {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 16px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

/* Recent Registrations List */
.reg-list-item {
    display: flex; align-items: center; gap: 1rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    transition: all 0.2s;
}
.reg-list-item:last-child { border-bottom: none; }
.reg-list-item:hover { padding-left: 4px; }
.reg-avatar {
    width: 40px; height: 40px; border-radius: 10px;
    background: linear-gradient(135deg, #10b981, #3b82f6);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.85rem; color: #fff; font-weight: 700; flex-shrink: 0;
}
.reg-info { flex: 1; min-width: 0; }
.reg-name { font-size: 0.88rem; font-weight: 600; color: #f8fafc; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.reg-vin  { font-size: 0.7rem; color: #475569; font-family: monospace; }
.reg-plate-badge {
    font-size: 0.7rem; font-weight: 700;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.12);
    color: #94a3b8;
    padding: 0.25rem 0.6rem;
    border-radius: 6px; white-space: nowrap;
}

/* Activity Feed */
.activity-item {
    display: flex; align-items: flex-start; gap: 0.75rem;
    padding: 0.7rem 0;
    border-bottom: 1px solid rgba(255,255,255,0.04);
}
.activity-item:last-child { border-bottom: none; }
.activity-dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: #10b981; flex-shrink: 0; margin-top: 5px;
    box-shadow: 0 0 0 3px rgba(16,185,129,0.15);
}
.activity-text { font-size: 0.8rem; color: #94a3b8; line-height: 1.5; }
.activity-time { font-size: 0.68rem; color: #475569; margin-top: 0.1rem; }

/* Transfer Table */
.dash-table { width: 100%; border-collapse: collapse; }
.dash-table th {
    font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.08em;
    color: #475569; font-weight: 700; padding: 0.6rem 0.75rem;
    background: rgba(255,255,255,0.03);
    border-bottom: 1px solid rgba(255,255,255,0.07);
}
.dash-table td {
    font-size: 0.82rem; color: #94a3b8;
    padding: 0.65rem 0.75rem;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    vertical-align: middle;
}
.dash-table tr:last-child td { border-bottom: none; }
.dash-table tr:hover td { background: rgba(255,255,255,0.02); color: #f8fafc; }
.plate-tag {
    font-family: monospace; font-size: 0.78rem; font-weight: 700;
    background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.2);
    color: #10b981; padding: 0.2rem 0.6rem; border-radius: 6px;
}

/* Quick Actions */
.quick-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
.qa-btn {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 0.5rem; padding: 1rem;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 14px; text-decoration: none;
    transition: all 0.3s ease; cursor: pointer;
}
.qa-btn:hover {
    background: rgba(16,185,129,0.08);
    border-color: rgba(16,185,129,0.3);
    transform: translateY(-3px);
}
.qa-btn .qa-icon {
    width: 42px; height: 42px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem;
}
.qa-btn .qa-label { font-size: 0.75rem; font-weight: 600; color: #94a3b8; text-align: center; }
.qa-btn:hover .qa-label { color: #10b981; }

/* Welcome Banner */
.welcome-banner {
    background: linear-gradient(135deg, rgba(16,185,129,0.12), rgba(59,130,246,0.08));
    border: 1px solid rgba(16,185,129,0.2);
    border-radius: 16px;
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.welcome-banner .wt { font-size: 1.1rem; font-weight: 700; color: #f8fafc; }
.welcome-banner .ws { font-size: 0.8rem; color: #64748b; margin-top: 0.15rem; }
</style>

<!-- Welcome Banner -->
<div class="welcome-banner">
    <div>
        <div class="wt">
            <?php
            $hour = (int)date('H');
            $greet = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');
            echo $greet . ', ' . htmlspecialchars(explode(' ', $user['full_name'] ?? 'Officer')[0]) . ' 👋';
            ?>
        </div>
        <div class="ws">
            <i class="fa-solid fa-calendar-day me-1"></i>
            <?= date('l, d F Y') ?> &nbsp;·&nbsp;
            <i class="fa-solid fa-clock me-1"></i>
            <span id="liveClock"></span> WAT &nbsp;·&nbsp;
            NVOTS National Registry &mdash; Operational
        </div>
    </div>
    <div class="d-none d-md-flex align-items-center gap-2">
        <div style="width:10px;height:10px;border-radius:50%;background:#10b981;box-shadow:0 0 0 3px rgba(16,185,129,0.25);animation:pulse-glow 2s infinite;"></div>
        <span style="font-size:0.78rem;color:#10b981;font-weight:600;">SYSTEM ONLINE</span>
    </div>
</div>

<!-- Row 1: Primary Stats -->
<div class="row g-3 mb-3">
    <?php if (Auth::role() === ROLE_SUPER_ADMIN || Auth::hasFeature('registration')): ?>
    <!-- Total Vehicles -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card-v2">
            <div class="glow-orb" style="background:#10b981;"></div>
            <div class="stat-icon-box" style="background:rgba(16,185,129,0.15);">
                <i class="fa-solid fa-car" style="color:#10b981;"></i>
            </div>
            <div class="stat-label">Total Vehicles</div>
            <div class="stat-value count-up" data-target="<?= $stats['vehicles'] ?>"><?= number_format($stats['vehicles']) ?></div>
            <div class="stat-footer"><i class="fa-solid fa-circle-check" style="color:#10b981;"></i> Active Registry Database</div>
        </div>
    </div>
    <!-- Total Owners -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card-v2">
            <div class="glow-orb" style="background:#3b82f6;"></div>
            <div class="stat-icon-box" style="background:rgba(59,130,246,0.15);">
                <i class="fa-solid fa-users" style="color:#3b82f6;"></i>
            </div>
            <div class="stat-label">Registered Owners</div>
            <div class="stat-value"><?= number_format($stats['owners']) ?></div>
            <div class="stat-footer"><i class="fa-solid fa-fingerprint" style="color:#3b82f6;"></i> NIN-Verified Identities</div>
        </div>
    </div>
    <!-- Total Transfers -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card-v2">
            <div class="glow-orb" style="background:#f59e0b;"></div>
            <div class="stat-icon-box" style="background:rgba(245,158,11,0.15);">
                <i class="fa-solid fa-right-left" style="color:#f59e0b;"></i>
            </div>
            <div class="stat-label">Ownership Transfers</div>
            <div class="stat-value"><?= number_format($stats['transfers']) ?></div>
            <div class="stat-footer"><i class="fa-solid fa-link" style="color:#f59e0b;"></i> Immutable Trace Chains</div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (Auth::role() === ROLE_SUPER_ADMIN || Auth::hasFeature('payments')): ?>
    <!-- Total Revenue -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card-v2">
            <div class="glow-orb" style="background:#8b5cf6;"></div>
            <div class="stat-icon-box" style="background:rgba(139,92,246,0.15);">
                <i class="fa-solid fa-naira-sign" style="color:#8b5cf6;"></i>
            </div>
            <div class="stat-label">Collected Revenue</div>
            <div class="stat-value" style="font-size:1.6rem;">₦<?= number_format($stats['revenue'], 0) ?></div>
            <div class="stat-footer"><i class="fa-solid fa-receipt" style="color:#8b5cf6;"></i> Cash &amp; Bank Transfers</div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Row 2: Mini Chips -->
<div class="mini-stat-chips mb-3">
    <?php if (Auth::role() === ROLE_SUPER_ADMIN || Auth::hasFeature('verification')): ?>
    <div class="mini-chip">
        <div class="chip-value" style="color:#f59e0b;"><?= $stats['pending_ver'] ?></div>
        <div class="chip-label">Pending Verif.</div>
    </div>
    <div class="mini-chip">
        <div class="chip-value" style="color:#10b981;"><?= $stats['approved_ver'] ?></div>
        <div class="chip-label">Approved Verif.</div>
    </div>
    <?php endif; ?>

    <?php if (Auth::role() === ROLE_SUPER_ADMIN || Auth::hasFeature('registration')): ?>
    <div class="mini-chip">
        <div class="chip-value" style="color:#10b981;"><?= $stats['active_vehicles'] ?></div>
        <div class="chip-label">Active Vehicles</div>
    </div>
    <div class="mini-chip">
        <div class="chip-value" style="color:#ef4444;"><?= $stats['suspended_vehicles'] ?></div>
        <div class="chip-label">Suspended</div>
    </div>
    <?php endif; ?>
</div>

<!-- Row 3: Chart + Right Panels -->
<div class="row g-3 mb-3">
    <!-- Chart -->
    <div class="col-lg-8">
        <div class="chart-panel">
            <div class="panel-header">
                <h5><i class="fa-solid fa-chart-line"></i> Monthly Registration Trends</h5>
                <span style="font-size:0.72rem;color:#475569;">Last 12 months</span>
            </div>
            <canvas id="registrationChart" style="max-height:280px;"></canvas>
        </div>

        <?php if (Auth::role() === ROLE_SUPER_ADMIN || Auth::hasFeature('registration')): ?>
        <!-- Recent Transfers Table -->
        <div class="chart-panel">
            <div class="panel-header">
                <h5><i class="fa-solid fa-right-left"></i> Recent Ownership Transfers</h5>
                <a href="<?= BASE_URL ?>/transfer/create"><i class="fa-solid fa-arrow-right"></i> View All</a>
            </div>
            <?php if (empty($recentTransfers)): ?>
                <div class="text-center py-4 text-secondary">
                    <i class="fa-solid fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                    No transfers recorded yet.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th>Plate</th><th>Seller</th><th>Buyer</th><th>Amount</th><th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentTransfers as $t): ?>
                            <tr>
                                <td><span class="plate-tag"><?= htmlspecialchars($t['plate_number']) ?></span></td>
                                <td><?= htmlspecialchars($t['seller_name']) ?></td>
                                <td><?= htmlspecialchars($t['buyer_name']) ?></td>
                                <td style="color:#10b981;font-weight:600;">₦<?= number_format($t['sale_price'], 2) ?></td>
                                <td><?= htmlspecialchars($t['transfer_date']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Right Column -->
    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="chart-panel mb-3">
            <div class="panel-header"><h5><i class="fa-solid fa-bolt"></i> Quick Actions</h5></div>
            <div class="quick-actions">
                <?php if (Auth::role() === ROLE_SUPER_ADMIN || Auth::hasFeature('registration')): ?>
                <a href="<?= BASE_URL ?>/vehicle/register" class="qa-btn">
                    <div class="qa-icon" style="background:rgba(16,185,129,0.15);">
                        <i class="fa-solid fa-car-rear" style="color:#10b981;"></i>
                    </div>
                    <div class="qa-label">Register Vehicle</div>
                </a>
                <a href="<?= BASE_URL ?>/owner/register" class="qa-btn">
                    <div class="qa-icon" style="background:rgba(59,130,246,0.15);">
                        <i class="fa-solid fa-user-plus" style="color:#3b82f6;"></i>
                    </div>
                    <div class="qa-label">Register Owner</div>
                </a>
                <a href="<?= BASE_URL ?>/transfer/create" class="qa-btn">
                    <div class="qa-icon" style="background:rgba(139,92,246,0.15);">
                        <i class="fa-solid fa-right-left" style="color:#8b5cf6;"></i>
                    </div>
                    <div class="qa-label">Transfer Ownership</div>
                </a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/search" class="qa-btn">
                    <div class="qa-icon" style="background:rgba(245,158,11,0.15);">
                        <i class="fa-solid fa-magnifying-glass" style="color:#f59e0b;"></i>
                    </div>
                    <div class="qa-label">Search & Trace</div>
                </a>
                <?php if (Auth::role() === ROLE_SUPER_ADMIN || Auth::hasFeature('verification')): ?>
                <a href="<?= BASE_URL ?>/verification/manage" class="qa-btn">
                    <div class="qa-icon" style="background:rgba(139,92,246,0.15);">
                        <i class="fa-solid fa-file-signature" style="color:#8b5cf6;"></i>
                    </div>
                    <div class="qa-label">Verifications</div>
                </a>
                <?php endif; ?>
                <?php if (Auth::role() === ROLE_SUPER_ADMIN || Auth::hasFeature('payments')): ?>
                <a href="<?= BASE_URL ?>/payment/manage" class="qa-btn">
                    <div class="qa-icon" style="background:rgba(245,158,11,0.15);">
                        <i class="fa-solid fa-receipt" style="color:#f59e0b;"></i>
                    </div>
                    <div class="qa-label">Payments</div>
                </a>
                <?php endif; ?>
                <?php if (Auth::role() === ROLE_SUPER_ADMIN || Auth::hasFeature('correction')): ?>
                <a href="<?= BASE_URL ?>/correction" class="qa-btn">
                    <div class="qa-icon" style="background:rgba(239,68,68,0.15);">
                        <i class="fa-solid fa-file-pen" style="color:#ef4444;"></i>
                    </div>
                    <div class="qa-label">Data Correction</div>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (Auth::role() === ROLE_SUPER_ADMIN || Auth::hasFeature('registration')): ?>
        <!-- Recent Registrations -->
        <div class="chart-panel mb-3">
            <div class="panel-header">
                <h5><i class="fa-solid fa-folder-open"></i> Recent Onboardings</h5>
                <a href="<?= BASE_URL ?>/vehicle/register"><i class="fa-solid fa-arrow-right"></i> New</a>
            </div>
            <?php if (empty($recentRegistrations)): ?>
                <div class="text-center py-3 text-secondary" style="font-size:0.82rem;">
                    <i class="fa-solid fa-inbox d-block fa-lg mb-2 opacity-50"></i>No registrations yet.
                </div>
            <?php else: ?>
                <?php foreach ($recentRegistrations as $r): ?>
                    <div class="reg-list-item">
                        <div class="reg-avatar"><?= strtoupper(substr($r['manufacturer'], 0, 2)) ?></div>
                        <div class="reg-info">
                            <div class="reg-name"><?= htmlspecialchars($r['manufacturer'] . ' ' . $r['model']) ?></div>
                            <div class="reg-vin"><?= htmlspecialchars($r['vin']) ?></div>
                        </div>
                        <span class="reg-plate-badge"><?= htmlspecialchars($r['plate_number']) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Activity Log -->
        <div class="chart-panel">
            <div class="panel-header">
                <h5><i class="fa-solid fa-clock-rotate-left"></i> Activity Log</h5>
            </div>
            <?php if (empty($recentActivities)): ?>
                <div class="text-center py-3 text-secondary" style="font-size:0.82rem;">
                    <i class="fa-solid fa-inbox d-block fa-lg mb-2 opacity-50"></i>No activity yet.
                </div>
            <?php else: ?>
                <?php foreach ($recentActivities as $a): ?>
                    <div class="activity-item">
                        <div class="activity-dot"></div>
                        <div>
                            <div class="activity-text"><?= htmlspecialchars($a['description']) ?></div>
                            <div class="activity-time"><i class="fa-solid fa-clock me-1"></i><?= htmlspecialchars($a['performed_at']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Live clock
function updateClock() {
    const now = new Date();
    const h = String(now.getHours()).padStart(2,'0');
    const m = String(now.getMinutes()).padStart(2,'0');
    const s = String(now.getSeconds()).padStart(2,'0');
    const el = document.getElementById('liveClock');
    if (el) el.textContent = `${h}:${m}:${s}`;
}
updateClock();
setInterval(updateClock, 1000);

// Chart
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('registrationChart').getContext('2d');
    const chartData = <?= json_encode($chartData['monthlyReg'] ?? []) ?>;
    const labels = chartData.length ? chartData.map(d => d.month) : ['Jan','Feb','Mar','Apr','May','Jun'];
    const counts = chartData.length ? chartData.map(d => d.count) : [0,0,0,0,0,0];

    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Vehicles Registered',
                data: counts,
                borderColor: '#10b981',
                backgroundColor: function(ctx) {
                    const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 280);
                    g.addColorStop(0, 'rgba(16,185,129,0.25)');
                    g.addColorStop(1, 'rgba(16,185,129,0)');
                    return g;
                },
                tension: 0.4, fill: true,
                pointBackgroundColor: '#10b981',
                pointBorderColor: '#fff',
                pointRadius: 4, pointHoverRadius: 7,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: true,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: { labels: { color: '#94a3b8', font: { family: 'Outfit', size: 12 } } },
                tooltip: {
                    backgroundColor: 'rgba(15,23,42,0.92)',
                    borderColor: 'rgba(16,185,129,0.4)',
                    borderWidth: 1,
                    titleColor: '#f8fafc',
                    bodyColor: '#94a3b8',
                    padding: 10,
                    callbacks: {
                        label: ctx => ` ${ctx.parsed.y} vehicle${ctx.parsed.y !== 1 ? 's' : ''}`
                    }
                }
            },
            scales: {
                y: {
                    ticks: { color: '#475569', font: { family: 'Outfit' } },
                    grid: { color: 'rgba(255,255,255,0.04)' },
                    border: { dash: [4,4] }
                },
                x: {
                    ticks: { color: '#475569', font: { family: 'Outfit' } },
                    grid: { color: 'rgba(255,255,255,0.04)' },
                    border: { dash: [4,4] }
                }
            }
        }
    });
});
</script>
