<?php
// Test Neon PostgreSQL connection
echo "Testing Neon PostgreSQL connection...\n";

// Test database connection
try {
    $host = 'ep-shiny-star-af00su9z-pooler.c-2.us-west-2.aws.neon.tech';
    $port = 5432;
    $dbname = 'neondb';
    $username = 'neondb_owner';
    $password = 'npg_L0WiwaDj7fSl';
    
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require;connect_timeout=30";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Successfully connected to Neon PostgreSQL!\n";
    
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
