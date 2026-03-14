<?php declare(strict_types=1);

final class FeesController extends BaseController
{
    public function index(): void
    {
        $vm = SiteVm::base($this->config) + [
            'page_title' => 'Fees & Charges',
            'heading' => 'Fees & Charges',
            'subheading' => 'Official fees for immigration services. Always confirm payments through approved channels.',
        ];

        $this->view->page('layout.html', 'fees.html', $vm);
    }
}
