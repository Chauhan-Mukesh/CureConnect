<?php

declare(strict_types=1);

/**
 * Article Model
 *
 * Handles database operations for articles including CRUD operations,
 * search, filtering, and SEO management.
 *
 * @package CureConnect\Models
 * @author  CureConnect Team
 * @since   1.0.0
 */

namespace CureConnect\Models;

use CureConnect\Core\Security;
use PDO;
use PDOException;

/**
 * Article model for managing medical tourism articles
 */
class Article
{
    private PDO $db;

    /**
     * Article constructor
     *
     * @param PDO $database Database connection
     */
    public function __construct(PDO $database)
    {
        $this->db = $database;
    }

    /**
     * Create a new article
     *
     * @param array $data Article data
     * @return int|false Article ID on success, false on failure
     */
    public function create(array $data)
    {
        try {
            $sql = "INSERT INTO articles (
                title, slug, content, language, meta_description, 
                tags, category, author_name, status, created_at
            ) VALUES (
                :title, :slug, :content, :language, :meta_description,
                :tags, :category, :author_name, :status, NOW()
            )";

            $stmt = $this->db->prepare($sql);

            $slug = $data['slug'] ?? Security::generateSlug($data['title']);
            $slug = $this->ensureUniqueSlug($slug);

            $result = $stmt->execute([
                'title' => $data['title'],
                'slug' => $slug,
                'content' => $data['content'],
                'language' => $data['language'] ?? 'en',
                'meta_description' => $data['meta_description'] ?? '',
                'tags' => json_encode($data['tags'] ?? []),
                'category' => $data['category'] ?? '',
                'author_name' => $data['author_name'] ?? '',
                'status' => $data['status'] ?? 'draft'
            ]);

            return $result ? $this->db->lastInsertId() : false;
        } catch (PDOException $e) {
            error_log("Article creation failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get article by ID
     *
     * @param int $id Article ID
     * @return array|false Article data or false if not found
     */
    public function getById(int $id)
    {
        try {
            $sql = "SELECT * FROM articles WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);

            $article = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($article) {
                $article['tags'] = json_decode($article['tags'] ?? '[]', true);
                return $article;
            }

            return false;
        } catch (PDOException $e) {
            error_log("Failed to get article by ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get article by slug
     *
     * @param string $slug Article slug
     * @param string $language Language code
     * @return array|false Article data or false if not found
     */
    public function getBySlug(string $slug, string $language = 'en')
    {
        try {
            $sql = "SELECT * FROM articles WHERE slug = :slug AND language = :language AND status = 'published'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['slug' => $slug, 'language' => $language]);

            $article = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($article) {
                $article['tags'] = json_decode($article['tags'] ?? '[]', true);
                return $article;
            }

            return false;
        } catch (PDOException $e) {
            error_log("Failed to get article by slug: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get published articles with pagination
     *
     * @param int $page Page number
     * @param int $limit Articles per page
     * @param string $language Language code
     * @param string|null $category Category filter
     * @return array Articles data with pagination info
     */
    public function getPublished(int $page = 1, int $limit = 10, string $language = 'en', ?string $category = null): array
    {
        try {
            $offset = ($page - 1) * $limit;

            $whereClause = "WHERE status = 'published' AND language = :language";
            $params = ['language' => $language];

            if ($category) {
                $whereClause .= " AND category = :category";
                $params['category'] = $category;
            }

            // Get total count
            $countSql = "SELECT COUNT(*) FROM articles {$whereClause}";
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetchColumn();

            // Get articles
            $sql = "SELECT * FROM articles {$whereClause} 
                   ORDER BY published_at DESC, created_at DESC 
                   LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Process tags for each article
            foreach ($articles as &$article) {
                $article['tags'] = json_decode($article['tags'] ?? '[]', true);
            }

            return [
                'articles' => $articles,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => ceil($total / $limit),
                    'total_items' => $total,
                    'items_per_page' => $limit
                ]
            ];
        } catch (PDOException $e) {
            error_log("Failed to get published articles: " . $e->getMessage());
            return ['articles' => [], 'pagination' => []];
        }
    }

    /**
     * Search articles
     *
     * @param string $query Search query
     * @param string $language Language code
     * @param int $limit Result limit
     * @return array Search results
     */
    public function search(string $query, string $language = 'en', int $limit = 20): array
    {
        try {
            $sql = "SELECT * FROM articles 
                   WHERE (title LIKE :query OR content LIKE :query OR tags LIKE :query)
                   AND language = :language AND status = 'published'
                   ORDER BY 
                       CASE 
                           WHEN title LIKE :exact_query THEN 1
                           WHEN title LIKE :query THEN 2
                           WHEN content LIKE :query THEN 3
                           ELSE 4
                       END,
                       published_at DESC
                   LIMIT :limit";

            $stmt = $this->db->prepare($sql);
            $searchTerm = '%' . $query . '%';
            $exactTerm = '%' . $query . '%';

            $stmt->bindValue(':query', $searchTerm);
            $stmt->bindValue(':exact_query', $exactTerm);
            $stmt->bindValue(':language', $language);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Process tags for each article
            foreach ($articles as &$article) {
                $article['tags'] = json_decode($article['tags'] ?? '[]', true);
            }

            return $articles;
        } catch (PDOException $e) {
            error_log("Article search failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get related articles
     *
     * @param int $articleId Current article ID
     * @param string $category Article category
     * @param int $limit Number of related articles
     * @return array Related articles
     */
    public function getRelated(int $articleId, string $category, int $limit = 3): array
    {
        try {
            $sql = "SELECT * FROM articles 
                   WHERE id != :article_id 
                   AND category = :category 
                   AND status = 'published'
                   ORDER BY published_at DESC 
                   LIMIT :limit";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':article_id', $articleId, PDO::PARAM_INT);
            $stmt->bindValue(':category', $category);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Process tags for each article
            foreach ($articles as &$article) {
                $article['tags'] = json_decode($article['tags'] ?? '[]', true);
            }

            return $articles;
        } catch (PDOException $e) {
            error_log("Failed to get related articles: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update article
     *
     * @param int $id Article ID
     * @param array $data Updated data
     * @return bool Success status
     */
    public function update(int $id, array $data): bool
    {
        try {
            $fields = [];
            $params = ['id' => $id];

            $allowedFields = [
                'title', 'slug', 'content', 'meta_description',
                'tags', 'category', 'author_name', 'status', 'published_at'
            ];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $fields[] = "{$field} = :{$field}";
                    if ($field === 'tags' && is_array($data[$field])) {
                        $params[$field] = json_encode($data[$field]);
                    } else {
                        $params[$field] = $data[$field];
                    }
                }
            }

            if (empty($fields)) {
                return false;
            }

            $fields[] = "updated_at = NOW()";
            $sql = "UPDATE articles SET " . implode(', ', $fields) . " WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Article update failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete article
     *
     * @param int $id Article ID
     * @return bool Success status
     */
    public function delete(int $id): bool
    {
        try {
            $sql = "DELETE FROM articles WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            error_log("Article deletion failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get article categories
     *
     * @param string $language Language code
     * @return array Categories with article counts
     */
    public function getCategories(string $language = 'en'): array
    {
        try {
            $sql = "SELECT category, COUNT(*) as article_count 
                   FROM articles 
                   WHERE status = 'published' AND language = :language AND category IS NOT NULL
                   GROUP BY category 
                   ORDER BY article_count DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['language' => $language]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Failed to get categories: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Ensure slug is unique
     *
     * @param string $slug Base slug
     * @param int|null $excludeId Article ID to exclude from check
     * @return string Unique slug
     */
    private function ensureUniqueSlug(string $slug, ?int $excludeId = null): string
    {
        try {
            $originalSlug = $slug;
            $counter = 1;

            while ($this->slugExists($slug, $excludeId)) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            return $slug;
        } catch (PDOException $e) {
            error_log("Slug uniqueness check failed: " . $e->getMessage());
            return $slug . '-' . time();
        }
    }

    /**
     * Check if slug exists
     *
     * @param string $slug Slug to check
     * @param int|null $excludeId Article ID to exclude
     * @return bool True if exists, false otherwise
     */
    private function slugExists(string $slug, ?int $excludeId = null): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM articles WHERE slug = :slug";
            $params = ['slug' => $slug];

            if ($excludeId) {
                $sql .= " AND id != :exclude_id";
                $params['exclude_id'] = $excludeId;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Slug existence check failed: " . $e->getMessage());
            return true; // Assume exists to be safe
        }
    }
}
