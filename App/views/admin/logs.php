<?php
// App/views/admin/logs.php
?>
<div class="card glass-panel border-0 p-4">
    <h4 class="text-white mb-4"><i class="fa-solid fa-clock-rotate-left text-success me-2"></i>System Trace & Audit Logs</h4>

    <ul class="nav nav-tabs mb-4" id="logTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active text-white" id="audit-tab" data-bs-toggle="tab" data-bs-target="#audit-pane" type="button" role="tab" aria-controls="audit-pane" aria-selected="true">
                <i class="fa-solid fa-shield-halved me-1"></i> Core Audit Logs (Immutable)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link text-white" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity-pane" type="button" role="tab" aria-controls="activity-pane" aria-selected="false">
                <i class="fa-solid fa-clock me-1"></i> Activity Tracking Logs
            </button>
        </li>
    </ul>

    <div class="tab-content" id="logTabsContent">
        <!-- Audit Logs Pane -->
        <div class="tab-pane fade show active" id="audit-pane" role="tabpanel" aria-labelledby="audit-tab">
            <div class="table-responsive">
                <table class="table text-secondary w-100" id="auditTable">
                    <thead>
                        <tr>
                            <th>User Email</th>
                            <th>Action Performed</th>
                            <th>IP Address</th>
                            <th>User Agent</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($auditLogs as $log): ?>
                            <tr>
                                <td class="text-white"><?= htmlspecialchars($log['email']) ?> <span class="badge bg-dark ms-1"><?= htmlspecialchars($log['role']) ?></span></td>
                                <td><span class="text-info"><?= htmlspecialchars($log['action']) ?></span></td>
                                <td><?= htmlspecialchars($log['ip_address']) ?></td>
                                <td class="small"><?= htmlspecialchars($log['user_agent']) ?></td>
                                <td><?= htmlspecialchars($log['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Activity Logs Pane -->
        <div class="tab-pane fade" id="activity-pane" role="tabpanel" aria-labelledby="activity-tab">
            <div class="table-responsive">
                <table class="table text-secondary w-100" id="activityTable">
                    <thead>
                        <tr>
                            <th>Activity Description</th>
                            <th>Performed By</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activityLogs as $log): ?>
                            <tr>
                                <td class="text-white"><?= htmlspecialchars($log['description']) ?></td>
                                <td><?= htmlspecialchars($log['email'] ?? 'System/Guest') ?></td>
                                <td><?= htmlspecialchars($log['performed_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Initialize DataTables
        $('#auditTable').DataTable({
            order: [[4, 'desc']],
            pageLength: 25
        });

        $('#activityTable').DataTable({
            order: [[2, 'desc']],
            pageLength: 25
        });
    });
</script>
