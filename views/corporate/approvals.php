<?php
$title = 'Approval Center';
require_once __DIR__ . '/../../middleware/Auth.php';
include __DIR__ . '/../layouts/corporate_header.php';

$auth = Auth::getInstance();
$user = $auth->getUser();

try {
    $pdo = getDBConnection();
    $approvals = $pdo->query("SELECT a.*, u.username as requester, r.username as reviewer FROM approvals a LEFT JOIN users u ON a.requested_by = u.id LEFT JOIN users r ON a.reviewed_by = r.id ORDER BY a.created_at DESC")->fetchAll();
    $pendingCount = $pdo->query("SELECT COUNT(*) FROM approvals WHERE status = 'Pending'")->fetchColumn();
} catch (PDOException $e) { $approvals = []; $pendingCount = 0; }
?>

<div class="panel">
    <div class="panel-header panel-header-between">
        <h3>Approval Center</h3>
        <?php if ($pendingCount > 0): ?><span class="badge"><?= $pendingCount ?> Pending</span><?php endif; ?>
    </div>
    <div class="panel-body">
        <?php if (empty($approvals)): ?><div class="empty-state"><div class="empty-icon">&#128203;</div><h3>No Approvals</h3><p>No approval requests have been created yet.</p></div>
        <?php else: ?>
        <div class="table-responsive"><table class="table"><thead><tr><th>Type</th><th>Status</th><th>Requester</th><th>Reviewer</th><th>Created</th><th>Actions</th></tr></thead>
            <tbody><?php foreach ($approvals as $a): ?><tr><td><?= $a['approval_type'] ?></td><td><span class="status-badge status-<?= strtolower($a['status']) ?>"><?= $a['status'] ?></span></td><td><?= htmlspecialchars($a['requester'] ?? '-') ?></td><td><?= htmlspecialchars($a['reviewer'] ?? '-') ?></td><td><?= date('M j, Y', strtotime($a['created_at'])) ?></td><td><?php if ($a['status'] === 'Pending'): ?><a href="#" class="btn btn-ghost btn-sm">Review</a><?php else: ?><span class="text-muted">Done</span><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/../layouts/corporate_footer.php'; ?>
