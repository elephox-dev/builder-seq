<?php
declare(strict_types=1);

namespace Elephox\Builder\Seq;

use Elephox\Configuration\Contract\Configuration;
use Elephox\DI\Contract\ServiceCollection;
use Elephox\Http\Url;
use Elephox\Logging\MultiSinkLogger;
use Elephox\Web\ConfigurationException;
use Psr\Log\LoggerInterface;

trait AddsSeq {
	abstract protected function getServices(): ServiceCollection;

	public function addSeq(): void {
		$this->getServices()->addSingleton(
			SeqConfiguration::class,
			factory: static function (Configuration $config) {
				$endpoint = $config['seq:endpoint'] ?? null;
				if (!is_string($endpoint)) {
					throw new ConfigurationException(
						'Seq configuration error: "seq:endpoint" must be a string.'
					);
				}

				$url = Url::fromString($endpoint)
					->with()
					->path('/api/events/raw')
					->get();

				$apiKey = $config['seq:apiKey'] ?? null;
				$flushTimeout = $config['seq:flushTimeoutMinutes'] ?? SeqConfiguration::DEFAULT_FLUSH_TIMEOUT_MINUTES;
				$maxBufferedMessages = $config['seq:maxBufferedMessages'] ?? SeqConfiguration::DEFAULT_MAX_BUFFERED_MESSAGES_COUNT;

				return new SeqConfiguration((string)$url, $apiKey, $flushTimeout, $maxBufferedMessages);
			}
		);

		$this->getServices()->addSingleton(SeqHttpClient::class);
		$this->getServices()->addSingleton(SeqHttpSink::class);
		$this->getServices()->addSingleton(LoggerInterface::class, MultiSinkLogger::class);

		$logger = $this->getServices()->requireService(LoggerInterface::class);
		if ($logger instanceof MultiSinkLogger) {
			$logger->addSink($this->getServices()->requireService(SeqHttpSink::class));
		} else {
			$logger->warning(
				"Cannot automatically add Seq sink to logger because it is not a MultiSinkLogger."
			);
		}
	}
}
