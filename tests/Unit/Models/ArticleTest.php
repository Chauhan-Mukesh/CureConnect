<?php

namespace CureConnect\Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use CureConnect\Models\Article;

class ArticleTest extends TestCase
{
    private \PDO $database;

    protected function setUp(): void
    {
        $this->database = createTestDatabase();
    }

    public function testArticleCanBeCreated(): void
    {
        $article = new Article($this->database);
        $this->assertInstanceOf(Article::class, $article);
    }

    public function testArticleCanBeSaved(): void
    {
        $article = new Article($this->database);
        
        $data = [
            'title' => 'Test Article',
            'slug' => 'test-article',
            'content' => '<p>This is test content</p>',
            'excerpt' => 'Test excerpt',
            'language' => 'en',
            'category' => 'Medical Tourism',
            'author_name' => 'Test Author',
            'status' => 'published',
            'published_at' => date('Y-m-d H:i:s')
        ];

        $id = $article->create($data);
        
        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);
    }

    public function testArticleCanBeRetrieved(): void
    {
        $article = new Article($this->database);
        
        // Create test article
        $data = [
            'title' => 'Test Article for Retrieval',
            'slug' => 'test-article-retrieval',
            'content' => '<p>Content for retrieval test</p>',
            'language' => 'en',
            'status' => 'published'
        ];
        
        $id = $article->create($data);
        
        // Retrieve by ID
        $retrieved = $article->findById($id);
        
        $this->assertIsArray($retrieved);
        $this->assertEquals('Test Article for Retrieval', $retrieved['title']);
        $this->assertEquals('test-article-retrieval', $retrieved['slug']);
    }

    public function testArticleCanBeRetrievedBySlug(): void
    {
        $article = new Article($this->database);
        
        $data = [
            'title' => 'Test Article by Slug',
            'slug' => 'test-article-by-slug',
            'content' => '<p>Content</p>',
            'language' => 'en',
            'status' => 'published'
        ];
        
        $article->create($data);
        
        // Retrieve by slug
        $retrieved = $article->findBySlug('test-article-by-slug');
        
        $this->assertIsArray($retrieved);
        $this->assertEquals('Test Article by Slug', $retrieved['title']);
    }

    public function testArticleListCanBeRetrieved(): void
    {
        $article = new Article($this->database);
        
        // Create multiple test articles
        for ($i = 1; $i <= 3; $i++) {
            $data = [
                'title' => "Test Article $i",
                'slug' => "test-article-$i",
                'content' => "<p>Content $i</p>",
                'language' => 'en',
                'status' => 'published'
            ];
            $article->create($data);
        }
        
        $articles = $article->getPublished();
        
        $this->assertIsArray($articles);
        $this->assertCount(5, $articles); // 3 created + 2 from seed data
    }

    public function testArticleCanBeUpdated(): void
    {
        $article = new Article($this->database);
        
        $data = [
            'title' => 'Original Title',
            'slug' => 'original-title',
            'content' => '<p>Original content</p>',
            'language' => 'en',
            'status' => 'draft'
        ];
        
        $id = $article->create($data);
        
        // Update the article
        $updateData = [
            'title' => 'Updated Title',
            'status' => 'published'
        ];
        
        $result = $article->update($id, $updateData);
        $this->assertTrue($result);
        
        // Verify update
        $updated = $article->findById($id);
        $this->assertEquals('Updated Title', $updated['title']);
        $this->assertEquals('published', $updated['status']);
    }

    public function testArticleCanBeDeleted(): void
    {
        $article = new Article($this->database);
        
        $data = [
            'title' => 'Article to Delete',
            'slug' => 'article-to-delete',
            'content' => '<p>Content</p>',
            'language' => 'en'
        ];
        
        $id = $article->create($data);
        
        // Delete the article
        $result = $article->delete($id);
        $this->assertTrue($result);
        
        // Verify deletion
        $deleted = $article->findById($id);
        $this->assertNull($deleted);
    }

    public function testArticleValidation(): void
    {
        $article = new Article($this->database);
        
        // Test with missing required fields
        $this->expectException(\InvalidArgumentException::class);
        
        $data = [
            'content' => '<p>Content without title</p>'
        ];
        
        $article->create($data);
    }
}