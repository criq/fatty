<?php

namespace Fatty\Vectors;

use Fatty\Calculator;

class Maintain extends \Fatty\Vector
{
	const LABEL_INFINITIVE = "udržovat hmotnost";
	const TDEE_QUOTIENT = 1.05;
	const TDEE_QUOTIENT__LARGE = 1.07;
	const WEIGHT_CHANGE_PER_WEEK = 0;

	public function getTdeeQuotient(Calculator $calculator)
	{
		return $calculator->getPhysicalActivityLevel()->getAmount() >= 2 ? static::TDEE_QUOTIENT__LARGE : static::TDEE_QUOTIENT;
	}

	public function getTdeeChangePerDay(Calculator $calculator)
	{
		return new \Fatty\Energy(0, 'kCal');
	}

	public function getGoalTdee(Calculator $calculator)
	{
		return $calculator->getTotalDailyEnergyExpenditure();
	}
}
