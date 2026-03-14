<?php declare(strict_types=1);

final class JobsController extends BaseController {
    /** @return array<int,array<string,string>> */
    private function all(): array {
        return [
            ['title'=>'Immigration Constable Recruitment', 'department'=>'HRM', 'deadline'=>'2026-02-28', 'href'=>'#'],
            ['title'=>'ICT Officer', 'department'=>'IIS', 'deadline'=>'2026-01-31', 'href'=>'#'],
            ['title'=>'Medical Assistant', 'department'=>'Medical Service', 'deadline'=>'', 'href'=>'#'],
        ];
    }

    public function index(): void {
        $vm = SiteVm::base($this->config);
        $vm['page_title'] = 'Jobs';

        $q = trim((string)($_GET['q'] ?? ''));
        $perPage = (int)($_GET['per_page'] ?? 12);
        if ($perPage < 1) $perPage = 12;

        $vm['q'] = $q;
        $vm['per_page'] = (string)$perPage;
        $vm['tenant_id'] = (string)($_GET['tenant_id'] ?? $this->config->tenantId);
        $vm['error'] = '';

        $rows = $this->all();
        if ($q !== '') {
            $rows = array_values(array_filter($rows, static function (array $j) use ($q): bool {
                return stripos($j['title'], $q) !== false || stripos($j['department'], $q) !== false;
            }));
        }

        $rows = array_slice($rows, 0, $perPage);
        $vm['items'] = array_map(static function (array $j): array {
            $dept = $j['department'];
            $deadline = $j['deadline'];

            return [
                'title' => $j['title'],
                'href' => $j['href'],
                'department' => $dept,
                'deadline' => $deadline,
                'has_department' => ($dept !== ''),
                'has_deadline' => ($deadline !== ''),
            ];
        }, $rows);

        $this->view->page('layout.html', 'jobs.html', $vm);
    }
}
