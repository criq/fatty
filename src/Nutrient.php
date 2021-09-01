<?php

namespace Fatty;

abstract class Nutrient extends Weight
{
	const KJ_IN_G = null;

	public function __construct(Amount $amount, string $unit = 'g')
	{
		return parent::__construct(new Amount(max($amount->getValue(), 0)), $unit);
	}

	public static function createFromEnergy(Energy $energy) : Nutrient
	{
		return new static(new Amount($energy->getInKJ()->getAmount()->getValue() / static::KJ_IN_G), 'g');
	}

	public function getEnergy()
	{
		return new Energy(new Amount($this->getInG()->getAmount()->getValue() * static::KJ_IN_G), 'kJ');
	}
}
