<?php declare(strict_types=1);

final class TrackController extends BaseController
{
    public function index(): void
    {
        $ref = trim((string)($_GET['ref'] ?? ''));

        $vm = SiteVm::base($this->config) + [
            'page_title' => 'Track Application',
            'heading' => 'Track Application',
            'subheading' => 'Check the status of your application using your reference number.',
            'ref' => $ref,
            'has_ref' => $ref !== '' ? '1' : '',
        ];

        $this->view->page('layout.html', 'track.html', $vm);
    }
}
