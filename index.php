<?php
// ============================================================
// TANTRA GROUP OF INDUSTRIES
// Corporate Management Portal - Phase 3 Enhanced
// ============================================================

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '0');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', '86400');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/database/config.php';
require_once __DIR__ . '/middleware/Session.php';
require_once __DIR__ . '/middleware/Auth.php';
require_once __DIR__ . '/middleware/AuditLogger.php';
require_once __DIR__ . '/routes/Router.php';

$router = new Router();

// ============================================================
// PUBLIC ROUTES
// ============================================================

$router->get('/', function() {
    include __DIR__ . '/views/public/home.php';
});

$router->get('/about', function() {
    include __DIR__ . '/views/public/about.php';
});

$router->get('/companies', function() {
    include __DIR__ . '/views/public/companies.php';
});

$router->get('/contact', function() {
    include __DIR__ . '/views/public/contact.php';
});
$router->post('/contact', function() {
    include __DIR__ . '/views/public/contact.php';
});

// Dynamic company pages (Phase 2)
$router->get('/companies/{slug}', function($params) {
    $_GET['slug'] = $params['slug'];
    include __DIR__ . '/views/public/company_single.php';
});

// Dynamic website pages (Phase 3) - must be after specific routes
// Handled in Router::dispatch()

// ============================================================
// CORPORATE AUTH ROUTES
// ============================================================

$router->get('/corporate/login', function() {
    include __DIR__ . '/views/auth/login.php';
});
$router->post('/corporate/login', function() {
    include __DIR__ . '/views/auth/login.php';
});

$router->get('/corporate/logout', function() {
    $auth = Auth::getInstance();
    $auth->logout();
    header('Location: /corporate/login');
    exit;
});

// ============================================================
// CORPORATE PROTECTED ROUTES
// ============================================================

$router->get('/corporate/dashboard', function() {
    include __DIR__ . '/views/corporate/dashboard.php';
});

// Company Management
$router->get('/corporate/companies', function() {
    include __DIR__ . '/views/corporate/companies/list.php';
});
$router->post('/corporate/companies', function() {
    include __DIR__ . '/views/corporate/companies/list.php';
});

$router->get('/corporate/companies/add', function() {
    include __DIR__ . '/views/corporate/companies/add.php';
});
$router->post('/corporate/companies/add', function() {
    include __DIR__ . '/views/corporate/companies/add.php';
});

$router->get('/corporate/companies/view/{id}', function($params) {
    $_GET['id'] = $params['id'];
    include __DIR__ . '/views/corporate/companies/view.php';
});

$router->get('/corporate/companies/edit/{id}', function($params) {
    $_GET['id'] = $params['id'];
    include __DIR__ . '/views/corporate/companies/edit.php';
});
$router->post('/corporate/companies/edit/{id}', function($params) {
    $_GET['id'] = $params['id'];
    include __DIR__ . '/views/corporate/companies/edit.php';
});

// Website Builder (Phase 3)
$router->get('/corporate/website-builder', function() {
    include __DIR__ . '/views/corporate/website_builder.php';
});

// Media Library (Phase 3)
$router->get('/corporate/media-library', function() {
    include __DIR__ . '/views/corporate/media_library.php';
});

// ============================================================
// PHASE 4 ROUTES
// ============================================================

// Enterprise Dashboard
$router->get('/corporate/enterprise', function() {
    include __DIR__ . '/views/corporate/enterprise_dashboard.php';
});

// Employee Management
$router->get('/corporate/employees', function() {
    include __DIR__ . '/views/corporate/employees.php';
});
$router->post('/corporate/employees', function() {
    include __DIR__ . '/views/corporate/employees.php';
});

// Department Management
$router->get('/corporate/departments', function() {
    include __DIR__ . '/views/corporate/departments.php';
});
$router->post('/corporate/departments', function() {
    include __DIR__ . '/views/corporate/departments.php';
});

// Approval Center
$router->get('/corporate/approvals', function() {
    include __DIR__ . '/views/corporate/approvals.php';
});

// Security Center
$router->get('/corporate/security', function() {
    include __DIR__ . '/views/corporate/security_center.php';
});

// Reports Center
$router->get('/corporate/reports', function() {
    include __DIR__ . '/views/corporate/reports.php';
});

// ============================================================
// User Profile
$router->get('/corporate/profile', function() {
    include __DIR__ . '/views/corporate/profile.php';
});

// Audit Logs
$router->get('/corporate/audit-logs', function() {
    include __DIR__ . '/views/corporate/audit_logs.php';
});

// ============================================================
// DISPATCH
// ============================================================
$router->dispatch();