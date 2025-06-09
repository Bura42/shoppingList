<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Represents an HTTP request.
 * Provides a simple, clean API for accessing request information.
 */
class Request
{
    public function getPath(): string
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $position = strpos($path, '?');
        if ($position === false) {
            return $path;
        }
        return substr($path, 0, $position);
    }

    public function getMethod(): string
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    /**
     * Gets the request body data (from $_POST).
     *
     * @return array The sanitized request body data.
     */
    public function getBody(): array
    {
        $body = [];
        if ($this->getMethod() === 'post') {
            foreach ($_POST as $key => $value) {
                $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }
        return $body;
    }
}
