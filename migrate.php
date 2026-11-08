<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'db-config.php';

try {
    $db = getDB();
    echo "<h3>Starting Migration...</h3>";
    
    // Add approval_status
    try {
        $db->exec("ALTER TABLE repair_parts ADD COLUMN approval_status VARCHAR(20) DEFAULT 'APPROVED'");
        echo "<p style='color:green;'>[SUCCESS] Added approval_status column.</p>";
    } catch (Exception $e) {
        echo "<p style='color:orange;'>[INFO] approval_status might already exist: " . $e->getMessage() . "</p>";
    }
    
    // Add approval_token
    try {
        $db->exec("ALTER TABLE repair_parts ADD COLUMN approval_token VARCHAR(64) NULL");
        echo "<p style='color:green;'>[SUCCESS] Added approval_token column.</p>";
    } catch (Exception $e) {
        echo "<p style='color:orange;'>[INFO] approval_token might already exist: " . $e->getMessage() . "</p>";
    }
    
    // Add customer_signature
    try {
        $db->exec("ALTER TABLE repair_parts ADD COLUMN customer_signature LONGTEXT NULL");
        echo "<p style='color:green;'>[SUCCESS] Added customer_signature column.</p>";
    } catch (Exception $e) {
        echo "<p style='color:orange;'>[INFO] customer_signature might already exist: " . $e->getMessage() . "</p>";
    }
    
    echo "<h3>Migration Complete! Pwede mo na i-test ulit yung feature.</h3>";
} catch (Exception $e) {
    echo "<p style='color:red;'>[ERROR] General error: " . $e->getMessage() . "</p>";
}
?>
