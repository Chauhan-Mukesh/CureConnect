<?php
/**
 * Basic template test
 */

require_once __DIR__ . '/bootstrap.php';

class TemplateTest
{
    private $app;
    
    public function __construct()
    {
        $this->app = createTestApplication();
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
        $templatesPath = $this->app->getConfig()['app']['templates_path'];
        
        foreach ($templates as $template) {
            $filePath = $templatesPath . '/' . $template;
            if (file_exists($filePath)) {
                $results[$template] = 'OK';
            } else {
                $results[$template] = 'FAILED: File not found';
            }
        }
        
        return $results;
    }
    
    public function testTemplateRender()
    {
        $sampleData = [
            'app_name' => 'CureConnect Test',
            'base_url' => '',
            'assets_url' => '/assets',
            'lang' => 'en'
        ];
        
        $results = [];
        $templates = ['pages/home.html.twig', 'pages/about.html.twig', 'pages/contact.html.twig'];
        $twig = $this->app->getTwig();
        
        foreach ($templates as $template) {
            try {
                $output = $twig->render($template, $sampleData);
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