<?php
declare(strict_types=1);

use App\Core\Application;
use App\Core\Database;
use App\Core\Response;
use App\Middleware\CorsMiddleware;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Application();
$db = new Database();

$app->use(new CorsMiddleware());

require_once __DIR__ . '/../routes/api.php';

$app->run();
