<?php declare(strict_types=1);

final class PublicationsController extends BaseController {
        /** @return array<int,array<string,string>> */
    private function all(): array {
        return [
            ['title'=>'Scheme of Service (Immigration Department)', 'date'=>'2026-02-01', 'type'=>'Policy', 'excerpt'=>'Official organisational structure and role definitions for the Immigration Department.', 'href'=>'/assets/sos-immigration-dept.pdf'],
            ['title'=>'Traveller Advisory – Document Checklist', 'date'=>'2026-01-20', 'type'=>'Guideline', 'excerpt'=>'Indicative checklist for travellers and applicants to prepare documents ahead of processing.', 'href'=>'/notices'],
            ['title'=>'Passport Application Guide (Indicative)', 'date'=>'2026-01-10', 'type'=>'Guide', 'excerpt'=>'Step-by-step overview of voucher purchase, online application, and biometrics booking.', 'href'=>'/passport'],
        ];
    }
public function index(): void {
        $vm = SiteVm::base($this->config);
        $vm['page_title'] = 'Publications';

        $q = trim((string)($_GET['q'] ?? ''));
        $perPage = (int)($_GET['per_page'] ?? 12);
        if ($perPage < 1) $perPage = 12;

        $vm['q'] = $q;
        $vm['per_page'] = (string)$perPage;
        $vm['tenant_id'] = (string)($_GET['tenant_id'] ?? $this->config->tenantId);
        $vm['error'] = '';

        $rows = $this->all();
        if ($q !== '') {
            $rows = array_values(array_filter($rows, static function (array $p) use ($q): bool {
                return stripos($p['title'], $q) !== false || stripos($p['excerpt'], $q) !== false;
            }));
        }

        $rows = array_slice($rows, 0, $perPage);

        $items = [];
        foreach ($rows as $p) {
            $date = $p['date'] ?? '';
            $excerpt = $p['excerpt'] ?? '';
            $items[] = [
                'title' => (string)($p['title'] ?? ''),
                'date' => $date,
                'mime' => (string)($p['mime'] ?? ''),
                'size' => (string)($p['size'] ?? ''),
                'excerpt' => $excerpt,
                'download_url' => (string)($p['download_url'] ?? '#'),
                'has_date' => ($date !== ''),
                'has_excerpt' => ($excerpt !== ''),
                'has_abstract' => false,
                'has_file' => true,
            ];
        }

        $vm['has_items'] = count($items) > 0;
        $vm['items'] = $items;

        $this->view->page('layout.html', 'publications.html', $vm);
    }

    /** @param array<string,string> $params */
    public function download(array $params): void {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo "Downloads are not configured in static (VM) mode.";
    }
}
