<?php declare(strict_types=1);

final class EventsController extends BaseController {
        /** @return array<int,array<string,string>> */
    private function all(): array {
        return [
            ['title'=>'Passport Outreach & Enrolment Support Desk', 'date'=>'2026-03-18', 'location'=>'Freetown', 'excerpt'=>'Support desk for applicants to understand voucher purchase, online forms, and biometric appointments.', 'href'=>'/passport'],
            ['title'=>'Stakeholder Engagement on Border Management', 'date'=>'2026-04-02', 'location'=>'Bo', 'excerpt'=>'Engagement with border communities and stakeholders on coordinated border management and traveller facilitation.', 'href'=>'/departments/operations'],
            ['title'=>'Unified Permit Portal User Clinic', 'date'=>'2026-04-15', 'location'=>'Kenema', 'excerpt'=>'Guidance session for employers and applicants on using the Unified Permit portal and document requirements.', 'href'=>'https://unifiedpermit.gov.sl'],
            ['title'=>'eVisa Service Awareness Webinar', 'date'=>'2026-05-07', 'location'=>'Online', 'excerpt'=>'Official webinar on applying through the eVisa portal and avoiding fraud.', 'href'=>'https://www.evisa.sl'],
        ];
    }
public function index(): void {
        $vm = SiteVm::base($this->config);
        $vm['page_title'] = 'Events';

        $q = trim((string)($_GET['q'] ?? ''));
        $perPage = (int)($_GET['per_page'] ?? 12);
        if ($perPage < 1) $perPage = 12;

        $tab = trim((string)($_GET['tab'] ?? 'upcoming'));
        if (!in_array($tab, ['upcoming','past','all'], true)) $tab = 'upcoming';

        $vm['q'] = $q;
        $vm['per_page'] = (string)$perPage;
        $vm['tenant_id'] = (string)($_GET['tenant_id'] ?? $this->config->tenantId);
        $vm['tab'] = $tab;

        $vm['tab_upcoming_href'] = '/events?tab=upcoming';
        $vm['tab_past_href'] = '/events?tab=past';
        $vm['tab_all_href'] = '/events?tab=all';

        $vm['is_tab_upcoming'] = ($tab === 'upcoming');
        $vm['is_tab_past'] = ($tab === 'past');
        $vm['is_tab_all'] = ($tab === 'all');

        $vm['error'] = '';

        $today = date('Y-m-d');
        $rows = $this->all();

        if ($q !== '') {
            $rows = array_values(array_filter($rows, static function (array $e) use ($q): bool {
                return stripos($e['title'], $q) !== false || stripos($e['location'], $q) !== false;
            }));
        }

        $upcoming = [];
        $past = [];

        foreach ($rows as $e) {
            if ($e['date'] >= $today) $upcoming[] = $this->decorate($e);
            else $past[] = $this->decorate($e);
        }

        $upcoming = array_slice($upcoming, 0, $perPage);
        $past = array_slice($past, 0, $perPage);

        $vm['show_upcoming'] = ($tab === 'upcoming' || $tab === 'all');
        $vm['show_past'] = ($tab === 'past' || $tab === 'all');

        $vm['has_upcoming_items'] = count($upcoming) > 0;
        $vm['has_past_items'] = count($past) > 0;

        $vm['upcoming_items'] = $upcoming;
        $vm['past_items'] = $past;

        $this->view->page('layout.html', 'events.html', $vm);
    }

    /** @param array<string,string> $e @return array<string,string|bool> */
    private function decorate(array $e): array {
        $date = $e['date'];
        $parts = explode('-', $date);
        $y = $parts[0] ?? '';
        $m = $parts[1] ?? '';
        $d = $parts[2] ?? '';

        return [
            'title' => $e['title'],
            'date' => $date,
            'year' => $y,
            'mon' => $m,
            'day' => $d,
            'location' => $e['location'],
            'url' => $e['url'],
            'has_date' => ($date !== ''),
            'has_location' => ($e['location'] !== ''),
            'has_url' => ($e['url'] !== ''),
        ];
    }
}
