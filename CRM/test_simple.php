<?php
// Simple Supabase connection test without Composer
echo "Testing Supabase connection...\n";

// Test database connection
try {
    $host = 'eclqgtdcgbejfpmoaupw.supabase.co';
    $port = 5432;
    $dbname = 'postgres';
    $username = 'postgres';
    $password = 'SM-crm25';
    
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Successfully connected to Supabase PostgreSQL!\n";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT version()");
    $version = $stmt->fetchColumn();
    echo "PostgreSQL Version: $version\n";
    
    // Test if tables exist
    $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'public'");
    $tableCount = $stmt->fetchColumn();
    echo "Tables in database: $tableCount\n";
    
} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n";
}
?>
