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

		$middleware = $this->getServices()->getService(LoggingMiddleware::class);
		if ($middleware === null) {
			$middleware = $this->getServices()->resolver()->instantiate(LoggingMiddleware::class);
		}

		$this->getPipeline()->push($middleware);
	}
}
