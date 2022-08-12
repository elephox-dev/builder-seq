<?php
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/../vendor/autoload.php';

require APP_ROOT . '/src/Routes/WebController.php';

use App\Routes\WebController;
use Elephox\Builder\Seq\AddsSeq;
use Elephox\Builder\Seq\AddsSeqRequestLogging;
use Elephox\Web\Routing\RequestRouter;
use Elephox\Web\WebApplicationBuilder;

class Builder extends WebApplicationBuilder {
	use AddsSeq;
	use AddsSeqRequestLogging;
}

$builder = Builder::create();

$builder->addSeqRequestLogging();

$builder->setRequestRouterEndpoint();
$builder->service(RequestRouter::class)->loadFromClass(WebController::class);

$app = $builder->build();
$app->run();
