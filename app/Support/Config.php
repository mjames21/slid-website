<?php declare(strict_types=1);

final class Config {
    public string $siteTitle;
    public string $apiBase;
    public string $tenantId;
    public bool $debug;
    /** @var array<string,mixed> */
    public array $home;

    /** @param array<string,mixed> $data */
    public function __construct(array $data) {
        $this->siteTitle = (string)($data['site_title'] ?? 'Memeh Web');
        $this->apiBase   = rtrim((string)($data['api_base'] ?? ''), '/');
        $this->tenantId  = (string)($data['tenant_id'] ?? '1');
        $this->debug     = (bool)($data['debug'] ?? false);
        $this->home      = is_array($data['home'] ?? null) ? $data['home'] : [];
    }

    public function siteOrigin(): string {
        return (string)(preg_replace('~/api/?$~', '', $this->apiBase) ?: $this->apiBase);
    }
}
