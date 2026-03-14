<?php declare(strict_types=1);

final class NavBuilder
{
    /** @return array<int,array<string,mixed>> */
    public static function build(HttpClient $http, string $tenantId): array
    {
        // Professional, predictable navigation (avoid CMS drift).
        return SiteVm::nav();
    }
}
