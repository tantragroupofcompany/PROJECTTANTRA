<?php
// ============================================================
// TANTRA GROUP OF INDUSTRIES
// Phase 2: Companies API Controller
// RESTful API for Company Publishing
// ============================================================

require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../middleware/Auth.php';
require_once __DIR__ . '/../middleware/AuditLogger.php';

class CompaniesAPI {
    private $auth;
    private $auditLogger;
    
    public function __construct() {
        $this->auth = Auth::getInstance();
        $this->auditLogger = AuditLogger::getInstance();
    }
    
    /**
     * GET /api/companies - List all companies
     */
    public function index() {
        if (! $this->auth->isAuthenticated()) {
            $this->jsonResponse(401, ['error' => 'Unauthorized']);
            return;
        }
        
        $pdo = getDBConnection();
        $stmt = $pdo->query("
            SELECT id, company_name, company_code, slug, industry, status, launch_year, created_at, updated_at
            FROM companies
            ORDER BY created_at DESC
        ");
        $companies = $stmt->fetchAll();
        
        $this->jsonResponse(200, [
            'success' => true,
            'data' => $companies,
            'count' => count($companies)
        ]);
    }
    
    /**
     * GET /api/companies/[slug] - Get single company by slug
     */
    public function show($slug) {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT c.*, 
                   m.file_path as logo_path,
                   b.file_path as banner_path
            FROM companies c
            LEFT JOIN company_media m ON c.id = m.company_id AND m.media_type = 'logo'
            LEFT JOIN company_media b ON c.id = b.company_id AND b.media_type = 'banner'
            WHERE c.slug = :slug
            LIMIT 1
        ");
        $stmt->execute([':slug' => $slug]);
        $company = $stmt->fetch();
        
        if (!$company) {
            $this->jsonResponse(404, ['error' => 'Company not found']);
            return;
        }
        
        $this->jsonResponse(200, [
            'success' => true,
            'data' => $company
        ]);
    }
    
    /**
     * POST /api/companies - Create company
     */
    public function create($data) {
        $this->requireAdmin();
        $pdo = getDBConnection();
        $user = $this->auth->getUser();
        
        // Validate required fields
        $name = trim($data['company_name'] ?? '');
        $code = trim($data['company_code'] ?? '');
        
        if (!$name || !$code) {
            $this->jsonResponse(400, ['error' => 'Company name and code are required']);
            return;
        }
        
        // Generate slug
        $slug = $this->generateSlug($name);
        
        // Check if slug exists
        $stmt = $pdo->prepare("SELECT id FROM companies WHERE slug = :slug");
        $stmt->execute([':slug' => $slug]);
        if ($stmt->fetch()) {
            $this->jsonResponse(409, ['error' => 'Company slug already exists']);
            return;
        }
        
        // Insert company
        $stmt = $pdo->prepare("
            INSERT INTO companies (company_name, company_code, slug, industry, description, short_description, website_url, status, launch_year, headquarters, contact_email, contact_number)
            VALUES (:name, :code, :slug, :industry, :description, :short_description, :website, 'Draft', :launch_year, :headquarters, :contact_email, :contact_number)
        ");
        
        $result = $stmt->execute([
            ':name' => $name,
            ':code' => strtoupper($code),
            ':slug' => $slug,
            ':industry' => $data['industry'] ?? null,
            ':description' => $data['description'] ?? null,
            ':short_description' => $data['short_description'] ?? null,
            ':website' => $data['website_url'] ?? null,
            ':launch_year' => $data['launch_year'] ?? null,
            ':headquarters' => $data['headquarters'] ?? null,
            ':contact_email' => $data['contact_email'] ?? null,
            ':contact_number' => $data['contact_number'] ?? null,
        ]);
        
        if ($result) {
            $companyId = $pdo->lastInsertId();
            $this->logCompanyHistory($companyId, $user['id'], 'CREATED', null, null, "Company created: $name");
            $this->jsonResponse(201, [
                'success' => true,
                'message' => 'Company created successfully',
                'data' => ['id' => $companyId, 'slug' => $slug]
            ]);
        } else {
            $this->jsonResponse(500, ['error' => 'Failed to create company']);
        }
    }
    
    /**
     * PUT /api/companies/[id] - Update company
     */
    public function update($id, $data) {
        $this->requireAdmin();
        $pdo = getDBConnection();
        $user = $this->auth->getUser();
        
        // Fetch existing company
        $stmt = $pdo->prepare("SELECT * FROM companies WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $company = $stmt->fetch();
        
        if (!$company) {
            $this->jsonResponse(404, ['error' => 'Company not found']);
            return;
        }
        
        // Build update query
        $fields = [];
        $params = [':id' => $id];
        
        if (isset($data['company_name'])) {
            $fields[] = 'company_name = :name';
            $params[':name'] = $data['company_name'];
        }
        if (isset($data['industry'])) {
            $fields[] = 'industry = :industry';
            $params[':industry'] = $data['industry'];
        }
        if (isset($data['description'])) {
            $fields[] = 'description = :description';
            $params[':description'] = $data['description'];
        }
        if (isset($data['short_description'])) {
            $fields[] = 'short_description = :short_description';
            $params[':short_description'] = $data['short_description'];
        }
        if (isset($data['website_url'])) {
            $fields[] = 'website_url = :website';
            $params[':website'] = $data['website_url'];
        }
        if (isset($data['launch_year'])) {
            $fields[] = 'launch_year = :launch_year';
            $params[':launch_year'] = $data['launch_year'];
        }
        if (isset($data['headquarters'])) {
            $fields[] = 'headquarters = :headquarters';
            $params[':headquarters'] = $data['headquarters'];
        }
        if (isset($data['contact_email'])) {
            $fields[] = 'contact_email = :contact_email';
            $params[':contact_email'] = $data['contact_email'];
        }
        if (isset($data['contact_number'])) {
            $fields[] = 'contact_number = :contact_number';
            $params[':contact_number'] = $data['contact_number'];
        }
        
        if (!empty($fields)) {
            $query = 'UPDATE companies SET ' . implode(', ', $fields) . ' WHERE id = :id';
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            
            $this->logCompanyHistory($id, $user['id'], 'UPDATED', null, null, 'Company information updated');
        }
        
        $this->jsonResponse(200, [
            'success' => true,
            'message' => 'Company updated successfully'
        ]);
    }
    
    /**
     * POST /api/companies/publish - Publish company
     */
    public function publish($id) {
        $this->requireAdmin();
        $pdo = getDBConnection();
        $user = $this->auth->getUser();
        
        // Fetch company
        $stmt = $pdo->prepare("SELECT * FROM companies WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $company = $stmt->fetch();
        
        if (!$company) {
            $this->jsonResponse(404, ['error' => 'Company not found']);
            return;
        }
        
        if ($company['status'] === 'Live') {
            $this->jsonResponse(400, ['error' => 'Company is already published']);
            return;
        }
        
        // Update status
        $stmt = $pdo->prepare("
            UPDATE companies 
            SET status = 'Live', published_at = NOW() 
            WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);
        
        $this->logCompanyHistory($id, $user['id'], 'PUBLISHED', 'Draft', 'Live', 'Company published');
        
        $this->jsonResponse(200, [
            'success' => true,
            'message' => 'Company published successfully'
        ]);
    }
    
    /**
     * POST /api/companies/unpublish - Unpublish company
     */
    public function unpublish($id) {
        $this->requireAdmin();
        $pdo = getDBConnection();
        $user = $this->auth->getUser();
        
        $stmt = $pdo->prepare("SELECT * FROM companies WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $company = $stmt->fetch();
        
        if (!$company) {
            $this->jsonResponse(404, ['error' => 'Company not found']);
            return;
        }
        
        $stmt = $pdo->prepare("UPDATE companies SET status = 'Draft' WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        $this->logCompanyHistory($id, $user['id'], 'UNPUBLISHED', 'Live', 'Draft', 'Company unpublished');
        
        $this->jsonResponse(200, [
            'success' => true,
            'message' => 'Company unpublished successfully'
        ]);
    }
    
    /**
     * POST /api/companies/archive - Archive company
     */
    public function archive($id) {
        $this->requireAdmin();
        $pdo = getDBConnection();
        $user = $this->auth->getUser();
        
        $stmt = $pdo->prepare("SELECT * FROM companies WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $company = $stmt->fetch();
        
        if (!$company) {
            $this->jsonResponse(404, ['error' => 'Company not found']);
            return;
        }
        
        $stmt = $pdo->prepare("
            UPDATE companies 
            SET status = 'Archived', archived_at = NOW() 
            WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);
        
        $this->logCompanyHistory($id, $user['id'], 'ARCHIVED', $company['status'], 'Archived', 'Company archived');
        
        $this->jsonResponse(200, [
            'success' => true,
            'message' => 'Company archived successfully'
        ]);
    }
    
    /**
     * POST /api/companies/media - Upload company media
     */
    public function uploadMedia($companyId, $file, $type) {
        $this->requireAdmin();
        
        if (!isset($_FILES[$file]) || $_FILES[$file]['error'] !== UPLOAD_ERR_OK) {
            $this->jsonResponse(400, ['error' => 'File upload failed']);
            return;
        }
        
        $uploadedFile = $_FILES[$file];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $uploadedFile['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            $this->jsonResponse(400, ['error' => 'Invalid file type. Allowed: JPG, PNG, GIF, SVG, WebP']);
            return;
        }
        
        // Validate file size
        if ($uploadedFile['size'] > $maxSize) {
            $this->jsonResponse(400, ['error' => 'File size exceeds 5MB limit']);
            return;
        }
        
        // Get image dimensions
        list($width, $height) = getimagesize($uploadedFile['tmp_name']);
        
        // Create upload directory
        $uploadDir = __DIR__ . '/../public/uploads/logos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $extension = pathinfo($uploadedFile['name'], PATHINFO_EXTENSION);
        $filename = "company_{$companyId}_{$type}_" . time() . '.' . $extension;
        $destination = $uploadDir . $filename;
        $filePath = "/public/uploads/logos/" . $filename;
        
        if (move_uploaded_file($uploadedFile['tmp_name'], $destination)) {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("
                INSERT INTO company_media (company_id, media_type, file_path, file_name, file_size, mime_type, width, height)
                VALUES (:company_id, :type, :path, :name, :size, :mime, :width, :height)
            ");
            $stmt->execute([
                ':company_id' => $companyId,
                ':type' => $type,
                ':path' => $filePath,
                ':name' => $uploadedFile['name'],
                ':size' => $uploadedFile['size'],
                ':mime' => $mimeType,
                ':width' => $width,
                ':height' => $height,
            ]);
            
            $this->jsonResponse(201, [
                'success' => true,
                'message' => ucfirst($type) . ' uploaded successfully',
                'data' => ['path' => $filePath]
            ]);
        } else {
            $this->jsonResponse(500, ['error' => 'Failed to save file']);
        }
    }
    
    /**
     * Helper: Require admin role
     */
    private function requireAdmin() {
        if (!$this->auth->hasAnyRole(['Founder', 'Chairman', 'CEO'])) {
            $this->jsonResponse(403, ['error' => 'Forbidden - Admin access required']);
        }
    }
    
    /**
     * Helper: Generate URL-friendly slug
     */
    private function generateSlug($text) {
        $text = preg_replace('/[^a-zA-Z0-9\s-]/', '', $text);
        $text = trim(strtolower($text));
        $text = preg_replace('/[\s-]+/', '-', $text);
        return trim($text, '-');
    }
    
    /**
     * Helper: Log company history
     */
    private function logCompanyHistory($companyId, $userId, $action, $oldValue, $newValue, $description) {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            INSERT INTO company_history (company_id, user_id, action, old_value, new_value, description, ip_address, user_agent)
            VALUES (:company_id, :user_id, :action, :old_value, :new_value, :description, :ip_address, :user_agent)
        ");
        $stmt->execute([
            ':company_id' => $companyId,
            ':user_id' => $userId,
            ':action' => $action,
            ':old_value' => $oldValue,
            ':new_value' => $newValue,
            ':description' => $description,
            ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        ]);
    }
    
    /**
     * Helper: JSON response
     */
    private function jsonResponse($statusCode, $data) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}