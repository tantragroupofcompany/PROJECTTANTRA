<?php
// ============================================================
// TANTRA GROUP OF INDUSTRIES
// Phase 3 Enhanced Router
// API routes + Website Builder + Dynamic rendering
// ============================================================

require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../middleware/Session.php';
require_once __DIR__ . '/../middleware/Auth.php';
require_once __DIR__ . '/../middleware/AuditLogger.php';
require_once __DIR__ . '/../api/CompaniesAPI.php';
require_once __DIR__ . '/../api/WebsitesAPI.php';
require_once __DIR__ . '/../api/PagesAPI.php';
require_once __DIR__ . '/../api/EmployeesAPI.php';
require_once __DIR__ . '/../api/DepartmentsAPI.php';
require_once __DIR__ . '/../api/ApprovalsAPI.php';
require_once __DIR__ . '/../api/NotificationsAPI.php';
require_once __DIR__ . '/../api/ReportsAPI.php';

class Router {
    private array $routes = [];
    private array $middleware = [];
    
    public function add(string $method, string $path, callable $handler, array $middleware = []): void {
        $this->routes[] = [
            'method'     => strtoupper($method),
            'path'       => $path,
            'pattern'    => $this->pathToPattern($path),
            'handler'    => $handler,
            'middleware' => $middleware,
        ];
    }
    
    public function get(string $path, callable $handler, array $middleware = []): void {
        $this->add('GET', $path, $handler, $middleware);
    }
    
    public function post(string $path, callable $handler, array $middleware = []): void {
        $this->add('POST', $path, $handler, $middleware);
    }
    
    public function put(string $path, callable $handler, array $middleware = []): void {
        $this->add('PUT', $path, $handler, $middleware);
    }
    
    public function addMiddleware(callable $middleware): void {
        $this->middleware[] = $middleware;
    }
    
    public function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Handle API routes
        if (strpos($uri, '/api/') === 0) {
            $this->handleAPI($uri, $method);
            return;
        }
        
        // Handle dynamic website pages (/{company_slug}/{page_slug})
        if ($method === 'GET' && preg_match('#^/([a-z0-9-]+)/([a-z0-9-]+)$#', $uri, $matches)) {
            $this->renderDynamicWebsite($matches[1], $matches[2]);
            return;
        }
        
        // Handle dynamic website home (/{company_slug})
        if ($method === 'GET' && preg_match('#^/([a-z0-9-]+)$#', $uri, $matches) && 
            !in_array($matches[1], ['companies', 'about', 'contact', 'corporate', 'api', 'admin'])) {
            $this->renderDynamicWebsite($matches[1], 'home');
            return;
        }
        
        // Run global middleware
        foreach ($this->middleware as $mw) {
            $mw();
        }
        
        // Find matching route
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            if (preg_match($route['pattern'], $uri, $matches)) {
                foreach ($route['middleware'] as $mw) {
                    $mw();
                }
                
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                call_user_func($route['handler'], $params);
                return;
            }
        }
        
        // 404
        http_response_code(404);
        $title = 'Page Not Found - TANTRA GROUP OF INDUSTRIES';
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= htmlspecialchars($title) ?></title>
            <link rel="stylesheet" href="/public/css/style.css">
        </head>
        <body>
            <div class="error-page">
                <div class="error-container">
                    <div class="error-code">404</div>
                    <h1 class="error-title">Page Not Found</h1>
                    <p class="error-message">The page you are looking for does not exist.</p>
                    <div class="error-actions">
                        <a href="/" class="btn btn-primary">Return Home</a>
                        <a href="/companies" class="btn btn-secondary">View Companies</a>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
    
    /**
     * Handle API requests
     */
    private function handleAPI(string $uri, string $method): void {
        $path = parse_url($uri, PHP_URL_PATH);
        $segments = explode('/', $path);
        
        // Companies API
        if (count($segments) >= 3 && $segments[2] === 'companies') {
            $api = new CompaniesAPI();
            
            if (count($segments) === 3) {
                if ($method === 'GET') {
                    $api->index();
                } elseif ($method === 'POST') {
                    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
                    $api->create($input);
                } else {
                    $this->jsonResponse(405, ['error' => 'Method not allowed']);
                }
                return;
            }
            
            if (count($segments) === 4 && $segments[3] === 'publish' && $method === 'POST') {
                $id = (int)($_POST['company_id'] ?? 0);
                $api->publish($id);
                return;
            }
            
            if (count($segments) === 4 && $segments[3] === 'unpublish' && $method === 'POST') {
                $id = (int)($_POST['company_id'] ?? 0);
                $api->unpublish($id);
                return;
            }
            
            if (count($segments) === 4 && $segments[3] === 'archive' && $method === 'POST') {
                $id = (int)($_POST['company_id'] ?? 0);
                $api->archive($id);
                return;
            }
            
            if (count($segments) === 4 && $segments[3] === 'media' && $method === 'POST') {
                $companyId = (int)($_POST['company_id'] ?? 0);
                $type = $_POST['media_type'] ?? 'logo';
                $api->uploadMedia($companyId, 'media_file', $type);
                return;
            }
            
            if (count($segments) === 4 && $method === 'GET') {
                $api->show($segments[3]);
                return;
            }
            
            if (count($segments) === 4 && $method === 'PUT') {
                $id = (int)$segments[3];
                $input = json_decode(file_get_contents('php://input'), true);
                $api->update($id, $input);
                return;
            }
        }
        
        // Websites API
        if (count($segments) >= 3 && $segments[2] === 'websites') {
            $api = new WebsitesAPI();
            
            if (count($segments) === 3 && $method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
                $api->create($input);
                return;
            }
            
            if (count($segments) === 4 && $segments[3] === 'publish' && $method === 'POST') {
                $id = (int)($_POST['website_id'] ?? 0);
                $api->publish($id);
                return;
            }
            
            if (count($segments) === 4 && $method === 'GET') {
                $api->get((int)$segments[3]);
                return;
            }
            
            if (count($segments) === 4 && $method === 'PUT') {
                $id = (int)$segments[3];
                $input = json_decode(file_get_contents('php://input'), true);
                $api->update($id, $input);
                return;
            }
        }
        
        // Pages API
        if (count($segments) >= 3 && $segments[2] === 'pages') {
            $api = new PagesAPI();
            
            if (count($segments) === 3 && $method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
                $api->create($input);
                return;
            }
            
            if (count($segments) === 4 && $segments[3] === 'delete' && $method === 'POST') {
                $id = (int)($_POST['page_id'] ?? 0);
                $api->delete($id);
                return;
            }
            
            if (count($segments) === 4 && $method === 'PUT') {
                $id = (int)$segments[3];
                $input = json_decode(file_get_contents('php://input'), true);
                $api->update($id, $input);
                return;
            }
            
            if (count($segments) === 4 && $method === 'GET') {
                $api->show($segments[3]);
                return;
            }
        }
        
        // Phase 4: Employees API
        if (count($segments) >= 3 && $segments[2] === 'employees') {
            $api = new EmployeesAPI();
            if ($method === 'GET' && count($segments) === 3) { $api->index(); return; }
            if ($method === 'POST' && count($segments) === 3) { $api->create(json_decode(file_get_contents('php://input'), true) ?? $_POST); return; }
            if ($method === 'GET' && count($segments) === 4) { $api->show((int)$segments[3]); return; }
            if ($method === 'PUT' && count($segments) === 4) { $api->update((int)$segments[3], json_decode(file_get_contents('php://input'), true)); return; }
            $this->jsonResponse(405, ['error' => 'Method not allowed']);
            return;
        }
        
        // Phase 4: Departments API
        if (count($segments) >= 3 && $segments[2] === 'departments') {
            $api = new DepartmentsAPI();
            if ($method === 'GET') { $api->index(); return; }
            if ($method === 'POST') { $api->create(json_decode(file_get_contents('php://input'), true) ?? $_POST); return; }
            $this->jsonResponse(405, ['error' => 'Method not allowed']);
            return;
        }
        
        // Phase 4: Approvals API
        if (count($segments) >= 3 && $segments[2] === 'approvals') {
            $api = new ApprovalsAPI();
            if ($method === 'GET') { $api->index(); return; }
            if ($method === 'POST') { $api->create(json_decode(file_get_contents('php://input'), true) ?? $_POST); return; }
            if ($method === 'PUT' && count($segments) === 4) { $api->review((int)$segments[3], json_decode(file_get_contents('php://input'), true)); return; }
            $this->jsonResponse(405, ['error' => 'Method not allowed']);
            return;
        }
        
        // Phase 4: Notifications API
        if (count($segments) >= 3 && $segments[2] === 'notifications') {
            $api = new NotificationsAPI();
            if ($method === 'GET') { $api->index(); return; }
            if ($method === 'PUT' && count($segments) === 4) { $api->markRead((int)$segments[3]); return; }
            $this->jsonResponse(405, ['error' => 'Method not allowed']);
            return;
        }
        
        // Phase 4: Reports API
        if (count($segments) >= 3 && $segments[2] === 'reports') {
            $api = new ReportsAPI();
            if ($method === 'GET') { $api->index(); return; }
            if ($method === 'POST') { $api->generate(json_decode(file_get_contents('php://input'), true) ?? $_POST); return; }
            $this->jsonResponse(405, ['error' => 'Method not allowed']);
            return;
        }
        
        $this->jsonResponse(404, ['error' => 'API endpoint not found']);
    }
    
    /**
     * Render dynamic website pages
     */
    private function renderDynamicWebsite(string $companySlug, string $pageSlug): void {
        try {
            require_once __DIR__ . '/../database/config.php';
            $pdo = getDBConnection();
            
            // Get website by company slug
            $stmt = $pdo->prepare("
                SELECT w.*, c.company_name, c.slug, c.industry, c.short_description, c.description,
                       c.website_url, c.launch_year, c.headquarters, c.contact_email, c.contact_number,
                       m.file_path as logo_path,
                       b.file_path as banner_path
                FROM websites w
                JOIN companies c ON w.company_id = c.id
                LEFT JOIN company_media m ON c.id = m.company_id AND m.media_type = 'logo'
                LEFT JOIN company_media b ON c.id = b.company_id AND b.media_type = 'banner'
                WHERE c.slug = :slug AND w.status = 'Published'
                LIMIT 1
            ");
            $stmt->execute([':slug' => $companySlug]);
            $website = $stmt->fetch();
            
            if (!$website) {
                // Check if company exists but website not published
                $stmt = $pdo->prepare("SELECT id FROM companies WHERE slug = :slug");
                $stmt->execute([':slug' => $companySlug]);
                if (!$stmt->fetch()) {
                    http_response_code(404);
                    echo "Website not found";
                    exit;
                }
                http_response_code(404);
                echo "Website not published yet";
                exit;
            }
            
            // Get page
            $pageStmt = $pdo->prepare("
                SELECT * FROM pages 
                WHERE website_id = :website_id AND page_slug = :slug AND page_status = 'Published'
                LIMIT 1
            ");
            $pageStmt->execute([':website_id' => $website['id'], ':slug' => $pageSlug]);
            $page = $pageStmt->fetch();
            
            if (!$page && $pageSlug !== 'home') {
                http_response_code(404);
                echo "Page not found";
                exit;
            }
            
            // Track analytics
            $this->trackAnalytics($pdo, $website['id'], $pageSlug);
            
            // Render website
            $this->renderWebsite($website, $page, $pageSlug);
            
        } catch (PDOException $e) {
            http_response_code(500);
            echo "Database error";
            exit;
        }
    }
    
    /**
     * Track website analytics
     */
    private function trackAnalytics($pdo, $websiteId, $pageSlug): void {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO website_analytics (website_id, page_slug, visitor_ip, user_agent, referrer)
                VALUES (:website_id, :page_slug, :ip, :agent, :referrer)
            ");
            $stmt->execute([
                ':website_id' => $websiteId,
                ':page_slug' => $pageSlug,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                ':agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                ':referrer' => $_SERVER['HTTP_REFERER'] ?? null,
            ]);
        } catch (PDOException $e) {
            error_log('Analytics tracking failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Render website with template
     */
    private function renderWebsite($website, $page, $pageSlug): void {
        $companyName = $website['company_name'];
        $pageName = $page['page_name'] ?? 'Home';
        $pageContent = $page['page_content'] ?? '';
        
        // Parse theme settings
        $theme = json_decode($website['theme_settings'] ?? '{}', true);
        $primaryColor = $theme['primary_color'] ?? '#1a1a2e';
        $secondaryColor = $theme['secondary_color'] ?? '#e94560';
        
        // Parse navigation
        $navigation = json_decode($website['navigation'] ?? '[]', true);
        
        header('Content-Type: text/html; charset=utf-8');
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= htmlspecialchars($companyName) ?> - <?= htmlspecialchars($pageName) ?></title>
            <meta name="description" content="<?= htmlspecialchars($website['short_description'] ?? '') ?>">
            <meta property="og:title" content="<?= htmlspecialchars($companyName) ?> - <?= htmlspecialchars($pageName) ?>">
            <meta property="og:type" content="website">
            <link rel="stylesheet" href="/public/css/style.css">
            <style>
                :root {
                    --website-primary: <?= htmlspecialchars($primaryColor) ?>;
                    --website-secondary: <?= htmlspecialchars($secondaryColor) ?>;
                }
            </style>
        </head>
        <body>
            <header class="site-header">
                <div class="container">
                    <div class="header-inner">
                        <a href="/<?= htmlspecialchars($website['slug']) ?>" class="logo">
                            <?php if ($website['logo_path']): ?>
                                <img src="<?= htmlspecialchars($website['logo_path']) ?>" alt="<?= htmlspecialchars($companyName) ?>" class="logo-img">
                            <?php else: ?>
                                <span class="logo-icon">T</span>
                            <?php endif; ?>
                            <div class="logo-text">
                                <span class="logo-title"><?= htmlspecialchars($companyName) ?></span>
                            </div>
                        </a>
                        <nav class="main-nav" id="mainNav">
                            <ul>
                                <?php foreach ($navigation as $item): ?>
                                    <?php if ($item['enabled']): ?>
                                        <li>
                                            <a href="/<?= htmlspecialchars($website['slug']) ?>/<?= htmlspecialchars($item['slug']) ?>">
                                                <?= htmlspecialchars($item['name']) ?>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        </nav>
                        <button class="mobile-toggle" id="mobileToggle">
                            <span></span><span></span><span></span>
                        </button>
                    </div>
                </div>
            </header>
            
            <main class="main-content">
                <?php if ($pageSlug === 'home' && $website['banner_path']): ?>
                    <section class="hero-section">
                        <div class="container">
                            <h1><?= htmlspecialchars($companyName) ?></h1>
                            <p><?= htmlspecialchars($website['short_description'] ?? '') ?></p>
                        </div>
                    </section>
                <?php endif; ?>
                
                <section class="page-content section">
                    <div class="container">
                        <?php if ($pageContent): ?>
                            <div class="content-body">
                                <?= $pageContent ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-page">
                                <h2>Welcome to <?= htmlspecialchars($companyName) ?></h2>
                                <p>This page is under construction.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            </main>
            
            <footer class="site-footer">
                <div class="container">
                    <div class="footer-grid">
                        <div class="footer-brand">
                            <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($companyName) ?>. All rights reserved.</p>
                            <p class="text-muted">Part of TANTRA GROUP OF INDUSTRIES</p>
                        </div>
                        <div class="footer-links">
                            <ul>
                                <?php foreach ($navigation as $item): ?>
                                    <?php if ($item['enabled']): ?>
                                        <li>
                                            <a href="/<?= htmlspecialchars($website['slug']) ?>/<?= htmlspecialchars($item['slug']) ?>">
                                                <?= htmlspecialchars($item['name']) ?>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </footer>
            
            <script src="/public/js/main.js"></script>
        </body>
        </html>
        <?php
        exit;
    }
    
    private function jsonResponse(int $statusCode, array $data): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    private function pathToPattern(string $path): string {
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = str_replace('/', '\/', $pattern);
        return '/^' . $pattern . '$/';
    }
}