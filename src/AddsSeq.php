<?php
declare(strict_types=1);

namespace Elephox\BuilderSeq;

use Elephox\Configuration\Contract\Configuration;
use Elephox\DI\Contract\ServiceCollection;
use Elephox\DI\ServiceNotFoundException;
use Elephox\Logging\MultiSinkLogger;
use Elephox\Web\ConfigurationException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use RicardoBoss\PhpSeq\Contract\SeqClient;
use RicardoBoss\PhpSeq\Contract\SeqLogger as SeqLoggerInterface;
use RicardoBoss\PhpSeq\SeqHttpClient;
use RicardoBoss\PhpSeq\SeqHttpClientConfiguration;
use RicardoBoss\PhpSeq\SeqLogger;
use RicardoBoss\PhpSeq\SeqLoggerConfiguration;

trait AddsSeq {
	abstract protected function getServices(): ServiceCollection;

	public function addHttpSeqClient(): void
	{
		foreach ([ClientInterface::class, RequestFactoryInterface::class, StreamFactoryInterface::class] as $service) {
			if (!$this->getServices()->has($service)) {
				throw $this->missingService($service, "This is required for HTTP ingestion.");
			}
		}

		$this->getServices()->tryAddSingleton(SeqHttpClientConfiguration::class, factory: function (Configuration $config) {
			return new SeqHttpClientConfiguration(
				$config["seq:http:endpoint"] ?? throw new ConfigurationException("Missing required configuration key: seq:http:endpoint"),
				$config["seq:http:api-key"] ?? null,
				$config["seq:http:max-retries"] ?? 3,
			);
		});

		$this->getServices()->tryAddSingleton(SeqClient::class, SeqHttpClient::class);
	}

	public function addSeqLogger(bool $replaceExisting = false): void
	{
		if (!$this->getServices()->has(SeqClient::class)) {
			throw $this->missingService(SeqClient::class, "Please add a client before adding the logger.");
		}

		$this->getServices()->tryAddSingleton(SeqLoggerConfiguration::class, factory: function (Configuration $config) {
			return new SeqLoggerConfiguration(
				$config["seq:logger:backlog-limit"] ?? 50,
				$config["seq:logger:context"] ?? null,
				$config["seq:logger:minimum-log-level"] ?? null,
			);
		});

		$this->getServices()->tryAddSingleton(SeqLoggerInterface::class, SeqLogger::class);
		$this->getServices()->addSingleton(SeqSink::class, SeqSink::class);

		if ($this->getServices()->has(LoggerInterface::class)) {
			$logger = $this->getServices()->get(LoggerInterface::class);
			if ($logger instanceof MultiSinkLogger) {
				$seqSink = $this->getServices()->get(SeqSink::class);
				$logger->addSink($seqSink);

				return;
			}

			if (!$replaceExisting) {
				$logger->error("Unable to add Seq logger to a non-MultiSink logger without replacing it.");

				return;
			}
		}

		// replaces any existing logger
		$this->getServices()->addSingleton(LoggerInterface::class, factory: function (SeqSink $sink) {
			$logger = new MultiSinkLogger();

			$logger->addSink($sink);

			return $logger;
		});
	}

	private function missingService(string $service, string $message): ServiceNotFoundException
	{
		return new ServiceNotFoundException("No '$service' service found. $message");
	}
}
