<?php

declare(strict_types=1);

/**
 * Core Functions for CureConnect Medical Tourism Portal
 *
 * @package CureConnect
 * @author  CureConnect Team
 * @since   1.0.0
 */

use CureConnect\Core\Security;
use CureConnect\Services\TranslationService;

/**
 * Generate CSRF token wrapper function
 *
 * @return string CSRF token
 */
function generate_csrf_token(): string
{
    return Security::generateCsrfToken();
}

/**
 * Verify CSRF token wrapper function
 *
 * @param string $token Token to verify
 * @return bool True if valid, false otherwise
 */
function verify_csrf_token(string $token): bool
{
    return Security::verifyCsrfToken($token);
}

/**
 * Sanitize input wrapper function
 *
 * @param string $data Input data to sanitize
 * @return string Sanitized data
 */
function sanitize_input(string $data): string
{
    return Security::sanitizeInput($data);
}

/**
 * Redirect to URL
 *
 * @param string $url URL to redirect to
 * @param bool $permanent Whether to use 301 redirect
 * @return void
 */
function redirect(string $url, bool $permanent = false): void
{
    $statusCode = $permanent ? 301 : 302;
    header('Location: ' . $url, true, $statusCode);
    exit();
}

/**
 * Get current URL
 *
 * @return string Current URL
 */
function current_url(): string
{
    $protocol = Security::isHttps() ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $uri = $_SERVER['REQUEST_URI'] ?? '';

    return $protocol . '://' . $host . $uri;
}

/**
 * Generate meta tags for SEO
 *
 * @param string $title Page title
 * @param string $description Meta description
 * @param string $keywords Meta keywords
 * @param string $image OG image URL
 * @return string Generated meta tags HTML
 */
function generate_meta_tags(string $title, string $description, string $keywords = '', string $image = ''): string
{
    $currentUrl = current_url();

    $meta = '<title>' . Security::escapeHtml($title) . '</title>' . "\n";
    $meta .= '<meta name="description" content="' . Security::escapeHtml($description) . '">' . "\n";

    if (!empty($keywords)) {
        $meta .= '<meta name="keywords" content="' . Security::escapeHtml($keywords) . '">' . "\n";
    }

    // Open Graph tags
    $meta .= '<meta property="og:title" content="' . Security::escapeHtml($title) . '">' . "\n";
    $meta .= '<meta property="og:description" content="' . Security::escapeHtml($description) . '">' . "\n";
    $meta .= '<meta property="og:url" content="' . Security::escapeHtml($currentUrl) . '">' . "\n";
    $meta .= '<meta property="og:type" content="website">' . "\n";

    if (!empty($image)) {
        $meta .= '<meta property="og:image" content="' . Security::escapeHtml($image) . '">' . "\n";
    }

    // Twitter Card tags
    $meta .= '<meta name="twitter:card" content="summary_large_image">' . "\n";
    $meta .= '<meta name="twitter:title" content="' . Security::escapeHtml($title) . '">' . "\n";
    $meta .= '<meta name="twitter:description" content="' . Security::escapeHtml($description) . '">' . "\n";

    return $meta;
}

/**
 * Translation function wrapper
 *
 * @param string $key Translation key
 * @param string $language Language code
 * @return string Translated string
 */
function __(string $key, string $language = 'en'): string
{
    return TranslationService::translate($key, $language);
}

/**
 * Format currency for display
 *
 * @param float $amount Amount to format
 * @param string $currency Currency code
 * @return string Formatted currency
 */
function format_currency(float $amount, string $currency = 'INR'): string
{
    return TranslationService::formatCurrency($amount, $currency);
}

/**
 * Log activity to file
 *
 * @param string $message Log message
 * @param string $level Log level
 * @return void
 */
function log_activity(string $message, string $level = 'info'): void
{
    $logFile = ROOT_PATH . '/logs/app.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = Security::getClientIp();
    $logEntry = "[{$timestamp}] {$level}: {$message} - IP: {$ip}" . PHP_EOL;

    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

/**
 * Check if request is AJAX
 *
 * @return bool True if AJAX request
 */
function is_ajax_request(): bool
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Send JSON response
 *
 * @param mixed $data Response data
 * @param int $statusCode HTTP status code
 * @return void
 */
function json_response($data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_THROW_ON_ERROR);
    exit();
}

/**
 * Get database connection
 *
 * @return PDO Database connection
 * @throws PDOException If connection fails
 */
function get_db(): PDO
{
    static $connection = null;

    if ($connection === null) {
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                DB_HOST,
                DB_PORT,
                DB_NAME
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];

            $connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            log_activity("Database connection failed: " . $e->getMessage(), 'error');
            throw $e;
        }
    }

    return $connection;
}
