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
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Yaml\Yaml;
use PDO;
use Dotenv\Dotenv;
use SensitiveParameter;

class Application
{
    private static ?self $instance = null;
    private array $config = [];
    private ?PDO $database = null;
    private ?object $twig = null;
    private ?TranslationService $translator = null;
    private ?object $request = null;
    private ?RouteCollection $routes = null;
    private string $rootPath;

    /**
     * The constructor is private to enforce the singleton pattern.
     */
    private function __construct(string $rootPath)
    {
        $this->rootPath = $rootPath;

        // Load fallback classes if composer is not used
        if (!class_exists(Request::class)) {
            require_once $this->rootPath . '/src/Core/Fallbacks.php';
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
        $this->request = Request::createFromGlobals();
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
        $configFiles = [
            'app' => $configPath . '/app.yaml',
            'database' => $configPath . '/database.yaml',
            'services' => $configPath . '/services.yaml'
        ];

        foreach ($configFiles as $key => $file) {
            if (file_exists($file)) {
                $this->config[$key] = class_exists(Yaml::class)
                    ? Yaml::parseFile($file)
                    : SimpleYaml::parseFile($file);
            }
        }

        // Set default config if files don't exist
        if (empty($this->config)) {
            $this->config = $this->getDefaultConfig();
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
        if (class_exists('Twig\Environment')) {
            $loader = new FilesystemLoader($templatesPath);
            $this->twig = new Environment($loader, [
                'cache' => $this->config['app']['environment'] === 'production' ? $this->config['app']['cache_path'] : false,
                'debug' => $this->config['app']['debug'] ?? true
            ]);
        } else {
            // Simple template renderer fallback
            $this->twig = new SimpleTwigFallback($templatesPath);
        }

        // Add global variables
        $this->twig->addGlobal('app_name', $this->config['app']['name']);
        $this->twig->addGlobal('assets_url', $this->config['app']['assets_url']);
        $this->twig->addGlobal('base_url', $this->config['app']['base_url']);
    }

    /**
     * Initialize translation service
     */
    private function initializeTranslator(): void
    {
        $this->translator = new TranslationService();
    }

    /**
     * Initialize routes
     */
    private function initializeRoutes(): void
    {
        $this->routes = new RouteCollection();

        // Define routes
        $this->routes->add('home', new Route('/', [
            '_controller' => 'CureConnect\Controller\HomeController::index'
        ]));

        $this->routes->add('about', new Route('/about', [
            '_controller' => 'CureConnect\Controller\PageController::about'
        ]));

        $this->routes->add('contact', new Route('/contact', [
            '_controller' => 'CureConnect\Controller\PageController::contact'
        ]));

        $this->routes->add('gallery', new Route('/gallery', [
            '_controller' => 'CureConnect\Controller\PageController::gallery'
        ]));

        $this->routes->add('government_schemes', new Route('/government-schemes', [
            '_controller' => 'CureConnect\Controller\PageController::governmentSchemes'
        ]));
    }

    /**
     * Handle incoming request and return response
     *
     * @return Response
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
        } catch (ResourceNotFoundException $e) {
            return $this->handleNotFound();
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Handle 404 errors
     *
     * @return Response
     */
    private function handleNotFound(): Response
    {
        $content = $this->twig->render('errors/404.html.twig', [
            'title' => 'Page Not Found'
        ]);

        return new Response($content, 404);
    }

    /**
     * Handle general errors
     *
     * @param \Exception $e Exception
     * @return Response
     */
    private function handleError(\Exception $e): Response
    {
        if ($this->config['app']['debug']) {
            $content = '<h1>Error</h1><pre>' . $e->getMessage() . "\n" . $e->getTraceAsString() . '</pre>';
        } else {
            $content = $this->twig->render('errors/500.html.twig', [
                'title' => 'Internal Server Error'
            ]);
        }

        return new Response($content, 500);
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
        foreach ($variables as $key => $value) {
            if (is_scalar($value) || (is_object($value) && method_exists($value, '__toString'))) {
                $content = str_replace('{{ ' . $key . ' }}', (string)$value, $content);
            }
        }
        return $content;
    }
}
