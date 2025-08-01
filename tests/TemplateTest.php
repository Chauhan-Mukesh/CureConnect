<?php
/**
 * Basic template test
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TemplateTest
{
    private $twig;
    
    public function __construct()
    {
        $loader = new FilesystemLoader(__DIR__ . '/../templates');
        $this->twig = new Environment($loader, [
            'debug' => true,
            'cache' => false,
        ]);
    }
    
    public function testTemplatesExist()
    {
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
        
        $results = [];
        foreach ($templates as $template) {
            try {
                $this->twig->load($template);
                $results[$template] = 'OK';
            } catch (Exception $e) {
                $results[$template] = 'FAILED: ' . $e->getMessage();
            }
        }
        
        return $results;
    }
    
    public function testTemplateRender()
    {
        $sampleData = [
            'app_name' => 'YourBlog',
            'base_url' => '',
            'assets_url' => '/assets',
            'lang' => 'en',
            'page' => ['title' => 'Test', 'description' => 'Test page']
        ];
        
        $results = [];
        $templates = ['pages/home.html.twig', 'pages/about.html.twig', 'pages/contact.html.twig'];
        
        foreach ($templates as $template) {
            try {
                $output = $this->twig->render($template, $sampleData);
                $results[$template] = strlen($output) > 100 ? 'OK' : 'FAILED: Output too short';
            } catch (Exception $e) {
                $results[$template] = 'FAILED: ' . $e->getMessage();
            }
        }
        
        return $results;
    }
    
    public function runAllTests()
    {
        echo "=== Template Existence Tests ===\n";
        $existenceResults = $this->testTemplatesExist();
        foreach ($existenceResults as $template => $result) {
            echo "$template: $result\n";
        }
        
        echo "\n=== Template Render Tests ===\n";
        $renderResults = $this->testTemplateRender();
        foreach ($renderResults as $template => $result) {
            echo "$template: $result\n";
        }
        
        // Summary
        $totalTests = count($existenceResults) + count($renderResults);
        $passedTests = 0;
        
        foreach (array_merge($existenceResults, $renderResults) as $result) {
            if ($result === 'OK') {
                $passedTests++;
            }
        }
        
        echo "\n=== Summary ===\n";
        echo "Total tests: $totalTests\n";
        echo "Passed: $passedTests\n";
        echo "Failed: " . ($totalTests - $passedTests) . "\n";
        
        if ($passedTests === $totalTests) {
            echo "All tests passed! ✓\n";
            return true;
        } else {
            echo "Some tests failed! ✗\n";
            return false;
        }
    }
}

// Run tests if script is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $test = new TemplateTest();
    $success = $test->runAllTests();
    exit($success ? 0 : 1);
}