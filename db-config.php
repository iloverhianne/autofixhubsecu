<?php
// AutoFix Hub - Database Configuration
// Replace these with your actual InfinityFree MySQL details

define('DB_HOST', 'sql201.infinityfree.com');
define('DB_USER', 'if0_40809635');
define('DB_PASS', 'KALISTO05');
define('DB_NAME', 'if0_40809635_2ndterm');

function getDB() {
    try {
        $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $conn;
    } catch (PDOException $e) {
        throw new Exception("Database Connection failed: " . $e->getMessage());
    }
}
