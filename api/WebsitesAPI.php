<?php
// ============================================================
// TANTRA GROUP OF INDUSTRIES
// Phase 3: Website Builder & Creation Platform
// RESTful API Controller
// ============================================================

require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../middleware/Auth.php';
require_once __DIR__ . '/../middleware/AuditLogger.php';

class WebsitesAPI {
    private $auth;
    private $auditLogger;
    
    public function __construct() {
        $this->auth = Auth::getInstance();
        $this->auditLogger = AuditLogger::getInstance();
    }
    
    /**
     * GET /api/websites/[company] - Get website by company slug
     */
    public function show($companySlug) {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("
            SELECT w.*, c.company_name, c.slug, c.industry,
                   m.file_path as logo_path,
                   b.file_path as banner_path
            FROM websites w
            JOIN companies c ON w.company_id = c.id
            LEFT JOIN company_media m ON c.id = m.company_id AND m.media_type = 'logo'
            LEFT JOIN company_media b ON c.id = b.company_id AND b.media_type = 'banner'
            WHERE c.slug = :slug
            LIMIT 1
        ");
        $stmt->execute([':slug' => $companySlug]);
        $website = $stmt->fetch();
        
        if (!$website) {
            $this->jsonResponse(404, ['error' => 'Website not found']);
            return;
        }
        
        // Only show published websites to public
        if (!$this->auth->isAuthenticated() && $website['status'] !== 'Published') {
            $this->jsonResponse(404, ['error' => 'Website not found']);
            return;
        }
        
        // Get pages
        $pagesStmt = $pdo->prepare("
            SELECT * FROM pages 
            WHERE website_id = :website_id AND page_status = 'Published'
            ORDER BY sort_order ASC
        ");
        $pagesStmt->execute([':website_id' => $website['id']]);
        $pages = $pagesStmt->fetchAll();
        
        $this->jsonResponse(200, [
            'success' => true,
            'data' => [
                'website' => $website,
                'pages' => $pages
            ]
        ]);
    }
    
    /**
     * POST /api/websites/create - Create website for company
     */
    public function create($data) {
        $this->requireAdmin();
        $pdo = getDBConnection();
        $user = $this->auth->getUser();
        
        $companyId = (int)($data['company_id'] ?? 0);
        $template = $data['template'] ?? 'corporate';
        
        if (!$companyId) {
            $this->jsonResponse(400, ['error' => 'Company ID is required']);
            return;
        }
        
        // Check if company exists
        $stmt = $pdo->prepare("SELECT id FROM companies WHERE id = :id");
        $stmt->execute([':id' => $companyId]);
        if (!$stmt->fetch()) {
            $this->jsonResponse(404, ['error' => 'Company not found']);
            return;
        }
        
        // Check if website already exists
        $stmt = $pdo->prepare("SELECT id FROM websites WHERE company_id = :company_id");
        $stmt->execute([':company_id' => $companyId]);
        if ($stmt->fetch()) {
            $this->jsonResponse(409, ['error' => 'Website already exists for this company']);
            return;
        }
        
        // Create website
        $stmt = $pdo->prepare("
            INSERT INTO websites (company_id, template, status, theme_settings, navigation)
            VALUES (:company_id, :template, 'Draft', :theme, :nav)
        ");
        
        $theme = json_encode([
            'primary_color' => '#1a1a2e',
            'secondary_color' => '#e94560',
            'font_family' => 'Inter',
            'layout' => 'modern'
        ]);
        
        $nav = json_encode([
            ['name' => 'Home', 'slug' => 'home', 'enabled' => true],
            ['name' => 'About', 'slug' => 'about', 'enabled' => true],
            ['name' => 'Services', 'slug' => 'services', 'enabled' => true],
            ['name' => 'Contact', 'slug' => 'contact', 'enabled' => true]
        ]);
        
        $result = $stmt->execute([
            ':company_id' => $companyId,
            ':template' => $template,
            ':theme' => $theme,
            ':nav' => $nav
        ]);
        
        if ($result) {
            $websiteId = $pdo->lastInsertId();
            
            // Create default pages
            $this->createDefaultPages($pdo, $websiteId, $companyId);
            
            $this->jsonResponse(201, [
                'success' => true,
                'message' => 'Website created successfully',
                'data' => ['website_id' => $websiteId]
            ]);
        } else {
            $this->jsonResponse(500, ['error' => 'Failed to create website']);
        }
    }
    
    /**
     * PUT /api/websites/update - Update website settings
     */
    public function update($websiteId, $data) {
        $this->requireAdmin();
        $pdo = getDBConnection();
        $user = $this->auth->getUser();
        
        // Verify website exists
        $stmt = $pdo->prepare("SELECT * FROM websites WHERE id = :id");
        $stmt->execute([':id' => $websiteId]);
        $website = $stmt->fetch();
        
        if (!$website) {
            $this->jsonResponse(404, ['error' => 'Website not found']);
            return;
        }
        
        $fields = [];
        $params = [':id' => $websiteId];
        
        if (isset($data['template'])) {
            $fields[] = 'template = :template';
            $params[':template'] = $data['template'];
        }
        if (isset($data['theme_settings'])) {
            $fields[] = 'theme_settings = :theme';
            $params[':theme'] = json_encode($data['theme_settings']);
        }
        if (isset($data['navigation'])) {
            $fields[] = 'navigation = :nav';
            $params[':nav'] = json_encode($data['navigation']);
        }
        if (isset($data['domain'])) {
            $fields[] = 'domain = :domain';
            $params[':domain'] = $data['domain'];
        }
        if (isset($data['subdomain'])) {
            $fields[] = 'subdomain = :subdomain';
            $params[':subdomain'] = $data['subdomain'];
        }
        
        if (!empty($fields)) {
            $query = 'UPDATE websites SET ' . implode(', ', $fields) . ' WHERE id = :id';
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
        }
        
        $this->jsonResponse(200, [
            'success' => true,
            'message' => 'Website updated successfully'
        ]);
    }
    
    /**
     * GET /api/websites/[id] - Get website details
     */
    public function get($websiteId) {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("SELECT * FROM websites WHERE id = :id");
        $stmt->execute([':id' => $websiteId]);
        $website = $stmt->fetch();
        
        if (!$website) {
            $this->jsonResponse(404, ['error' => 'Website not found']);
            return;
        }
        
        // Check auth for unpublished websites
        if (!$this->auth->isAuthenticated() && $website['status'] !== 'Published') {
            $this->jsonResponse(404, ['error' => 'Website not found']);
            return;
        }
        
        $this->jsonResponse(200, [
            'success' => true,
            'data' => $website
        ]);
    }
    
    /**
     * POST /api/websites/publish - Publish website
     */
    public function publish($websiteId) {
        $this->requireAdmin();
        $pdo = getDBConnection();
        $user = $this->auth->getUser();
        
        $stmt = $pdo->prepare("SELECT * FROM websites WHERE id = :id");
        $stmt->execute([':id' => $websiteId]);
        $website = $stmt->fetch();
        
        if (!$website) {
            $this->jsonResponse(404, ['error' => 'Website not found']);
            return;
        }
        
        $stmt = $pdo->prepare("
            UPDATE websites 
            SET status = 'Published', published_at = NOW() 
            WHERE id = :id
        ");
        $stmt->execute([':id' => $websiteId]);
        
        $this->jsonResponse(200, [
            'success' => true,
            'message' => 'Website published successfully'
        ]);
    }
    
    /**
     * Helper: Create default pages for website
     */
    private function createDefaultPages($pdo, $websiteId, $companyId) {
        $defaultPages = [
            ['Home', 'home', 1],
            ['About', 'about', 2],
            ['Services', 'services', 3],
            ['Contact', 'contact', 4]
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO pages (website_id, company_id, page_name, page_slug, page_status, sort_order, is_required)
            VALUES (:website_id, :company_id, :name, :slug, 'Published', :sort, 1)
        ");
        
        foreach ($defaultPages as $page) {
            $stmt->execute([
                ':website_id' => $websiteId,
                ':company_id' => $companyId,
                ':name' => $page[0],
                ':slug' => $page[1],
                ':sort' => $page[2]
            ]);
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
     * Helper: JSON response
     */
    private function jsonResponse($statusCode, $data) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}