<?php
require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../middleware/Auth.php';

class ReportsAPI {
    private $auth;
    
    public function __construct() {
        $this->auth = Auth::getInstance();
    }
    
    public function index() {
        if (!$this->auth->isAuthenticated()) {
            $this->jsonResponse(401, ['error' => 'Unauthorized']);
            return;
        }
        $pdo = getDBConnection();
        $stmt = $pdo->query("SELECT * FROM reports ORDER BY created_at DESC");
        $this->jsonResponse(200, ['success' => true, 'data' => $stmt->fetchAll()]);
    }
    
    public function generate($data) {
        $this->requireAdmin();
        $pdo = getDBConnection();
        $user = $this->auth->getUser();
        $stmt = $pdo->prepare("INSERT INTO reports (company_id, report_type, report_name, report_format, generated_by) VALUES (:cid, :type, :name, :fmt, :uid)");
        $stmt->execute([
            ':cid' => $data['company_id'] ?? null,
            ':type' => $data['report_type'],
            ':name' => $data['report_name'],
            ':fmt' => $data['report_format'] ?? 'PDF',
            ':uid' => $user['id']
        ]);
        $this->jsonResponse(201, ['success' => true, 'message' => 'Report generated']);
    }
    
    private function requireAdmin() {
        if (!$this->auth->hasAnyRole(['Founder', 'Chairman', 'CEO', 'Director'])) {
            $this->jsonResponse(403, ['error' => 'Forbidden']);
        }
    }
    
    private function jsonResponse($code, $data) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
