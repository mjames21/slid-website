<?php declare(strict_types=1);

// FILE: app/Controllers/FaqController.php

final class FaqController extends BaseController
{
    public function index(): void
    {
        $sections = [
            [
                'id' => 'passport',
                'title' => 'Passport Online',
                'desc' => 'Voucher purchase, online application, documents, biometrics and timelines.',
                'items' => [
                    [
                        'id' => 'passport-apply',
                        'q' => 'How do I apply for a passport online?',
                        'a' => 'Buy a voucher, start your application online, upload required documents, and book biometrics for submission.',
                    ],
                    [
                        'id' => 'passport-voucher',
                        'q' => 'Where do I buy the passport voucher?',
                        'a' => 'Use the official voucher channel and keep your voucher code safe. You will need it to start the application.',
                    ],
                ],
            ],
            [
                'id' => 'visa',
                'title' => 'Visa Services',
                'desc' => 'Official eVisa portal guidance and what to expect.',
                'items' => [
                    [
                        'id' => 'visa-where',
                        'q' => 'Where do I apply for an eVisa?',
                        'a' => 'Use the official eVisa portal at evisa.sl.',
                    ],
                ],
            ],
            [
                'id' => 'permits',
                'title' => 'Permits & Residency',
                'desc' => 'Work/residence permits and unified portal guidance.',
                'items' => [
                    [
                        'id' => 'permits-where',
                        'q' => 'Where do I apply for permits and residency services?',
                        'a' => 'Use the Unified Permit portal at unifiedpermit.gov.sl.',
                    ],
                ],
            ],
            [
                'id' => 'fraud',
                'title' => 'Fraud & Safety',
                'desc' => 'How to stay safe and avoid scams.',
                'items' => [
                    [
                        'id' => 'fraud-avoid',
                        'q' => 'How do I avoid fraud and scams?',
                        'a' => 'Only use official portals and approved payment channels. Do not share voucher codes or personal details with unverified third parties.',
                    ],
                ],
            ],
        ];

        $categories = array_map(
            static fn(array $s): array => ['id' => $s['id'], 'title' => $s['title'], 'desc' => $s['desc']],
            $sections
        );

        $vm = SiteVm::base($this->config) + [
            'page_title' => 'FAQ',
            'heading' => 'Frequently Asked Questions',
            'subheading' => 'Clear guidance for passport, visa, and permit services.',
            'categories' => $categories,
            'sections' => $sections,
        ];

        $this->view->page('layout.html', 'faq.html', $vm);
    }
}