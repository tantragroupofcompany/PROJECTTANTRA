<?php
// ============================================================
// TANTRA GROUP OF INDUSTRIES
// Public Website Header
// ============================================================
$currentPage = basename($_SERVER['REQUEST_URI'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'TANTRA GROUP OF INDUSTRIES') ?></title>
    <meta name="description" content="TANTRA GROUP OF INDUSTRIES - A diversified conglomerate with brands including ShopTantra and HireTantra.">
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="header-inner">
                <a href="/" class="logo">
                    <span class="logo-icon">T</span>
                    <div class="logo-text">
                        <span class="logo-title">TANTRA</span>
                        <span class="logo-subtitle">GROUP OF INDUSTRIES</span>
                    </div>
                </a>
                <nav class="main-nav" id="mainNav">
                    <ul>
                        <li><a href="/" class="<?= $currentPage === '' || $currentPage === 'index.php' ? 'active' : '' ?>">Home</a></li>
                        <li><a href="/about" class="<?= $currentPage === 'about' ? 'active' : '' ?>">About Us</a></li>
                        <li><a href="/companies" class="<?= $currentPage === 'companies' ? 'active' : '' ?>">Companies</a></li>
                        <li><a href="/contact" class="<?= $currentPage === 'contact' ? 'active' : '' ?>">Contact Us</a></li>
                        <li><a href="/corporate/login" class="btn btn-outline btn-sm">Corporate Access</a></li>
                    </ul>
                </nav>
                <button class="mobile-toggle" id="mobileToggle" aria-label="Toggle navigation">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
    </header>
    <main class="main-content">