<?php

declare(strict_types=1);

/**
 * Base Controller
 *
 * @package CureConnect\Controller
 * @author  CureConnect Team
 * @since   1.0.0
 */

namespace CureConnect\Controller;

use CureConnect\Core\Application;
use CureConnect\Services\TranslationService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * Abstract base controller providing common functionality
 */
abstract class BaseController
{
    protected Application $app;
    protected $twig; // Allow both Twig\Environment and SimpleTwigFallback
    protected TranslationService $translator;
    protected Request $request;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->twig = $app->getTwig();
        $this->translator = $app->getTranslator();
        $this->request = $app->getRequest();
    }

    /**
     * Render a template with data
     *
     * @param string $template Template name
     * @param array $data Template variables
     * @param int $statusCode HTTP status code
     * @return Response
     */
    protected function render(string $template, array $data = [], int $statusCode = 200): Response
    {
        // Add global template variables
        $globalData = [
            'app' => $this->app,
            'lang' => $this->translator->getCurrentLanguage(),
            'request' => $this->request,
            'config' => $this->app->getConfig(),
        ];

        $content = $this->twig->render($template, array_merge($globalData, $data));

        return new Response($content, $statusCode);
    }

    /**
     * Render JSON response
     *
     * @param mixed $data Response data
     * @param int $statusCode HTTP status code
     * @return Response
     */
    protected function json($data, int $statusCode = 200): Response
    {
        return new Response(
            json_encode($data, JSON_THROW_ON_ERROR),
            $statusCode,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * Create redirect response
     *
     * @param string $url Redirect URL
     * @param int $statusCode HTTP status code
     * @return Response
     */
    protected function redirect(string $url, int $statusCode = 302): Response
    {
        return new Response('', $statusCode, ['Location' => $url]);
    }

    /**
     * Get current language
     *
     * @return string Language code
     */
    protected function getLanguage(): string
    {
        return $this->translator->getCurrentLanguage();
    }

    /**
     * Translate a key
     *
     * @param string $key Translation key
     * @param array $params Parameters for replacement
     * @return string Translated text
     */
    protected function trans(string $key, array $params = []): string
    {
        return $this->translator->translate($key, null, $params);
    }

    /**
     * Generate SEO meta tags
     *
     * @param string $title Page title
     * @param string $description Meta description
     * @param string $keywords Meta keywords
     * @param string $image OG image URL
     * @return array Meta tags data
     */
    protected function generateMetaTags(string $title, string $description, string $keywords = '', string $image = ''): array
    {
        return [
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'og_title' => $title,
            'og_description' => $description,
            'og_image' => $image,
            'og_url' => $this->request->getUri(),
            'twitter_title' => $title,
            'twitter_description' => $description,
        ];
    }
}
