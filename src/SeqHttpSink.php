<?php
declare(strict_types=1);

namespace Elephox\Builder\Seq;

use Elephox\Logging\Contract\LogLevel;
use Elephox\Logging\Contract\Sink;
use Elephox\Logging\SinkCapability;
use JsonException;

class SeqHttpSink implements Sink {
	public function __construct(
		protected readonly SeqHttpClient $client,
	) {}

	public function hasCapability(SinkCapability $capability): bool {
		return false;
	}

	/**
	 * @throws JsonException
	 */
	public function write(LogLevel $level, string $message, array $context): void {
		$data = [
			'@t' => date('c'),
			'@m' => $message,
			'@l' => $level->getName(),
		];

		if (isset($context['exception'])) {
			$data['@e'] = $context['exception'];
			unset ($context['exception']);
		}

		foreach ($context as $key => $value) {
			if (str_starts_with($key, '@')) {
				$key = "@$key";
			}

			$data[$key] = $value;
		}

		$this->client->send($data);
	}
}
