<?php
// ============================================================
// TANTRA GROUP OF INDUSTRIES
// Session Management Middleware
// Server-side session handling with database storage
// ============================================================

require_once __DIR__ . '/../database/config.php';

class Session {
    private static ?Session $instance = null;
    private ?array $user = null;
    private string $sessionId;
    
    private function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->sessionId = session_id();
    }
    
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Authenticate user with username and password
     */
    public function login(string $username, string $password): array {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("
            SELECT id, username, email, password_hash, role, status 
            FROM users 
            WHERE username = :username 
            LIMIT 1
        ");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();
        
        if (!$user) {
            $this->logAudit(0, 'LOGIN_FAILED', 'Auth', "Failed login attempt for username: $username");
            return ['success' => false, 'message' => 'Invalid username or password.'];
        }
        
        if ($user['status'] !== 'Active') {
            $this->logAudit($user['id'], 'LOGIN_BLOCKED', 'Auth', "Blocked login - account inactive: $username");
            return ['success' => false, 'message' => 'Your account is inactive. Contact administrator.'];
        }
        
        if (!password_verify($password, $user['password_hash'])) {
            $this->logAudit($user['id'], 'LOGIN_FAILED', 'Auth', "Failed login attempt - invalid password for: $username");
            return ['success' => false, 'message' => 'Invalid username or password.'];
        }
        
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
        $this->sessionId = session_id();
        
        // Store user in session
        $this->user = [
            'id'       => (int)$user['id'],
            'username' => $user['username'],
            'email'    => $user['email'],
            'role'     => $user['role'],
        ];
        
        $_SESSION['user'] = $this->user;
        $_SESSION['login_time'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        // Store session in database
        $this->storeSession();
        
        $this->logAudit($this->user['id'], 'LOGIN_SUCCESS', 'Auth', "Successful login: $username");
        
        return ['success' => true, 'message' => 'Login successful.', 'user' => $this->user];
    }
    
    /**
     * Logout user and destroy session
     */
    public function logout(): void {
        if ($this->isLoggedIn()) {
            $this->logAudit($this->user['id'], 'LOGOUT', 'Auth', "User logged out: {$this->user['username']}");
        }
        
        // Remove session from database
        $this->destroySession();
        
        $_SESSION = [];
        
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        
        session_destroy();
        $this->user = null;
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn(): bool {
        if ($this->user !== null) {
            return true;
        }
        
        if (isset($_SESSION['user'])) {
            $this->user = $_SESSION['user'];
            
            // Verify session in database
            if ($this->validateSession()) {
                return true;
            }
            
            // Session expired or invalid
            $this->logout();
            return false;
        }
        
        return false;
    }
    
    /**
     * Get current user data
     */
    public function getUser(): ?array {
        return $this->user;
    }
    
    /**
     * Get current user ID
     */
    public function getUserId(): ?int {
        return $this->user['id'] ?? null;
    }
    
    /**
     * Get current user role
     */
    public function getRole(): ?string {
        return $this->user['role'] ?? null;
    }
    
    /**
     * Check if user has required role
     */
    public function hasRole(string $role): bool {
        return $this->user['role'] === $role;
    }
    
    /**
     * Check if user has any of the required roles
     */
    public function hasAnyRole(array $roles): bool {
        return in_array($this->user['role'] ?? '', $roles);
    }
    
    /**
     * Store session in database
     */
    private function storeSession(): void {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("
                INSERT INTO sessions (id, user_id, ip_address, user_agent, payload, last_activity)
                VALUES (:id, :user_id, :ip_address, :user_agent, :payload, NOW())
                ON DUPLICATE KEY UPDATE
                    last_activity = NOW(),
                    payload = :payload2,
                    ip_address = :ip_address2,
                    user_agent = :user_agent2
            ");
            $stmt->execute([
                ':id'          => $this->sessionId,
                ':user_id'     => $this->user['id'],
                ':ip_address'  => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                ':user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                ':payload'     => json_encode($_SESSION),
                ':payload2'    => json_encode($_SESSION),
                ':ip_address2' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                ':user_agent2' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            ]);
        } catch (PDOException $e) {
            error_log('Session storage failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Validate session exists in database and is not expired
     */
    private function validateSession(): bool {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("
                SELECT id FROM sessions 
                WHERE id = :id 
                AND user_id = :user_id
                AND last_activity > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                LIMIT 1
            ");
            $stmt->execute([
                ':id'      => $this->sessionId,
                ':user_id' => $this->user['id'],
            ]);
            return (bool)$stmt->fetch();
        } catch (PDOException $e) {
            error_log('Session validation failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Destroy session from database
     */
    private function destroySession(): void {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("DELETE FROM sessions WHERE id = :id");
            $stmt->execute([':id' => $this->sessionId]);
        } catch (PDOException $e) {
            error_log('Session destruction failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Log audit entry
     */
    private function logAudit(int $userId, string $action, string $module, string $description = ''): void {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("
                INSERT INTO audit_logs (user_id, action, module, description, ip_address, user_agent, timestamp)
                VALUES (:user_id, :action, :module, :description, :ip_address, :user_agent, NOW())
            ");
            $stmt->execute([
                ':user_id'     => $userId,
                ':action'      => $action,
                ':module'      => $module,
                ':description' => $description,
                ':ip_address'  => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                ':user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            ]);
        } catch (PDOException $e) {
            error_log('Audit log failed: ' . $e->getMessage());
        }
    }
}