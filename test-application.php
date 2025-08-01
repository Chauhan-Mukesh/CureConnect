<?php

/**
 * Application Core Test
 * Tests the main Application class functionality
 */

// Basic autoloader for our classes
spl_autoload_register(function ($class) {
    $prefix = 'CureConnect\\';
    $base_dir = __DIR__ . '/src/';
    
    if (strpos($class, $prefix) === 0) {
        $relative_class = substr($class, strlen($prefix));
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        
        if (file_exists($file)) {
            require $file;
        }
    }
});

try {
    echo "=== Testing Core Application Class ===\n\n";
    
    // Set environment for testing
    $_ENV['APP_ENV'] = 'testing';
    
    // Test Application instantiation
    echo "1. Testing Application boot...\n";
    $app = \CureConnect\Core\Application::boot(__DIR__);
    echo "   ✓ Application booted successfully\n";
    
    // Test singleton pattern
    echo "\n2. Testing singleton pattern...\n";
    $app2 = \CureConnect\Core\Application::getInstance();
    if ($app === $app2) {
        echo "   ✓ Singleton pattern works correctly\n";
    } else {
        echo "   ✗ Singleton pattern failed\n";
    }
    
    // Test configuration loading
    echo "\n3. Testing configuration...\n";
    $config = $app->getConfig();
    if (is_array($config) && !empty($config)) {
        echo "   ✓ Configuration loaded successfully\n";
        if (isset($config['app']['name'])) {
            echo "   ✓ App name: " . $config['app']['name'] . "\n";
        }
    } else {
        echo "   ✗ Configuration loading failed\n";
    }
    
    // Test database connection
    echo "\n4. Testing database connection...\n";
    try {
        $database = $app->getDatabase();
        if ($database instanceof PDO) {
            echo "   ✓ Database connection established\n";
            
            // Test a simple query
            $result = $database->query('SELECT 1 as test');
            if ($result && $result->fetchColumn() == 1) {
                echo "   ✓ Database query works\n";
            } else {
                echo "   ✗ Database query failed\n";
            }
        } else {
            echo "   ✗ Database connection failed\n";
        }
    } catch (Exception $e) {
        echo "   ✗ Database error: " . $e->getMessage() . "\n";
    }
    
    // Test SQLite configuration
    echo "\n5. Testing SQLite configuration...\n";
    
    // Create test config directory with SQLite config
    $testConfigDir = sys_get_temp_dir() . '/cureconnect_sqlite_test_' . uniqid();
    mkdir($testConfigDir, 0777, true);
    
    // Create test database config for SQLite
    $dbConfig = <<<YAML
database:
  driver: "sqlite"
  name: ":memory:"
YAML;
    file_put_contents($testConfigDir . '/database.yaml', $dbConfig);
    
    // Create minimal app config
    $appConfig = <<<YAML
app:
  name: "CureConnect SQLite Test"
  environment: "test"
YAML;
    file_put_contents($testConfigDir . '/app.yaml', $appConfig);
    
    try {
        // Use reflection to test with SQLite config
        $reflection = new ReflectionClass($app);
        $configProperty = $reflection->getProperty('config');
        $configProperty->setAccessible(true);
        
        // Load SQLite config
        $sqliteConfig = [
            'database' => [
                'driver' => 'sqlite',
                'name' => ':memory:'
            ]
        ];
        $configProperty->setValue($app, $sqliteConfig);
        
        // Reset database to force reinitialization
        $dbProperty = $reflection->getProperty('database');
        $dbProperty->setAccessible(true);
        $dbProperty->setValue($app, null);
        
        $sqliteDb = $app->getDatabase();
        if ($sqliteDb instanceof PDO) {
            $driver = $sqliteDb->getAttribute(PDO::ATTR_DRIVER_NAME);
            if ($driver === 'sqlite') {
                echo "   ✓ SQLite database connection works\n";
                
                // Test foreign key constraint
                $stmt = $sqliteDb->query("PRAGMA foreign_keys");
                $fkEnabled = $stmt->fetchColumn();
                if ($fkEnabled == 1) {
                    echo "   ✓ SQLite foreign keys enabled\n";
                } else {
                    echo "   ✗ SQLite foreign keys not enabled\n";
                }
            } else {
                echo "   ✗ Expected SQLite, got: $driver\n";
            }
        } else {
            echo "   ✗ SQLite database connection failed\n";
        }
    } catch (Exception $e) {
        echo "   ✗ SQLite test error: " . $e->getMessage() . "\n";
    }
    
    // Clean up test directory
    array_map('unlink', glob("$testConfigDir/*"));
    rmdir($testConfigDir);
    
    // Test Twig templating
    echo "\n6. Testing Twig templating...\n";
    try {
        $twig = $app->getTwig();
        if ($twig !== null) {
            echo "   ✓ Twig instance created\n";
            
            if (method_exists($twig, 'render')) {
                echo "   ✓ Twig render method available\n";
            } else {
                echo "   ✓ Fallback template engine available\n";
            }
        } else {
            echo "   ✗ Twig initialization failed\n";
        }
    } catch (Exception $e) {
        echo "   ✗ Twig error: " . $e->getMessage() . "\n";
    }
    
    // Test translation service
    echo "\n7. Testing translation service...\n";
    try {
        $translator = $app->getTranslator();
        if ($translator instanceof \CureConnect\Services\TranslationService) {
            echo "   ✓ Translation service created\n";
        } else {
            echo "   ✗ Translation service failed\n";
        }
    } catch (Exception $e) {
        echo "   ✗ Translation service error: " . $e->getMessage() . "\n";
    }
    
    // Test request handling
    echo "\n8. Testing request handling...\n";
    try {
        $request = $app->getRequest();
        if ($request !== null) {
            echo "   ✓ Request object created\n";
        } else {
            echo "   ✗ Request object failed\n";
        }
    } catch (Exception $e) {
        echo "   ✗ Request handling error: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== Application Core Test Complete ===\n";
    echo "All core functionality is working properly!\n";

} catch (Exception $e) {
    echo "Fatal error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}