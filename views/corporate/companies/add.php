<?php
$title = 'Add Company';
require_once __DIR__ . '/../../../middleware/Auth.php';
require_once __DIR__ . '/../../../middleware/AuditLogger.php';
include __DIR__ . '/../../layouts/corporate_header.php';

$auth = Auth::getInstance();
$user = $auth->getUser();
$auditLogger = AuditLogger::getInstance();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $companyName = trim($_POST['company_name'] ?? '');
    $companyCode = trim($_POST['company_code'] ?? '');
    $companyDescription = trim($_POST['company_description'] ?? '');
    $shortDescription = trim($_POST['short_description'] ?? '');
    $industry = trim($_POST['industry'] ?? '');
    $websiteUrl = trim($_POST['website_url'] ?? '');
    $launchYear = trim($_POST['launch_year'] ?? '');
    $headquarters = trim($_POST['headquarters'] ?? '');
    $contactEmail = trim($_POST['contact_email'] ?? '');
    $contactNumber = trim($_POST['contact_number'] ?? '');
    $status = $_POST['status'] ?? 'Draft';
    
    if (!$companyName) {
        $error = 'Company name is required.';
    } elseif (!$companyCode) {
        $error = 'Company code is required.';
    } elseif (!preg_match('/^[A-Z0-9_]+$/', $companyCode)) {
        $error = 'Company code must contain only uppercase letters, numbers, and underscores.';
    } elseif (!in_array($status, ['Draft', 'Review', 'Live', 'Archived'])) {
        $error = 'Invalid status value.';
    } else {
        try {
            $pdo = getDBConnection();
            
            // Check if company code already exists
            $stmt = $pdo->prepare("SELECT id FROM companies WHERE company_code = :code LIMIT 1");
            $stmt->execute([':code' => $companyCode]);
            if ($stmt->fetch()) {
                $error = 'Company code already exists. Please use a unique code.';
            } else {
                // Generate slug
                $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $companyName));
                $slug = trim($slug, '-');
                
                // Check if slug exists
                $stmt = $pdo->prepare("SELECT id FROM companies WHERE slug = :slug LIMIT 1");
                $stmt->execute([':slug' => $slug]);
                if ($stmt->fetch()) {
                    $slug .= '-' . time();
                }
                
                // Handle logo upload
                $logoPath = null;
                if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/../../../public/uploads/logos/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $ext = strtolower(pathinfo($_FILES['company_logo']['name'], PATHINFO_EXTENSION));
                    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
                    if (in_array($ext, $allowedExts)) {
                        $filename = $companyCode . '_logo_' . time() . '.' . $ext;
                        move_uploaded_file($_FILES['company_logo']['tmp_name'], $uploadDir . $filename);
                        $logoPath = '/public/uploads/logos/' . $filename;
                    }
                }
                
                // Handle banner upload
                $bannerPath = null;
                if (isset($_FILES['company_banner']) && $_FILES['company_banner']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/../../../public/uploads/logos/';
                    $ext = strtolower(pathinfo($_FILES['company_banner']['name'], PATHINFO_EXTENSION));
                    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
                    if (in_array($ext, $allowedExts)) {
                        $filename = $companyCode . '_banner_' . time() . '.' . $ext;
                        move_uploaded_file($_FILES['company_banner']['tmp_name'], $uploadDir . $filename);
                        $bannerPath = '/public/uploads/logos/' . $filename;
                    }
                }
                
                $publishedAt = ($status === 'Live') ? 'NOW()' : null;
                
                $stmt = $pdo->prepare("
                    INSERT INTO companies 
                    (company_name, company_code, slug, industry, description, short_description, 
                     company_logo, banner, website_url, status, launch_year, headquarters, 
                     contact_email, contact_number, published_at)
                    VALUES 
                    (:name, :code, :slug, :industry, :description, :short_description,
                     :logo, :banner, :website, :status, :launch_year, :headquarters,
                     :contact_email, :contact_number, :published_at)
                ");
                
                $stmt->execute([
                    ':name' => $companyName,
                    ':code' => strtoupper($companyCode),
                    ':slug' => $slug,
                    ':industry' => $industry ?: null,
                    ':description' => $companyDescription ?: null,
                    ':short_description' => $shortDescription ?: null,
                    ':logo' => $logoPath,
                    ':banner' => $bannerPath,
                    ':website' => $websiteUrl ?: null,
                    ':status' => $status,
                    ':launch_year' => $launchYear ?: null,
                    ':headquarters' => $headquarters ?: null,
                    ':contact_email' => $contactEmail ?: null,
                    ':contact_number' => $contactNumber ?: null,
                    ':published_at' => $publishedAt,
                ]);
                
                $companyId = $pdo->lastInsertId();
                
                // Log company history
                $historyStmt = $pdo->prepare("
                    INSERT INTO company_history (company_id, user_id, action, description, ip_address, user_agent)
                    VALUES (:company_id, :user_id, 'CREATED', :description, :ip_address, :user_agent)
                ");
                $historyStmt->execute([
                    ':company_id' => $companyId,
                    ':user_id' => $user['id'],
                    ':description' => "Company created: $companyName",
                    ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                    ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                ]);
                
                $auditLogger->log($user['id'], 'COMPANY_CREATED', 'Company Management', "Created company: $companyName");
                
                $_SESSION['flash_message'] = "Company '$companyName' created successfully.";
                $_SESSION['flash_type'] = 'success';
                header('Location: /corporate/companies');
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="panel">
    <div class="panel-header">
        <h3>Add New Company</h3>
    </div>
    <div class="panel-body">
        <form method="POST" action="/corporate/companies/add" class="form" enctype="multipart/form-data">
            <div class="form-section">
                <h4>Basic Information</h4>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="company_name">Company Name <span class="required">*</span></label>
                        <input type="text" id="company_name" name="company_name" required 
                               value="<?= htmlspecialchars($_POST['company_name'] ?? '') ?>"
                               placeholder="e.g., ShopTantra">
                    </div>
                    <div class="form-group">
                        <label for="company_code">Company Code <span class="required">*</span></label>
                        <input type="text" id="company_code" name="company_code" required 
                               value="<?= htmlspecialchars($_POST['company_code'] ?? '') ?>"
                               placeholder="e.g., SHOPTANTRA"
                               style="text-transform: uppercase;">
                        <small class="form-hint">Uppercase letters, numbers, and underscores only.</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="industry">Industry</label>
                    <input type="text" id="industry" name="industry" 
                           value="<?= htmlspecialchars($_POST['industry'] ?? '') ?>"
                           placeholder="e.g., E-Commerce, Technology, Healthcare">
                </div>
                
                <div class="form-group">
                    <label for="short_description">Short Description</label>
                    <input type="text" id="short_description" name="short_description" 
                           value="<?= htmlspecialchars($_POST['short_description'] ?? '') ?>"
                           placeholder="Brief one-line description (max 255 characters)"
                           maxlength="255">
                </div>
                
                <div class="form-group">
                    <label for="company_description">Full Description</label>
                    <textarea id="company_description" name="company_description" rows="4" 
                              placeholder="Detailed company overview..."><?= htmlspecialchars($_POST['company_description'] ?? '') ?></textarea>
                </div>
            </div>
            
            <div class="form-section">
                <h4>Launch Information</h4>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="launch_year">Launch Year</label>
                        <input type="number" id="launch_year" name="launch_year" 
                               value="<?= htmlspecialchars($_POST['launch_year'] ?? '') ?>"
                               placeholder="e.g., 2026"
                               min="2000" max="2099">
                    </div>
                    <div class="form-group">
                        <label for="headquarters">Headquarters</label>
                        <input type="text" id="headquarters" name="headquarters" 
                               value="<?= htmlspecialchars($_POST['headquarters'] ?? '') ?>"
                               placeholder="e.g., India, USA, UK">
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h4>Contact Information</h4>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="contact_email">Contact Email</label>
                        <input type="email" id="contact_email" name="contact_email" 
                               value="<?= htmlspecialchars($_POST['contact_email'] ?? '') ?>"
                               placeholder="contact@company.com">
                    </div>
                    <div class="form-group">
                        <label for="contact_number">Contact Number</label>
                        <input type="text" id="contact_number" name="contact_number" 
                               value="<?= htmlspecialchars($_POST['contact_number'] ?? '') ?>"
                               placeholder="+1-000-000-0000">
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h4>Media</h4>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="company_logo">Company Logo</label>
                        <input type="file" id="company_logo" name="company_logo" accept="image/*">
                        <small class="form-hint">Accepted: JPG, PNG, GIF, SVG, WebP (max 5MB)</small>
                    </div>
                    <div class="form-group">
                        <label for="company_banner">Cover Banner</label>
                        <input type="file" id="company_banner" name="company_banner" accept="image/*">
                        <small class="form-hint">Accepted: JPG, PNG, GIF, SVG, WebP (max 5MB)</small>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h4>Website & Status</h4>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="website_url">Website URL</label>
                        <input type="url" id="website_url" name="website_url" 
                               value="<?= htmlspecialchars($_POST['website_url'] ?? '') ?>"
                               placeholder="https://example.com">
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="Draft" <?= (($_POST['status'] ?? 'Draft') === 'Draft') ? 'selected' : '' ?>>Draft</option>
                            <option value="Review" <?= (($_POST['status'] ?? '') === 'Review') ? 'selected' : '' ?>>Review</option>
                            <option value="Live" <?= (($_POST['status'] ?? '') === 'Live') ? 'selected' : '' ?>>Live</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Company</button>
                <a href="/corporate/companies" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/corporate_footer.php'; ?>