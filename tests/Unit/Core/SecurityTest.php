<?php

declare(strict_types=1);

namespace CureConnect\Tests\Unit\Core;

use CureConnect\Core\Security;
use PHPUnit\Framework\TestCase;

/**
 * Security Core Class Tests
 *
 * @package CureConnect\Tests\Unit\Core
 * @author  CureConnect Team
 * @since   1.0.0
 */
class SecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Start session for CSRF token tests
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    protected function tearDown(): void
    {
        // Clean up session data after each test
        if (isset($_SESSION['csrf_token'])) {
            unset($_SESSION['csrf_token']);
        }
        parent::tearDown();
    }

    public function testEscapeHtml(): void
    {
        $input = '<script>alert("XSS")</script>';
        $expected = '&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;';

        $result = Security::escapeHtml($input);

        $this->assertEquals($expected, $result);
    }

    public function testEscapeHtmlWithSpecialCharacters(): void
    {
        $input = 'Test & "quotes" & \'apostrophes\'';
        $expected = 'Test &amp; &quot;quotes&quot; &amp; &apos;apostrophes&apos;';

        $result = Security::escapeHtml($input);

        $this->assertEquals($expected, $result);
    }

    public function testSanitizeInput(): void
    {
        $input = '  <script>alert("test")</script>  ';
        $expected = '&lt;script&gt;alert(&quot;test&quot;)&lt;/script&gt;';

        $result = Security::sanitizeInput($input);

        $this->assertEquals($expected, $result);
    }

    public function testGenerateCsrfToken(): void
    {
        $token1 = Security::generateCsrfToken();
        $token2 = Security::generateCsrfToken();

        $this->assertIsString($token1);
        $this->assertEquals(64, strlen($token1)); // 32 bytes = 64 hex chars
        $this->assertEquals($token1, $token2); // Should return same token in same session
    }

    public function testVerifyCsrfToken(): void
    {
        $token = Security::generateCsrfToken();

        $this->assertTrue(Security::verifyCsrfToken($token));
        $this->assertFalse(Security::verifyCsrfToken('invalid_token'));
        $this->assertFalse(Security::verifyCsrfToken(''));
    }

    public function testValidateEmail(): void
    {
        $this->assertTrue(Security::validateEmail('user@example.com'));
        $this->assertTrue(Security::validateEmail('test.email+tag@domain.co.uk'));

        $this->assertFalse(Security::validateEmail('invalid-email'));
        $this->assertFalse(Security::validateEmail('user@'));
        $this->assertFalse(Security::validateEmail('@domain.com'));
        $this->assertFalse(Security::validateEmail(''));
    }

    public function testValidatePhone(): void
    {
        $this->assertTrue(Security::validatePhone('+1-234-567-8900'));
        $this->assertTrue(Security::validatePhone('1234567890'));
        $this->assertTrue(Security::validatePhone('+91 98765 43210'));
        $this->assertTrue(Security::validatePhone('(555) 123-4567'));

        $this->assertFalse(Security::validatePhone('123')); // Too short
        $this->assertFalse(Security::validatePhone('abc123def')); // Contains letters
        $this->assertFalse(Security::validatePhone(''));
    }

    public function testGenerateSecurePassword(): void
    {
        $password = Security::generateSecurePassword();

        $this->assertIsString($password);
        $this->assertEquals(12, strlen($password)); // Default length

        $customPassword = Security::generateSecurePassword(20);
        $this->assertEquals(20, strlen($customPassword));
    }

    public function testHashAndVerifyPassword(): void
    {
        $password = 'testPassword123!';
        $hash = Security::hashPassword($password);

        $this->assertIsString($hash);
        $this->assertNotEquals($password, $hash);
        $this->assertTrue(Security::verifyPassword($password, $hash));
        $this->assertFalse(Security::verifyPassword('wrongPassword', $hash));
    }

    public function testGenerateSlug(): void
    {
        $this->assertEquals('hello-world', Security::generateSlug('Hello World'));
        $this->assertEquals('test-with-special-chars', Security::generateSlug('Test with @#$% Special Chars!'));
        $this->assertEquals('multiple-spaces-test', Security::generateSlug('Multiple    Spaces   Test'));
        $this->assertEquals('', Security::generateSlug('!@#$%^&*()'));
    }

    public function testCheckRateLimit(): void
    {
        $key = 'test_rate_limit_' . uniqid();

        // First attempts should pass
        for ($i = 1; $i <= 5; $i++) {
            $this->assertTrue(Security::checkRateLimit($key, 5, 60));
        }

        // 6th attempt should fail
        $this->assertFalse(Security::checkRateLimit($key, 5, 60));
    }

    /**
     * @dataProvider clientIpProvider
     */
    public function testGetClientIp(array $serverVars, string $expected): void
    {
        // Backup original $_SERVER
        $originalServer = $_SERVER;

        // Set test server variables
        $_SERVER = array_merge($_SERVER, $serverVars);

        $result = Security::getClientIp();
        $this->assertEquals($expected, $result);

        // Restore original $_SERVER
        $_SERVER = $originalServer;
    }

    public function clientIpProvider(): array
    {
        return [
            'direct_remote_addr' => [
                ['REMOTE_ADDR' => '192.168.1.100'],
                '192.168.1.100'
            ],
            'forwarded_header' => [
                [
                    'HTTP_X_FORWARDED_FOR' => '203.0.113.195',
                    'REMOTE_ADDR' => '192.168.1.100'
                ],
                '203.0.113.195'
            ],
            'client_ip_header' => [
                [
                    'HTTP_CLIENT_IP' => '203.0.113.200',
                    'REMOTE_ADDR' => '192.168.1.100'
                ],
                '203.0.113.200'
            ],
            'fallback_remote_addr' => [
                [
                    'HTTP_X_FORWARDED_FOR' => '192.168.1.1', // Private IP should be filtered
                    'REMOTE_ADDR' => '203.0.113.150'
                ],
                '203.0.113.150'
            ]
        ];
    }
}
