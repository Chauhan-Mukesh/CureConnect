<?php

declare(strict_types=1);

/**
 * Translation Service
 *
 * Handles internationalization and localization for the medical tourism portal.
 * Supports multiple languages including English, Bengali, and Arabic.
 *
 * @package CureConnect\Services
 * @author  CureConnect Team
 * @since   1.0.0
 */

namespace CureConnect\Services;

use CureConnect\Core\Security;

/**
 * Translation service for handling multi-language support
 */
class TranslationService
{
    /**
     * Path to language files
     */
    private static string $langPath = '';
    
    /**
     * Supported languages
     */
    private const SUPPORTED_LANGUAGES = ['en', 'bn', 'ar'];

    /**
     * Default language
     */
    private const DEFAULT_LANGUAGE = 'en';

    /**
     * Cache for loaded translations
     */
    private static array $translationCache = [];

    /**
     * Initialize the translation service with language path
     */
    public static function init(string $langPath): void
    {
        self::$langPath = rtrim($langPath, '/');
    }

    /**
     * Get current language from session or default
     *
     * @return string Current language code
     */
    public static function getCurrentLanguage(): string
    {
        // Check URL parameter first
        if (isset($_GET['lang']) && self::isValidLanguage($_GET['lang'])) {
            $_SESSION['language'] = $_GET['lang'];
            return $_GET['lang'];
        }

        // Check session
        if (isset($_SESSION['language']) && self::isValidLanguage($_SESSION['language'])) {
            return $_SESSION['language'];
        }

        // Check browser language
        $browserLang = self::getBrowserLanguage();
        if ($browserLang && self::isValidLanguage($browserLang)) {
            $_SESSION['language'] = $browserLang;
            return $browserLang;
        }

        // Return default
        $_SESSION['language'] = self::DEFAULT_LANGUAGE;
        return self::DEFAULT_LANGUAGE;
    }

    /**
     * Set current language
     *
     * @param string $language Language code to set
     * @return bool True if successful, false otherwise
     */
    public static function setLanguage(string $language): bool
    {
        if (!self::isValidLanguage($language)) {
            return false;
        }

        $_SESSION['language'] = $language;
        return true;
    }

    /**
     * Get translation for a key
     *
     * @param string $key Translation key
     * @param string|null $language Language code (null for current language)
     * @param array $params Parameters for string replacement
     * @return string Translated string
     */
    public static function translate(string $key, ?string $language = null, array $params = []): string
    {
        $language = $language ?? self::getCurrentLanguage();

        if (!self::isValidLanguage($language)) {
            $language = self::DEFAULT_LANGUAGE;
        }

        $translations = self::loadTranslations($language);
        $translation = $translations[$key] ?? $key;

        // Replace parameters if provided
        if (!empty($params)) {
            foreach ($params as $param => $value) {
                $translation = str_replace("{{$param}}", (string)$value, $translation);
            }
        }

        return $translation;
    }

    /**
     * Load translations for a language
     *
     * @param string $language Language code
     * @return array Translation array
     */
    private static function loadTranslations(string $language): array
    {
        if (isset(self::$translationCache[$language])) {
            return self::$translationCache[$language];
        }

        $translationFile = self::$langPath . "/{$language}.json";

        if (!file_exists($translationFile)) {
            // Fallback to default language
            $translationFile = self::$langPath . "/" . self::DEFAULT_LANGUAGE . ".json";
        }

        if (!file_exists($translationFile)) {
            self::$translationCache[$language] = [];
            return [];
        }

        $content = file_get_contents($translationFile);
        $translations = json_decode($content, true) ?? [];

        self::$translationCache[$language] = $translations;
        return $translations;
    }

    /**
     * Check if language is supported
     *
     * @param string $language Language code to check
     * @return bool True if supported, false otherwise
     */
    private static function isValidLanguage(string $language): bool
    {
        return in_array($language, self::SUPPORTED_LANGUAGES, true);
    }

    /**
     * Get browser's preferred language
     *
     * @return string|null Browser language or null
     */
    private static function getBrowserLanguage(): ?string
    {
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return null;
        }

        $languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);

        foreach ($languages as $language) {
            $lang = strtolower(substr(trim($language), 0, 2));
            if (self::isValidLanguage($lang)) {
                return $lang;
            }
        }

        return null;
    }

    /**
     * Get all supported languages with their names
     *
     * @return array Array of language codes and names
     */
    public static function getSupportedLanguages(): array
    {
        return [
            'en' => 'English',
            'bn' => 'বাংলা',
            'ar' => 'العربية'
        ];
    }

    /**
     * Get language direction (LTR or RTL)
     *
     * @param string|null $language Language code
     * @return string 'ltr' or 'rtl'
     */
    public static function getLanguageDirection(?string $language = null): string
    {
        $language = $language ?? self::getCurrentLanguage();
        return in_array($language, ['ar'], true) ? 'rtl' : 'ltr';
    }

    /**
     * Format number according to language
     *
     * @param float $number Number to format
     * @param int $decimals Number of decimal places
     * @param string|null $language Language code
     * @return string Formatted number
     */
    public static function formatNumber(float $number, int $decimals = 0, ?string $language = null): string
    {
        $language = $language ?? self::getCurrentLanguage();

        switch ($language) {
            case 'bn':
                // Bengali number formatting
                return number_format($number, $decimals, '.', ',');
            case 'ar':
                // Arabic number formatting
                return number_format($number, $decimals, '٫', '٬');
            default:
                // English/default formatting
                return number_format($number, $decimals, '.', ',');
        }
    }

    /**
     * Format currency according to language and region
     *
     * @param float $amount Amount to format
     * @param string $currency Currency code
     * @param string|null $language Language code
     * @return string Formatted currency
     */
    public static function formatCurrency(float $amount, string $currency = 'INR', ?string $language = null): string
    {
        $language = $language ?? self::getCurrentLanguage();
        $formattedAmount = self::formatNumber($amount, 2, $language);

        $currencySymbols = [
            'INR' => '₹',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£'
        ];

        $symbol = $currencySymbols[$currency] ?? $currency;

        // Different positioning for different languages
        switch ($language) {
            case 'ar':
                return $formattedAmount . ' ' . $symbol;
            default:
                return $symbol . ' ' . $formattedAmount;
        }
    }

    /**
     * Get localized date format
     *
     * @param string $date Date string
     * @param string|null $language Language code
     * @return string Formatted date
     */
    public static function formatDate(string $date, ?string $language = null): string
    {
        $language = $language ?? self::getCurrentLanguage();
        $timestamp = strtotime($date);

        if ($timestamp === false) {
            return $date;
        }

        switch ($language) {
            case 'bn':
                // Bengali date format
                return date('j F, Y', $timestamp);
            case 'ar':
                // Arabic date format
                return date('j F Y', $timestamp);
            default:
                // English date format
                return date('F j, Y', $timestamp);
        }
    }

    /**
     * Clear translation cache
     *
     * @return void
     */
    public static function clearCache(): void
    {
        self::$translationCache = [];
    }
}
