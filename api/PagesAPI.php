<?php
// ============================================================
// TANTRA GROUP OF INDUSTRIES
// Phase 3: Website Builder - Pages API
// ============================================================

require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../middleware/Auth.php';
require_once __DIR__ . '/../middleware/AuditLogger.php';

class PagesAPI {
    private $auth;
    private $auditLogger;
    
    public function __construct() {
        $this->auth = Auth::getInstance();
        $this->auditLogger = AuditLogger::getInstance();
    }
    
    /**
     * GET /api/pages/[slug] - Get page by slug
     */
    public function show($slug) {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("
            SELECT p.*, w.company_id, c.company_name, c.slug
            FROM pages p
            JOIN websites w ON p.website_id = w.id
            JOIN companies c ON w.company_id = c.id
            WHERE p.page_slug = :slug AND p.page_status = 'Published'
            LIMIT 1
        ");
        $stmt->execute([':slug' => $slug]);
        $page = $stmt->fetch();
        
        if (!$page) {
            $this->jsonResponse(404, ['error' => 'Page not found']);
            return;
        }
        
        // Check if website is published
        $websiteStmt = $pdo->prepare("SELECT status FROM websites WHERE id = :id");
        $websiteStmt->execute([':id' => $page['website_id']]);
        $website = $websiteStmt->fetch();
        
        if (!$this->auth->isAuthenticated() && $website['status'] !== 'Published') {
            $this->jsonResponse(404, ['error' => 'Page not found']);
            return;
        }
        
        $this->jsonResponse(200, [
            'success' => true,
            'data' => $page
        ]);
    }
    
    /**
     * POST /api/pages/create - Create new page
     */
    public function create($data) {
        $this->requireAdmin();
        $pdo = getDBConnection();
        $user = $this->auth->getUser();
        
        $websiteId = (int)($data['website_id'] ?? 0);
        $pageName = trim($data['page_name'] ?? '');
        $pageSlug = trim($data['page_slug'] ?? '');
        $pageContent = $data['page_content'] ?? '';
        
        if (!$websiteId || !$pageName || !$pageSlug) {
            $this->jsonResponse(400, ['error' => 'Website ID, page name, and slug are required']);
            return;
        }
        
        // Verify website exists
        $stmt = $pdo->prepare("SELECT id FROM websites WHERE id = :id");
        $stmt->execute([':id' => $websiteId]);
        if (!$stmt->fetch()) {
            $this->jsonResponse(404, ['error' => 'Website not found']);
            return;
        }
        
        // Get company_id from website
        $stmt = $pdo->prepare("SELECT company_id FROM websites WHERE id = :id");
        $stmt->execute([':id' => $websiteId]);
        $companyId = $stmt->fetchColumn();
        
        // Generate slug if not provided
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $pageSlug));
        $slug = trim($slug, '-');
        
        // Check if slug already exists for this website
        $stmt = $pdo->prepare("SELECT id FROM pages WHERE website_id = :website_id AND page_slug = :slug");
        $stmt->execute([':website_id' => $websiteId, ':slug' => $slug]);
        if ($stmt->fetch()) {
            $this->jsonResponse(409, ['error' => 'Page slug already exists for this website']);
            return;
        }
        
        // Get max sort order
        $stmt = $pdo->prepare("SELECT MAX(sort_order) FROM pages WHERE website_id = :id");
        $stmt->execute([':id' => $websiteId]);
        $maxSort = $stmt->fetchColumn();
        $sortOrder = ($maxSort ?: 0) + 1;
        
        // Insert page
        $stmt = $pdo->prepare("
            INSERT INTO pages (website_id, company_id, page_name, page_slug, page_content, page_status, sort_order)
            VALUES (:website_id, :company_id, :name, :slug, :content, 'Draft', :sort)
        ");
        
        $result = $stmt->execute([
            ':website_id' => $websiteId,
            ':company_id' => $companyId,
            ':name' => $pageName,
            ':slug' => $slug,
            ':content' => $pageContent,
            ':sort' => $sortOrder
        ]);
        
        if ($result) {
            $pageId = $pdo->lastInsertId();
            $this->jsonResponse(201, [
                'success' => true,
                'message' => 'Page created successfully',
                'data' => ['page_id' => $pageId]
            ]);
        } else {
            $this->jsonResponse(500, ['error' => 'Failed to create page']);
        }
    }
    
    /**
     * PUT /api/pages/update - Update page
     */
    public function update($pageId, $data) {
        $this->requireAdmin();
        $pdo = getDBConnection();
        $user = $this->auth->getUser();
        
        // Verify page exists
        $stmt = $pdo->prepare("SELECT * FROM pages WHERE id = :id");
        $stmt->execute([':id' => $pageId]);
        $page = $stmt->fetch();
        
        if (!$page) {
            $this->jsonResponse(404, ['error' => 'Page not found']);
            return;
        }
        
        $fields = [];
        $params = [':id' => $pageId];
        
        if (isset($data['page_name'])) {
            $fields[] = 'page_name = :name';
            $params[':name'] = $data['page_name'];
        }
        if (isset($data['page_slug'])) {
            $fields[] = 'page_slug = :slug';
            $params[':slug'] = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $data['page_slug']));
        }
        if (isset($data['page_content'])) {
            $fields[] = 'page_content = :content';
            $params[':content'] = $data['page_content'];
        }
        if (isset($data['page_status'])) {
            $fields[] = 'page_status = :status';
            $params[':status'] = $data['page_status'];
        }
        if (isset($data['meta_title'])) {
            $fields[] = 'meta_title = :meta_title';
            $params[':meta_title'] = $data['meta_title'];
        }
        if (isset($data['meta_description'])) {
            $fields[] = 'meta_description = :meta_desc';
            $params[':meta_desc'] = $data['meta_description'];
        }
        if (isset($data['sort_order'])) {
            $fields[] = 'sort_order = :sort';
            $params[':sort'] = (int)$data['sort_order'];
        }
        
        if (!empty($fields)) {
            $query = 'UPDATE pages SET ' . implode(', ', $fields) . ' WHERE id = :id';
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
        }
        
        $this->jsonResponse(200, [
            'success' => true,
            'message' => 'Page updated successfully'
        ]);
    }
    
    /**
     * DELETE /api/pages/delete - Delete page
     */
    public function delete($pageId) {
        $this->requireAdmin();
        $pdo = getDBConnection();
        
        // Verify page exists and is not required
        $stmt = $pdo->prepare("SELECT * FROM pages WHERE id = :id");
        $stmt->execute([':id' => $pageId]);
        $page = $stmt->fetch();
        
        if (!$page) {
            $this->jsonResponse(404, ['error' => 'Page not found']);
            return;
        }
        
        if ($page['is_required']) {
            $this->jsonResponse(400, ['error' => 'Cannot delete required pages']);
            return;
        }
        
        $stmt = $pdo->prepare("DELETE FROM pages WHERE id = :id");
        $stmt->execute([':id' => $pageId]);
        
        $this->jsonResponse(200, [
            'success' => true,
            'message' => 'Page deleted successfully'
        ]);
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