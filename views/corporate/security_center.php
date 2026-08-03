<?php
$title = 'Security Center';
require_once __DIR__ . '/../../middleware/Auth.php';
include __DIR__ . '/../layouts/corporate_header.php';

$auth = Auth::getInstance();
$user = $auth->getUser();

try {
    $pdo = getDBConnection();
    $totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalSessions = $pdo->query("SELECT COUNT(*) FROM sessions")->fetchColumn();
    $totalAuditLogs = $pdo->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();
    $recentLogs = $pdo->query("SELECT a.*, u.username FROM audit_logs a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.timestamp DESC LIMIT 20")->fetchAll();
} catch (PDOException $e) {
    $totalUsers = $totalSessions = $totalAuditLogs = 0;
    $recentLogs = [];
}
?>

<div class="panel">
    <div class="panel-header"><h3>Security Center</h3></div>
    <div class="panel-body">
        <div class="dashboard-grid">
            <div class="stat-card"><div class="stat-icon stat-icon-blue">&#128101;</div><div class="stat-content"><h3><?= $totalUsers ?></h3><p>System Users</p></div></div>
            <div class="stat-card"><div class="stat-icon stat-icon-green">&#128274;</div><div class="stat-content"><h3><?= $totalSessions ?></h3><p>Active Sessions</p></div></div>
            <div class="stat-card"><div class="stat-icon stat-icon-orange">&#128196;</div><div class="stat-content"><h3><?= $totalAuditLogs ?></h3><p>Audit Log Entries</p></div></div>
            <div class="stat-card"><div class="stat-icon stat-icon-red">&#128737;</div><div class="stat-content"><h3>3</h3><p>Roles Configured</p></div></div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header"><h3>Recent Activity</h3></div>
    <div class="panel-body">
        <div class="table-responsive"><table class="table"><thead><tr><th>User</th><th>Action</th><th>Module</th><th>Timestamp</th></tr></thead>
            <tbody><?php foreach ($recentLogs as $log): ?><tr><td><?= htmlspecialchars($log['username'] ?? 'System') ?></td><td><?= htmlspecialchars($log['action']) ?></td><td><?= htmlspecialchars($log['module']) ?></td><td><?= date('M j, Y g:i A', strtotime($log['timestamp'])) ?></td></tr><?php endforeach; ?></tbody></table></div>
    </div>
</div>
<?php include __DIR__ . '/../layouts/corporate_footer.php'; ?>
