<?php
require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../middleware/Auth.php';

class NotificationsAPI {
    private $auth;
    
    public function __construct() {
        $this->auth = Auth::getInstance();
    }
    
    public function index() {
        if (!$this->auth->isAuthenticated()) {
            $this->jsonResponse(401, ['error' => 'Unauthorized']);
            return;
        }
        $user = $this->auth->getUser();
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = :uid OR user_id IS NULL ORDER BY created_at DESC LIMIT 50");
        $stmt->execute([':uid' => $user['id']]);
        $this->jsonResponse(200, ['success' => true, 'data' => $stmt->fetchAll()]);
    }
    
    public function markRead($id) {
        if (!$this->auth->isAuthenticated()) {
            $this->jsonResponse(401, ['error' => 'Unauthorized']);
            return;
        }
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $this->jsonResponse(200, ['success' => true, 'message' => 'Notification marked as read']);
    }
    
    private function jsonResponse($code, $data) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
