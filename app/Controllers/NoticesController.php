<?php declare(strict_types=1);

final class NoticesController extends BaseController {
        /** @return array<int,array<string,string>> */
    private function all(): array {
        return [
            ['title'=>'Passport Application Service Notice', 'status'=>'Active', 'date'=>'2026-03-01', 'category'=>'Passports', 'ref'=>'SLID/PAS/001', 'excerpt'=>'Online applications require a valid voucher code before form submission. Ensure your documents are ready prior to booking biometrics.', 'href'=>'/passport'],
            ['title'=>'eVisa Service Advisory', 'status'=>'Active', 'date'=>'2026-02-20', 'category'=>'Visas', 'ref'=>'SLID/VIS/004', 'excerpt'=>'Applicants should apply only via the official eVisa portal. Do not share OTPs or personal data with third parties.', 'href'=>'https://www.evisa.sl'],
            ['title'=>'Unified Permit Portal – Public Guidance', 'status'=>'Active', 'date'=>'2026-02-10', 'category'=>'Permits', 'ref'=>'SLID/PER/003', 'excerpt'=>'Work and residence permit applications are processed through the Unified Permit portal. Follow the document checklist before submission.', 'href'=>'https://unifiedpermit.gov.sl'],
            ['title'=>'Travel Advisory: Document Validity', 'status'=>'Active', 'date'=>'2026-01-28', 'category'=>'Travel', 'ref'=>'SLID/TRV/002', 'excerpt'=>'Travellers are advised to ensure passports have at least 6 months validity prior to travel and comply with entry requirements.', 'href'=>'/notices'],
            ['title'=>'Border Operations Update', 'status'=>'Active', 'date'=>'2026-01-15', 'category'=>'Border Management', 'ref'=>'SLID/OPS/011', 'excerpt'=>'Operational changes may affect processing times at select points of entry. Allow additional time for clearance.', 'href'=>'/departments/operations'],
            ['title'=>'Fees & Charges (Indicative)', 'status'=>'Active', 'date'=>'2026-01-05', 'category'=>'Fees', 'ref'=>'SLID/FEE/001', 'excerpt'=>'Fees vary by service type and category. Always confirm charges at authorised channels before payment.', 'href'=>'/downloads'],
        ];
    }
public function index(): void {
        $vm = SiteVm::base($this->config);
        $vm['page_title'] = 'Notices';

        $q = trim((string)($_GET['q'] ?? ''));
        $perPage = (int)($_GET['per_page'] ?? 12);
        if ($perPage < 1) $perPage = 12;

        $vm['q'] = $q;
        $vm['per_page'] = (string)$perPage;
        $vm['tenant_id'] = (string)($_GET['tenant_id'] ?? $this->config->tenantId);
        $vm['error'] = '';

        $rows = $this->all();
        if ($q !== '') {
            $rows = array_values(array_filter($rows, static function (array $n) use ($q): bool {
                return stripos($n['title'], $q) !== false || stripos($n['category'], $q) !== false;
            }));
        }

        $rows = array_slice($rows, 0, $perPage);

        $items = [];
        foreach ($rows as $n) {
            $items[] = [
                'title' => $n['title'],
                'href' => $n['href'],
                'status' => $n['status'],
                'status_class' => ($n['status']==='Active' ? 'bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200' : 'bg-zinc-100 text-zinc-700'),
                'date' => $n['date'],
                'category' => $n['category'],
                'ref' => $n['ref'],
                'excerpt' => $n['excerpt'],

                'has_status' => ($n['status'] !== ''),
                'has_date' => ($n['date'] !== ''),
                'has_category' => ($n['category'] !== ''),
                'has_ref' => ($n['ref'] !== ''),
                'has_excerpt' => ($n['excerpt'] !== ''),
            ];
        }

        $vm['has_items'] = count($items) > 0;
        $vm['items'] = $items;

        $this->view->page('layout.html', 'notices.html', $vm);
    }
}
