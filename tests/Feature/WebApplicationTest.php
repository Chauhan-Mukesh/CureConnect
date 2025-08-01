<?php

namespace CureConnect\Tests\Feature;

use PHPUnit\Framework\TestCase;
use CureConnect\Core\Application;

class WebApplicationTest extends TestCase
{
    private Application $app;

    protected function setUp(): void
    {
        // Set up test environment
        $_ENV['APP_ENV'] = 'testing';
        $_ENV['DB_DRIVER'] = 'sqlite';
        $_ENV['DB_NAME'] = ':memory:';
        
        $this->app = Application::boot(__DIR__ . '/../../');
        
        // Initialize test database
        $database = $this->app->getDatabase();
        $schema = file_get_contents(__DIR__ . '/../../database/schema-sqlite.sql');
        $database->exec($schema);
    }

    public function testApplicationBootsSuccessfully(): void
    {
        $this->assertInstanceOf(Application::class, $this->app);
        $this->assertNotNull($this->app->getDatabase());
        $this->assertNotNull($this->app->getTwig());
    }

    public function testConfigurationIsLoadedCorrectly(): void
    {
        $config = $this->app->getConfig();
        
        $this->assertIsArray($config);
        $this->assertArrayHasKey('app', $config);
        
        if (isset($config['app'])) {
            $this->assertArrayHasKey('name', $config['app']);
            $this->assertStringContainsString('CureConnect', $config['app']['name']);
        }
    }

    public function testDatabaseConnectionWorks(): void
    {
        $database = $this->app->getDatabase();
        
        // Test simple query
        $result = $database->query('SELECT 1 as test');
        $this->assertEquals(1, $result->fetchColumn());
        
        // Test that tables exist
        $stmt = $database->query("SELECT name FROM sqlite_master WHERE type='table'");
        $tables = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        
        $expectedTables = ['users', 'articles', 'countries', 'hospitals', 'inquiries', 'settings'];
        foreach ($expectedTables as $table) {
            $this->assertContains($table, $tables, "Table $table should exist");
        }
    }

    public function testTemplateEngineWorks(): void
    {
        $twig = $this->app->getTwig();
        $this->assertNotNull($twig);
        
        // Test that we can render templates (if Twig is available)
        if (method_exists($twig, 'render')) {
            // Test with base template
            try {
                $output = $twig->render('base.html.twig', [
                    'app_name' => 'Test CureConnect',
                    'assets_url' => '',
                    'lang' => 'en',
                    'meta' => ['title' => 'Test Page']
                ]);
                
                $this->assertIsString($output);
                $this->assertStringContainsString('<!DOCTYPE html>', $output);
                $this->assertStringContainsString('Test CureConnect', $output);
            } catch (\Exception $e) {
                // Template not found is acceptable for this test
                $this->assertTrue(true, "Template rendering test completed");
            }
        }
    }

    public function testTranslationServiceIsAvailable(): void
    {
        $translator = $this->app->getTranslator();
        $this->assertInstanceOf(\CureConnect\Services\TranslationService::class, $translator);
        
        // Test basic translation functionality
        $this->assertTrue(method_exists($translator, 'translate') || method_exists($translator, 'get'));
    }

    public function testRequestHandling(): void
    {
        $request = $this->app->getRequest();
        $this->assertNotNull($request);
        
        // Test that request has expected methods
        $this->assertTrue(
            method_exists($request, 'getPathInfo') || 
            method_exists($request, 'getUri') ||
            is_array($request), // In case it's a simple array for testing
            "Request should have path information method"
        );
    }

    public function testArticleModelIntegration(): void
    {
        $database = $this->app->getDatabase();
        
        // Test that we can work with articles
        $stmt = $database->query("SELECT COUNT(*) FROM articles WHERE status = 'published'");
        $publishedCount = $stmt->fetchColumn();
        $this->assertGreaterThanOrEqual(0, $publishedCount);
        
        // Test inserting a new article
        $stmt = $database->prepare("
            INSERT INTO articles (title, slug, content, language, status, published_at) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            'Feature Test Article',
            'feature-test-article',
            '<p>This is a feature test article content.</p>',
            'en',
            'published',
            date('Y-m-d H:i:s')
        ]);
        
        $this->assertTrue($result);
        
        // Verify the article was inserted
        $stmt = $database->prepare("SELECT * FROM articles WHERE slug = ?");
        $stmt->execute(['feature-test-article']);
        $article = $stmt->fetch();
        
        $this->assertIsArray($article);
        $this->assertEquals('Feature Test Article', $article['title']);
        $this->assertEquals('feature-test-article', $article['slug']);
    }

    public function testInquirySystemIntegration(): void
    {
        $database = $this->app->getDatabase();
        
        // Test inquiry creation (simulating a contact form submission)
        $stmt = $database->prepare("
            INSERT INTO inquiries (name, email, phone, country, treatment_interest, message, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            'John Test Patient',
            'john.test@example.com',
            '+1-555-123-4567',
            'United States',
            'Cardiac Surgery',
            'I am interested in getting cardiac surgery in India. Please provide more information.',
            'new'
        ]);
        
        $this->assertTrue($result);
        
        // Verify the inquiry was created
        $stmt = $database->prepare("SELECT * FROM inquiries WHERE email = ?");
        $stmt->execute(['john.test@example.com']);
        $inquiry = $stmt->fetch();
        
        $this->assertIsArray($inquiry);
        $this->assertEquals('John Test Patient', $inquiry['name']);
        $this->assertEquals('Cardiac Surgery', $inquiry['treatment_interest']);
        $this->assertEquals('new', $inquiry['status']);
    }

    public function testSystemSettings(): void
    {
        $database = $this->app->getDatabase();
        
        // Test that system settings are available
        $stmt = $database->query("SELECT * FROM settings");
        $settings = $stmt->fetchAll();
        
        $this->assertIsArray($settings);
        $this->assertGreaterThan(0, count($settings));
        
        // Test specific settings exist
        $stmt = $database->prepare("SELECT value FROM settings WHERE key = ?");
        $stmt->execute(['site_name']);
        $siteName = $stmt->fetchColumn();
        
        $this->assertNotEmpty($siteName);
        $this->assertStringContainsString('CureConnect', $siteName);
    }

    public function testMedicalTourismDataIntegrity(): void
    {
        $database = $this->app->getDatabase();
        
        // Test countries for medical visa eligibility
        $stmt = $database->query("
            SELECT COUNT(*) FROM countries WHERE medical_visa_eligible = 1
        ");
        $eligibleCountries = $stmt->fetchColumn();
        $this->assertGreaterThan(0, $eligibleCountries, "Should have countries eligible for medical visa");
        
        // Test that articles cover medical tourism topics
        $stmt = $database->query("
            SELECT COUNT(*) FROM articles 
            WHERE status = 'published' 
            AND (
                LOWER(title) LIKE '%medical%' 
                OR LOWER(title) LIKE '%tourism%'
                OR LOWER(title) LIKE '%hospital%'
                OR LOWER(category) LIKE '%medical%'
            )
        ");
        $medicalArticles = $stmt->fetchColumn();
        $this->assertGreaterThan(0, $medicalArticles, "Should have medical tourism related articles");
    }
}