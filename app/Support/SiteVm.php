<?php declare(strict_types=1);

final class SiteVm {
    /** @return array<int,array<string,mixed>> */
    public static function nav(): array {
        $aboutLinks = [
            ['href'=>'/home#mission', 'label'=>'Mandate & Mission', 'external'=>false],
            ['href'=>'/home#leadership', 'label'=>'Leadership', 'external'=>false],
            ['href'=>'/departments', 'label'=>'Directorates & Units', 'external'=>false],
        ];

        $serviceLinks = [
            ['href'=>'/passport', 'label'=>'Passport Online', 'external'=>false],
            ['href'=>'https://www.evisa.sl', 'label'=>'Apply for eVisa', 'external'=>true],
            ['href'=>'https://unifiedpermit.gov.sl', 'label'=>'Visa & Permits Portal', 'external'=>true],
            ['href'=>'/fees', 'label'=>'Fees & Charges', 'external'=>false],
            ['href'=>'/track', 'label'=>'Track Application', 'external'=>false],
            ['href'=>'/report', 'label'=>'Report an Issue', 'external'=>false],
            ['href'=>'/faq', 'label'=>'FAQ', 'external'=>false],
        ];

        $newsLinks = [
            ['href'=>'/articles', 'label'=>'Latest News', 'external'=>false],
            ['href'=>'/notices', 'label'=>'Notices & Advisories', 'external'=>false],
            ['href'=>'/publications', 'label'=>'Publications', 'external'=>false],
        ];

        return [
            ['href'=>'/home', 'label'=>'Home', 'has_children'=>false, 'children'=>[], 'groups'=>[], 'mega_cols_class'=>'grid-cols-1'],

            ['href'=>'/home#about', 'label'=>'About', 'has_children'=>true, 'children'=>[], 'mega_cols_class'=>'grid-cols-1', 'groups'=>[
                ['heading'=>'', 'links'=>$aboutLinks],
            ]],

            // Nigeria-style: services are first-class, task-based.
            ['href'=>'/home#services', 'label'=>'Services', 'has_children'=>true, 'children'=>[], 'mega_cols_class'=>'grid-cols-1', 'groups'=>[
                ['heading'=>'Online Services', 'links'=>$serviceLinks],
            ]],

            ['href'=>'/articles', 'label'=>'News', 'has_children'=>true, 'children'=>[], 'mega_cols_class'=>'grid-cols-1', 'groups'=>[
                ['heading'=>'Updates', 'links'=>$newsLinks],
            ]],

            ['href'=>'/home#contact', 'label'=>'Contact', 'has_children'=>false, 'children'=>[], 'groups'=>[], 'mega_cols_class'=>'grid-cols-1'],
        ];
    }

    /** @return array<string,mixed> */
    public static function base(Config $config): array {
        return [
            'site_title' => $config->siteTitle,
            'page_title' => '',
            'logo_url'   => '/assets/slid-logo.png',
            'tagline'    => 'Facilitating Travel • Protecting Our Borders',
            'passport_url' => '/passport',
            'evisa_portal_url' => 'https://www.evisa.sl',
            'unified_permit_url' => 'https://unifiedpermit.gov.sl',
            'fees_url' => '/fees',
            'track_url' => '/track',
            'emergency_number' => '019',
            'nav'        => self::nav(),
        ];
    }
}
