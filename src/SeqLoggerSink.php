<?php
declare(strict_types=1);

namespace Elephox\Builder\Seq;

use Elephox\Logging\Contract\LogLevel;
use Elephox\Logging\Contract\Sink;
use Elephox\Logging\SinkCapability;
use RicardoBoss\PhpSeq\Contract\SeqLogger;

class SeqLoggerSink implements Sink
{
	public function __construct(
		private readonly SeqLogger $logger,
	) {
	}

	public function hasCapability(SinkCapability $capability): bool
	{
		return $capability === SinkCapability::MessageTemplates;
	}

	public function write(LogLevel $level, string $message, array $context): void
	{
		$this->logger->log($level->getName(), $message, $context);
	}
}
