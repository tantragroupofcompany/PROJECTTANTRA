<?php
$title = 'Our Companies - TANTRA GROUP OF INDUSTRIES';
include __DIR__ . '/../layouts/public_header.php';

// Get search/filter params
$search = trim($_GET['search'] ?? '');
$industry = trim($_GET['industry'] ?? '');
$sort = $_GET['sort'] ?? 'name';

// Build query
try {
    require_once __DIR__ . '/../../database/config.php';
    $pdo = getDBConnection();
    
    $where = ["c.status = 'Live'"];
    $params = [];
    
    if ($search) {
        $where[] = "(c.company_name LIKE :search OR c.short_description LIKE :search OR c.industry LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    if ($industry) {
        $where[] = "c.industry = :industry";
        $params[':industry'] = $industry;
    }
    
    $whereClause = implode(' AND ', $where);
    
    // Order by
    $orderBy = "ORDER BY c.company_name ASC";
    if ($sort === 'year') {
        $orderBy = "ORDER BY c.launch_year DESC, c.company_name ASC";
    } elseif ($sort === 'newest') {
        $orderBy = "ORDER BY c.published_at DESC, c.company_name ASC";
    }
    
    $stmt = $pdo->prepare("
        SELECT DISTINCT c.id, c.company_name, c.company_code, c.slug, c.industry, 
               c.short_description, c.launch_year, c.headquarters,
               m.file_path as logo_path,
               b.file_path as banner_path
        FROM companies c
        LEFT JOIN company_media m ON c.id = m.company_id AND m.media_type = 'logo'
        LEFT JOIN company_media b ON c.id = b.company_id AND b.media_type = 'banner'
        WHERE $whereClause
        $orderBy
    ");
    $stmt->execute($params);
    $companies = $stmt->fetchAll();
    
    // Get all industries for filter
    $industries = $pdo->query("SELECT DISTINCT industry FROM companies WHERE status = 'Live' AND industry IS NOT NULL ORDER BY industry ASC")->fetchAll(PDO::FETCH_COLUMN);
    
} catch (PDOException $e) {
    $companies = [];
    $industries = [];
    $error = 'Unable to load companies at this time.';
}
?>

<section class="page-hero section">
    <div class="container">
        <div class="page-hero-content">
            <h1>Our <span class="text-accent">Companies</span></h1>
            <p>Discover the innovative brands operating under TANTRA GROUP OF INDUSTRIES.</p>
        </div>
    </div>
</section>

<section class="companies-search section">
    <div class="container">
        <form method="GET" action="/companies" class="search-filter-form">
            <div class="search-bar">
                <input type="text" name="search" placeholder="Search companies..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
            <div class="filter-bar">
                <div class="filter-group">
                    <label>Industry</label>
                    <select name="industry" onchange="this.form.submit()">
                        <option value="">All Industries</option>
                        <?php foreach ($industries as $ind): ?>
                            <option value="<?= htmlspecialchars($ind) ?>" <?= $industry === $ind ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ind) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Sort By</label>
                    <select name="sort" onchange="this.form.submit()">
                        <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Name (A-Z)</option>
                        <option value="year" <?= $sort === 'year' ? 'selected' : '' ?>>Launch Year</option>
                        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                    </select>
                </div>
                <?php if ($search || $industry): ?>
                    <a href="/companies" class="btn btn-ghost btn-sm">Clear Filters</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</section>

<section class="companies-list section">
    <div class="container">
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if (empty($companies)): ?>
            <div class="empty-state">
                <div class="empty-icon">&#9733;</div>
                <h3>No Companies Found</h3>
                <p>Try adjusting your search or filter criteria.</p>
            </div>
        <?php else: ?>
            <div class="results-info">
                <p>Showing <?= count($companies) ?> company<?= count($companies) !== 1 ? 'ies' : '' ?></p>
            </div>
            <div class="company-cards">
                <?php foreach ($companies as $company): ?>
                <a href="/companies/<?= htmlspecialchars($company['slug']) ?>" class="company-card-link">
                    <div class="company-card">
                        <div class="company-card-logo">
                            <?php if ($company['logo_path']): ?>
                                <img src="<?= htmlspecialchars($company['logo_path']) ?>" alt="<?= htmlspecialchars($company['company_name']) ?> logo">
                            <?php else: ?>
                                <div class="company-logo-placeholder"><?= strtoupper(substr($company['company_name'], 0, 1)) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="company-card-body">
                            <div class="company-card-header">
                                <h3><?= htmlspecialchars($company['company_name']) ?></h3>
                                <span class="status-badge status-live">Live</span>
                            </div>
                            <p class="company-card-code"><?= htmlspecialchars($company['industry'] ?? 'Company') ?></p>
                            <p class="company-card-desc"><?= htmlspecialchars($company['short_description'] ?? $company['industry']) ?></p>
                            <div class="company-card-meta">
                                <?php if ($company['launch_year']): ?>
                                    <span class="meta-tag">&#128197; <?= htmlspecialchars($company['launch_year']) ?></span>
                                <?php endif; ?>
                                <?php if ($company['headquarters']): ?>
                                    <span class="meta-tag">&#127759; <?= htmlspecialchars($company['headquarters']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../layouts/public_footer.php'; ?>