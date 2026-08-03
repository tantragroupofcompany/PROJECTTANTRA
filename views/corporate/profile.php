<?php
$title = 'User Profile';
require_once __DIR__ . '/../../middleware/Auth.php';
include __DIR__ . '/../layouts/corporate_header.php';

$auth = Auth::getInstance();
$user = $auth->getUser();
?>

<div class="panel">
    <div class="panel-header">
        <h3>My Profile</h3>
    </div>
    <div class="panel-body">
        <div class="profile-section">
            <div class="profile-avatar">
                <div class="user-avatar user-avatar-lg"><?= strtoupper(substr($user['username'] ?? 'U', 0, 1)) ?></div>
            </div>
            <div class="profile-details">
                <div class="detail-row">
                    <div class="detail-label">Username</div>
                    <div class="detail-value"><?= htmlspecialchars($user['username'] ?? '') ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Email</div>
                    <div class="detail-value"><?= htmlspecialchars($user['email'] ?? '') ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Role</div>
                    <div class="detail-value">
                        <span class="role-badge role-<?= strtolower($user['role'] ?? '') ?>"><?= htmlspecialchars($user['role'] ?? '') ?></span>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Session</div>
                    <div class="detail-value">
                        <span class="text-muted">Active since <?= date('M j, Y g:i A', $_SESSION['login_time'] ?? time()) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <h3>Account Information</h3>
    </div>
    <div class="panel-body">
        <div class="info-box">
            <p><strong>Account Type:</strong> Corporate Administrator</p>
            <p><strong>Access Level:</strong> Full corporate access with role-based permissions</p>
            <p><strong>Password:</strong> Managed by system administrator</p>
            <p class="text-muted mt-1">For security reasons, profile changes must be made by the system administrator through the database.</p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/corporate_footer.php'; ?>