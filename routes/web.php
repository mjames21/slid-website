<?php declare(strict_types=1);

return [
    '/'             => [HomeController::class, 'index'],
    '/home'         => [HomeController::class, 'index'],

    '/passport'     => [PassportController::class, 'index'],
    '/fees'         => [FeesController::class, 'index'],
    '/track'        => [TrackController::class, 'index'],

    '/articles'     => [ArticlesController::class, 'index'],
    '/events'       => [EventsController::class, 'index'],
    '/jobs'         => [JobsController::class, 'index'],
    '/departments'  => [DepartmentsController::class, 'index'],
    '/notices'      => [NoticesController::class, 'index'],
    '/publications' => [PublicationsController::class, 'index'],
    '/publications/download/{slug}' => [PublicationsController::class, 'download'],
    '/news'         => [ArticlesController::class, 'index'],
    '/news/{slug}'  => [ArticlesController::class, 'show'],
    '/report'       => [ReportController::class, 'index'],
    '/faq'          => [FaqController::class, 'index'],
    '/downloads/convicted-drug-matters-public' => [DownloadsController::class, 'convictedDrugMattersPublic'],
];