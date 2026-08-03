<?php
$title = 'Employee Management';
require_once __DIR__ . '/../../middleware/Auth.php';
include __DIR__ . '/../layouts/corporate_header.php';

$auth = Auth::getInstance();
$user = $auth->getUser();

try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT e.*, c.company_name, d.department_name FROM employees e LEFT JOIN companies c ON e.company_id = c.id LEFT JOIN departments d ON e.department_id = d.id ORDER BY e.created_at DESC");
    $employees = $stmt->fetchAll();
    $companies = $pdo->query("SELECT id, company_name FROM companies ORDER BY company_name ASC")->fetchAll();
    $departments = $pdo->query("SELECT id, department_name FROM departments ORDER BY department_name ASC")->fetchAll();
} catch (PDOException $e) {
    $employees = []; $companies = []; $departments = [];
}
?>

<div class="panel">
    <div class="panel-header panel-header-between">
        <h3>Employee Management (<?= count($employees) ?>)</h3>
        <button class="btn btn-primary btn-sm" onclick="document.getElementById('addEmployeeModal').style.display='block'">+ Add Employee</button>
    </div>
    <div class="panel-body">
        <?php if (empty($employees)): ?>
            <div class="empty-state"><div class="empty-icon">&#128101;</div><h3>No Employees</h3><p>Add employees to your organization.</p></div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>Employee ID</th><th>Name</th><th>Company</th><th>Department</th><th>Designation</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($employees as $emp): ?>
                    <tr>
                        <td><code><?= htmlspecialchars($emp['employee_id']) ?></code></td>
                        <td><strong><?= htmlspecialchars($emp['full_name']) ?></strong></td>
                        <td><?= htmlspecialchars($emp['company_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($emp['department_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($emp['designation']) ?></td>
                        <td><span class="role-badge"><?= htmlspecialchars($emp['role']) ?></span></td>
                        <td><span class="status-badge status-<?= strtolower($emp['status']) ?>"><?= $emp['status'] ?></span></td>
                        <td><a href="#" class="btn btn-ghost btn-sm">Edit</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<div id="addEmployeeModal" class="modal">
    <div class="modal-content">
        <div class="modal-header"><h3>Add Employee</h3><span class="modal-close" onclick="this.closest('.modal').style.display='none'">&times;</span></div>
        <div class="modal-body">
            <form method="POST" action="/corporate/employees">
                <div class="form-grid">
                    <div class="form-group"><label>Employee ID *</label><input type="text" name="employee_id" required></div>
                    <div class="form-group"><label>Full Name *</label><input type="text" name="full_name" required></div>
                    <div class="form-group"><label>Email *</label><input type="email" name="email" required></div>
                    <div class="form-group"><label>Phone</label><input type="text" name="phone"></div>
                    <div class="form-group"><label>Company</label><select name="company_id"><option value="">Select Company</option><?php foreach ($companies as $c): ?><option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['company_name']) ?></option><?php endforeach; ?></select></div>
                    <div class="form-group"><label>Department</label><select name="department_id"><option value="">Select Department</option><?php foreach ($departments as $d): ?><option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['department_name']) ?></option><?php endforeach; ?></select></div>
                    <div class="form-group"><label>Designation *</label><input type="text" name="designation" required></div>
                    <div class="form-group"><label>Role</label><select name="role"><option value="Employee">Employee</option><option value="Manager">Manager</option><option value="Director">Director</option><option value="Viewer">Viewer</option></select></div>
                    <div class="form-group"><label>Joining Date</label><input type="date" name="joining_date"></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="this.closest('.modal').style.display='none'">Cancel</button><button type="submit" class="btn btn-primary">Add Employee</button></div>
            </form>
        </div>
    </div>
</div>

<style>
.role-badge {
    background: var(--color-bg-light);
    padding: 2px 8px;
    border-radius: var(--radius-sm);
    font-size: 0.75rem;
    font-weight: 600;
}
</style>
<?php include __DIR__ . '/../layouts/corporate_footer.php'; ?>
