<?php

namespace Fatty\Strategies;

use Fatty\Calculator;
use Fatty\Errors\MissingDietApproachError;
use Fatty\Metrics\AmountMetricResult;
use Fatty\Metrics\QuantityMetricResult;
use Fatty\Metrics\WeightGoalEnergyExpenditureMetric;
use Fatty\Strategy;
use Fatty\Weight;

class Zivot20 extends Strategy
{
	const WEIGHT_GOAL_QUOTIENT = null;

	public function getBodyMassIndexWeight(Calculator $calculator): ?Weight
	{
		return $calculator->getWeight();
	}

	public function calcWeightGoalQuotient(Calculator $calculator): AmountMetricResult
	{
		return $calculator->getGoal()->getVector()->calcWeightGoalQuotient($calculator);
	}

	public function calcWeightGoalEnergyExpenditure(Calculator $calculator): QuantityMetricResult
	{
		$result = new QuantityMetricResult(new WeightGoalEnergyExpenditureMetric);

		if (!$calculator->getDiet()->getApproach()) {
			$result->addError(new MissingDietApproachError);
		} else {
			return $calculator->getDiet()->getApproach()->calcWeightGoalEnergyExpenditure($calculator);
		}

		return $result;
	}
}
