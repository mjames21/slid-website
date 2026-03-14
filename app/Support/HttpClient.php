<?php declare(strict_types=1);

final class HttpClient {
    private Config $config;

    public function __construct(Config $config) {
        $this->config = $config;
    }

    /** @param array<string,string> $query */
    public function get(string $path, array $query = []): object {
        $base = rtrim($this->config->apiBase, '/');
        $url  = $base . '/' . ltrim($path, '/');

        if (!empty($query)) $url .= '?' . http_build_query($query);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        ]);

        $body = curl_exec($ch);
        $err  = curl_error($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false) throw new RuntimeException('HTTP error: ' . $err);

        return (object)[
            'status' => $code,
            'body'   => (string)$body,
            'url'    => $url,
        ];
    }
}
