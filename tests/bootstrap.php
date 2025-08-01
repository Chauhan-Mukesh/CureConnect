<?php

/**
 * PHPUnit Bootstrap File
 * Sets up the testing environment
 */

// Define constants for testing
define('RUNNING_TESTS', true);

// Load composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Set error reporting for tests
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('UTC');

// Load environment variables for testing
if (class_exists('\Dotenv\Dotenv') && file_exists(__DIR__ . '/../.env.testing')) {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../', '.env.testing');
    $dotenv->load();
}

// Test helper functions
function createTestDatabase(): PDO 
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON');
    
    // Load test schema
    $schema = file_get_contents(__DIR__ . '/../database/schema-sqlite.sql');
    $pdo->exec($schema);
    
    return $pdo;
}

function createTestApplication(): \CureConnect\Core\Application
{
    // Create test configuration
    $_ENV['APP_ENV'] = 'testing';
    $_ENV['DB_DRIVER'] = 'sqlite';
    $_ENV['DB_NAME'] = ':memory:';
    
    return \CureConnect\Core\Application::boot(__DIR__ . '/../');
}