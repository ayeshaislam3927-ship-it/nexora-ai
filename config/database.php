<?php
/**
 * NEXORA - Database Credentials & Connection Manager
 */

// MySQL Database Credentials
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'nexora');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Get PDO Database Connection
 * Supports MySQL as primary and SQLite as automatic local dev fallback.
 */
function getDBConnection(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $dbHost = DB_HOST;
    $dbName = DB_NAME;
    $dbUser = DB_USER;
    $dbPass = DB_PASS;

    // Try MySQL connection first
    try {
        $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Fallback to SQLite for local development when MySQL service is unavailable
        $sqlitePath = STORAGE_PATH . '/nexora.sqlite';
        $needsInit = !file_exists($sqlitePath) || filesize($sqlitePath) === 0;

        $pdo = new PDO("sqlite:" . $sqlitePath, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        if ($needsInit) {
            initSqliteDatabase($pdo);
        }

        return $pdo;
    }
}

/**
 * Initialize SQLite tables if SQLite fallback is triggered
 */
function initSqliteDatabase(PDO $pdo): void {
    $sql = "
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        first_name VARCHAR(100),
        last_name VARCHAR(100),
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255),
        google_id VARCHAR(255) DEFAULT NULL,
        date_of_birth DATE DEFAULT NULL,
        profile_image VARCHAR(255) DEFAULT NULL,
        email_verified TINYINT DEFAULT 0,
        role VARCHAR(20) DEFAULT 'user',
        status VARCHAR(20) DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        last_login DATETIME DEFAULT NULL
    );

    CREATE TABLE IF NOT EXISTS email_otps (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email VARCHAR(255) NOT NULL,
        otp_code VARCHAR(10) NOT NULL,
        purpose VARCHAR(50) DEFAULT 'registration',
        expires_at DATETIME NOT NULL,
        verified TINYINT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS chats (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER DEFAULT NULL,
        title VARCHAR(255) DEFAULT 'New Chat',
        model VARCHAR(100) DEFAULT 'gemini-2.5-flash',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS messages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        chat_id INTEGER NOT NULL,
        role VARCHAR(20) NOT NULL,
        content TEXT NOT NULL,
        model VARCHAR(100) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS user_settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL UNIQUE,
        theme VARCHAR(20) DEFAULT 'dark',
        language VARCHAR(20) DEFAULT 'en',
        selected_model VARCHAR(100) DEFAULT 'gemini-2.5-flash',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS admin_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        admin_id INTEGER NOT NULL,
        action VARCHAR(255) NOT NULL,
        details TEXT DEFAULT NULL,
        ip_address VARCHAR(45) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS system_settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        setting_key VARCHAR(100) NOT NULL UNIQUE,
        setting_value TEXT DEFAULT NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    ";

    $pdo->exec($sql);

    // Seed default admin account (admin@nexora.ai / admin123) if empty
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = 'admin@nexora.ai'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $hash = password_hash('admin123', PASSWORD_BCRYPT);
        $adminStmt = $pdo->prepare("
            INSERT INTO users (first_name, last_name, email, password, email_verified, role, status)
            VALUES ('NEXORA', 'Admin', 'admin@nexora.ai', :hash, 1, 'admin', 'active')
        ");
        $adminStmt->execute([':hash' => $hash]);
    }

    // Seed default system settings
    $settings = [
        'platform_name' => 'NEXORA',
        'default_model' => 'gemini-2.5-flash',
        'registration_enabled' => '1',
        'guest_chat_enabled' => '1',
        'maintenance_mode' => '0',
        'max_upload_size' => '10485760',
    ];

    foreach ($settings as $key => $val) {
        $st = $pdo->prepare("INSERT OR IGNORE INTO system_settings (setting_key, setting_value) VALUES (:k, :v)");
        $st->execute([':k' => $key, ':v' => $val]);
    }
}
