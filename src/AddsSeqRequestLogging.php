<?php
declare(strict_types=1);

namespace Elephox\Builder\Seq;

use Elephox\Builder\RequestLogging\LoggingMiddleware;
use Elephox\DI\Contract\ServiceCollection;
use Elephox\Web\RequestPipelineBuilder;

trait AddsSeqRequestLogging {
	abstract public function addSeq(): void;

	abstract protected function getServices(): ServiceCollection;
	abstract protected function getPipeline(): RequestPipelineBuilder;

	public function addSeqRequestLogging(): void {
		$this->addSeq();

		if ($this->getServices()->has(LoggingMiddleware::class)) {
			$middleware = $this->getServices()->requireService(LoggingMiddleware::class);
		} else {
			$middleware = $this->getServices()->resolver()->instantiate(LoggingMiddleware::class);
		}

		$this->getPipeline()->push($middleware);
	}
}