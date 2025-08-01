<?php

declare(strict_types=1);

namespace CureConnect\Tests\Unit\Services;

use CureConnect\Services\TranslationService;
use PHPUnit\Framework\TestCase;

/**
 * Translation Service Tests
 *
 * @package CureConnect\Tests\Unit\Services
 * @author  CureConnect Team
 * @since   1.0.0
 */
class TranslationServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Clear session before each test
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Clear any existing language setting
        unset($_SESSION['language']);
        TranslationService::clearCache();
    }

    public function testGetCurrentLanguageDefault(): void
    {
        $language = TranslationService::getCurrentLanguage();
        $this->assertEquals('en', $language);
    }

    public function testSetLanguage(): void
    {
        $this->assertTrue(TranslationService::setLanguage('bn'));
        $this->assertEquals('bn', TranslationService::getCurrentLanguage());

        $this->assertFalse(TranslationService::setLanguage('invalid'));
        $this->assertEquals('bn', TranslationService::getCurrentLanguage()); // Should remain unchanged
    }

    public function testTranslateWithExistingKey(): void
    {
        $result = TranslationService::translate('Home', 'en');
        $this->assertIsString($result);
    }

    public function testTranslateWithNonExistentKey(): void
    {
        $key = 'nonexistent_key_12345';
        $result = TranslationService::translate($key, 'en');
        $this->assertEquals($key, $result); // Should return the key itself
    }

    public function testTranslateWithParameters(): void
    {
        // Test parameter replacement (if translation contains placeholders)
        $result = TranslationService::translate('test_with_param', 'en', ['name' => 'John']);
        $this->assertIsString($result);
    }

    public function testGetSupportedLanguages(): void
    {
        $languages = TranslationService::getSupportedLanguages();

        $this->assertIsArray($languages);
        $this->assertArrayHasKey('en', $languages);
        $this->assertArrayHasKey('bn', $languages);
        $this->assertArrayHasKey('ar', $languages);
        $this->assertEquals('English', $languages['en']);
    }

    public function testGetLanguageDirection(): void
    {
        $this->assertEquals('ltr', TranslationService::getLanguageDirection('en'));
        $this->assertEquals('ltr', TranslationService::getLanguageDirection('bn'));
        $this->assertEquals('rtl', TranslationService::getLanguageDirection('ar'));
    }

    public function testFormatNumber(): void
    {
        $this->assertEquals('1,234', TranslationService::formatNumber(1234, 0, 'en'));
        $this->assertEquals('1,234.56', TranslationService::formatNumber(1234.56, 2, 'en'));
    }

    public function testFormatCurrency(): void
    {
        $result = TranslationService::formatCurrency(1234.56, 'INR', 'en');
        $this->assertStringContains('₹', $result);
        $this->assertStringContains('1,234.56', $result);

        $resultUsd = TranslationService::formatCurrency(1234.56, 'USD', 'en');
        $this->assertStringContains('$', $resultUsd);
    }

    public function testFormatDate(): void
    {
        $date = '2025-01-31';
        $result = TranslationService::formatDate($date, 'en');

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function testClearCache(): void
    {
        // Load a translation to populate cache
        TranslationService::translate('Home', 'en');

        // Clear cache
        TranslationService::clearCache();

        // This should work without issues
        $result = TranslationService::translate('Home', 'en');
        $this->assertIsString($result);
    }
}
