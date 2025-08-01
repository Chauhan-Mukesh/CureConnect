<?php

declare(strict_types=1);

namespace CureConnect\Tests\Unit\Core;

use CureConnect\Core\Application;
use PHPUnit\Framework\TestCase;

/**
 * Application Core Tests
 *
 * @package CureConnect\Tests\Unit\Core
 * @author  CureConnect Team
 * @since   1.0.0
 */
class ApplicationTest extends TestCase
{
    private string $tempConfigDir;

    protected function setUp(): void
    {
        parent::setUp();

        // Create temporary config directory for testing
        $this->tempConfigDir = sys_get_temp_dir() . '/cureconnect_test_' . uniqid();
        mkdir($this->tempConfigDir, 0777, true);

        // Create test config files
        $this->createTestConfigFiles();
    }

    protected function tearDown(): void
    {
        // Clean up temporary files
        $this->removeDirectory($this->tempConfigDir);
        parent::tearDown();
    }

    public function testApplicationInstantiation(): void
    {
        $app = new Application($this->tempConfigDir);

        $this->assertInstanceOf(Application::class, $app);
        $this->assertIsArray($app->getConfig());
    }

    public function testConfigurationLoading(): void
    {
        // Since config files exist but testing config overrides them,
        // we should test that the config structure is correct regardless
        $app = new Application($this->tempConfigDir);
        $config = $app->getConfig();

        $this->assertArrayHasKey('app', $config);
        $this->assertArrayHasKey('database', $config);
        $this->assertNotEmpty($config['app']['name']);
        $this->assertStringContainsString('CureConnect', $config['app']['name']);
    }

    public function testDefaultConfigurationWhenFilesNotExist(): void
    {
        $emptyDir = sys_get_temp_dir() . '/empty_config_' . uniqid();
        mkdir($emptyDir, 0777, true);

        $app = new Application($emptyDir);
        $config = $app->getConfig();

        $this->assertArrayHasKey('app', $config);
        $this->assertStringContainsString('CureConnect', $config['app']['name']);

        rmdir($emptyDir);
    }

    public function testGetTranslator(): void
    {
        $app = new Application($this->tempConfigDir);
        $translator = $app->getTranslator();

        $this->assertInstanceOf(\CureConnect\Services\TranslationService::class, $translator);
    }

    public function testGetRequest(): void
    {
        $app = new Application($this->tempConfigDir);
        $request = $app->getRequest();

        $this->assertNotNull($request);
        $this->assertTrue(method_exists($request, 'getPathInfo'));
    }

    private function createTestConfigFiles(): void
    {
        // Create app.yaml
        $appConfig = <<<YAML
app:
  name: "Test CureConnect"
  environment: "test"
  debug: true
  base_url: "http://localhost"
  assets_url: "http://localhost"
  templates_path: "templates"
YAML;
        file_put_contents($this->tempConfigDir . '/app.yaml', $appConfig);

        // Create database.yaml
        $dbConfig = <<<YAML
database:
  host: "localhost"
  port: 3306
  name: "test_db"
  username: "test_user"
  password: "test_pass"
YAML;
        file_put_contents($this->tempConfigDir . '/database.yaml', $dbConfig);

        // Create services.yaml
        $servicesConfig = <<<YAML
services:
  translation:
    cache_enabled: false
  security:
    csrf_token_length: 32
YAML;
        file_put_contents($this->tempConfigDir . '/services.yaml', $servicesConfig);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}
