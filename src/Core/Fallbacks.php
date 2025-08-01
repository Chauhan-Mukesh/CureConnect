<?php

declare(strict_types=1);

/**
 * Simple HTTP Foundation Classes (Fallbacks)
 *
 * @package CureConnect\Core
 * @author  CureConnect Team
 * @since   1.0.0
 */

namespace CureConnect\Core;

/**
 * Simple Request class as fallback for Symfony HttpFoundation
 */
class SimpleRequest
{
    private array $query;
    private array $request;
    private array $server;
    private string $method;
    private string $pathInfo;

    public function __construct()
    {
        $this->query = $_GET;
        $this->request = $_POST;
        $this->server = $_SERVER;
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->pathInfo = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    }

    public static function createFromGlobals(): self
    {
        return new self();
    }

    public function isMethod(string $method): bool
    {
        return strtoupper($this->method) === strtoupper($method);
    }

    public function getPathInfo(): string
    {
        return $this->pathInfo;
    }

    public function getUri(): string
    {
        $scheme = isset($_SERVER['HTTPS']) ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        return $scheme . '://' . $host . $uri;
    }

    public function get(string $key, $default = null)
    {
        return $this->query[$key] ?? $default;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public $request_data;

    public function __get($name)
    {
        if ($name === 'request') {
            return (object)['get' => function($key, $default = null) {
                return $_POST[$key] ?? $default;
            }];
        }
        return null;
    }
}

/**
 * Simple Response class as fallback for Symfony HttpFoundation
 */
class SimpleResponse
{
    private string $content;
    private int $statusCode;
    private array $headers;

    public function __construct(string $content = '', int $statusCode = 200, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        echo $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}

/**
 * Simple YAML parser as fallback for Symfony YAML
 */
class SimpleYaml
{
    public static function parseFile(string $filename): array
    {
        if (!file_exists($filename)) {
            return [];
        }

        $content = file_get_contents($filename);
        return self::parse($content);
    }

    public static function parse(string $content): array
    {
        $lines = explode("\n", $content);
        $result = [];
        $currentKey = null;
        $indent = 0;

        foreach ($lines as $line) {
            $line = rtrim($line);
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }

            $currentIndent = strlen($line) - strlen(ltrim($line));
            $line = trim($line);

            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);
                $key = trim($key);
                $value = trim($value);

                if ($currentIndent === 0) {
                    $currentKey = $key;
                    if (!empty($value)) {
                        $result[$key] = self::parseValue($value);
                    } else {
                        $result[$key] = [];
                    }
                } elseif ($currentKey && $currentIndent === 2) {
                    if (!empty($value)) {
                        $result[$currentKey][$key] = self::parseValue($value);
                    } else {
                        $result[$currentKey][$key] = [];
                    }
                }
            }
        }

        return $result;
    }

    private static function parseValue(string $value)
    {
        $value = trim($value, '"\'');

        if ($value === 'true') return true;
        if ($value === 'false') return false;
        if ($value === 'null') return null;
        if (is_numeric($value)) return is_float($value + 0) ? (float)$value : (int)$value;

        return $value;
    }
}
