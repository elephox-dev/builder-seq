<?php
declare(strict_types=1);

namespace Elephox\BuilderSeq;

use Elephox\Logging\Contract\LogLevel;
use Elephox\Logging\Contract\Sink;
use Elephox\Logging\SinkCapability;

class SeqSink implements Sink
{
	public function hasCapability(SinkCapability $capability): bool {
		// TODO: Implement hasCapability() method.
	}

	public function write(LogLevel $level, string $message, array $context): void {
		// TODO: Implement write() method.
	}
}
