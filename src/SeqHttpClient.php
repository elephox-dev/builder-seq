<?php
declare(strict_types=1);

namespace Elephox\Builder\Seq;

use CurlHandle;
use DateTime;
use DateTimeInterface;
use JsonException;
use LogicException;
use RuntimeException;

class SeqHttpClient {
	public const CURL_RETRYABLE_ERROR_CODES = [
		CURLE_COULDNT_RESOLVE_HOST,
		CURLE_COULDNT_CONNECT,
		CURLE_HTTP_NOT_FOUND,
		CURLE_READ_ERROR,
		CURLE_OPERATION_TIMEOUTED,
		CURLE_HTTP_POST_ERROR,
		CURLE_SSL_CONNECT_ERROR,
	];

	private CurlHandle|false $handle = false;
	private array $buffer = [];
	private ?DateTimeInterface $lastFlush = null;

	public function __construct(
		protected readonly SeqConfiguration $configuration,
	) {}

	private function getHandle(): CurlHandle
	{
		if ($this->handle === false) {
			$this->handle = $this->initCurlHandle();
		}

		return $this->handle;
	}

	private function initCurlHandle(): CurlHandle
	{
		$handle = curl_init();
		if (!$handle) {
			throw new LogicException('Could not initialize curl handle');
		}

		$headers = [
			'Content-Type: application/vnd.serilog.clef',
		];

		if ($this->configuration->apiKey !== null) {
			$headers[] = 'X-Seq-ApiKey: ' . $this->configuration->apiKey;
		}

		curl_setopt($handle, CURLOPT_URL, $this->configuration->endpoint);
		curl_setopt($handle, CURLOPT_POST, true);
		curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);

		return $handle;
	}

	/**
	 * @throws JsonException
	 */
	public function send(mixed $data): void
	{
		$this->buffer[] = $data;
		if (!$this->shouldFlush()) {
			return;
		}

		$this->flush();
	}

	protected function shouldFlush(): bool {
		return $this->lastFlush === null ||
			$this->lastFlush->diff(new DateTime())->i > $this->configuration->flushTimeoutMinutes ||
			count($this->buffer) >= $this->configuration->maxNumBufferedMessages;
	}

	/**
	 * @throws JsonException
	 */
	public function flush(): void {
		$body = "";
		while ($data = array_shift($this->buffer)) {
			$body .= json_encode($data, flags: JSON_THROW_ON_ERROR) . "\r\n";
		}

		curl_setopt($this->getHandle(), CURLOPT_POSTFIELDS, $body);
		curl_setopt($this->getHandle(), CURLOPT_RETURNTRANSFER, true);

		$this->tryExecute($this->getHandle());
	}

	private function tryExecute(CurlHandle $ch): void
	{
		$retries = 5;
		while ($retries--) {
			$curlResponse = curl_exec($ch);
			if ($curlResponse !== false) {
				return;
			}

			$curlErrno = curl_errno($ch);

			if ($retries > 0 && in_array($curlErrno, self::CURL_RETRYABLE_ERROR_CODES, true)) {
				continue;
			}

			$curlError = curl_error($ch);

			throw new RuntimeException(sprintf('Curl error (code %d): %s', $curlErrno, $curlError));
		}
	}
}
