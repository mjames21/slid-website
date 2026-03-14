<?php declare(strict_types=1);

final class Normalize {
    /** @param mixed $decoded */
    public static function listVm(string $title, $decoded, bool $debug): array {
        $items = [];

        if (is_array($decoded) && isset($decoded['data']) && is_array($decoded['data'])) {
            foreach ($decoded['data'] as $row) {
                if (!is_array($row)) continue;

                $pairs = [];
                foreach ($row as $k => $v) {
                    if (is_array($v) || is_object($v)) continue;
                    $pairs[] = ['key' => (string)$k, 'value' => (string)$v];
                }

                $items[] = [
                    'title' => (string)($row['title'] ?? $row['name'] ?? $row['slug'] ?? 'Item'),
                    'pairs' => $pairs,
                ];
            }
        }

        return [
            'page_title'  => $title,
            'items'       => $items,
            'json_pretty' => ($debug ? json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : ''),
        ];
    }

    public static function toAbs(string $siteOrigin, ?string $urlOrPath): ?string {
        $urlOrPath = $urlOrPath !== null ? trim($urlOrPath) : null;
        if ($urlOrPath === null || $urlOrPath === '') return null;

        if (preg_match('~^https?://~i', $urlOrPath)) return $urlOrPath;
        if (str_starts_with($urlOrPath, '/')) return rtrim($siteOrigin, '/') . $urlOrPath;
        return rtrim($siteOrigin, '/') . '/' . ltrim($urlOrPath, '/');
    }
}
