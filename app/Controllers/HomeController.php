<?php
// FILE: app/Controllers/HomeController.php
declare(strict_types=1);

final class HomeController extends BaseController
{
    private const EVISA_URL = 'https://www.evisa.sl';
    private const UNIFIED_PERMIT_URL = 'https://unifiedpermit.gov.sl';
    private const HOTLINE_NUMBER = '019';

    /**
     * Base URL for the passport application system.
     * Change this one value to switch environments.
     */
    private const PASSPORT_BASE_URL = 'https://www.slid.bigfive.gov.sl';

    private const CIO_NAME = 'Dr. Moses Tiffa Baio, Esq';
    private const CIO_TITLE = 'Chief Immigration Officer';

    private const WEBMAIL_URL = '#';

    public function index(): void
    {
        $tenantId = (string)($_GET['tenant_id'] ?? $this->config->tenantId);
        $nav = NavBuilder::build($this->http, $tenantId);
        $origin = $this->config->siteOrigin();

        $passportStartUrl = $this->passportUrl('/'); //passport/start
        $passportVoucherUrl = $this->passportUrl('/passport/voucher');

        $articles = $this->fetchLatestArticles(
            tenantId: $tenantId,
            origin: $origin,
            perPage: 4
        );

        $welcome = $this->buildWelcomeMessage();

        $vm = [
            'site_title' => 'Sierra Leone Immigration Department',
            'page_title' => 'Home',

            'nav' => $nav,

            'webmail_url' => self::WEBMAIL_URL,
            'emergency_number' => self::HOTLINE_NUMBER,

            'passport_start_url' => $passportStartUrl,
            'passport_voucher_url' => $passportVoucherUrl,

            'hero_slides' => [
                [
                    'kicker' => 'Passport Applications',
                    'title'  => 'Passport — Apply Online, Book Biometrics',
                    'text'   => 'Buy a voucher, complete your application online, then book biometrics for passport issuance and renewal.',
                    'image'  => '/assets/hero-2_.jpg',
                    'cta1_label' => 'Buy Voucher (Passport)',
                    'cta1_href'  => $passportVoucherUrl,
                    'cta2_label' => 'Requirements & Fees',
                    'cta2_href'  => '/fees',
                ],
                [
                    'kicker' => 'Public Information Portal',
                    'title'  => 'Passport, Visas & Permits — Online Services',
                    'text'   => 'Apply for passports, visas, and permits online. Get clear requirements, fees, and official updates from the Immigration Department.',
                    'image'  => '/assets/hero-1_.jpg',
                    'cta1_label' => 'Apply eVisa',
                    'cta1_href'  => self::EVISA_URL,
                    'cta2_label' => 'Permits Portal',
                    'cta2_href'  => self::UNIFIED_PERMIT_URL,
                ],
                [
                    'kicker' => 'Border Management',
                    'title'  => 'Discipline, Professionalism & Zero Tolerance to Corruption',
                    'text'   => 'Priorities: improved service delivery, discipline, welfare, and maintaining professionalism across the Force.',
                    'image'  => '/assets/hero-3_.jpg',
                    'cta1_label' => 'Open FAQ',
                    'cta1_href'  => '/faq',
                    'cta2_label' => 'Report an Immigration Issue',
                    'cta2_href'  => '/report',
                ],
            ],

            'quick_actions' => [
                [
                    'title' => 'Passport Online',
                    'value' => 'Apply Now',
                    'hint'  => 'Start your passport application online.',
                    'href'  => $passportStartUrl,
                    'external' => true,
                    'icon'  => 'passport',
                    'accent'=> '#005028',
                ],
                [
                    'title' => 'Buy Voucher',
                    'value' => 'Passport Voucher',
                    'hint'  => 'Purchase a voucher to begin your passport application.',
                    'href'  => $passportVoucherUrl,
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

            'cio_image' => '/assets/cio.jpg',
            'cio_name'  => self::CIO_NAME,
            'cio_title' => self::CIO_TITLE,

            'welcome_title' => 'Chief Immigration Officer’s Welcome Message — Sierra Leone Immigration Department',
            'welcome_intro' => $welcome['intro'],
            'welcome_more'  => $welcome['more'],

            'articles_title'     => 'Latest News',
            'articles_subtitle'  => 'Official updates, announcements, and activities.',
            'articles'           => $articles,
            'articles_all_href'  => '/articles',
            'articles_all_label' => 'View all →',
        ];

        $this->view->page('layout.html', 'home.html', $vm);
    }

    private function passportUrl(string $path): string
    {
        $base = rtrim(self::PASSPORT_BASE_URL, '/');
        $p = '/' . ltrim($path, '/');

        return $base . $p;
    }

    /**
     * @return array<int,array{slug:string,href:string,title:string,excerpt:string,date:string,image_url:?string}>
     */
    private function fetchLatestArticles(string $tenantId, string $origin, int $perPage = 4): array
    {
        try {
            $resp = $this->http->get('/articles', array_filter([
                'tenant_id'  => $tenantId,
                'status'     => 'published',
                'per_page'   => (string)$perPage,
                'sort_field' => 'published_at',
                'sort_dir'   => 'desc',
                'include'    => 'feature,category,tags',
            ], static fn($v) => $v !== ''));

            $decoded = json_decode($resp->body, true);
            $rows = (is_array($decoded) && isset($decoded['data']) && is_array($decoded['data']))
                ? $decoded['data']
                : [];

            return array_map(static function (array $a) use ($origin): array {
                $feature = is_array($a['feature'] ?? null) ? $a['feature'] : null;

                $img = null;
                if ($feature) {
                    $img = Normalize::toAbs($origin, (string)($feature['url'] ?? ''))
                        ?: Normalize::toAbs($origin, (string)($feature['path'] ?? ''));
                }

                if (!$img) {
                    $img = Normalize::toAbs($origin, (string)($a['image_url'] ?? ''))
                        ?: Normalize::toAbs($origin, (string)($a['image'] ?? ''));
                }

                $slug = (string)($a['slug'] ?? '');
                $publishedAt = (string)($a['published_at'] ?? '');
                $apiHref = (string)($a['href'] ?? '');

                $href = $apiHref !== ''
                    ? $apiHref
                    : ($slug !== '' ? ('/articles/' . $slug) : '/articles');

                return [
                    'slug'      => $slug,
                    'href'      => $href,
                    'title'     => (string)($a['title'] ?? ''),
                    'excerpt'   => (string)($a['summary'] ?? ''),
                    'date'      => $publishedAt !== '' ? substr($publishedAt, 0, 10) : '',
                    'image_url' => $img,
                ];
            }, $rows);
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * @return array{intro: array<int,array{text:string}>, more: array<int,array{text:string}>}
     */
    private function buildWelcomeMessage(): array
    {
        $welcomeFull = [
            'All of us can honestly accept that in recent times our Immigration Force has suffered a massive decline in public confidence of our service delivery. Therefore, against this difficult backdrop, our collective challenge is on how to provide an excellent and improved service delivery for the citizens of this country.',
            'I have a desire to uplift the Force from its current abysmal status to one of envy. But would that stop me from being real? The answer is, no! I owe a duty to God Almighty, the nation and my conscience to be realistic.',
            'In spite of the challenges I inherited, I am confident that with collective responsibility in partnership with our local communities and the established Immigration Partnership Boards nationwide, we can mobilize our efforts to restore Public Confidence in the Immigration.',
            'On this note, I want to express my profound thanks and appreciation to you all for your selfless service to the nation under difficult circumstances and to assert that my focus will be primarily on the Work Force as it is the most important asset for Organizational Development.',
            'I have realized that the general morale within the Force has been greatly dampened for obvious reasons.',
            'With the support of the entire Executive Management Board of the Immigration, we shall collectively endeavor to create conducive working environments for all Immigration Officers, geared towards professionalism in their respective policing tasks.',
        ];

        return [
            'intro' => [
                ['text' => $welcomeFull[0] ?? ''],
                ['text' => $welcomeFull[1] ?? ''],
            ],
            'more' => array_values(array_map(
                static fn(string $t): array => ['text' => $t],
                array_slice($welcomeFull, 2),
            )),
        ];
    }
}

?>