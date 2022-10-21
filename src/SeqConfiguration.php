<?php
declare(strict_types=1);

namespace Elephox\Builder\Seq;

class SeqConfiguration {
	public const DEFAULT_FLUSH_TIMEOUT_MINUTES = 2;
	public const DEFAULT_MAX_BUFFERED_MESSAGES_COUNT = 20;

	public function __construct(
		public readonly string $endpoint,
		public readonly ?string $apiKey = null,
		public readonly int $flushTimeoutMinutes = self::DEFAULT_FLUSH_TIMEOUT_MINUTES,
		public readonly int $maxNumBufferedMessages = self::DEFAULT_MAX_BUFFERED_MESSAGES_COUNT,
	) {}
}
