<?php declare(strict_types=1);

final class ArticlesController extends BaseController {

     public function index(): void
    {
        $tenantId = (string)($_GET['tenant_id'] ?? $this->config->tenantId);
        $nav      = NavBuilder::build($this->http, $tenantId);
        $origin   = $this->config->siteOrigin();

        $q       = trim((string)($_GET['q'] ?? ''));
        $perPage = trim((string)($_GET['per_page'] ?? '12'));
        $page    = (int)($_GET['page'] ?? 1);
        if ($page < 1) { $page = 1; }

        $items = [];
        $error = '';

        // Pagination VM
        $hasPrev   = $page > 1;
        $hasNext   = false;
        $prevHref  = '';
        $nextHref  = '';
        $pageLabel = 'Page ' . $page;

        // helper to build local links while preserving params
        $buildHref = static function (int $toPage) use ($tenantId, $q, $perPage): string {
            $params = array_filter([
                'tenant_id' => $tenantId,
                'q'         => $q,
                'per_page'  => $perPage,
                'page'      => (string)$toPage,
            ], static fn($v) => $v !== '' && $v !== null);

            return '/articles' . (count($params) ? ('?' . http_build_query($params)) : '');
        };

        try {
            $resp = $this->http->get('/articles', array_filter([
                'tenant_id'  => $tenantId,
                'status'     => 'published',
                'q'          => $q,
                'per_page'   => $perPage,
                'page'       => (string)$page,
                'sort_field' => 'published_at',
                'sort_dir'   => 'desc',
                'include'    => 'feature,category',
            ], static fn($v) => $v !== ''));

            $decoded = json_decode($resp->body, true);

            $rows = (is_array($decoded) && isset($decoded['data']) && is_array($decoded['data']))
                ? $decoded['data']
                : [];

            $items = array_map(static function (array $a) use ($origin) {
                $feature = is_array($a['feature'] ?? null) ? $a['feature'] : null;

                $img = null;
                if ($feature) {
                    $img = Normalize::toAbs($origin, (string)($feature['url'] ?? ''))
                        ?: Normalize::toAbs($origin, (string)($feature['path'] ?? ''));
                }

                $slug = (string)($a['slug'] ?? '');
                $publishedAt = (string)($a['published_at'] ?? '');

                $category = '';
                if (isset($a['category']) && is_array($a['category'])) {
                    $category = (string)($a['category']['name'] ?? '');
                }

                return [
                    'href'      => $slug !== '' ? ('/articles/' . $slug) : '/articles',
                    'title'     => (string)($a['title'] ?? ''),
                    'excerpt'   => (string)($a['summary'] ?? ''),
                    'date'      => $publishedAt ? substr($publishedAt, 0, 10) : '',
                    'category'  => $category,
                    'image_url' => $img,
                ];
            }, $rows);

            // ---- Pagination detection (supports common formats) ----
            $meta  = (isset($decoded['meta'])  && is_array($decoded['meta']))  ? $decoded['meta']  : [];
            $links = (isset($decoded['links']) && is_array($decoded['links'])) ? $decoded['links'] : [];

            // Laravel-style: meta.current_page, meta.last_page
            $currentPage = isset($meta['current_page']) ? (int)$meta['current_page'] : $page;
            $lastPage    = isset($meta['last_page']) ? (int)$meta['last_page'] : 0;

            if ($lastPage > 0) {
                $hasPrev = $currentPage > 1;
                $hasNext = $currentPage < $lastPage;
                $pageLabel = "Page {$currentPage} of {$lastPage}";

                if ($hasPrev) $prevHref = $buildHref($currentPage - 1);
                if ($hasNext) $nextHref = $buildHref($currentPage + 1);
            } else {
                // Fallback: links.next / links.prev presence
                $apiNext = is_string($links['next'] ?? null) ? trim((string)$links['next']) : '';
                $apiPrev = is_string($links['prev'] ?? null) ? trim((string)$links['prev']) : '';

                // Some APIs use meta.next_page_url / meta.prev_page_url
                if ($apiNext === '' && isset($meta['next_page_url']) && is_string($meta['next_page_url'])) {
                    $apiNext = trim((string)$meta['next_page_url']);
                }
                if ($apiPrev === '' && isset($meta['prev_page_url']) && is_string($meta['prev_page_url'])) {
                    $apiPrev = trim((string)$meta['prev_page_url']);
                }

                $hasPrev = $page > 1;
                $hasNext = ($apiNext !== '');

                if ($hasPrev) $prevHref = $buildHref($page - 1);
                if ($hasNext) $nextHref = $buildHref($page + 1);
            }

            // If still not set, set defaults
            if ($prevHref === '' && $hasPrev) $prevHref = $buildHref($page - 1);
            if ($nextHref === '' && $hasNext) $nextHref = $buildHref($page + 1);

        } catch (Throwable $e) {
            $error = ($this->config->debug ?? false) ? $e->getMessage() : 'Upstream error';
        }

        $vm = [
            'site_title' => $this->config->siteTitle,
            'page_title' => 'News',
            'nav'        => $nav,

            'q'          => $q,
            'per_page'   => $perPage,
            'tenant_id'  => $tenantId,

            'items'      => $items,
            'error'      => $error,

            // Pagination VM
            'page_label' => $pageLabel,
            'has_prev'   => $hasPrev ? '1' : '',
            'has_next'   => $hasNext ? '1' : '',
            'prev_href'  => $prevHref,
            'next_href'  => $nextHref,
        ];

        $this->view->page('layout.html', 'articles.html', $vm);
    }

 /** @param array{slug:string} $params */
public function show(array $params): void
{
    $slug = trim((string)($params['slug'] ?? ''));
    if ($slug === '') {
        http_response_code(404);
        echo "404 Not Found";
        return;
    }

    $tenantId = (string)($_GET['tenant_id'] ?? $this->config->tenantId);
    $nav      = NavBuilder::build($this->http, $tenantId);
    $origin   = $this->config->siteOrigin();

    $error   = '';
    $article = null;

    try {
        $resp = $this->http->get('/articles/' . rawurlencode($slug), array_filter([
            'tenant_id' => $tenantId,
            'include'   => 'feature,category,tags',
        ], static fn($v) => $v !== ''));

        $decoded = json_decode($resp->body, true);
        $a = (is_array($decoded) && isset($decoded['data']) && is_array($decoded['data']))
            ? $decoded['data']
            : null;

        if (!$a) {
            http_response_code(404);
            echo "404 Not Found";
            return;
        }

        // Feature image
        $feature = is_array($a['feature'] ?? null) ? $a['feature'] : null;
        $img = null;
        if ($feature) {
            $img = Normalize::toAbs($origin, (string)($feature['url'] ?? ''))
                ?: Normalize::toAbs($origin, (string)($feature['path'] ?? ''));
        }

        // Meta
        $publishedAt = (string)($a['published_at'] ?? '');
        $date = $publishedAt ? substr($publishedAt, 0, 10) : '';

        $category = '';
        if (isset($a['category']) && is_array($a['category'])) {
            $category = (string)($a['category']['name'] ?? '');
        }

        // Tags
        $tags = [];
        if (isset($a['tags']) && is_array($a['tags'])) {
            foreach ($a['tags'] as $t) {
                if (!is_array($t)) continue;
                $name = trim((string)($t['name'] ?? ''));
                if ($name !== '') $tags[] = ['name' => $name];
            }
        }

        // ✅ Body html (robust: supports strings + nested arrays)
        $bodyHtml = '';

        $pickString = static function ($v): string {
            return (is_string($v) && trim($v) !== '') ? $v : '';
        };

        // 1) common direct keys
        foreach (['body_html', 'content_html', 'html'] as $k) {
            $bodyHtml = $pickString($a[$k] ?? null);
            if ($bodyHtml !== '') break;
        }

        // 2) nested: body.html / content.html / body.value etc
        if ($bodyHtml === '') {
            foreach (['body', 'content'] as $k) {
                $v = $a[$k] ?? null;

                // body/content might be a string
                $bodyHtml = $pickString($v);
                if ($bodyHtml !== '') break;

                // or body/content might be an array
                if (is_array($v)) {
                    foreach (['body_html', 'content_html', 'html', 'value', 'text'] as $kk) {
                        $bodyHtml = $pickString($v[$kk] ?? null);
                        if ($bodyHtml !== '') break 2;
                    }
                }
            }
        }

        // 3) fallback: plain text -> convert to safe HTML
        if ($bodyHtml === '') {
            $plain = '';
            foreach (['summary', 'excerpt', 'body', 'content'] as $k) {
                $vv = $a[$k] ?? null;
                if (is_string($vv) && trim($vv) !== '') { $plain = $vv; break; }
            }
            if ($plain !== '') {
                $bodyHtml = nl2br(htmlspecialchars($plain, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
            }
        }

        
        // Attachments (robust: supports common API shapes)
        $attachments = [];

        $toAbs = static function (string $u) use ($origin): string {
            $u = trim($u);
            if ($u === '') return '';
            // Normalize::toAbs returns '' for invalid; fallback to raw
            $abs = Normalize::toAbs($origin, $u);
            return $abs !== '' ? $abs : $u;
        };

        $pushAttachment = static function (array $row) use (&$attachments, $toAbs): void {
            $url = '';
            foreach (['download_url','url','href','file_url','pdf_url','link'] as $k) {
                if (isset($row[$k]) && is_string($row[$k]) && trim($row[$k]) !== '') { $url = (string)$row[$k]; break; }
            }
            if ($url === '' && isset($row['path']) && is_string($row['path'])) $url = (string)$row['path'];
            $href = $toAbs($url);
            if ($href === '') return;

            $label = '';
            foreach (['title','name','original_name','filename','file_name','label'] as $k) {
                if (isset($row[$k]) && is_string($row[$k]) && trim($row[$k]) !== '') { $label = (string)$row[$k]; break; }
            }
            if ($label === '') $label = 'Download attachment';

            $attachments[] = [
                'href'  => $href,
                'label' => $label,
            ];
        };

        // 1) array collections
        foreach (['attachments','files','documents'] as $k) {
            if (!isset($a[$k]) || !is_array($a[$k])) continue;
            foreach ($a[$k] as $row) {
                if (!is_array($row)) continue;
                $pushAttachment($row);
            }
        }

        // 2) single attachment object
        foreach (['attachment','file','document'] as $k) {
            if (!isset($a[$k]) || !is_array($a[$k])) continue;
            $pushAttachment($a[$k]);
        }

        // 3) direct url fields
        foreach (['download_url','file_url','pdf_url'] as $k) {
            $u = isset($a[$k]) && is_string($a[$k]) ? trim((string)$a[$k]) : '';
            if ($u === '') continue;
            $attachments[] = ['href' => $toAbs($u), 'label' => 'Download attachment'];
        }

        // de-dup by href
        if (count($attachments) > 1) {
            $seen = [];
            $attachments = array_values(array_filter($attachments, static function (array $x) use (&$seen): bool {
                $h = (string)($x['href'] ?? '');
                if ($h === '' || isset($seen[$h])) return false;
                $seen[$h] = true;
                return true;
            }));
        }

$article = [
            'title'     => (string)($a['title'] ?? ''),
            'excerpt'   => (string)($a['summary'] ?? ''),
            'date'      => $date,
            'category'  => $category,
            'image_url' => $img,
           // 'body_html' => $bodyHtml,

            'has_tags'  => count($tags) > 0,
            'tags'      => $tags,
            'has_body'  => trim($bodyHtml) !== '',
            'has_attachments' => count($attachments) > 0,
            'attachments' => $attachments,
        ];

    } catch (Throwable $e) {
        $error = ($this->config->debug ?? false) ? $e->getMessage() : 'Upstream error';
    }

    $vm = [
        'site_title'  => $this->config->siteTitle,
        'page_title'  => $article ? ($article['title'] ?: 'Article') : 'Article',
        'nav'         => $nav,

        'has_article' => (bool)$article,
        'article'     => $article ? [$article] : [],
        'body_html'    => $bodyHtml,

        'error'       => $error,
        'back_href'   => '/articles',
    ];
    $this->view->page('layout.html', 'article_show.html', $vm);
}


}
