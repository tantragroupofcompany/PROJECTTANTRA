<?php
// ============================================================
// TANTRA GROUP OF INDUSTRIES
// Audit Logger Middleware
// Immutable audit trail for all corporate actions
// ============================================================

require_once __DIR__ . '/../database/config.php';

class AuditLogger {
    private static ?AuditLogger $instance = null;
    
    private function __construct() {}
    
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Log an action to the audit trail
     */
    public function log(int $userId, string $action, string $module, string $description = ''): void {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("
                INSERT INTO audit_logs (user_id, action, module, description, ip_address, user_agent, timestamp)
                VALUES (:user_id, :action, :module, :description, :ip_address, :user_agent, NOW())
            ");
            $stmt->execute([
                ':user_id'     => $userId,
                ':action'      => $action,
                ':module'      => $module,
                ':description' => $description,
                ':ip_address'  => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                ':user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            ]);
        } catch (PDOException $e) {
            error_log('AuditLogger failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Get audit logs with pagination
     */
    public function getLogs(int $page = 1, int $perPage = 50, ?string $module = null, ?string $action = null): array {
        try {
            $pdo = getDBConnection();
            $where = [];
            $params = [];
            
            if ($module) {
                $where[] = 'al.module = :module';
                $params[':module'] = $module;
            }
            
            if ($action) {
                $where[] = 'al.action = :action';
                $params[':action'] = $action;
            }
            
            $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            
            // Get total count
            $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM audit_logs al $whereClause");
            $countStmt->execute($params);
            $total = (int)$countStmt->fetch()['total'];
            
            // Get paginated results
            $offset = ($page - 1) * $perPage;
            $stmt = $pdo->prepare("
                SELECT al.*, u.username, u.role
                FROM audit_logs al
                LEFT JOIN users u ON al.user_id = u.id
                $whereClause
                ORDER BY al.timestamp DESC
                LIMIT :limit OFFSET :offset
            ");
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'logs'       => $stmt->fetchAll(),
                'total'      => $total,
                'page'       => $page,
                'perPage'    => $perPage,
                'totalPages' => ceil($total / $perPage),
            ];
        } catch (PDOException $e) {
            error_log('AuditLogger fetch failed: ' . $e->getMessage());
            return ['logs' => [], 'total' => 0, 'page' => 1, 'perPage' => $perPage, 'totalPages' => 0];
        }
    }
    
    /**
     * Get recent audit logs for dashboard
     */
    public function getRecentLogs(int $limit = 10): array {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("
                SELECT al.*, u.username, u.role
                FROM audit_logs al
                LEFT JOIN users u ON al.user_id = u.id
                ORDER BY al.timestamp DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('AuditLogger recent fetch failed: ' . $e->getMessage());
            return [];
        }
    }
}