<?php
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/../vendor/autoload.php';

require APP_ROOT . '/src/Routes/WebController.php';

use App\Routes\WebController;
use Elephox\Builder\RequestLogging\AddsRequestLogging;
use Elephox\Builder\Seq\AddsSeq;
use Elephox\Web\WebApplicationBuilder;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Builder extends WebApplicationBuilder {
	use AddsSeq;
	use AddsRequestLogging;
}

$builder = Builder::create();

$builder->services->addSingleton(ClientInterface::class, Client::class);
$builder->services->addSingleton(RequestFactoryInterface::class, HttpFactory::class);
$builder->services->addSingleton(StreamFactoryInterface::class, HttpFactory::class);

$builder->addSeq();
$builder->addRequestLogging();

$builder->addRouting();
$builder->getRouter()->addRoutesFromClass(WebController::class);

$app = $builder->build();
$app->run();
