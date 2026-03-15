<?php declare(strict_types=1);

final class PassportController extends BaseController
{
    private const PASSPORT_VOUCHER_URL = 'https://www.slid.bigfive.gov.sl/passport/voucher';
    private const PASSPORT_START_URL = 'https://www.slid.bigfive.gov.sl/passport/start';

    public function index(): void
    {
        $vm = SiteVm::base($this->config) + [
            'page_title' => 'Passport Online',
            'heading' => 'Passport Online',
            'subheading' => 'Buy a voucher, apply online, and book biometrics for passport issuance or renewal.',
            // You can replace these with real portal URLs anytime.
            'voucher_url' => self::PASSPORT_VOUCHER_URL,
            'apply_url'   => self::PASSPORT_START_URL,
        ];

        $this->view->page('layout.html', 'passport.html', $vm);
    }
}
