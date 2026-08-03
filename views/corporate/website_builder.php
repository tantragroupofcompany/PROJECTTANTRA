<?php
$title = 'Website Builder';
require_once __DIR__ . '/../../middleware/Auth.php';
include __DIR__ . '/../layouts/corporate_header.php';

$auth = Auth::getInstance();
$user = $auth->getUser();

// Fetch companies for dropdown
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT id, company_name, company_code, slug FROM companies ORDER BY company_name ASC");
    $companies = $stmt->fetchAll();
    
    // Fetch existing websites
    $websitesStmt = $pdo->query("
        SELECT w.*, c.company_name, c.slug 
        FROM websites w
        JOIN companies c ON w.company_id = c.id
        ORDER BY w.created_at DESC
    ");
    $websites = $websitesStmt->fetchAll();
    
} catch (PDOException $e) {
    $companies = [];
    $websites = [];
}
?>

<div class="panel">
    <div class="panel-header panel-header-between">
        <h3>Website Builder</h3>
        <button class="btn btn-primary btn-sm" onclick="document.getElementById('createWebsiteModal').style.display='block'">
            + Create New Website
        </button>
    </div>
    <div class="panel-body">
        <?php if (empty($websites)): ?>
            <div class="empty-state">
                <div class="empty-icon">&#127760;</div>
                <h3>No Websites Yet</h3>
                <p>Create your first subsidiary website by clicking the button above.</p>
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Template</th>
                        <th>Status</th>
                        <th>Subdomain</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($websites as $website): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($website['company_name']) ?></strong>
                            <br><small class="text-muted"><?= htmlspecialchars($website['slug']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($website['template']) ?></td>
                        <td>
                            <span class="status-badge status-<?= strtolower($website['status']) ?>">
                                <?= htmlspecialchars($website['status']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($website['subdomain'] ?? '-') ?></td>
                        <td><?= date('M j, Y', strtotime($website['created_at'])) ?></td>
                        <td class="actions-cell">
                            <a href="/corporate/website-builder/edit/<?= $website['id'] ?>" class="btn btn-ghost btn-sm">Edit</a>
                            <?php if ($website['status'] !== 'Published'): ?>
                                <button class="btn btn-success btn-sm" onclick="publishWebsite(<?= $website['id'] ?>)">Publish</button>
                            <?php else: ?>
                                <button class="btn btn-warning btn-sm" onclick="unpublishWebsite(<?= $website['id'] ?>)">Unpublish</button>
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

<!-- Create Website Modal -->
<div id="createWebsiteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Create New Website</h3>
            <span class="modal-close" onclick="this.closest('.modal').style.display='none'">&times;</span>
        </div>
        <div class="modal-body">
            <form id="createWebsiteForm">
                <div class="form-group">
                    <label for="company_id">Select Company <span class="required">*</span></label>
                    <select id="company_id" name="company_id" required>
                        <option value="">Choose a company...</option>
                        <?php foreach ($companies as $company): ?>
                            <option value="<?= $company['id'] ?>"><?= htmlspecialchars($company['company_name']) ?> (<?= htmlspecialchars($company['company_code']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="template">Template <span class="required">*</span></label>
                    <select id="template" name="template" required>
                        <option value="corporate">Corporate Company</option>
                        <option value="technology">Technology Company</option>
                        <option value="ecommerce">E-Commerce Brand</option>
                        <option value="service">Service Company</option>
                        <option value="startup">Startup Company</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="subdomain">Subdomain</label>
                    <input type="text" id="subdomain" name="subdomain" placeholder="e.g., shoptantra">
                    <small class="form-hint">Will create: subdomain.tantragroupofindustries.com</small>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('createWebsiteModal').style.display='none'">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="createWebsite()">Create Website</button>
        </div>
    </div>
</div>

<style>
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}
.modal-content {
    background-color: white;
    margin: 10% auto;
    padding: 0;
    border-radius: var(--radius-lg);
    width: 90%;
    max-width: 500px;
    box-shadow: var(--shadow-xl);
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-lg) var(--spacing-xl);
    border-bottom: 1px solid var(--color-border);
}
.modal-header h3 {
    margin: 0;
}
.modal-close {
    font-size: 28px;
    font-weight: bold;
    color: var(--color-text-muted);
    cursor: pointer;
    line-height: 1;
}
.modal-close:hover {
    color: var(--color-text);
}
.modal-body {
    padding: var(--spacing-xl);
}
.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: var(--spacing-md);
    padding: var(--spacing-lg) var(--spacing-xl);
    border-top: 1px solid var(--color-border);
}
.btn-success {
    background-color: var(--color-success);
    border-color: var(--color-success);
    color: white;
}
.btn-success:hover {
    background-color: #00a085;
    color: white;
}
.btn-warning {
    background-color: var(--color-warning);
    border-color: var(--color-warning);
    color: #333;
}
.btn-warning:hover {
    background-color: #e6b800;
    color: #333;
}
</style>

<script>
function createWebsite() {
    const companyId = document.getElementById('company_id').value;
    const template = document.getElementById('template').value;
    const subdomain = document.getElementById('subdomain').value;
    
    if (!companyId) {
        alert('Please select a company.');
        return;
    }
    
    fetch('/api/websites/create', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            company_id: companyId,
            template: template,
            subdomain: subdomain
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Website created successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        alert('Error creating website: ' + error.message);
    });
}

function publishWebsite(websiteId) {
    if (!confirm('Publish this website? It will be publicly accessible.')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('website_id', websiteId);
    
    fetch('/api/websites/publish', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Website published successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        alert('Error publishing website: ' + error.message);
    });
}

function unpublishWebsite(websiteId) {
    if (!confirm('Unpublish this website? It will no longer be publicly accessible.')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('website_id', websiteId);
    
    fetch('/api/websites/unpublish', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Website unpublished successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        alert('Error unpublishing website: ' + error.message);
    });
}
</script>

<?php include __DIR__ . '/../layouts/corporate_footer.php'; ?>