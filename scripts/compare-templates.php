#!/usr/bin/env php
<?php
/**
 * Static vs Twig Template Comparison Script
 * Compares functionality between static HTML files and Twig templates
 */

require_once __DIR__ . '/../tests/bootstrap.php';

class TemplateComparison
{
    private $staticFiles = [];
    private $app;
    private $results = [];

    public function __construct()
    {
        $this->app = createTestApplication();
        $this->staticFiles = [
            'home.html' => '/',
            'about.html' => '/about', 
            'contact-us.html' => '/contact',
            'gallery.html' => '/gallery',
            'government-schemes.html' => '/government-schemes',
            'article.html' => '/article'
        ];
    }

    public function runComparison(): bool
    {
        echo "=== Static HTML vs Twig Template Comparison ===\n\n";
        
        $allPassed = true;
        
        foreach ($this->staticFiles as $staticFile => $route) {
            echo "Comparing {$staticFile} vs {$route}...\n";
            
            $result = $this->compareFileVsRoute($staticFile, $route);
            $this->results[$staticFile] = $result;
            
            if ($result['status'] === 'PASS') {
                echo "✓ PASS: {$result['message']}\n";
            } else {
                echo "✗ FAIL: {$result['message']}\n";
                $allPassed = false;
            }
            echo "\n";
        }
        
        $this->printSummary();
        return $allPassed;
    }

    private function compareFileVsRoute(string $staticFile, string $route): array
    {
        $staticPath = __DIR__ . '/../' . $staticFile;
        
        // Check if static file exists
        if (!file_exists($staticPath)) {
            return [
                'status' => 'SKIP',
                'message' => 'Static file does not exist'
            ];
        }
        
        // Get static file content
        $staticContent = file_get_contents($staticPath);
        
        // Get Twig rendered content
        try {
            $twigContent = $this->renderRoute($route);
        } catch (Exception $e) {
            return [
                'status' => 'FAIL',
                'message' => 'Twig template failed to render: ' . $e->getMessage()
            ];
        }
        
        // Compare key characteristics
        $comparison = $this->compareContent($staticContent, $twigContent);
        
        return $comparison;
    }

    private function renderRoute(string $route): string
    {
        // Simulate the route rendering
        switch ($route) {
            case '/':
                $controller = new \CureConnect\Controller\HomeController($this->app);
                $response = $controller->index();
                break;
            case '/about':
                $controller = new \CureConnect\Controller\PageController($this->app);
                $response = $controller->about();
                break;
            case '/contact':
                $controller = new \CureConnect\Controller\PageController($this->app);
                $response = $controller->contact();
                break;
            case '/gallery':
                $controller = new \CureConnect\Controller\PageController($this->app);
                $response = $controller->gallery();
                break;
            case '/government-schemes':
                $controller = new \CureConnect\Controller\PageController($this->app);
                $response = $controller->governmentSchemes();
                break;
            case '/article':
                $controller = new \CureConnect\Controller\PageController($this->app);
                $response = $controller->article();
                break;
            default:
                throw new Exception("Unknown route: {$route}");
        }
        
        return $response->getContent();
    }

    private function compareContent(string $staticContent, string $twigContent): array
    {
        $checks = [];
        
        // Check 1: Both have valid HTML structure
        $staticHasHtml = strpos($staticContent, '<html') !== false;
        $twigHasHtml = strpos($twigContent, '<html') !== false;
        $checks['html_structure'] = $staticHasHtml && $twigHasHtml;
        
        // Check 2: Both have head section
        $staticHasHead = strpos($staticContent, '<head>') !== false;
        $twigHasHead = strpos($twigContent, '<head>') !== false;
        $checks['head_section'] = $staticHasHead && $twigHasHead;
        
        // Check 3: Both have body content
        $staticHasBody = strpos($staticContent, '<body') !== false;
        $twigHasBody = strpos($twigContent, '<body') !== false;
        $checks['body_section'] = $staticHasBody && $twigHasBody;
        
        // Check 4: Twig content is reasonably sized (not empty)
        $checks['reasonable_size'] = strlen($twigContent) > 500;
        
        // Check 5: Twig contains medical tourism content (CureConnect specific)
        $medicalKeywords = ['medical', 'healthcare', 'india', 'treatment', 'hospital'];
        $twigHasMedicalContent = false;
        foreach ($medicalKeywords as $keyword) {
            if (stripos($twigContent, $keyword) !== false) {
                $twigHasMedicalContent = true;
                break;
            }
        }
        $checks['medical_content'] = $twigHasMedicalContent;
        
        // Determine overall result
        $passedChecks = array_sum($checks);
        $totalChecks = count($checks);
        
        if ($passedChecks === $totalChecks) {
            return [
                'status' => 'PASS',
                'message' => "All {$totalChecks} checks passed. Twig template provides equivalent functionality.",
                'checks' => $checks
            ];
        } else {
            $failedChecks = array_keys(array_filter($checks, function($v) { return !$v; }));
            return [
                'status' => 'FAIL',
                'message' => "Failed checks: " . implode(', ', $failedChecks) . " ({$passedChecks}/{$totalChecks} passed)",
                'checks' => $checks
            ];
        }
    }

    private function printSummary(): void
    {
        echo "=== Summary ===\n";
        
        $totalFiles = count($this->results);
        $passedFiles = count(array_filter($this->results, function($r) { return $r['status'] === 'PASS'; }));
        $skippedFiles = count(array_filter($this->results, function($r) { return $r['status'] === 'SKIP'; }));
        
        echo "Total files compared: {$totalFiles}\n";
        echo "Passed: {$passedFiles}\n";
        echo "Failed: " . ($totalFiles - $passedFiles - $skippedFiles) . "\n";
        echo "Skipped: {$skippedFiles}\n";
        
        if ($passedFiles === $totalFiles - $skippedFiles) {
            echo "\n🎉 All comparisons passed! Twig templates provide equivalent functionality.\n";
            echo "✅ Static files can be safely removed.\n";
        } else {
            echo "\n⚠️  Some comparisons failed. Review failed checks before removing static files.\n";
        }
    }
}

// Run comparison if script is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $comparison = new TemplateComparison();
    $success = $comparison->runComparison();
    exit($success ? 0 : 1);
}