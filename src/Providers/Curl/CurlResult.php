<?php

namespace Providers\Curl;


class CurlResult {

    private $body;

    private $headers;

    private $cookies;

    public function __construct($body, $headers) {
        $this->body = $body;
        $this->headers = $headers;
        $this->cookies = $this->parseCookies($headers);
    }

    private function parseCookies($headers) {
        $matches = [];
        preg_match_all("/Set-Cookie: (.*?)=(.*?);/i", $headers, $matches);

        $cookies = [];

        foreach ($matches[1] as $key => $value) {
            $cookies[$value] = $matches[2][$key];
        };

        return $cookies;
    }

    public function getBody() {
        return $this->body;
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function getCookies() {
        return $this->cookies;
    }

} 