<?php

declare(strict_types=1);

namespace CureConnect\Tests;

use PHPUnit\Framework\TestCase;
use CureConnect\Core\Application;

/**
 * Template Tests
 *
 * @package CureConnect\Tests
 * @author  CureConnect Team
 * @since   1.0.0
 */
class TemplateTest extends TestCase
{
    private Application $app;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->app = createTestApplication();
    }
    
    public function testTemplatesExist(): void
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
        
        $templatesPath = dirname(__DIR__) . '/templates';
        
        foreach ($templates as $template) {
            $filePath = $templatesPath . '/' . $template;
            $this->assertFileExists($filePath, "Template file {$template} should exist");
        }
    }
    
    public function testTemplateRender(): void
    {
        $sampleData = [
            'app_name' => 'CureConnect Test',
            'base_url' => '',
            'assets_url' => '',
            'lang' => 'en'
        ];
        
        $templates = ['pages/home.html.twig', 'pages/about.html.twig', 'pages/contact.html.twig'];
        $twig = $this->app->getTwig();
        
        foreach ($templates as $template) {
            try {
                $output = $twig->render($template, $sampleData);
                $this->assertGreaterThan(100, strlen($output), "Template {$template} should render content longer than 100 characters");
            } catch (\Exception $e) {
                $this->fail("Template {$template} failed to render: " . $e->getMessage());
            }
        }
    }
    
    public function testBaseTemplateExists(): void
    {
        $baseTemplate = dirname(__DIR__) . '/templates/base.html.twig';
        $this->assertFileExists($baseTemplate, 'Base template should exist');
        
        $content = file_get_contents($baseTemplate);
        $this->assertStringContainsString('{% block main %}', $content, 'Base template should contain main block');
    }
}