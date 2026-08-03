<?php
$title = 'View Company';
require_once __DIR__ . '/../../../middleware/Auth.php';
include __DIR__ . '/../../layouts/corporate_header.php';

$auth = Auth::getInstance();
$user = $auth->getUser();

$companyId = (int)($_GET['id'] ?? 0);
if (!$companyId) {
    header('Location: /corporate/companies');
    exit;
}

// Fetch company
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM companies WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $companyId]);
    $company = $stmt->fetch();
    
    if (!$company) {
        header('Location: /corporate/companies');
        exit;
    }
    
    // Fetch company history
    $historyStmt = $pdo->prepare("
        SELECT ch.*, u.username, u.role 
        FROM company_history ch
        LEFT JOIN users u ON ch.user_id = u.id
        WHERE ch.company_id = :company_id
        ORDER BY ch.timestamp DESC
        LIMIT 50
    ");
    $historyStmt->execute([':company_id' => $companyId]);
    $history = $historyStmt->fetchAll();
    
    // Fetch media
    $mediaStmt = $pdo->prepare("SELECT * FROM company_media WHERE company_id = :company_id");
    $mediaStmt->execute([':company_id' => $companyId]);
    $media = $mediaStmt->fetchAll();
    
} catch (PDOException $e) {
    header('Location: /corporate/companies');
    exit;
}
?>

<div class="panel">
    <div class="panel-header panel-header-between">
        <h3>Company Details</h3>
        <div class="panel-actions">
            <a href="/corporate/companies/edit/<?= $companyId ?>" class="btn btn-primary btn-sm">Edit Company</a>
            <a href="/corporate/companies" class="btn btn-secondary btn-sm">Back to List</a>
        </div>
    </div>
    <div class="panel-body">
        <div class="company-detail">
            <div class="company-detail-logo">
                <?php if ($company['company_logo']): ?>
                    <img src="<?= htmlspecialchars($company['company_logo']) ?>" alt="<?= htmlspecialchars($company['company_name']) ?> logo">
                <?php else: ?>
                    <div class="company-logo-placeholder company-logo-placeholder-lg"><?= strtoupper(substr($company['company_name'], 0, 1)) ?></div>
                <?php endif; ?>
            </div>
            
            <div class="company-detail-info">
                <div class="detail-row">
                    <div class="detail-label">Company Name</div>
                    <div class="detail-value"><?= htmlspecialchars($company['company_name']) ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Company Code</div>
                    <div class="detail-value"><code><?= htmlspecialchars($company['company_code']) ?></code></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Slug</div>
                    <div class="detail-value"><code><?= htmlspecialchars($company['slug']) ?></code></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Industry</div>
                    <div class="detail-value"><?= htmlspecialchars($company['industry'] ?? '-') ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Status</div>
                    <div class="detail-value">
                        <span class="status-badge status-<?= strtolower($company['status']) ?>"><?= htmlspecialchars($company['status']) ?></span>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Website URL</div>
                    <div class="detail-value">
                        <?php if ($company['website_url']): ?>
                            <a href="<?= htmlspecialchars($company['website_url']) ?>" target="_blank"><?= htmlspecialchars($company['website_url']) ?></a>
                        <?php else: ?>
                            <span class="text-muted">Not provided</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Launch Year</div>
                    <div class="detail-value"><?= htmlspecialchars($company['launch_year'] ?? '-') ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Headquarters</div>
                    <div class="detail-value"><?= htmlspecialchars($company['headquarters'] ?? '-') ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Contact Email</div>
                    <div class="detail-value"><?= htmlspecialchars($company['contact_email'] ?? '-') ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Contact Number</div>
                    <div class="detail-value"><?= htmlspecialchars($company['contact_number'] ?? '-') ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Published At</div>
                    <div class="detail-value"><?= $company['published_at'] ? date('M j, Y g:i A', strtotime($company['published_at'])) : 'Not published' ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Created</div>
                    <div class="detail-value"><?= date('F j, Y \a\t g:i A', strtotime($company['created_at'])) ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Last Updated</div>
                    <div class="detail-value"><?= date('F j, Y \a\t g:i A', strtotime($company['updated_at'])) ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($media)): ?>
<div class="panel">
    <div class="panel-header">
        <h3>Media Assets</h3>
    </div>
    <div class="panel-body">
        <div class="media-grid">
            <?php foreach ($media as $item): ?>
            <div class="media-item">
                <img src="<?= htmlspecialchars($item['file_path']) ?>" alt="<?= htmlspecialchars($item['media_type']) ?>">
                <div class="media-info">
                    <p><strong>Type:</strong> <?= ucfirst($item['media_type']) ?></p>
                    <p><strong>Size:</strong> <?= number_format($item['file_size'] / 1024, 2) ?> KB</p>
                    <p><strong>Dimensions:</strong> <?= $item['width'] ?> x <?= $item['height'] ?></p>
                    <p><small><?= date('M j, Y', strtotime($item['created_at'])) ?></small></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="panel">
    <div class="panel-header">
        <h3>Company History (<?= count($history) ?> events)</h3>
    </div>
    <div class="panel-body">
        <?php if (empty($history)): ?>
            <p class="text-muted">No history recorded yet.</p>
        <?php else: ?>
        <div class="history-timeline">
            <?php foreach ($history as $event): ?>
            <div class="history-item">
                <div class="history-marker">
                    <span class="history-icon">&#9679;</span>
                </div>
                <div class="history-content">
                    <div class="history-header">
                        <strong><?= htmlspecialchars($event['action']) ?></strong>
                        <span class="text-muted"><?= date('M j, Y g:i A', strtotime($event['timestamp'])) ?></span>
                    </div>
                    <p><?= htmlspecialchars($event['description'] ?? '') ?></p>
                    <?php if ($event['old_value'] || $event['new_value']): ?>
                        <p class="text-muted">
                            <?php if ($event['old_value']): ?>From: <code><?= htmlspecialchars($event['old_value']) ?></code><?php endif; ?>
                            <?php if ($event['old_value'] && $event['new_value']): ?> → <?php endif; ?>
                            <?php if ($event['new_value']): ?>To: <code><?= htmlspecialchars($event['new_value']) ?></code><?php endif; ?>
                        </p>
                    <?php endif; ?>
                    <p class="text-muted">
                        By: <?= htmlspecialchars($event['username'] ?? 'System') ?> (<?= htmlspecialchars($event['role'] ?? '') ?>)
                    </p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.media-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}
.media-item {
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    overflow: hidden;
}
.media-item img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}
.media-info {
    padding: 1rem;
}
.media-info p {
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}
.media-info p:last-child {
    margin-bottom: 0;
}
.history-timeline {
    position: relative;
    padding-left: 2rem;
}
.history-timeline::before {
    content: '';
    position: absolute;
    left: 6px;
    top: 0;
    bottom: 0;
    width: 2px;
    background-color: var(--color-border);
}
.history-item {
    position: relative;
    padding-bottom: 1.5rem;
}
.history-marker {
    position: absolute;
    left: -2rem;
    top: 0;
}
.history-icon {
    font-size: 12px;
    color: var(--color-accent);
    background-color: var(--color-white);
    border-radius: 50%;
}
.history-content {
    background-color: var(--color-bg-light);
    padding: 1rem;
    border-radius: var(--radius-md);
}
.history-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}
</style>

<?php include __DIR__ . '/../../layouts/corporate_footer.php'; ?>