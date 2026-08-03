<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Forbidden - TANTRA GROUP OF INDUSTRIES</title>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>
    <div class="error-page">
        <div class="error-container">
            <div class="error-code">403</div>
            <h1 class="error-title">Access Denied</h1>
            <p class="error-message"><?= htmlspecialchars($message ?? 'You do not have permission to access this resource.') ?></p>
            <div class="error-actions">
                <a href="/" class="btn btn-primary">Return Home</a>
                <a href="/corporate/dashboard" class="btn btn-secondary">Go to Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>