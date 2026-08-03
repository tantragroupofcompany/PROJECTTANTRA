<?php
$title = 'Company Management';
require_once __DIR__ . '/../../../middleware/Auth.php';
require_once __DIR__ . '/../../../middleware/AuditLogger.php';
include __DIR__ . '/../../layouts/corporate_header.php';

$auth = Auth::getInstance();
$user = $auth->getUser();
$auditLogger = AuditLogger::getInstance();

// Handle status change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $companyId = (int)($_POST['company_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $newStatus = $_POST['new_status'] ?? '';
    
    if ($companyId && $action) {
        try {
            $pdo = getDBConnection();
            
            // Get current company status
            $stmt = $pdo->prepare("SELECT status FROM companies WHERE id = :id");
            $stmt->execute([':id' => $companyId]);
            $currentStatus = $stmt->fetchColumn();
            
            $allowedTransitions = [
                'submit_review' => ['Draft' => 'Review'],
                'publish' => ['Review' => 'Live', 'Draft' => 'Live'],
                'unpublish' => ['Live' => 'Draft'],
                'archive' => ['Live' => 'Archived', 'Review' => 'Archived', 'Draft' => 'Archived'],
            ];
            
            if (isset($allowedTransitions[$action]) && isset($allowedTransitions[$action][$currentStatus])) {
                $newStatus = $allowedTransitions[$action][$currentStatus];
                
                $updateData = ['status' => $newStatus];
                
                if ($action === 'publish') {
                    $updateData['published_at'] = 'NOW()';
                } elseif ($action === 'archive') {
                    $updateData['archived_at'] = 'NOW()';
                }
                
                $fields = [];
                foreach ($updateData as $field => $value) {
                    $fields[] = "$field = $value";
                }
                
                $stmt = $pdo->prepare("UPDATE companies SET " . implode(', ', $fields) . " WHERE id = :id");
                $stmt->execute([':id' => $companyId]);
                
                // Log company history
                $historyStmt = $pdo->prepare("
                    INSERT INTO company_history (company_id, user_id, action, old_value, new_value, description, ip_address, user_agent)
                    VALUES (:company_id, :user_id, :action, :old_value, :new_value, :description, :ip_address, :user_agent)
                ");
                $historyStmt->execute([
                    ':company_id' => $companyId,
                    ':user_id' => $user['id'],
                    ':action' => strtoupper($action),
                    ':old_value' => $currentStatus,
                    ':new_value' => $newStatus,
                    ':description' => "Status changed from $currentStatus to $newStatus",
                    ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                    ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                ]);
                
                // Log audit
                $auditLogger->log($user['id'], 'COMPANY_STATUS_CHANGED', 'Company Management', "Changed company ID $companyId status: $currentStatus → $newStatus");
                
                $_SESSION['flash_message'] = "Company status updated to $newStatus successfully.";
                $_SESSION['flash_type'] = 'success';
                header('Location: /corporate/companies');
                exit;
            } else {
                $_SESSION['flash_message'] = "Invalid status transition.";
                $_SESSION['flash_type'] = 'error';
            }
        } catch (PDOException $e) {
            $_SESSION['flash_message'] = 'Failed to update company status.';
            $_SESSION['flash_type'] = 'error';
        }
    }
    header('Location: /corporate/companies');
    exit;
}

// Fetch all companies
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("
        SELECT c.*, 
               COUNT(ch.id) as history_count
        FROM companies c
        LEFT JOIN company_history ch ON c.id = ch.company_id
        GROUP BY c.id
        ORDER BY c.created_at DESC
    ");
    $companies = $stmt->fetchAll();
} catch (PDOException $e) {
    $companies = [];
    $_SESSION['flash_message'] = 'Failed to load companies.';
    $_SESSION['flash_type'] = 'error';
}

$flashMessage = $_SESSION['flash_message'] ?? '';
$flashType = $_SESSION['flash_type'] ?? '';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);
?>

<?php if ($flashMessage): ?>
    <div class="alert alert-<?= $flashType ?>"><?= htmlspecialchars($flashMessage) ?></div>
<?php endif; ?>

<div class="panel">
    <div class="panel-header panel-header-between">
        <h3>All Companies (<?= count($companies) ?>)</h3>
        <a href="/corporate/companies/add" class="btn btn-primary btn-sm">+ Add Company</a>
    </div>
    <div class="panel-body">
        <?php if (empty($companies)): ?>
            <div class="empty-state">
                <div class="empty-icon">&#9733;</div>
                <h3>No Companies Yet</h3>
                <p>Start by adding your first company.</p>
                <a href="/corporate/companies/add" class="btn btn-primary">Add Company</a>
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Code</th>
                        <th>Industry</th>
                        <th>Status</th>
                        <th>Launch Year</th>
                        <th>History</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($companies as $company): ?>
                    <tr>
                        <td>
                            <div class="company-cell">
                                <?php if ($company['company_logo']): ?>
                                    <img src="<?= htmlspecialchars($company['company_logo']) ?>" alt="" class="company-thumb">
                                <?php endif; ?>
                                <div>
                                    <strong><?= htmlspecialchars($company['company_name']) ?></strong>
                                    <br><small class="text-muted"><?= htmlspecialchars($company['headquarters'] ?? '') ?></small>
                                </div>
                            </div>
                        </td>
                        <td><code><?= htmlspecialchars($company['company_code']) ?></code></td>
                        <td><?= htmlspecialchars($company['industry'] ?? '-') ?></td>
                        <td>
                            <span class="status-badge status-<?= strtolower($company['status']) ?>">
                                <?= htmlspecialchars($company['status']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($company['launch_year'] ?? '-') ?></td>
                        <td>
                            <a href="/corporate/companies/view/<?= $company['id'] ?>" class="btn btn-ghost btn-sm">
                                <?= $company['history_count'] ?> events
                            </a>
                        </td>
                        <td class="actions-cell">
                            <a href="/corporate/companies/view/<?= $company['id'] ?>" class="btn btn-ghost btn-sm">View</a>
                            <a href="/corporate/companies/edit/<?= $company['id'] ?>" class="btn btn-ghost btn-sm">Edit</a>
                            
                            <?php if ($company['status'] === 'Draft'): ?>
                                <form method="POST" action="/corporate/companies" class="inline-form" style="display:inline;">
                                    <input type="hidden" name="action" value="submit_review">
                                    <input type="hidden" name="company_id" value="<?= $company['id'] ?>">
                                    <button type="submit" class="btn btn-ghost btn-sm">Submit for Review</button>
                                </form>
                            <?php elseif ($company['status'] === 'Review'): ?>
                                <form method="POST" action="/corporate/companies" class="inline-form" style="display:inline;">
                                    <input type="hidden" name="action" value="publish">
                                    <input type="hidden" name="company_id" value="<?= $company['id'] ?>">
                                    <button type="submit" class="btn btn-success btn-sm">Publish</button>
                                </form>
                            <?php elseif ($company['status'] === 'Live'): ?>
                                <form method="POST" action="/corporate/companies" class="inline-form" style="display:inline;">
                                    <input type="hidden" name="action" value="unpublish">
                                    <input type="hidden" name="company_id" value="<?= $company['id'] ?>">
                                    <button type="submit" class="btn btn-warning btn-sm">Unpublish</button>
                                </form>
                            <?php endif; ?>
                            
                            <?php if (in_array($company['status'], ['Live', 'Review', 'Draft'])): ?>
                                <form method="POST" action="/corporate/companies" class="inline-form" style="display:inline;" onsubmit="return confirm('Archive this company?')">
                                    <input type="hidden" name="action" value="archive">
                                    <input type="hidden" name="company_id" value="<?= $company['id'] ?>">
                                    <button type="submit" class="btn btn-ghost btn-sm">Archive</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.company-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.company-thumb {
    width: 40px;
    height: 40px;
    border-radius: 6px;
    object-fit: cover;
}
.btn-success {
    background-color: var(--color-success);
    border-color: var(--color-success);
    color: white;
}
.btn-warning {
    background-color: var(--color-warning);
    border-color: var(--color-warning);
    color: #333;
}
</style>

<?php include __DIR__ . '/../../layouts/corporate_footer.php'; ?>