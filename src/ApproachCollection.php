<?php

namespace Fatty;

use Katu\Tools\Options\OptionCollection;
use Katu\Tools\Rest\RestResponse;
use Katu\Tools\Rest\RestResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ApproachCollection extends \ArrayObject implements RestResponseInterface
{
	public static function createDefault(): ApproachCollection
	{
		return new static([
			new \Fatty\Approaches\DIA150,
			new \Fatty\Approaches\DiaMama\HighCarb,
			new \Fatty\Approaches\DiaMama\LowCarb,
			new \Fatty\Approaches\DiaMama\Standard,
			new \Fatty\Approaches\Keto,
			new \Fatty\Approaches\LowCarb,
			new \Fatty\Approaches\LowEnergy,
			new \Fatty\Approaches\LowEnergyTransition,
			new \Fatty\Approaches\Mediterranean,
			new \Fatty\Approaches\Standard,
		]);
	}

	public function filterByCode($code): self
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (Approach $approach) use ($code) {
			return $approach->getCode() == $code;
		})));
	}

	public function getFirst(): ?Approach
	{
		return array_values($this->getArrayCopy())[0] ?? null;
	}

	public function getAssoc(): ApproachCollection
	{
		return new static(array_combine(
			array_map(function (Approach $approach) {
				return $approach->getCode();
			}, $this->getArrayCopy()),
			array_values($this->getArrayCopy()),
		));
	}

	public function getRestResponse(?ServerRequestInterface $request = null, ?OptionCollection $options = null): RestResponse
	{
		$data = [];
		foreach ($this as $approach) {
			$data[] = $approach->getRestResponse($request, $options)->getPayload();
		}

		return new RestResponse($data);
	}
}
