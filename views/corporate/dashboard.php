<?php
$title = 'Dashboard Overview';
require_once __DIR__ . '/../../middleware/Auth.php';
require_once __DIR__ . '/../../middleware/AuditLogger.php';
include __DIR__ . '/../layouts/corporate_header.php';

$auth = Auth::getInstance();
$user = $auth->getUser();
$auditLogger = AuditLogger::getInstance();

// Get dashboard stats
try {
    $pdo = getDBConnection();
    
    // Total companies
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM companies");
    $totalCompanies = (int)$stmt->fetch()['total'];
    
    // Live companies
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM companies WHERE status = 'Live'");
    $liveCompanies = (int)$stmt->fetch()['total'];
    
    // Draft companies
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM companies WHERE status = 'Draft'");
    $draftCompanies = (int)$stmt->fetch()['total'];
    
    // Recent audit logs
    $recentLogs = $auditLogger->getRecentLogs(5);
    
} catch (PDOException $e) {
    $totalCompanies = 0;
    $liveCompanies = 0;
    $draftCompanies = 0;
    $recentLogs = [];
}
?>

<div class="dashboard-stats">
    <div class="stat-card">
        <div class="stat-icon stat-icon-total">&#9733;</div>
        <div class="stat-content">
            <span class="stat-value"><?= $totalCompanies ?></span>
            <span class="stat-label">Total Companies</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-live">&#10003;</div>
        <div class="stat-content">
            <span class="stat-value"><?= $liveCompanies ?></span>
            <span class="stat-label">Live</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-draft">&#9998;</div>
        <div class="stat-content">
            <span class="stat-value"><?= $draftCompanies ?></span>
            <span class="stat-label">Draft</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-user">&#9679;</div>
        <div class="stat-content">
            <span class="stat-value"><?= htmlspecialchars($user['role'] ?? '') ?></span>
            <span class="stat-label">Your Role</span>
        </div>
    </div>
</div>

<div class="dashboard-panels">
    <div class="panel panel-welcome">
        <div class="panel-body">
            <h2>Welcome, <?= htmlspecialchars($user['username'] ?? 'User') ?>!</h2>
            <p>You are logged in as <strong><?= htmlspecialchars($user['role'] ?? '') ?></strong>.</p>
            <p>Use the sidebar to navigate between modules. From here you can manage companies, view your profile, and review audit logs.</p>
        </div>
    </div>
    
    <div class="panel panel-quick-actions">
        <div class="panel-header">
            <h3>Quick Actions</h3>
        </div>
        <div class="panel-body">
            <div class="quick-actions">
                <a href="/corporate/companies/add" class="quick-action-btn">
                    <span class="qa-icon">+</span>
                    <span class="qa-label">Add Company</span>
                </a>
                <a href="/corporate/companies" class="quick-action-btn">
                    <span class="qa-icon">&#9776;</span>
                    <span class="qa-label">View Companies</span>
                </a>
                <a href="/corporate/audit-logs" class="quick-action-btn">
                    <span class="qa-icon">&#9776;</span>
                    <span class="qa-label">Audit Logs</span>
                </a>
                <a href="/corporate/profile" class="quick-action-btn">
                    <span class="qa-icon">&#9679;</span>
                    <span class="qa-label">My Profile</span>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <h3>Recent Activity</h3>
    </div>
    <div class="panel-body">
        <?php if (empty($recentLogs)): ?>
            <p class="text-muted">No recent activity.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Module</th>
                        <th>User</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentLogs as $log): ?>
                    <tr>
                        <td><span class="badge badge-<?= strtolower($log['action']) ?>"><?= htmlspecialchars($log['action']) ?></span></td>
                        <td><?= htmlspecialchars($log['module']) ?></td>
                        <td><?= htmlspecialchars($log['username'] ?? 'System') ?></td>
                        <td><?= htmlspecialchars(date('M j, Y g:i A', strtotime($log['timestamp']))) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../layouts/corporate_footer.php'; ?>