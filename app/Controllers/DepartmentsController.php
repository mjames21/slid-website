<?php declare(strict_types=1);

final class DepartmentsController extends BaseController
{
    /** @return array<string,array<string,mixed>> */
    private function catalog(): array
    {
        return [
            'office-of-the-chief-immigration-officer' => [
                'name' => 'Office of the Chief Immigration Officer (CIO)',
                'badge' => 'CIO',
                'category' => 'Leadership',
                'mandate' => 'Provides overall strategic leadership and direction for the Sierra Leone Immigration Department, ensuring effective delivery of immigration services and national security outcomes.',
                'functions' => [
                    'Provide strategic leadership and oversight for immigration administration and service delivery.',
                    'Ensure effective coordination of operations, border control, and enforcement activities.',
                    'Strengthen collaboration with national and international stakeholders on migration and border security.',
                ],
            ],
            'office-of-the-deputy-chief-immigration-officer' => [
                'name' => 'Office of the Deputy Chief Immigration Officer (DCIO)',
                'badge' => 'DCIO',
                'category' => 'Leadership',
                'mandate' => 'Coordinates implementation of leadership directives and supports oversight of immigration operations, administration, and service delivery across the Department.',
                'functions' => [
                    'Support the CIO in leadership, coordination, and monitoring of departmental performance.',
                    'Oversee implementation of policies and operational directives across directorates and offices.',
                    'Strengthen coordination with regional and field offices and partner agencies.',
                ],
            ],
            'operations' => [
                'name' => 'Directorate of Operations',
                'badge' => 'OPS',
                'category' => 'Directorate',
                'mandate' => 'Coordinates and provides strategic leadership for intelligence gathering, border threat identification and investigation, enforcement consequences, management of entry and exit points, and facilitation and regulation of foreign nationals and visas.',
                'functions' => [
                    'Lead policies and programmes for effective management of designated entry and exit points.',
                    'Coordinate intelligence gathering to identify and investigate threats to border security.',
                    'Oversee enforcement actions in line with the severity of immigration infractions.',
                    'Facilitate and regulate the entry, stay and exit of foreign nationals, including visa issuance processes.',
                ],
            ],
            'passport' => [
                'name' => 'Directorate of Passport',
                'badge' => 'PASS',
                'category' => 'Directorate',
                'mandate' => 'Formulates policies, procedures and strategies to ensure effective administration, control and issuance of passports to Sierra Leoneans, and oversees consistent, timely processing of passport applications.',
                'functions' => [
                    'Manage day-to-day operations of passport services and address public enquiries and concerns.',
                    'Develop and review operational manuals and procedures for passport issuance.',
                    'Oversee timely processing and approval workflows for passport applications, including foreign mission submissions.',
                    'Coordinate reporting and statistics related to passport operations.',
                ],
            ],
            'corporate-strategy-and-policy' => [
                'name' => 'Directorate of Corporate Strategy & Policy',
                'badge' => 'CSP',
                'category' => 'Directorate',
                'mandate' => 'Provides corporate strategy, policy coordination and research to strengthen planning, performance, and quality assurance across the Department.',
                'functions' => [
                    'Coordinate corporate strategy, planning and performance monitoring.',
                    'Support policy development, harmonisation and implementation guidance.',
                    'Lead research, reporting, and quality assurance for continuous improvement.',
                ],
            ],
            'administration-and-finance' => [
                'name' => 'Directorate of Administration & Finance',
                'badge' => 'A&F',
                'category' => 'Directorate',
                'mandate' => 'Provides administrative, human resource and financial management services to ensure compliance with policies and guidelines, and to support efficient operations of SLID.',
                'functions' => [
                    'Provide administrative support, human resource coordination and staff welfare services.',
                    'Support budgeting, financial management and accountability processes.',
                    'Coordinate procurement, assets, logistics and general administration support.',
                ],
            ],
        ];
    }

    public function index(): void
    {
        $vm = SiteVm::base($this->config);
        $vm['page_title'] = 'Departments';
        $vm['error'] = '';

        $catalog = $this->catalog();

        $leadershipKeys = [
            'office-of-the-chief-immigration-officer',
            'office-of-the-deputy-chief-immigration-officer',
        ];

        $vm['leadership'] = array_map(static function (string $k) use ($catalog): array {
            $d = $catalog[$k];

            return [
                'name' => (string) $d['name'],
                'badge' => (string) $d['badge'],
                'mandate' => (string) $d['mandate'],
                'href' => '/departments/' . $k,
            ];
        }, $leadershipKeys);

        $directorateKeys = array_values(array_filter(
            array_keys($catalog),
            static fn (string $k): bool => !in_array($k, $leadershipKeys, true)
        ));

        $vm['directorates'] = array_map(static function (string $k) use ($catalog): array {
            $d = $catalog[$k];

            return [
                'name' => (string) $d['name'],
                'category' => (string) ($d['category'] ?? 'Directorate'),
                'mandate' => (string) $d['mandate'],
                'href' => '/departments/' . $k,
            ];
        }, $directorateKeys);

        $vm['breadcrumbs'] = [
            ['href' => '/home', 'label' => 'Home'],
            ['href' => '/departments', 'label' => 'Departments'],
        ];

        $this->view->page('layout.html', 'departments.html', $vm);
    }

    /** @param array{slug:string} $params */
    public function show(array $params): void
    {
        $slug = (string) ($params['slug'] ?? '');
        $catalog = $this->catalog();

        if (!isset($catalog[$slug])) {
            http_response_code(404);
            echo '404 Not Found';
            return;
        }

        $vm = SiteVm::base($this->config);
        $d = $catalog[$slug];

        $vm['name'] = (string) $d['name'];
        $vm['mandate'] = (string) $d['mandate'];

        $functions = $d['functions'] ?? [];
        $vm['has_functions'] = is_array($functions) && count($functions) > 0;
        $vm['functions'] = $vm['has_functions'] ? $functions : [];

        $vm['evisa_url'] = 'https://www.evisa.sl';
        $vm['permit_url'] = 'https://unifiedpermit.gov.sl';

        $vm['breadcrumbs'] = [
            ['href' => '/home', 'label' => 'Home'],
            ['href' => '/departments', 'label' => 'Departments'],
            ['href' => '/departments/' . $slug, 'label' => (string) $d['name']],
        ];

        $this->view->page('layout.html', 'department_show.html', $vm);
    }
}