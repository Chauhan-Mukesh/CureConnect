<?php

declare(strict_types=1);

/**
 * Core Application Class
 *
 * @package CureConnect\Core
 * @author  CureConnect Team
 * @since   1.0.0
 */

namespace CureConnect\Core;

use CureConnect\Services\TranslationService;
use PDO;

class Application
{
    private static ?self $instance = null;
    private array $config = [];
    private ?PDO $database = null;
    private ?object $twig = null;
    private ?TranslationService $translator = null;
    private ?object $request = null;
    private ?array $routes = null;
    private string $rootPath;

    /**
     * The constructor is private to enforce the singleton pattern.
     */
    private function __construct(string $rootPath)
    {
        $this->rootPath = $rootPath;

        // Load fallback classes if composer is not used
        if (!class_exists(\Symfony\Component\HttpFoundation\Request::class)) {
            require_once $this->rootPath . '/src/Core/Fallbacks.php';
            // Create aliases for fallback classes
            if (!class_exists(\Symfony\Component\HttpFoundation\Request::class)) {
                class_alias(\CureConnect\Core\SimpleRequest::class, 'Symfony\Component\HttpFoundation\Request');
                class_alias(\CureConnect\Core\SimpleResponse::class, 'Symfony\Component\HttpFoundation\Response');
                class_alias(\CureConnect\Core\SimpleYaml::class, 'Symfony\Component\Yaml\Yaml');
            }
        }

        $this->loadConfiguration($this->rootPath . '/config');
        $this->initializeServices();
    }

    /**
     * Boots the application and returns the singleton instance.
     *
     * @param string $rootPath The root path of the application.
     * @return self
     */
    public static function boot(string $rootPath): self
    {
        if (self::$instance === null) {
            self::$instance = new self($rootPath);
        }
        return self::$instance;
    }

    /**
     * Get the singleton instance of the Application.
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            // This is a fallback for contexts where the app is not yet booted.
            // The boot() method should be the primary entry point.
            self::boot(dirname(__DIR__, 2));
        }
        return self::$instance;
    }

    /**
     * Initialize all core services.
     */
    private function initializeServices(): void
    {
        if (class_exists(\Symfony\Component\HttpFoundation\Request::class)) {
            $this->request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        } else {
            $this->request = new \CureConnect\Core\SimpleRequest();
        }
        $this->initializeDatabase();
        $this->initializeTwig();
        $this->initializeTranslator();
        $this->initializeRoutes();
    }


    /**
     * Load configuration from YAML files
     *
     * @param string $configPath Path to config directory
     */
    private function loadConfiguration(string $configPath): void
    {
        $this->config = [];
        
        // Check if we're in testing mode
        $isTestMode = ($_ENV['APP_ENV'] ?? '') === 'testing';
        
        if ($isTestMode) {
            // Use test configuration
            $this->config = [
                'app' => [
                    'name' => 'CureConnect Test',
                    'environment' => 'testing',
                    'debug' => true,
                    'base_url' => 'http://localhost:8001',
                    'assets_url' => 'http://localhost:8001',
                    'templates_path' => $this->rootPath . '/templates'
                ],
                'database' => [
                    'driver' => 'sqlite',
                    'name' => ':memory:'
                ],
                'services' => []
            ];
        } else {
            // Normal configuration loading
            $configFiles = [
                'app' => $configPath . '/app.yaml',
                'database' => $configPath . '/database.yaml',
                'services' => $configPath . '/services.yaml'
            ];

            foreach ($configFiles as $key => $file) {
                if (file_exists($file)) {
                    $this->config[$key] = class_exists(\Symfony\Component\Yaml\Yaml::class)
                        ? \Symfony\Component\Yaml\Yaml::parseFile($file)
                        : \CureConnect\Core\SimpleYaml::parseFile($file);
                }
            }

            // Set default config if files don't exist
            if (empty($this->config)) {
                $this->config = $this->getDefaultConfig();
            }
        }
    }

    /**
     * Initialize database connection
     */
    private function initializeDatabase(): void
    {
        if ($this->database === null) {
            $dbConfig = $this->config['database'] ?? [];
            $driver = $dbConfig['driver'] ?? 'mysql';
            
            if ($driver === 'sqlite') {
                // SQLite configuration
                $database = $dbConfig['name'] ?? ':memory:';
                if ($database !== ':memory:' && !str_starts_with($database, '/')) {
                    // Relative path, make it absolute
                    $database = $this->rootPath . '/' . $database;
                }
                $dsn = 'sqlite:' . $database;
                
                $this->database = new PDO($dsn, null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]);
                
                // Enable foreign key constraints for SQLite
                $this->database->exec('PRAGMA foreign_keys = ON');
                
            } else {
                // MySQL configuration (default)
                $dsn = sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                    $dbConfig['host'] ?? 'localhost',
                    $dbConfig['port'] ?? 3306,
                    $dbConfig['name'] ?? 'cureconnect_db'
                );

                $this->database = new PDO(
                    $dsn,
                    $dbConfig['username'] ?? 'root',
                    $dbConfig['password'] ?? '',
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            }
        }
    }

    /**
     * Initialize Twig templating engine
     */
    private function initializeTwig(): void
    {
        $templatesPath = $this->config['app']['templates_path'] ?? $this->rootPath . '/templates';
        if (class_exists(\Twig\Environment::class)) {
            $loader = new \Twig\Loader\FilesystemLoader($templatesPath);
            $this->twig = new \Twig\Environment($loader, [
                'cache' => $this->config['app']['environment'] === 'production' ? $this->config['app']['cache_path'] : false,
                'debug' => $this->config['app']['debug'] ?? true
            ]);
        } else {
            // Simple template renderer fallback
            $this->twig = new SimpleTwigFallback($templatesPath);
        }

        // Add global variables
        if (method_exists($this->twig, 'addGlobal')) {
            $this->twig->addGlobal('app_name', $this->config['app']['name'] ?? 'CureConnect');
            $this->twig->addGlobal('assets_url', $this->config['app']['assets_url'] ?? '');
            $this->twig->addGlobal('base_url', $this->config['app']['base_url'] ?? '/');
        }
    }

    /**
     * Initialize translation service
     */
    private function initializeTranslator(): void
    {
        // Initialize with language path
        TranslationService::init($this->rootPath . '/lang');
        $this->translator = new TranslationService();
    }

    /**
     * Initialize routes
     */
    private function initializeRoutes(): void
    {
        $this->routes = [];

        // Define routes as simple array
        $this->routes = [
            '/' => [
                'controller' => 'CureConnect\Controller\HomeController',
                'method' => 'index'
            ],
            '/about' => [
                'controller' => 'CureConnect\Controller\PageController',
                'method' => 'about'
            ],
            '/contact' => [
                'controller' => 'CureConnect\Controller\PageController',
                'method' => 'contact'
            ],
            '/gallery' => [
                'controller' => 'CureConnect\Controller\PageController',
                'method' => 'gallery'
            ],
            '/government-schemes' => [
                'controller' => 'CureConnect\Controller\PageController',
                'method' => 'governmentSchemes'
            ]
        ];
    }

    /**
     * Handle incoming request and return response
     */
    public function handleRequest()
    {
        try {
            $pathInfo = $this->request->getPathInfo();

            // Simple routing
            if (isset($this->routes[$pathInfo])) {
                $route = $this->routes[$pathInfo];
                $controllerClass = $route['controller'];
                $method = $route['method'];

                if (!class_exists($controllerClass)) {
                    throw new \Exception("Controller class not found: {$controllerClass}");
                }

                // Instantiate controller and call method
                $controller = new $controllerClass($this);
                return $controller->$method();
            }

            return $this->handleNotFound();
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Handle 404 errors
     */
    private function handleNotFound()
    {
        $content = $this->twig->render('errors/404.html.twig', [
            'title' => 'Page Not Found'
        ]);

        if (class_exists(\Symfony\Component\HttpFoundation\Response::class)) {
            return new \Symfony\Component\HttpFoundation\Response($content, 404);
        } else {
            return new \CureConnect\Core\SimpleResponse($content, 404);
        }
    }

    /**
     * Handle general errors
     */
    private function handleError(\Exception $e)
    {
        if ($this->config['app']['debug']) {
            $content = '<h1>Error</h1><pre>' . $e->getMessage() . "\n" . $e->getTraceAsString() . '</pre>';
        } else {
            $content = $this->twig->render('errors/500.html.twig', [
                'title' => 'Internal Server Error'
            ]);
        }

        if (class_exists(\Symfony\Component\HttpFoundation\Response::class)) {
            return new \Symfony\Component\HttpFoundation\Response($content, 500);
        } else {
            return new \CureConnect\Core\SimpleResponse($content, 500);
        }
    }

    // Getters for dependency injection
    public function getConfig(): array
    {
        return $this->config;
    }

    public function getRootPath(): string
    {
        return $this->rootPath;
    }

    public function getDatabase(): PDO
    {
        if ($this->database === null) {
            $this->initializeDatabase();
        }
        return $this->database;
    }

    public function getTwig(): object
    {
        return $this->twig;
    }

    public function getTranslator(): TranslationService
    {
        return $this->translator;
    }

    public function getRequest(): object
    {
        return $this->request;
    }

    /**
     * Get default configuration
     */
    private function getDefaultConfig(): array
    {
        return [
            'app' => [
                'name' => 'CureConnect Medical Tourism Portal',
                'version' => '1.0.0',
                'environment' => 'development',
                'debug' => true,
                'timezone' => 'Asia/Kolkata',
                'base_url' => 'http://localhost/CureConnect',
                'assets_url' => 'http://localhost/CureConnect',
                'templates_path' => 'templates',
                'cache_path' => 'var/cache',
                'logs_path' => 'var/logs'
            ],
            'database' => [
                'driver' => 'sqlite',
                'name' => ':memory:',
                'host' => '',
                'port' => '',
                'username' => '',
                'password' => '',
                'charset' => 'utf8'
            ],
            'services' => []
        ];
    }
}

class SimpleTwigFallback
{
    private string $templatesPath;
    private array $globals = [];

    public function __construct(string $templatesPath)
    {
        $this->templatesPath = $templatesPath;
    }

    public function addGlobal(string $name, $value): void
    {
        $this->globals[$name] = $value;
    }

    public function render(string $template, array $variables = []): string
    {
        $templateFile = $this->templatesPath . '/' . $template;

        if (!file_exists($templateFile)) {
            throw new \Exception("Template not found: {$templateFile}");
        }

        // Simple template parsing for .twig files
        $content = file_get_contents($templateFile);
        if ($content === false) {
            throw new \Exception("Could not read template file: {$templateFile}");
        }
        return $this->parseTemplate($content, array_merge($this->globals, $variables));
    }

    private function renderPhpTemplate(string $templateFile, array $variables): string
    {
        extract($variables, EXTR_SKIP);
        ob_start();
        include $templateFile;
        return ob_get_clean() ?: '';
    }

    private function parseTemplate(string $content, array $variables): string
    {
        // Handle template inheritance
        if (preg_match('/\{% extends [\'"](.+?)[\'"] %\}/', $content, $matches)) {
            $baseTemplate = $matches[1];
            $baseContent = $this->loadTemplate($baseTemplate);
            
            // Extract block content from child template
            $blocks = $this->extractBlocks($content);
            
            // Replace blocks in base template
            $content = $this->replaceBlocks($baseContent, $blocks);
        }
        
        // Remove remaining Twig syntax that we can't process
        $content = preg_replace('/\{% .*? %\}/', '', $content);
        $content = preg_replace('/\{\# .*? \#\}/', '', $content);
        
        // Process simple variables
        foreach ($variables as $key => $value) {
            if (is_scalar($value) || (is_object($value) && method_exists($value, '__toString'))) {
                $content = str_replace('{{ ' . $key . ' }}', (string)$value, $content);
            } elseif (is_array($value)) {
                // Handle array access like {{ array.key }}
                foreach ($value as $subKey => $subValue) {
                    if (is_scalar($subValue)) {
                        $content = str_replace('{{ ' . $key . '.' . $subKey . ' }}', (string)$subValue, $content);
                    }
                }
            }
        }
        
        // Clean up remaining variables that couldn't be processed
        $content = preg_replace('/\{\{ .*? \}\}/', '', $content);
        
        return $content;
    }
    
    private function loadTemplate(string $template): string
    {
        $templateFile = $this->templatesPath . '/' . $template;
        if (!file_exists($templateFile)) {
            throw new \Exception("Base template not found: {$templateFile}");
        }
        return file_get_contents($templateFile);
    }
    
    private function extractBlocks(string $content): array
    {
        $blocks = [];
        preg_match_all('/\{% block (\w+) %\}(.*?)\{% endblock %\}/s', $content, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $blocks[$match[1]] = $match[2];
        }
        
        return $blocks;
    }
    
    private function replaceBlocks(string $baseContent, array $blocks): string
    {
        foreach ($blocks as $blockName => $blockContent) {
            $pattern = '/\{% block ' . $blockName . ' %\}.*?\{% endblock %\}/s';
            $replacement = '{% block ' . $blockName . ' %}' . $blockContent . '{% endblock %}';
            $baseContent = preg_replace($pattern, $replacement, $baseContent);
        }
        
        return $baseContent;
    }
}
