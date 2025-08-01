<?php

/**
 * Simple Application Test Runner
 * Tests core functionality without external dependencies
 */

// Define constants
define('RUNNING_TESTS', true);

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

class SimpleTestRunner
{
    private int $tests = 0;
    private int $passed = 0;
    private int $failed = 0;
    
    public function run(): void
    {
        echo "=== CureConnect Core Tests ===\n\n";
        
        $this->testDatabaseSupport();
        $this->testSQLiteSchema();
        $this->testApplicationConfiguration();
        $this->testTemplateExistence();
        $this->testAssetExtraction();
        
        echo "\n=== Test Summary ===\n";
        echo "Total tests: {$this->tests}\n";
        echo "Passed: {$this->passed}\n";
        echo "Failed: {$this->failed}\n";
        
        if ($this->failed === 0) {
            echo "All tests passed! ✓\n";
        } else {
            echo "Some tests failed! ✗\n";
        }
    }
    
    private function testDatabaseSupport(): void
    {
        echo "Testing Database Support...\n";
        
        // Test SQLite PDO support
        $this->assert(
            class_exists('PDO') && in_array('sqlite', PDO::getAvailableDrivers()),
            "SQLite PDO driver is available"
        );
        
        // Test in-memory database creation
        try {
            $pdo = new PDO('sqlite::memory:');
            $pdo->exec('PRAGMA foreign_keys = ON');
            $this->assert(true, "In-memory SQLite database can be created");
        } catch (Exception $e) {
            $this->assert(false, "In-memory SQLite database creation failed: " . $e->getMessage());
        }
    }
    
    private function testSQLiteSchema(): void
    {
        echo "\nTesting SQLite Schema...\n";
        
        $schemaFile = __DIR__ . '/database/schema-sqlite.sql';
        $this->assert(file_exists($schemaFile), "SQLite schema file exists");
        
        if (file_exists($schemaFile)) {
            $schema = file_get_contents($schemaFile);
            $this->assert(!empty($schema), "SQLite schema file is not empty");
            
            // Test schema execution
            try {
                $pdo = new PDO('sqlite::memory:');
                $pdo->exec($schema);
                
                // Test that tables were created
                $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                $expectedTables = ['users', 'articles', 'countries', 'hospitals', 'inquiries', 'settings'];
                foreach ($expectedTables as $table) {
                    $this->assert(in_array($table, $tables), "Table '$table' was created");
                }
                
                // Test seed data
                $stmt = $pdo->query("SELECT COUNT(*) FROM users");
                $userCount = $stmt->fetchColumn();
                $this->assert($userCount > 0, "Seed data was inserted (users table has data)");
                
            } catch (Exception $e) {
                $this->assert(false, "Schema execution failed: " . $e->getMessage());
            }
        }
    }
    
    private function testApplicationConfiguration(): void
    {
        echo "\nTesting Application Configuration...\n";
        
        // Test config files exist
        $configFiles = [
            __DIR__ . '/config/app.yaml',
            __DIR__ . '/config/database.yaml',
            __DIR__ . '/config/services.yaml',
            __DIR__ . '/config/database-test.yaml'
        ];
        
        foreach ($configFiles as $file) {
            $this->assert(file_exists($file), "Config file exists: " . basename($file));
        }
        
        // Test composer.json
        $composerFile = __DIR__ . '/composer.json';
        $this->assert(file_exists($composerFile), "composer.json exists");
        
        if (file_exists($composerFile)) {
            $composer = json_decode(file_get_contents($composerFile), true);
            $this->assert(!empty($composer['require']['php']), "PHP version requirement is specified");
            $this->assert(!empty($composer['autoload']['psr-4']), "PSR-4 autoloading is configured");
        }
    }
    
    private function testTemplateExistence(): void
    {
        echo "\nTesting Template Files...\n";
        
        $templates = [
            'base.html.twig',
            'pages/home.html.twig',
            'pages/about.html.twig',
            'pages/contact.html.twig',
            'pages/gallery.html.twig',
            'pages/government-schemes.html.twig',
            'pages/article.html.twig',
            'shared/header.html.twig',
            'shared/footer.html.twig'
        ];
        
        foreach ($templates as $template) {
            $templatePath = __DIR__ . '/templates/' . $template;
            $this->assert(file_exists($templatePath), "Template exists: $template");
            
            if (file_exists($templatePath)) {
                $content = file_get_contents($templatePath);
                $this->assert(!empty($content), "Template is not empty: $template");
            }
        }
    }
    
    private function testAssetExtraction(): void
    {
        echo "\nTesting CSS/JS Extraction...\n";
        
        // Test that CSS file exists and has content
        $cssFile = __DIR__ . '/public/css/blog-theme.css';
        $this->assert(file_exists($cssFile), "blog-theme.css exists");
        
        if (file_exists($cssFile)) {
            $css = file_get_contents($cssFile);
            $this->assert(strpos($css, '.article-hero') !== false, "Article styles were extracted to CSS");
            $this->assert(strpos($css, '.contact-image') !== false, "Contact styles were extracted to CSS");
            $this->assert(strpos($css, '.gallery-grid') !== false, "Gallery styles were extracted to CSS");
        }
        
        // Test that JS file exists and has content
        $jsFile = __DIR__ . '/public/js/app.js';
        $this->assert(file_exists($jsFile), "blog-theme.js exists");
        
        if (file_exists($jsFile)) {
            $js = file_get_contents($jsFile);
            $this->assert(strpos($js, 'initializeArticleFeatures') !== false, "Article JS was extracted");
            $this->assert(strpos($js, 'initializeGalleryFeatures') !== false, "Gallery JS was extracted");
        }
        
        // Test that inline styles were removed from templates
        $templates = [
            'pages/article.html.twig',
            'pages/contact.html.twig',
            'pages/gallery.html.twig'
        ];
        
        foreach ($templates as $template) {
            $templatePath = __DIR__ . '/templates/' . $template;
            if (file_exists($templatePath)) {
                $content = file_get_contents($templatePath);
                $this->assert(
                    strpos($content, '<style>') === false,
                    "Inline styles removed from $template"
                );
            }
        }
    }
    
    private function assert(bool $condition, string $message): void
    {
        $this->tests++;
        
        if ($condition) {
            $this->passed++;
            echo "  ✓ $message\n";
        } else {
            $this->failed++;
            echo "  ✗ $message\n";
        }
    }
}

// Run tests
$runner = new SimpleTestRunner();
$runner->run();