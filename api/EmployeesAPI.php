 <?php
require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../middleware/Auth.php';
require_once __DIR__ . '/../middleware/AuditLogger.php';

class EmployeesAPI {
    private $auth;
    private $auditLogger;
    
    public function __construct() {
        $this->auth = Auth::getInstance();
        $this->auditLogger = AuditLogger::getInstance();
    public function create($data) {
        $this->requireAdmin();
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("
            INSERT INTO employees (employee_id, full_name, email, designation, role, status)
            VALUES (:emp_id, :name, :email, :designation, :role, 'Active')
        ");
    private function requireAdmin() {
        if (!$this->auth->hasAnyRole(['Founder', 'Chairman', 'CEO', 'Director', 'Manager'])) {
            $this->jsonResponse(403, ['error' => 'Forbidden']);
        }
    private function jsonResponse($statusCode, $data) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
    public function index() {
        if (!$this->auth->isAuthenticated()) {
            $this->jsonResponse(401, ['error' => 'Unauthorized']);
            return;
        }
        
        $pdo = getDBConnection();
        $stmt = $pdo->query("SELECT * FROM employees ORDER BY created_at DESC");
        $employees = $stmt->fetchAll();
        
        $this->jsonResponse(200, ['success' => true, 'data' => $employees]);
