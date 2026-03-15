<?php
// FILE: app/Controllers/HomeController.php
declare(strict_types=1);

final class HomeController extends BaseController
{
    private const IMMIGRATION_OFFICES_URL = '/departments';
    private const EVISA_URL = 'https://www.evisa.sl';
    private const UNIFIED_PERMIT_URL = 'https://unifiedpermit.gov.sl';
    private const HOTLINE_NUMBER = '019';
    
    private const PASSPORT_VOUCHER_URL = 'http://127.0.0.1:8000/passport/voucher';
    private const PASSPORT_START_URL = 'http://127.0.0.1:8000/passport/start';
    private const CIO_NAME = 'Dr. Moses Tiffa Baio, Esq';

    // Optional top-bar links; set to your real domains when available.
    private const WEBMAIL_URL = '#';
    private const GPS_TRACKER_URL = '#';

    /**
     * Upload your PDF here (on YOUR domain):
     * public_html/assets/notices/convicted-drug-matters-as-at-29-jan-2026-public-redacted.pdf
     *
     * Public URL:
     * /assets/notices/convicted-drug-matters-as-at-29-jan-2026-public-redacted.pdf
     */
    private const DRUG_PDF_URL = '/assets/notices/convicted-drug-matters-as-at-29-jan-2026-public-redacted.pdf';

    public function index(): void
    {
        // ==========================
        // NAV + API CONTEXT (same pattern as your inspiration)
        // ==========================
        $tenantId = (string)($_GET['tenant_id'] ?? $this->config->tenantId);
        $nav = NavBuilder::build($this->http, $tenantId);
        $origin = $this->config->siteOrigin();

        // ==========================
        // Poster-first notices
        // ==========================
        $wantedPhotos = [
            'https://memehcms.com/storage/uploads/6789132a-25d7-4291-a0ee-f0f6b82f3692/2026/01/glYIwPtO4osz5K54dktW3xSe5BF7VJZoHUGWW741.jpg',
            '/assets/notices/wanted-01.jpg',
            '/assets/notices/wanted-02.jpg',
            '/assets/notices/wanted-03.jpg',
        ];

        $missingPhotos = [
            '/assets/notices/missing-01.jpg',
            '/assets/notices/missing-02.jpg',
            '/assets/notices/missing-03.jpg',
        ];

        $stolenPhotos = [
            '/assets/notices/stolen-01.jpg',
            '/assets/notices/stolen-02.jpg',
            '/assets/notices/stolen-03.jpg',
        ];

        $wantedItems = $this->buildPosterNotices(
            type: 'Wanted',
            photos: $wantedPhotos,
            defaultTitlePrefix: 'WANTED NOTICE',
            defaultDetail: 'If you recognize this person, do not engage. Submit verified information through official channels.'
        );

        $missingItems = $this->buildPosterNotices(
            type: 'Missing',
            photos: $missingPhotos,
            defaultTitlePrefix: 'MISSING PERSON',
            defaultDetail: 'If you have verified information, please report immediately through official channels.'
        );

        $stolenVehicles = $this->buildVehicleNotices(
            photos: $stolenPhotos,
            defaultMake: 'Vehicle (Poster)',
            defaultStatus: 'Reported'
        );

        // ==========================
        // PDF existence check (filesystem)
        // ==========================
        $docRoot = rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
        $drugPdfFsPath = $docRoot . self::DRUG_PDF_URL;
        $drugPdfExists = is_file($drugPdfFsPath);

        $drugPdfTitle = 'CONVICTED DRUG MATTERS — As at 29 January 2026 (Public Copy, Redacted)';
        $drugPdfUpdated = 'Feb 2026';

        // ==========================
        // Latest Articles from API (same pattern as your inspiration)
        // ==========================
        $articles = [];
        try {
            $resp = $this->http->get('/articles', array_filter([
                'tenant_id'  => $tenantId,
                'status'     => 'published',
                'per_page'   => '4',
                'sort_field' => 'published_at',
                'sort_dir'   => 'desc',
                // include feature for image, and category/tags (your screenshot “b”)
                'include'    => 'feature,category,tags',
            ], static fn($v) => $v !== ''));

            $decoded = json_decode($resp->body, true);
            $rows = (is_array($decoded) && isset($decoded['data']) && is_array($decoded['data']))
                ? $decoded['data']
                : [];

            $articles = array_map(static function (array $a) use ($origin) {
                // feature image
                $feature = is_array($a['feature'] ?? null) ? $a['feature'] : null;

                $img = null;
                if ($feature) {
                    $img = Normalize::toAbs($origin, (string)($feature['url'] ?? ''))
                        ?: Normalize::toAbs($origin, (string)($feature['path'] ?? ''));
                }

                // sometimes APIs also provide image_url directly
                if (!$img) {
                    $img = Normalize::toAbs($origin, (string)($a['image_url'] ?? ''))
                        ?: Normalize::toAbs($origin, (string)($a['image'] ?? ''));
                }

                $slug = (string)($a['slug'] ?? '');
                $publishedAt = (string)($a['published_at'] ?? '');

                // Some APIs provide href already (your screenshot shows "href")
                $apiHref = (string)($a['href'] ?? '');

                $href =
                    $apiHref !== '' ? $apiHref :
                    ($slug !== '' ? ('/articles/' . $slug) : '/articles');

                return [
                    'slug'      => $slug,
                    'href'      => $href,
                    'title'     => (string)($a['title'] ?? ''),
                    'excerpt'   => (string)($a['summary'] ?? ''),
                    'date'      => $publishedAt ? substr($publishedAt, 0, 10) : '',
                    'image_url' => $img,
                ];
            }, $rows);
        } catch (Throwable $e) {
            // ignore - homepage should still render even if API fails
        }

        // ==========================
        // CIO message
        // ==========================
        $welcomeFull = [
            'All of us can honestly accept that in recent times our Immigration Force has suffered a massive decline in public confidence of our service delivery. Therefore, against this difficult backdrop, our collective challenge is on how to provide an excellent and improved service delivery for the citizens of this country.',
            'I have a desire to uplift the Force from its current abysmal status to one of envy. But would that stop me from being real? The answer is, no! I owe a duty to God Almighty, the nation and my conscience to be realistic.',
            'In spite of the challenges I inherited, I am confident that with collective responsibility in partnership with our local communities and the established Immigration Partnership Boards nationwide, we can mobilize our efforts to restore Public Confidence in the Immigration.',
            'On this note, I want to express my profound thanks and appreciation to you all for your selfless service to the nation under difficult circumstances and to assert that my focus will be primarily on the Work Force as it is the most important asset for Organizational Development.',
            'I have realized that the general morale within the Force has been greatly dampened for obvious reasons.',
            'With the support of the entire Executive Management Board of the Immigration, we shall collectively endeavor to create conducive working environments for all Immigration Officers, geared towards professionalism in their respective policing tasks.',
                  ];

        // ==========================
        // View Model
        // ==========================
        $vm = [
            'site_title' => 'Sierra Leone Immigration Department',
            'page_title' => 'Home',

            // nav from API builder (inspiration pattern)
            'nav' => $nav,

            // top bar links
            'webmail_url' => self::WEBMAIL_URL,
            //'gps_tracker_url' => self::GPS_TRACKER_URL,

            // emergency
            'emergency_number' => self::HOTLINE_NUMBER,

            // bulletin / PDF
            'drug_pdf_title' => $drugPdfTitle,
            'drug_pdf_updated' => $drugPdfUpdated,
            'drug_pdf_url' => self::DRUG_PDF_URL,
            'drug_pdf_exists' => $drugPdfExists ? '1' : '',

           // hero
            'hero_slides' => [
                [
                    'kicker' => 'Passport Applications',
                    'title'  => 'Passport Applications — Apply Online, Book Biometrics',
                    'text'   => 'Buy a voucher, complete your application online, then book biometrics for passport issuance and renewal.',
                    'image'  => '/assets/hero-2.jpg',
                    'cta1_label' => 'Buy Voucher (Passport)',
                    'cta1_href'  => '/passport',
                    'cta2_label' => 'Requirements & Fees',
                    'cta2_href'  => '/fees',
                ],
                [
                    'kicker' => 'Public Information Portal',
                    'title'  => 'Passport, Visas & Permits — Online Services',
                    'text'   => 'Apply for passports, visas, and permits online. Get clear requirements, fees, and official updates from the Immigration Department.',
                    'image'  => '/assets/hero-1.jpg',
                    'cta1_label' => 'Apply eVisa',
                    'cta1_href'  => self::EVISA_URL,
                    'cta2_label' => 'Permits Portal',
                    'cta2_href'  => self::UNIFIED_PERMIT_URL,
                ],
                
                [
                    'kicker' => 'Border Management',
                    'title'  => 'Discipline, Professionalism & Zero Tolerance to Corruption',
                    'text'   => 'Priorities: improved service delivery, discipline, welfare, and maintaining professionalism across the Force.',
                    'image'  => '/assets/hero-3.jpg',
                    'cta1_label' => 'Open FAQ',
                    'cta1_href'  => '/faq',
                    'cta2_label' => 'Report an Immigration Issue',
                    'cta2_href'  => '/report',
                ],
            ],

            // quick actions
            'quick_actions' => [
                [
                    'title' => 'Passport Online',
                    'value' => 'Apply Now',
                    'hint'  => 'Start your passport application online.',
                    'href'  => self::PASSPORT_START_URL,
                    'external' => true,
                    'icon'  => 'passport',
                    'accent'=> '#005028',
                ],
                [
                    'title' => 'Buy Voucher',
                    'value' => 'Passport Voucher',
                    'hint'  => 'Purchase a voucher to begin your passport application.',
                    'href'  => self::PASSPORT_VOUCHER_URL,
                    'external' => true,
                    'icon'  => 'voucher',
                    'accent'=> '#005028',
                ],
                [
                    'title' => 'Apply eVisa',
                    'value' => 'Official Portal',
                    'hint'  => 'Apply online via the official Sierra Leone eVisa portal.',
                    'href'  => self::EVISA_URL,
                    'external' => true,
                    'icon'  => 'visa',
                    'accent'=> '#005028',
                ],
                [
                    'title' => 'Visa & Permits',
                    'value' => 'Unified Permit',
                    'hint'  => 'Work permits, residence permits, extensions and re-entry.',
                    'href'  => self::UNIFIED_PERMIT_URL,
                    'external' => true,
                    'icon'  => 'permit',
                    'accent'=> '#005028',
                ],
            ],


            // CIO
            'igp_image' => '/assets/cio.jpg',
            'igp_name'  => self::CIO_NAME,

            'igp_title' => 'Dr. Moses Tiffa Baio, Esq',
            'welcome_title' => 'Chief Immigration Officer’s Welcome Message — Sierra Leone Immigration Department',
            'welcome_full' => $welcomeFull,
            'welcome_intro' => [
                ['text' => $welcomeFull[0] ?? ''],
                ['text' => $welcomeFull[1] ?? ''],
            ],
            'welcome_more' => array_values(array_map(
                static fn(string $t): array => ['text' => $t],
                array_slice($welcomeFull, 2),
            )),

            // ✅ Articles section (API)
            'articles_title'     => 'Latest News',
            'articles_subtitle'  => 'Official updates, announcements, and activities.',
            'articles'           => $articles,
            'articles_all_href'  => '/articles',
            'articles_all_label' => 'View all →',

            // campaigns
            'girls_flyer' => '/assets/hands-off-our-girls.jpg',
            'kush_flyer'  => '/assets/say-no-to-kush.jpg',

            // notices + modal
            'wanted_items' => $wantedItems,
            'missing_items' => $missingItems,
            'stolen_vehicles' => $stolenVehicles,

            // keep image-only arrays
            'wanted_photos' => array_map(static fn(string $p): array => ['src' => $p], $wantedPhotos),
            'missing_photos' => array_map(static fn(string $p): array => ['src' => $p], $missingPhotos),
            'stolen_photos' => array_map(static fn(string $p): array => ['src' => $p], $stolenPhotos),
        ];

        $this->view->page('layout.html', 'home.html', $vm);
    }

    /**
     * @param string[] $photos
     * @return array<int,array<string,string>>
     */
    private function buildPosterNotices(string $type, array $photos, string $defaultTitlePrefix, string $defaultDetail): array
    {
        $out = [];
        $i = 1;

        foreach ($photos as $src) {
            $out[] = [
                'photo' => $src,
                'name' => sprintf('%s %02d', $defaultTitlePrefix, $i),
                'detail' => $defaultDetail,
                'case_no' => '',
                'ref' => '',
                'region' => '',
                'last_seen' => '',
                'last_known' => '',
                'type' => $type,
            ];
            $i++;
        }

        return $out;
    }

    /**
     * @param string[] $photos
     * @return array<int,array<string,string>>
     */
    private function buildVehicleNotices(array $photos, string $defaultMake, string $defaultStatus): array
    {
        $out = [];
        $i = 1;

        foreach ($photos as $src) {
            $out[] = [
                'photo' => $src,
                'plate' => sprintf('PLATE-%02d', $i),
                'make' => $defaultMake,
                'status' => $defaultStatus,
                'model' => '',
                'color' => '',
                'type' => 'Stolen Vehicle',
            ];
            $i++;
        }

        return $out;
    }
}