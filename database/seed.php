<?php
// ============================================================
// TANTRA GROUP OF INDUSTRIES
// Database Seed Script
// Creates initial corporate users
// Run: php database/seed.php
// ============================================================

require_once __DIR__ . '/config.php';

echo "============================================\n";
echo "TANTRA GROUP OF INDUSTRIES\n";
echo "Database Seed Script\n";
echo "============================================\n\n";

try {
    $pdo = getDBConnection();
    
    echo "[1/3] Checking existing users...\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $count = (int)$stmt->fetch()['count'];
    
    if ($count > 0) {
        echo "  → $count user(s) already exist. Skipping user creation.\n\n";
    } else {
        echo "  → No users found. Creating initial users...\n\n";
        
        $users = [
            [
                'username' => 'founder',
                'email'    => 'founder@tantragroup.com',
                'password' => 'Tantra@2026#Founder',
                'role'     => 'Founder',
            ],
            [
                'username' => 'chairman',
                'email'    => 'chairman@tantragroup.com',
                'password' => 'Tantra@2026#Chairman',
                'role'     => 'Chairman',
            ],
            [
                'username' => 'ceo',
                'email'    => 'ceo@tantragroup.com',
                'password' => 'Tantra@2026#CEO',
                'role'     => 'CEO',
            ],
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password_hash, role, status)
            VALUES (:username, :email, :password_hash, :role, 'Active')
        ");
        
        foreach ($users as $user) {
            $passwordHash = password_hash($user['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            
            $stmt->execute([
                ':username'      => $user['username'],
                ':email'         => $user['email'],
                ':password_hash' => $passwordHash,
                ':role'          => $user['role'],
            ]);
            
            echo "  ✓ Created user: {$user['username']} ({$user['role']})\n";
            echo "    Email: {$user['email']}\n";
            echo "    Password: {$user['password']}\n\n";
        }
    }
    
    echo "[2/3] Checking existing companies...\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM companies");
    $count = (int)$stmt->fetch()['count'];
    
    if ($count > 0) {
        echo "  → $count company/ies already exist. Skipping company creation.\n\n";
    } else {
        echo "  → No companies found. Creating initial companies...\n\n";
        
        $companies = [
            [
                'name'        => 'ShopTantra',
                'code'        => 'SHOPTANTRA',
                'description' => 'ShopTantra is a multi-category e-commerce platform offering a wide range of products from fashion to electronics, delivering quality and value to customers across India.',
                'website'     => 'https://shoptantra.com',
                'status'      => 'Live',
            ],
            [
                'name'        => 'HireTantra',
                'code'        => 'HIRETANTRA',
                'description' => 'HireTantra is a comprehensive recruitment and staffing solutions provider, connecting talented professionals with leading organizations worldwide.',
                'website'     => 'https://hiretantra.com',
                'status'      => 'Live',
            ],
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO companies (company_name, company_code, company_description, website_url, status)
            VALUES (:name, :code, :description, :website, :status)
        ");
        
        foreach ($companies as $company) {
            $stmt->execute([
                ':name'        => $company['name'],
                ':code'        => $company['code'],
                ':description' => $company['description'],
                ':website'     => $company['website'],
                ':status'      => $company['status'],
            ]);
            
            echo "  ✓ Created company: {$company['name']} ({$company['code']})\n";
            echo "    Status: {$company['status']}\n\n";
        }
    }
    
    echo "[3/3] Verifying database setup...\n";
    $tables = ['users', 'companies', 'audit_logs', 'sessions'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
        $count = (int)$stmt->fetch()['count'];
        echo "  → Table '$table': $count record(s)\n";
    }
    
    echo "\n============================================\n";
    echo "Database seeding completed successfully!\n";
    echo "============================================\n";
    echo "\nCorporate Login Credentials:\n";
    echo "  URL: /corporate/login\n";
    echo "  Username: founder | Password: Tantra@2026#Founder\n";
    echo "  Username: chairman | Password: Tantra@2026#Chairman\n";
    echo "  Username: ceo | Password: Tantra@2026#CEO\n";
    echo "\nIMPORTANT: Change these passwords immediately after first login.\n";
    echo "============================================\n";
    
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}