<?php
$title = 'Enterprise Dashboard - TANTRA GROUP OF INDUSTRIES';
require_once __DIR__ . '/../../middleware/Auth.php';
include __DIR__ . '/../layouts/corporate_header.php';

$auth = Auth::getInstance();
$user = $auth->getUser();

try {
    $pdo = getDBConnection();
    $totalCompanies = $pdo->query("SELECT COUNT(*) FROM companies")->fetchColumn();
    $liveCompanies = $pdo->query("SELECT COUNT(*) FROM companies WHERE status = 'Live'")->fetchColumn();
    $totalEmployees = $pdo->query("SELECT COUNT(*) FROM employees")->fetchColumn();
    $activeEmployees = $pdo->query("SELECT COUNT(*) FROM employees WHERE status = 'Active'")->fetchColumn();
    $totalDepartments = $pdo->query("SELECT COUNT(*) FROM departments")->fetchColumn();
    $pendingApprovals = $pdo->query("SELECT COUNT(*) FROM approvals WHERE status = 'Pending'")->fetchColumn();
    $totalWebsites = $pdo->query("SELECT COUNT(*) FROM websites WHERE status = 'Published'")->fetchColumn();
    $totalNotifications = $pdo->query("SELECT COUNT(*) FROM notifications WHERE is_read = 0")->fetchColumn();
} catch (PDOException $e) {
    $totalCompanies = $liveCompanies = $totalEmployees = $activeEmployees = 0;
    $totalDepartments = $pendingApprovals = $totalWebsites = $totalNotifications = 0;
}
?>

<div class="panel">
    <div class="panel-header">
        <h3>Executive Dashboard</h3>
    </div>
    <div class="panel-body">
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-icon stat-icon-blue">&#127970;</div>
                <div class="stat-content">
                    <h3><?= $totalCompanies ?></h3>
                    <p>Total Companies</p>
                </div>
                <div class="stat-footer">
                    <span class="stat-percent"><?= $liveCompanies ?> Live</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-green">&#128101;</div>
                <div class="stat-content">
                    <h3><?= $totalEmployees ?></h3>
                    <p>Total Employees</p>
                </div>
                <div class="stat-footer">
                    <span class="stat-percent"><?= $activeEmployees ?> Active</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-purple">&#128196;</div>
                <div class="stat-content">
                    <h3><?= $totalDepartments ?></h3>
                    <p>Departments</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-orange">&#128203;</div>
                <div class="stat-content">
                    <h3><?= $pendingApprovals ?></h3>
                    <p>Pending Approvals</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-teal">&#127760;</div>
                <div class="stat-content">
                    <h3><?= $totalWebsites ?></h3>
                    <p>Published Websites</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-red">&#128276;</div>
                <div class="stat-content">
                    <h3><?= $totalNotifications ?></h3>
                    <p>Unread Notifications</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <h3>Quick Actions</h3>
    </div>
    <div class="panel-body">
        <div class="quick-actions">
            <a href="/corporate/employees" class="action-card">
                <span class="action-icon">&#128101;</span>
                <span class="action-label">Employee Management</span>
            </a>
            <a href="/corporate/departments" class="action-card">
                <span class="action-icon">&#128196;</span>
                <span class="action-label">Department Management</span>
            </a>
            <a href="/corporate/approvals" class="action-card">
                <span class="action-icon">&#128203;</span>
                <span class="action-label">Approval Center</span>
            </a>
            <a href="/corporate/security" class="action-card">
                <span class="action-icon">&#128737;</span>
                <span class="action-label">Security Center</span>
            </a>
            <a href="/corporate/reports" class="action-card">
                <span class="action-icon">&#128202;</span>
                <span class="action-label">Reports Center</span>
            </a>
            <a href="/corporate/companies" class="action-card">
                <span class="action-icon">&#127970;</span>
                <span class="action-label">Company Management</span>
            </a>
        </div>
    </div>
</div>

<style>
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
}
.stat-card {
    background: var(--color-white);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    transition: all var(--transition-base);
}
.stat-card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}
.stat-icon {
    font-size: 2rem;
    margin-bottom: 1rem;
    display: inline-block;
    width: 48px;
    height: 48px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
}
.stat-icon-blue { background: #e8f0fe; color: #1a73e8; }
.stat-icon-green { background: #e6f4ea; color: #1e8e3e; }
.stat-icon-purple { background: #f3e8fd; color: #7c3aed; }
.stat-icon-orange { background: #fef3e0; color: #ea8600; }
.stat-icon-teal { background: #e0f2f1; color: #00796b; }
.stat-icon-red { background: #fce8e6; color: #d93025; }
.stat-content h3 { font-size: 2rem; margin: 0 0 0.25rem 0; }
.stat-content p { color: var(--color-text-muted); margin: 0; font-size: 0.875rem; }
.stat-footer { margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--color-border); }
.stat-percent { font-size: 0.875rem; color: var(--color-accent); font-weight: 600; }
.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
}
.action-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
    padding: 1.5rem;
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    text-decoration: none;
    color: var(--color-text);
    transition: all var(--transition-base);
}
.action-card:hover {
    border-color: var(--color-accent);
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}
.action-icon { font-size: 2rem; }
.action-label { font-weight: 600; font-size: 0.875rem; text-align: center; }
</style>

<?php include __DIR__ . '/../layouts/corporate_footer.php'; ?>
