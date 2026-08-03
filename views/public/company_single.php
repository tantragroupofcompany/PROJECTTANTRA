<?php
$title = 'Company Details - TANTRA GROUP OF INDUSTRIES';
include __DIR__ . '/../layouts/public_header.php';

$slug = $_GET['slug'] ?? '';

if (!$slug) {
    header('Location: /companies');
    exit;
}

// Fetch company by slug
try {
    require_once __DIR__ . '/../../database/config.php';
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("
        SELECT c.*, 
               m.file_path as logo_path,
               b.file_path as banner_path
        FROM companies c
        LEFT JOIN company_media m ON c.id = m.company_id AND m.media_type = 'logo'
        LEFT JOIN company_media b ON c.id = b.company_id AND b.media_type = 'banner'
        WHERE c.slug = :slug AND c.status = 'Live'
        LIMIT 1
    ");
    $stmt->execute([':slug' => $slug]);
    $company = $stmt->fetch();
    
    if (!$company) {
        header('Location: /companies');
        exit;
    }
    
    // Set SEO meta tags
    $metaTitle = $company['company_name'] . ' - ' . ($company['short_description'] ?? 'TANTRA GROUP OF INDUSTRIES');
    $metaDescription = $company['short_description'] ?? $company['description'];
    
} catch (PDOException $e) {
    header('Location: /companies');
    exit;
}

// Update page title with SEO
$title = $company['company_name'] . ' - ' . ($company['industry'] ?? 'Company') . ' | TANTRA GROUP';
?>

<!-- SEO Meta Tags -->
<meta name="description" content="<?= htmlspecialchars($metaDescription ?? '') ?>">
<meta name="keywords" content="<?= htmlspecialchars($company['company_name'] ?? '') ?>, <?= htmlspecialchars($company['industry'] ?? '') ?>, TANTRA GROUP OF INDUSTRIES">
<meta property="og:title" content="<?= htmlspecialchars($metaTitle) ?>">
<meta property="og:description" content="<?= htmlspecialchars($metaDescription ?? '') ?>">
<meta property="og:type" content="website">
<meta property="og:url" content="<?= 'https://' . $_SERVER['HTTP_HOST'] . '/companies/' . $slug ?>">
<?php if ($company['logo_path']): ?>
<meta property="og:image" content="<?= htmlspecialchars($company['logo_path']) ?>">
<?php endif; ?>
<link rel="canonical" href="<?= 'https://' . $_SERVER['HTTP_HOST'] . '/companies/' . $slug ?>">

<section class="company-hero section">
    <?php if ($company['banner_path']): ?>
    <div class="company-banner">
        <img src="<?= htmlspecialchars($company['banner_path']) ?>" alt="<?= htmlspecialchars($company['company_name']) ?> banner">
    </div>
    <?php endif; ?>
    <div class="container">
        <div class="company-hero-content">
            <div class="company-logo">
                <?php if ($company['logo_path']): ?>
                    <img src="<?= htmlspecialchars($company['logo_path']) ?>" alt="<?= htmlspecialchars($company['company_name']) ?> logo">
                <?php else: ?>
                    <div class="company-logo-placeholder company-logo-placeholder-xl">
                        <?= strtoupper(substr($company['company_name'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="company-info">
                <span class="company-industry"><?= htmlspecialchars($company['industry'] ?? 'Company') ?></span>
                <h1><?= htmlspecialchars($company['company_name']) ?></h1>
                <p class="company-tagline"><?= htmlspecialchars($company['short_description'] ?? $company['description']) ?></p>
                <div class="company-meta">
                    <?php if ($company['launch_year']): ?>
                        <span class="meta-item">
                            <strong>Launched:</strong> <?= htmlspecialchars($company['launch_year']) ?>
                        </span>
                    <?php endif; ?>
                    <?php if ($company['headquarters']): ?>
                        <span class="meta-item">
                            <strong>HQ:</strong> <?= htmlspecialchars($company['headquarters']) ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="company-actions">
                    <?php if ($company['website_url']): ?>
                        <a href="<?= htmlspecialchars($company['website_url']) ?>" class="btn btn-primary btn-lg" target="_blank" rel="noopener">
                            Visit Website
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="company-about section">
    <div class="container">
        <div class="section-header">
            <h2>About <span class="text-accent"><?= htmlspecialchars($company['company_name']) ?></span></h2>
        </div>
        <div class="company-about-content">
            <p><?= nl2br(htmlspecialchars($company['description'] ?? 'No description available.')) ?></p>
        </div>
    </div>
</section>

<?php if ($company['contact_email'] || $company['contact_number']): ?>
<section class="company-contact section bg-light">
    <div class="container">
        <div class="section-header">
            <h2>Get in <span class="text-accent">Touch</span></h2>
        </div>
        <div class="contact-grid">
            <?php if ($company['contact_email']): ?>
                <div class="contact-item">
                    <span class="contact-icon">&#9993;</span>
                    <div>
                        <h4>Email</h4>
                        <p><a href="mailto:<?= htmlspecialchars($company['contact_email']) ?>"><?= htmlspecialchars($company['contact_email']) ?></a></p>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($company['contact_number']): ?>
                <div class="contact-item">
                    <span class="contact-icon">&#9742;</span>
                    <div>
                        <h4>Phone</h4>
                        <p><a href="tel:<?= htmlspecialchars($company['contact_number']) ?>"><?= htmlspecialchars($company['contact_number']) ?></a></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="company-parent section">
    <div class="container">
        <div class="parent-company">
            <h3>Parent Company</h3>
            <p>This brand is part of the <strong>TANTRA GROUP OF INDUSTRIES</strong> family of companies.</p>
            <a href="/" class="btn btn-outline">Learn More About TANTRA GROUP</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../layouts/public_footer.php'; ?>