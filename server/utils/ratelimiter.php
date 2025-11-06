<?php
// server/utils/RateLimiter.php
class RateLimiter {
    private $pdo;
    private $limit;
    private $window;
    
    public function __construct($pdo, $limit = 5, $window = 3600) {
        $this->pdo = $pdo;
        $this->limit = $limit;
        $this->window = $window;
    }
    
    public function check($ip, $action) {
        // Clean old records
        $this->cleanup();
        
        // Check current count
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count 
            FROM rate_limits 
            WHERE ip = ? AND action = ? AND timestamp > DATE_SUB(NOW(), INTERVAL ? SECOND)
        ");
        $stmt->execute([$ip, $action, $this->window]);
        $result = $stmt->fetch();
        
        if ($result['count'] >= $this->limit) {
            return false;
        }
        
        // Record this attempt
        $stmt = $this->pdo->prepare("
            INSERT INTO rate_limits (ip, action, timestamp) VALUES (?, ?, NOW())
        ");
        $stmt->execute([$ip, $action]);
        
        return true;
    }
    
    private function cleanup() {
        $this->pdo->exec("
            DELETE FROM rate_limits 
            WHERE timestamp < DATE_SUB(NOW(), INTERVAL " . ($this->window * 2) . " SECOND)
        ");
    }
}
?>