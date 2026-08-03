<?php
$title = 'Audit Logs';
require_once __DIR__ . '/../../middleware/Auth.php';
require_once __DIR__ . '/../../middleware/AuditLogger.php';
include __DIR__ . '/../layouts/corporate_header.php';

$auth = Auth::getInstance();
$user = $auth->getUser();
$auditLogger = AuditLogger::getInstance();

$page = max(1, (int)($_GET['page'] ?? 1));
$module = $_GET['module'] ?? null;
$action = $_GET['action'] ?? null;

$result = $auditLogger->getLogs($page, 50, $module, $action);
$logs = $result['logs'];
$totalPages = $result['totalPages'];
$currentPage = $result['page'];
?>

<div class="panel">
    <div class="panel-header panel-header-between">
        <h3>Audit Trail</h3>
        <div class="panel-actions">
            <a href="/corporate/audit-logs" class="btn btn-ghost btn-sm">Clear Filters</a>
        </div>
    </div>
    <div class="panel-body">
        <div class="filters">
            <form method="GET" action="/corporate/audit-logs" class="filter-form">
                <div class="filter-group">
                    <select name="module">
                        <option value="">All Modules</option>
                        <option value="Auth" <?= $module === 'Auth' ? 'selected' : '' ?>>Auth</option>
                        <option value="Company Management" <?= $module === 'Company Management' ? 'selected' : '' ?>>Company Management</option>
                    </select>
                </div>
                <div class="filter-group">
                    <select name="action">
                        <option value="">All Actions</option>
                        <option value="LOGIN_SUCCESS" <?= $action === 'LOGIN_SUCCESS' ? 'selected' : '' ?>>Login Success</option>
                        <option value="LOGIN_FAILED" <?= $action === 'LOGIN_FAILED' ? 'selected' : '' ?>>Login Failed</option>
                        <option value="LOGOUT" <?= $action === 'LOGOUT' ? 'selected' : '' ?>>Logout</option>
                        <option value="COMPANY_CREATED" <?= $action === 'COMPANY_CREATED' ? 'selected' : '' ?>>Company Created</option>
                        <option value="COMPANY_UPDATED" <?= $action === 'COMPANY_UPDATED' ? 'selected' : '' ?>>Company Updated</option>
                        <option value="COMPANY_STATUS_CHANGED" <?= $action === 'COMPANY_STATUS_CHANGED' ? 'selected' : '' ?>>Status Changed</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            </form>
        </div>
        
        <?php if (empty($logs)): ?>
            <div class="empty-state">
                <div class="empty-icon">&#9776;</div>
                <h3>No Audit Logs Found</h3>
                <p>No activity has been recorded yet.</p>
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th>Description</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?= htmlspecialchars(date('M j, Y g:i:s A', strtotime($log['timestamp']))) ?></td>
                        <td><?= htmlspecialchars($log['username'] ?? 'System') ?></td>
                        <td><span class="role-badge role-<?= strtolower($log['role'] ?? '') ?>"><?= htmlspecialchars($log['role'] ?? 'N/A') ?></span></td>
                        <td><span class="badge badge-<?= strtolower($log['action']) ?>"><?= htmlspecialchars($log['action']) ?></span></td>
                        <td><?= htmlspecialchars($log['module']) ?></td>
                        <td><?= htmlspecialchars($log['description'] ?? '') ?></td>
                        <td><code><?= htmlspecialchars($log['ip_address'] ?? '') ?></code></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($currentPage > 1): ?>
                <a href="?page=<?= $currentPage - 1 ?>&module=<?= urlencode($module ?? '') ?>&action=<?= urlencode($action ?? '') ?>" class="btn btn-ghost btn-sm">&laquo; Previous</a>
            <?php endif; ?>
            
            <span class="pagination-info">Page <?= $currentPage ?> of <?= $totalPages ?></span>
            
            <?php if ($currentPage < $totalPages): ?>
                <a href="?page=<?= $currentPage + 1 ?>&module=<?= urlencode($module ?? '') ?>&action=<?= urlencode($action ?? '') ?>" class="btn btn-ghost btn-sm">Next &raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../layouts/corporate_footer.php'; ?>