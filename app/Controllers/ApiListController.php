<?php declare(strict_types=1);

final class ApiListController extends BaseController {
    /**
     * @param array<string,string> $query
     */
    public function renderList(string $title, string $endpoint, array $query, string $view = 'list.html'): void {
        $tenantId = (string)($query['tenant_id'] ?? $this->config->tenantId);
        $nav = NavBuilder::build($this->http, $tenantId);

        try {
            $resp = $this->http->get($endpoint, $query);
            $decoded = json_decode($resp->body, true);
            if (!is_array($decoded)) $decoded = ['raw' => $resp->body];

            $vm = array_merge(
                ['site_title' => $this->config->siteTitle, 'nav' => $nav],
                Normalize::listVm($title, $decoded, $this->config->debug),
                $query
            );

            $this->view->page('layout.html', $view, $vm);
        } catch (Throwable $e) {
            $vm = [
                'site_title' => $this->config->siteTitle,
                'nav'        => $nav,
                'page_title' => $title,
                'items'      => [],
                'json_pretty'=> '',
                'error'      => $this->config->debug ? $e->getMessage() : 'Upstream error',
            ] + $query;

            $this->view->page('layout.html', $view, $vm);
        }
    }
}
