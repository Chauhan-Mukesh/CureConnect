<?php

declare(strict_types=1);

/**
 * Security Core Class
 *
 * Handles security operations including input sanitization,
 * CSRF protection, and output escaping.
 *
 * @package CureConnect\Core
 * @author  CureConnect Team
 * @since   1.0.0
 */

namespace CureConnect\Core;

/**
 * Security utility class for handling common security operations
 */
class Security
{
    /**
     * Escape HTML output to prevent XSS attacks
     *
     * @param string $string The string to escape
     * @param int $flags Optional flags for htmlspecialchars
     * @param string $encoding Character encoding
     * @return string Escaped string
     */
    public static function escapeHtml(string $string, int $flags = ENT_QUOTES | ENT_HTML5, string $encoding = 'UTF-8'): string
    {
        return htmlspecialchars($string, $flags, $encoding);
    }

    /**
     * Sanitize input data
     *
     * @param string $input Raw input data
     * @return string Sanitized input
     */
    public static function sanitizeInput(string $input): string
    {
        $input = trim($input);
        $input = stripslashes($input);
        return self::escapeHtml($input);
    }

    /**
     * Generate CSRF token for forms
     *
     * @return string CSRF token
     */
    public static function generateCsrfToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF token
     *
     * @param string $token Token to verify
     * @return bool True if valid, false otherwise
     */
    public static function verifyCsrfToken(string $token): bool
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Validate email address
     *
     * @param string $email Email to validate
     * @return bool True if valid, false otherwise
     */
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate phone number (basic validation)
     *
     * @param string $phone Phone number to validate
     * @return bool True if valid, false otherwise
     */
    public static function validatePhone(string $phone): bool
    {
        $phone = preg_replace('/[^0-9+\-\s\(\)]/', '', $phone);
        return preg_match('/^[\+]?[0-9\-\s\(\)]{7,20}$/', $phone);
    }

    /**
     * Generate secure random password
     *
     * @param int $length Password length
     * @return string Generated password
     */
    public static function generateSecurePassword(int $length = 12): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*';
        $password = '';
        $charactersLength = strlen($characters);

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $password;
    }

    /**
     * Hash password securely
     *
     * @param string $password Plain text password
     * @return string Hashed password
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID);
    }

    /**
     * Verify password against hash
     *
     * @param string $password Plain text password
     * @param string $hash Stored password hash
     * @return bool True if password matches, false otherwise
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Generate secure slug from string
     *
     * @param string $string Input string
     * @return string URL-safe slug
     */
    public static function generateSlug(string $string): string
    {
        $string = strtolower($string);
        $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
        $string = preg_replace('/[\s-]+/', '-', $string);
        return trim($string, '-');
    }

    /**
     * Check if request is made via HTTPS
     *
     * @return bool True if HTTPS, false otherwise
     */
    public static function isHttps(): bool
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    }

    /**
     * Get client IP address
     *
     * @return string Client IP address
     */
    public static function getClientIp(): string
    {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];

        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) && !empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Rate limiting check
     *
     * @param string $key Unique identifier for rate limiting
     * @param int $maxAttempts Maximum attempts allowed
     * @param int $timeWindow Time window in seconds
     * @return bool True if within limits, false otherwise
     */
    public static function checkRateLimit(string $key, int $maxAttempts = 5, int $timeWindow = 300): bool
    {
        $sessionKey = "rate_limit_{$key}";
        $now = time();

        if (!isset($_SESSION[$sessionKey])) {
            $_SESSION[$sessionKey] = ['count' => 1, 'start_time' => $now];
            return true;
        }

        $data = $_SESSION[$sessionKey];

        // Reset if time window has passed
        if (($now - $data['start_time']) > $timeWindow) {
            $_SESSION[$sessionKey] = ['count' => 1, 'start_time' => $now];
            return true;
        }

        // Check if within limits
        if ($data['count'] >= $maxAttempts) {
            return false;
        }

        // Increment counter
        $_SESSION[$sessionKey]['count']++;
        return true;
    }
}
