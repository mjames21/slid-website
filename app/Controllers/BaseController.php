<?php declare(strict_types=1);

abstract class BaseController {
    protected Config $config;
    protected HttpClient $http;
    protected View $view;

    public function __construct(Config $config, HttpClient $http, View $view) {
        $this->config = $config;
        $this->http   = $http;
        $this->view   = $view;
    }

    /** @param array<int,string> $allowed @return array<string,string> */
    protected function query(array $allowed): array {
        $out = [];
        foreach ($allowed as $k) {
            if (!isset($_GET[$k])) continue;
            $v = trim((string)$_GET[$k]);
            if ($v === '') continue;
            $out[$k] = $v;
        }
        return $out;
    }
}
