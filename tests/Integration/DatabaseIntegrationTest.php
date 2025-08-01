<?php

namespace CureConnect\Tests\Integration;

use PHPUnit\Framework\TestCase;
use CureConnect\Core\Application;

class DatabaseIntegrationTest extends TestCase
{
    private Application $app;
    private \PDO $database;

    protected function setUp(): void
    {
        // Set environment for testing
        $_ENV['APP_ENV'] = 'testing';
        $_ENV['DB_DRIVER'] = 'sqlite';
        $_ENV['DB_NAME'] = ':memory:';
        
        $this->app = Application::boot(__DIR__ . '/../../');
        $this->database = $this->app->getDatabase();
        
        // Load test schema
        $schema = file_get_contents(__DIR__ . '/../../database/schema-sqlite.sql');
        $this->database->exec($schema);
    }

    public function testDatabaseConnection(): void
    {
        $this->assertInstanceOf(\PDO::class, $this->database);
    }

    public function testDatabaseTablesExist(): void
    {
        $tables = [
            'users',
            'articles', 
            'countries',
            'hospitals',
            'inquiries',
            'settings'
        ];

        foreach ($tables as $table) {
            $stmt = $this->database->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name=?");
            $stmt->execute([$table]);
            $result = $stmt->fetchColumn();
            
            $this->assertEquals($table, $result, "Table $table should exist");
        }
    }

    public function testDatabaseSeededData(): void
    {
        // Test users
        $stmt = $this->database->query("SELECT COUNT(*) FROM users");
        $userCount = $stmt->fetchColumn();
        $this->assertGreaterThan(0, $userCount, "Users table should have seed data");

        // Test articles  
        $stmt = $this->database->query("SELECT COUNT(*) FROM articles WHERE status = 'published'");
        $articleCount = $stmt->fetchColumn();
        $this->assertGreaterThan(0, $articleCount, "Articles table should have published articles");

        // Test countries
        $stmt = $this->database->query("SELECT COUNT(*) FROM countries");
        $countryCount = $stmt->fetchColumn();
        $this->assertGreaterThan(0, $countryCount, "Countries table should have seed data");

        // Test settings
        $stmt = $this->database->query("SELECT COUNT(*) FROM settings");
        $settingsCount = $stmt->fetchColumn();
        $this->assertGreaterThan(0, $settingsCount, "Settings table should have seed data");
    }

    public function testForeignKeyConstraints(): void
    {
        // Test that foreign key constraints are enabled
        $stmt = $this->database->query("PRAGMA foreign_keys");
        $fkEnabled = $stmt->fetchColumn();
        $this->assertEquals(1, $fkEnabled, "Foreign keys should be enabled");
    }

    public function testCRUDOperations(): void
    {
        // Test INSERT
        $stmt = $this->database->prepare("
            INSERT INTO articles (title, slug, content, language, status) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $result = $stmt->execute([
            'Integration Test Article',
            'integration-test-article', 
            '<p>Test content</p>',
            'en',
            'published'
        ]);
        $this->assertTrue($result);
        $articleId = $this->database->lastInsertId();

        // Test SELECT
        $stmt = $this->database->prepare("SELECT * FROM articles WHERE id = ?");
        $stmt->execute([$articleId]);
        $article = $stmt->fetch();
        $this->assertIsArray($article);
        $this->assertEquals('Integration Test Article', $article['title']);

        // Test UPDATE
        $stmt = $this->database->prepare("UPDATE articles SET title = ? WHERE id = ?");
        $result = $stmt->execute(['Updated Integration Test Article', $articleId]);
        $this->assertTrue($result);
        
        // Verify update
        $stmt = $this->database->prepare("SELECT title FROM articles WHERE id = ?");
        $stmt->execute([$articleId]);
        $title = $stmt->fetchColumn();
        $this->assertEquals('Updated Integration Test Article', $title);

        // Test DELETE
        $stmt = $this->database->prepare("DELETE FROM articles WHERE id = ?");
        $result = $stmt->execute([$articleId]);
        $this->assertTrue($result);
        
        // Verify deletion
        $stmt = $this->database->prepare("SELECT COUNT(*) FROM articles WHERE id = ?");
        $stmt->execute([$articleId]);
        $count = $stmt->fetchColumn();
        $this->assertEquals(0, $count);
    }

    public function testTransactionSupport(): void
    {
        $this->database->beginTransaction();
        
        // Insert test data
        $stmt = $this->database->prepare("
            INSERT INTO articles (title, slug, content, language, status) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            'Transaction Test Article',
            'transaction-test-article',
            '<p>Transaction test</p>',
            'en',
            'draft'
        ]);
        
        // Rollback transaction
        $this->database->rollback();
        
        // Verify data was not saved
        $stmt = $this->database->prepare("SELECT COUNT(*) FROM articles WHERE slug = ?");
        $stmt->execute(['transaction-test-article']);
        $count = $stmt->fetchColumn();
        $this->assertEquals(0, $count, "Transaction rollback should prevent data from being saved");
    }

    public function testJoinOperations(): void
    {
        // Test a join query (using existing seed data)
        $sql = "
            SELECT a.title, a.author_name, COUNT(*) as article_count
            FROM articles a 
            WHERE a.status = 'published'
            GROUP BY a.author_name
            ORDER BY article_count DESC
        ";
        
        $stmt = $this->database->query($sql);
        $results = $stmt->fetchAll();
        
        $this->assertIsArray($results);
        $this->assertGreaterThan(0, count($results));
        
        foreach ($results as $result) {
            $this->assertArrayHasKey('title', $result);
            $this->assertArrayHasKey('author_name', $result);
            $this->assertArrayHasKey('article_count', $result);
        }
    }
}