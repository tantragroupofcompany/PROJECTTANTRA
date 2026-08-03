<?php
// ============================================================
// TANTRA GROUP OF INDUSTRIES
// Corporate Dashboard Header
// Requires authentication
// ============================================================
require_once __DIR__ . '/../../middleware/Auth.php';
$auth = Auth::getInstance();
$auth->requireAuth();
$user = $auth->getUser();
$currentModule = basename($_SERVER['REQUEST_URI'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Corporate Dashboard') ?> - TANTRA GROUP OF INDUSTRIES</title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard-layout">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="/corporate/dashboard" class="sidebar-logo">
                    <span class="logo-icon">T</span>
                    <div class="logo-text">
                        <span class="logo-title">TANTRA</span>
                        <span class="logo-subtitle">CORPORATE</span>
                    </div>
                </a>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="/corporate/dashboard" class="<?= strpos($currentModule, 'dashboard') !== false ? 'active' : '' ?>">
                            <span class="nav-icon">&#9632;</span>
                            <span class="nav-label">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="/corporate/companies" class="<?= strpos($currentModule, 'companies') !== false ? 'active' : '' ?>">
                            <span class="nav-icon">&#9632;</span>
                            <span class="nav-label">Company Management</span>
                        </a>
                    </li>
                    <li>
                        <a href="/corporate/profile" class="<?= strpos($currentModule, 'profile') !== false ? 'active' : '' ?>">
                            <span class="nav-icon">&#9632;</span>
                            <span class="nav-label">User Profile</span>
                        </a>
                    </li>
                    <li>
                        <a href="/corporate/audit-logs" class="<?= strpos($currentModule, 'audit') !== false ? 'active' : '' ?>">
                            <span class="nav-icon">&#9632;</span>
                            <span class="nav-label">Audit Logs</span>
                        </a>
                    </li>
                    <li class="nav-divider"></li>
                    <li>
                        <a href="/corporate/logout" class="nav-logout">
                            <span class="nav-icon">&#9632;</span>
                            <span class="nav-label">Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="sidebar-user">
                <div class="user-avatar"><?= strtoupper(substr($user['username'] ?? 'U', 0, 1)) ?></div>
                <div class="user-info">
                    <span class="user-name"><?= htmlspecialchars($user['username'] ?? 'User') ?></span>
                    <span class="user-role"><?= htmlspecialchars($user['role'] ?? '') ?></span>
                </div>
            </div>
        </aside>
        <div class="dashboard-main">
            <header class="dashboard-topbar">
                <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
                    <span></span><span></span><span></span>
                </button>
                <div class="topbar-title">
                    <h1><?= htmlspecialchars($title ?? 'Dashboard') ?></h1>
                </div>
                <div class="topbar-actions">
                    <a href="/" class="btn btn-ghost btn-sm" target="_blank">View Website</a>
                </div>
            </header>
            <div class="dashboard-content">