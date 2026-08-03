<?php
$title = 'Department Management';
require_once __DIR__ . '/../../middleware/Auth.php';
include __DIR__ . '/../layouts/corporate_header.php';

$auth = Auth::getInstance();
try {
    $pdo = getDBConnection();
    $depts = $pdo->query("SELECT d.*, c.company_name FROM departments d LEFT JOIN companies c ON d.company_id = c.id ORDER BY d.created_at DESC")->fetchAll();
    $companies = $pdo->query("SELECT id, company_name FROM companies ORDER BY company_name ASC")->fetchAll();
} catch (PDOException $e) { $depts = []; $companies = []; }
?>

<div class="panel">
    <div class="panel-header panel-header-between">
        <h3>Departments (<?= count($depts) ?>)</h3>
        <button class="btn btn-primary btn-sm" onclick="document.getElementById('addDeptModal').style.display='block'">+ Add Department</button>
    </div>
    <div class="panel-body">
        <?php if (empty($depts)): ?><div class="empty-state"><div class="empty-icon">&#128196;</div><h3>No Departments</h3><p>Create departments for your organization.</p></div>
        <?php else: ?>
        <div class="table-responsive"><table class="table"><thead><tr><th>Department</th><th>Code</th><th>Company</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody><?php foreach ($depts as $d): ?><tr><td><strong><?= htmlspecialchars($d['department_name']) ?></strong></td><td><code><?= htmlspecialchars($d['department_code']) ?></code></td><td><?= htmlspecialchars($d['company_name'] ?? '-') ?></td><td><span class="status-badge status-<?= strtolower($d['status']) ?>"><?= $d['status'] ?></span></td><td><a href="#" class="btn btn-ghost btn-sm">Edit</a></td></tr><?php endforeach; ?></tbody></table></div>
        <?php endif; ?>
    </div>
</div>

<div id="addDeptModal" class="modal">
    <div class="modal-content">
        <div class="modal-header"><h3>Add Department</h3><span class="modal-close" onclick="this.closest('.modal').style.display='none'">&times;</span></div>
        <div class="modal-body"><form method="POST" action="/corporate/departments">
            <div class="form-group"><label>Department Name *</label><input type="text" name="department_name" required></div>
            <div class="form-group"><label>Department Code *</label><input type="text" name="department_code" required placeholder="e.g., OPS, TECH, FIN"></div>
            <div class="form-group"><label>Company</label><select name="company_id"><option value="">Select Company</option><?php foreach ($companies as $c): ?><option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['company_name']) ?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label>Description</label><textarea name="description" rows="3"></textarea></div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="this.closest('.modal').style.display='none'">Cancel</button><button type="submit" class="btn btn-primary">Create Department</button></div>
        </form></div>
    </div>
</div>
<?php include __DIR__ . '/../layouts/corporate_footer.php'; ?>
