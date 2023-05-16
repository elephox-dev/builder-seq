<?php
declare(strict_types=1);

namespace Elephox\Builder\Seq;

use Elephox\Configuration\Contract\Configuration;
use Elephox\DI\Contract\ServiceCollection;
use Elephox\Http\Url;
use Elephox\Http\UrlScheme;
use Elephox\Logging\MultiSinkLogger;
use Elephox\Web\ConfigurationException;
use LogicException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use RicardoBoss\PhpSeq\Contract\SeqClient;
use RicardoBoss\PhpSeq\Contract\SeqLogger as SeqLoggerContract;
use RicardoBoss\PhpSeq\SeqHttpClient;
use RicardoBoss\PhpSeq\SeqHttpClientConfiguration;
use RicardoBoss\PhpSeq\SeqLogger;
use RicardoBoss\PhpSeq\SeqLoggerConfiguration;

trait AddsSeq {
	abstract protected function getServices(): ServiceCollection;

	public function addSeq(): void {
		if (!$this->getServices()->has(RequestFactoryInterface::class)) {
			throw new LogicException("No RequestFactoryInterface service available");
		}

		if (!$this->getServices()->has(StreamFactoryInterface::class)) {
			throw new LogicException("No StreamFactoryInterface service available");
		}

		if (!$this->getServices()->has(ClientInterface::class)) {
			throw new LogicException("No ClientInterface service available");
		}

		$this->getServices()->addSingleton(
			SeqHttpClientConfiguration::class,
			factory: static function (Configuration $config) {
				$endpoint = $config['seq:client:host'] ?? null;
				if ($endpoint === null) {
					throw new ConfigurationException("Configuration 'seq:client:host' is required for Seq");
				}

				$url = Url::fromString($endpoint)
					->with()
					->path('/api/events/raw')
					->get();

				$apiKey = $config['seq:client:apiKey'] ?? null;
				$maxRetries = $config['seq:client:maxRetries'] ?? 3;

				return new SeqHttpClientConfiguration((string)$url, $apiKey, $maxRetries);
			},
		);

		$this->getServices()->addSingleton(
			SeqLoggerConfiguration::class,
			factory: static function (Configuration $config) {
				$backlogLimit = $config['seq:logger:backlogLimit'] ?? 10;
				$globalContext = $config['seq:logger:globalContext'] ?? null;

				return new SeqLoggerConfiguration($backlogLimit, $globalContext);
			}
		);

		$this->getServices()->addSingleton(SeqClient::class, SeqHttpClient::class);
		$this->getServices()->addSingleton(SeqLoggerContract::class, SeqLogger::class);
		$this->getServices()->addSingleton(SeqLoggerSink::class);
		$this->getServices()->tryAddSingleton(
			LoggerInterface::class,
			factory: static function (SeqLoggerSink $seqSink): MultiSinkLogger {
				$logger = new MultiSinkLogger();
				$logger->addSink($seqSink);
				return $logger;
			},
		);
	}
}
