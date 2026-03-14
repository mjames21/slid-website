<?php declare(strict_types=1);

final class PassportController extends BaseController
{
    public function index(): void
    {
        $vm = SiteVm::base($this->config) + [
            'page_title' => 'Passport Online',
            'heading' => 'Passport Online',
            'subheading' => 'Buy a voucher, apply online, and book biometrics for passport issuance or renewal.',
            // You can replace these with real portal URLs anytime.
            'voucher_url' => '#',
            'apply_url'   => '#',
        ];

        $this->view->page('layout.html', 'passport.html', $vm);
    }
}
