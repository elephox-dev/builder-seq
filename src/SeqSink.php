<?php
declare(strict_types=1);

namespace Elephox\BuilderSeq;

use Elephox\Logging\Contract\LogLevel;
use Elephox\Logging\Contract\Sink;
use Elephox\Logging\SinkCapability;
use RicardoBoss\PhpSeq\Contract\SeqLogger;

readonly class SeqSink implements Sink
{
	public function __construct(
		private SeqLogger $logger,
	) {}

	public function hasCapability(SinkCapability $capability): bool {
		return $capability === SinkCapability::MessageTemplates;
	}

	public function write(LogLevel $level, string $message, ?array $context): void {
		$this->logger->log($level, $message, $context);
	}
}
