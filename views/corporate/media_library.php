<?php
$title = 'Media Library';
require_once __DIR__ . '/../../middleware/Auth.php';
include __DIR__ . '/../layouts/corporate_header.php';

$auth = Auth::getInstance();
$user = $auth->getUser();

$companyId = $_GET['company_id'] ?? null;

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media_file'])) {
    if ($_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
        $uploadedFile = $_FILES['media_file'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp', 'application/pdf'];
        $maxSize = 10 * 1024 * 1024; // 10MB
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $uploadedFile['tmp_name']);
        finfo_close($finfo);
        
        if (in_array($mimeType, $allowedTypes) && $uploadedFile['size'] <= $maxSize) {
            $uploadDir = __DIR__ . '/../../public/uploads/logos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $extension = pathinfo($uploadedFile['name'], PATHINFO_EXTENSION);
            $filename = 'media_' . time() . '_' . uniqid() . '.' . $extension;
            $destination = $uploadDir . $filename;
            $filePath = '/public/uploads/logos/' . $filename;
            
            if (move_uploaded_file($uploadedFile['tmp_name'], $destination)) {
                list($width, $height) = getimagesize($destination);
                
                $fileType = 'image';
                if (strpos($mimeType, 'pdf') !== false) {
                    $fileType = 'document';
                }
                
                $pdo = getDBConnection();
                $stmt = $pdo->prepare("
                    INSERT INTO media_library (company_id, uploaded_by, file_name, file_url, file_type, file_size, mime_type, width, height)
                    VALUES (:company_id, :user_id, :name, :url, :type, :size, :mime, :width, :height)
                ");
                $stmt->execute([
                    ':company_id' => $companyId ?: 0,
                    ':user_id' => $user['id'],
                    ':name' => $uploadedFile['name'],
                    ':url' => $filePath,
                    ':type' => $fileType,
                    ':size' => $uploadedFile['size'],
                    ':mime' => $mimeType,
                    ':width' => $width,
                    ':height' => $height,
                ]);
                
                $_SESSION['flash_message'] = 'File uploaded successfully.';
                $_SESSION['flash_type'] = 'success';
            }
        }
    }
    header('Location: /corporate/media-library');
    exit;
}

// Fetch media
try {
    $pdo = getDBConnection();
    
    if ($companyId) {
        $stmt = $pdo->prepare("
            SELECT m.*, c.company_name 
            FROM media_library m
            LEFT JOIN companies c ON m.company_id = c.id
            WHERE m.company_id = :company_id
            ORDER BY m.created_at DESC
        ");
        $stmt->execute([':company_id' => $companyId]);
    } else {
        $stmt = $pdo->query("
            SELECT m.*, c.company_name 
            FROM media_library m
            LEFT JOIN companies c ON m.company_id = c.id
            ORDER BY m.created_at DESC
            LIMIT 100
        ");
    }
    $media = $stmt->fetchAll();
    
    // Fetch companies for filter
    $companiesStmt = $pdo->query("SELECT id, company_name FROM companies ORDER BY company_name ASC");
    $companies = $companiesStmt->fetchAll();
    
} catch (PDOException $e) {
    $media = [];
    $companies = [];
}
?>

<div class="panel">
    <div class="panel-header panel-header-between">
        <h3>Media Library</h3>
        <button class="btn btn-primary btn-sm" onclick="document.getElementById('uploadModal').style.display='block'">
            + Upload Media
        </button>
    </div>
    <div class="panel-body">
        <?php if (!empty($companies)): ?>
        <div class="filter-bar" style="margin-bottom: 1.5rem;">
            <form method="GET" action="/corporate/media-library" style="display: flex; gap: 1rem; align-items: flex-end;">
                <div class="form-group" style="margin-bottom: 0; flex: 1;">
                    <label>Filter by Company</label>
                    <select name="company_id" onchange="this.form.submit()">
                        <option value="">All Companies</option>
                        <?php foreach ($companies as $comp): ?>
                            <option value="<?= $comp['id'] ?>" <?= $companyId == $comp['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($comp['company_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($companyId): ?>
                    <a href="/corporate/media-library" class="btn btn-ghost btn-sm">Clear Filter</a>
                <?php endif; ?>
            </form>
        </div>
        <?php endif; ?>
        
        <?php if (empty($media)): ?>
            <div class="empty-state">
                <div class="empty-icon">&#128248;</div>
                <h3>No Media Files</h3>
                <p>Upload your first media file by clicking the button above.</p>
            </div>
        <?php else: ?>
        <div class="media-grid">
            <?php foreach ($media as $item): ?>
            <div class="media-card">
                <div class="media-preview">
                    <?php if (strpos($item['mime_type'], 'image') !== false): ?>
                        <img src="<?= htmlspecialchars($item['file_url']) ?>" alt="<?= htmlspecialchars($item['file_name']) ?>">
                    <?php else: ?>
                        <div class="media-icon">&#128196;</div>
                    <?php endif; ?>
                </div>
                <div class="media-details">
                    <p class="media-name"><?= htmlspecialchars($item['file_name']) ?></p>
                    <p class="meta-info">
                        <?php if ($item['company_name']): ?>
                            <span><?= htmlspecialchars($item['company_name']) ?></span><br>
                        <?php endif; ?>
                        <span><?= number_format($item['file_size'] / 1024, 2) ?> KB</span>
                        <?php if ($item['width'] && $item['height']): ?>
                            <br><span><?= $item['width'] ?>x<?= $item['height'] ?></span>
                        <?php endif; ?>
                    </p>
                    <p class="text-muted"><?= date('M j, Y', strtotime($item['created_at'])) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Upload Media</h3>
            <span class="modal-close" onclick="this.closest('.modal').style.display='none'">&times;</span>
        </div>
        <div class="modal-body">
            <form method="POST" action="/corporate/media-library" enctype="multipart/form-data">
                <?php if (!empty($companies)): ?>
                <div class="form-group">
                    <label for="company_id">Company (Optional)</label>
                    <select id="company_id" name="company_id">
                        <option value="">General (No specific company)</option>
                        <?php foreach ($companies as $comp): ?>
                            <option value="<?= $comp['id'] ?>"><?= htmlspecialchars($comp['company_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <div class="form-group">
                    <label for="media_file">Select File <span class="required">*</span></label>
                    <input type="file" id="media_file" name="media_file" required accept="image/*,.pdf">
                    <small class="form-hint">Accepted: JPG, PNG, GIF, SVG, WebP, PDF (max 10MB)</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="this.closest('.modal').style.display='none'">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.media-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1.5rem;
}
.media-card {
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    overflow: hidden;
    transition: all var(--transition-base);
}
.media-card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}
.media-preview {
    width: 100%;
    height: 150px;
    overflow: hidden;
    background-color: var(--color-bg-light);
    display: flex;
    align-items: center;
    justify-content: center;
}
.media-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.media-icon {
    font-size: 3rem;
    color: var(--color-text-muted);
}
.media-details {
    padding: 1rem;
}
.media-name {
    font-weight: 600;
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
    word-break: break-word;
}
.media-name {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.meta-info {
    font-size: 0.75rem;
    color: var(--color-text-muted);
    margin-bottom: 0.25rem;
}
</style>

<?php include __DIR__ . '/../layouts/corporate_footer.php'; ?>