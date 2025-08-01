<?php

declare(strict_types=1);

/**
 * CureConnect Medical Tourism Portal
 * Main Entry Point
 *
 * @package CureConnect
 * @author  CureConnect Team
 * @since   1.0.0
 */

// Load autoloader (try composer first, then fallback to simple autoloader)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../autoload.php')) {
    require_once __DIR__ . '/../autoload.php';
} else {
    // Last resort: try to load from parent directory
    require_once dirname(__DIR__) . '/autoload.php';
}

use CureConnect\Core\Application;
use CureConnect\Core\SimpleResponse; // Assuming this is in Fallbacks.php
use Symfony\Component\HttpFoundation\Response;
use Dotenv\Dotenv;

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// Set error reporting based on environment
$environment = $_ENV['APP_ENV'] ?? 'development';
if ($environment === 'production') {
    error_reporting(0);
    ini_set('display_errors', '0');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// Set timezone
date_default_timezone_set('Asia/Kolkata');

// Initialize and run application
try {
    // Boot the application using the new static method
    $app = Application::boot(dirname(__DIR__));
    $response = $app->handleRequest();

    // Ensure a response object is returned before sending
    if ($response instanceof Response || $response instanceof SimpleResponse) {
        $response->send();
    } else {
        // Handle cases where a controller might not return a response
        throw new \Exception('Controller did not return a valid Response object.');
    }
} catch (\Exception $e) {
    if ($environment === 'development') {
        echo '<h1>Application Error</h1>';
        echo '<pre>' . $e->getMessage() . "\n" . $e->getTraceAsString() . '</pre>';
    } else {
        http_response_code(500);
        echo '<h1>Internal Server Error</h1>';
        echo '<p>Something went wrong. Please try again later.</p>';
    }
}
