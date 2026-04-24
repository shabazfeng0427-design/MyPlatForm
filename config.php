<?php
// ============================================================
//  config.php — Database & Site Configuration
//  Edit the DB credentials below before running.
// ============================================================

// ─── Database ────────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_NAME', 'myplatform');
define('DB_USER', 'root');          // ← change to your DB username
define('DB_PASS', '');              // ← change to your DB password
define('DB_CHARSET', 'utf8mb4');

// ─── Site ────────────────────────────────────────────────────
define('SITE_URL',  'http://localhost');   // no trailing slash
define('SITE_ENV',  'development');        // 'development' or 'production'

// ─── Email (contact form) ────────────────────────────────────
define('MAIL_TO',      'alex@alexchen.dev');
define('MAIL_FROM',    'noreply@alexchen.dev');
define('MAIL_SUBJECT', 'New Portfolio Contact Message');

// ─── Admin ───────────────────────────────────────────────────
define('ADMIN_SESSION_NAME', 'pf_admin');
define('ADMIN_SESSION_LIFE', 3600 * 2);   // 2 hours

// ─── Rate Limiting ───────────────────────────────────────────
define('RATE_LIMIT_MAX',    5);    // max form submissions
define('RATE_LIMIT_WINDOW', 3600); // per hour (seconds)

// ─── Error display ───────────────────────────────────────────
if (SITE_ENV === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// ─── Timezone ────────────────────────────────────────────────
date_default_timezone_set('America/Los_Angeles');

// ============================================================
//  Database class — PDO singleton
// ============================================================
class DB {
    private static ?PDO $instance = null;

    public static function get(): PDO {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                DB_HOST, DB_NAME, DB_CHARSET
            );
            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                if (SITE_ENV === 'development') {
                    die('<b>Database Error:</b> ' . htmlspecialchars($e->getMessage()));
                }
                die('A database error occurred. Please try again later.');
            }
        }
        return self::$instance;
    }

    /** Run a query, return all rows */
    public static function query(string $sql, array $params = []): array {
        $stmt = self::get()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Run a query, return single row */
    public static function row(string $sql, array $params = []): ?array {
        $stmt = self::get()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Run INSERT/UPDATE/DELETE, return affected rows */
    public static function exec(string $sql, array $params = []): int {
        $stmt = self::get()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /** Run INSERT, return last insert ID */
    public static function insert(string $sql, array $params = []): int {
        $stmt = self::get()->prepare($sql);
        $stmt->execute($params);
        return (int) self::get()->lastInsertId();
    }

    /** Get a setting value from the settings table */
    public static function setting(string $key, string $default = ''): string {
        $row = self::row('SELECT value FROM settings WHERE `key` = ?', [$key]);
        return $row ? (string)$row['value'] : $default;
    }
}

// ============================================================
//  Helper functions
// ============================================================

/** Sanitize output */
function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Get client IP */
function getClientIP(): string {
    foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) {
            return trim(explode(',', $_SERVER[$key])[0]);
        }
    }
    return '0.0.0.0';
}

/** Check rate limit. Returns true if allowed, false if blocked */
function checkRateLimit(string $action): bool {
    $ip  = getClientIP();
    $now = time();
    $db  = DB::get();

    // Clean old windows
    $db->prepare('DELETE FROM rate_limits WHERE UNIX_TIMESTAMP(window_start) < ?')
       ->execute([$now - RATE_LIMIT_WINDOW]);

    $row = DB::row(
        'SELECT attempts FROM rate_limits WHERE ip_address = ? AND action = ?',
        [$ip, $action]
    );

    if (!$row) {
        DB::exec(
            'INSERT INTO rate_limits (ip_address, action, attempts) VALUES (?,?,1)',
            [$ip, $action]
        );
        return true;
    }

    if ((int)$row['attempts'] >= RATE_LIMIT_MAX) {
        return false;
    }

    DB::exec(
        'UPDATE rate_limits SET attempts = attempts + 1 WHERE ip_address = ? AND action = ?',
        [$ip, $action]
    );
    return true;
}

/** Validate email */
function isValidEmail(string $email): bool {
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

/** CSRF token */
function csrfToken(): string {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(string $token): bool {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/** JSON response helper */
function jsonResponse(bool $success, string $message, array $data = []): void {
    header('Content-Type: application/json');
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

/** Log a page view */
function logPageView(string $page = '/'): void {
    try {
        DB::exec(
            'INSERT INTO page_views (page, ip_address, user_agent, referer) VALUES (?,?,?,?)',
            [
                $page,
                getClientIP(),
                substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
                substr($_SERVER['HTTP_REFERER'] ?? '', 0, 1000),
            ]
        );
    } catch (Exception $e) {
        // Non-critical — fail silently
    }
}
