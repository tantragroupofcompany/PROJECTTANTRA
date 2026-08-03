<?php
require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../middleware/Auth.php';

class ApprovalsAPI {
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
        $stmt = $pdo->query("SELECT a.*, u.username as requester FROM approvals a LEFT JOIN users u ON a.requested_by = u.id ORDER BY a.created_at DESC");
        $this->jsonResponse(200, ['success' => true, 'data' => $stmt->fetchAll()]);
    }
    
    public function create($data) {
        $this->requireAdmin();
        $pdo = getDBConnection();
        $user = $this->auth->getUser();
        $stmt = $pdo->prepare("INSERT INTO approvals (company_id, approval_type, requested_by, status) VALUES (:cid, :type, :uid, 'Pending')");
        $stmt->execute([
            ':cid' => $data['company_id'] ?? null,
            ':type' => $data['approval_type'],
            ':uid' => $user['id']
        ]);
        $this->jsonResponse(201, ['success' => true, 'message' => 'Approval request created']);
    }
    
    public function review($id, $data) {
        $this->requireAdmin();
        $pdo = getDBConnection();
        $user = $this->auth->getUser();
        $stmt = $pdo->prepare("UPDATE approvals SET status = :status, reviewed_by = :uid, review_notes = :notes, reviewed_at = NOW() WHERE id = :id");
        $stmt->execute([
            ':status' => $data['status'],
            ':uid' => $user['id'],
            ':notes' => $data['review_notes'] ?? '',
            ':id' => $id
        ]);
        $this->jsonResponse(200, ['success' => true, 'message' => 'Approval reviewed']);
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
