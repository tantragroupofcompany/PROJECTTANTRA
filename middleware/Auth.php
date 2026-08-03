<?php
// ============================================================
// TANTRA GROUP OF INDUSTRIES
// Authentication Middleware
// Route protection and access control
// ============================================================

require_once __DIR__ . '/Session.php';

class Auth {
    private static ?Auth $instance = null;
    private Session $session;
    
    private function __construct() {
        $this->session = Session::getInstance();
    }
    
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Require authentication to access a route
     * Redirects to login page if not authenticated
     */
    public function requireAuth(): void {
        if (!$this->session->isLoggedIn()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/corporate/dashboard';
            $this->redirect('/corporate/login');
        }
    }
    
    /**
     * Require specific role to access a route
     */
    public function requireRole(string $role): void {
        $this->requireAuth();
        if (!$this->session->hasRole($role)) {
            $this->forbidden();
        }
    }
    
    /**
     * Require any of the specified roles to access a route
     */
    public function requireAnyRole(array $roles): void {
        $this->requireAuth();
        if (!$this->session->hasAnyRole($roles)) {
            $this->forbidden();
        }
    }
    
    /**
     * Redirect authenticated users away from login page
     */
    public function redirectIfAuthenticated(string $redirect = '/corporate/dashboard'): void {
        if ($this->session->isLoggedIn()) {
            $this->redirect($redirect);
        }
    }
    
    /**
     * Get current authenticated user
     */
    public function getUser(): ?array {
        return $this->session->getUser();
    }
    
    /**
     * Get current user ID
     */
    public function getUserId(): ?int {
        return $this->session->getUserId();
    }
    
    /**
     * Get current user role
     */
    public function getRole(): ?string {
        return $this->session->getRole();
    }
    
    /**
     * Check if user is authenticated
     */
    public function isAuthenticated(): bool {
        return $this->session->isLoggedIn();
    }
    
    /**
     * Perform login
     */
    public function login(string $username, string $password): array {
        return $this->session->login($username, $password);
    }
    
    /**
     * Perform logout
     */
    public function logout(): void {
        $this->session->logout();
    }
    
    /**
     * Redirect to a URL
     */
    private function redirect(string $url): void {
        header('Location: ' . $url);
        exit;
    }
    
    /**
     * Show 403 Forbidden page
     */
    private function forbidden(): void {
        http_response_code(403);
        $title = 'Access Denied - TANTRA GROUP OF INDUSTRIES';
        $message = 'You do not have permission to access this resource.';
        include __DIR__ . '/../views/errors/403.php';
        exit;
    }
}