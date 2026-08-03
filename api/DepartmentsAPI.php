<?php
require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../middleware/Auth.php';
require_once __DIR__ . '/../middleware/AuditLogger.php';

class DepartmentsAPI {
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
        $stmt = $pdo->query("SELECT * FROM departments ORDER BY created_at DESC");
        $this->jsonResponse(200, ['success' => true, 'data' => $stmt->fetchAll()]);
    }
    
    public function create($data) {
        $this->requireAdmin();
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("INSERT INTO departments (company_id, department_name, department_code, description) VALUES (:cid, :name, :code, :desc)");
        $stmt->execute([
            ':cid' => $data['company_id'],
            ':name' => $data['department_name'],
            ':code' => $data['department_code'],
            ':desc' => $data['description'] ?? ''
        ]);
        $this->jsonResponse(201, ['success' => true, 'message' => 'Department created']);
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
