<?php declare(strict_types=1);

require_once __DIR__ . '/../app/Support/Config.php';
require_once __DIR__ . '/../app/Support/HttpClient.php';
require_once __DIR__ . '/../app/Support/MustacheLite.php';
require_once __DIR__ . '/../app/Support/Normalize.php';
require_once __DIR__ . '/../app/Support/SiteVm.php';
require_once __DIR__ . '/../app/Support/NavBuilder.php';
require_once __DIR__ . '/../app/Support/View.php';

require_once __DIR__ . '/../app/Controllers/BaseController.php';
require_once __DIR__ . '/../app/Controllers/ApiListController.php';
require_once __DIR__ . '/../app/Controllers/HomeController.php';
require_once __DIR__ . '/../app/Controllers/PassportController.php';
require_once __DIR__ . '/../app/Controllers/FeesController.php';
require_once __DIR__ . '/../app/Controllers/TrackController.php';
require_once __DIR__ . '/../app/Controllers/ArticlesController.php';
require_once __DIR__ . '/../app/Controllers/EventsController.php';
require_once __DIR__ . '/../app/Controllers/JobsController.php';
require_once __DIR__ . '/../app/Controllers/DepartmentsController.php';
require_once __DIR__ . '/../app/Controllers/NoticesController.php';
require_once __DIR__ . '/../app/Controllers/PublicationsController.php';
require_once __DIR__ . '/../app/Controllers/FaqController.php';
require_once __DIR__ . '/../app/Controllers/ReportController.php';

$configData = require __DIR__ . '/../app/config/config.php';
$config = new Config($configData);

$http = new HttpClient($config);
$view = new View(__DIR__ . '/../resources/views');