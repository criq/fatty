<?php

namespace Fatty\Strategies;

use Fatty\Amount;
use Fatty\Calculator;
use Fatty\Energy;
use Fatty\Metrics\AmountMetricResult;
use Fatty\Metrics\QuantityMetricResult;
use Fatty\Metrics\WeightGoalEnergyExpenditureMetric;
use Fatty\Metrics\WeightGoalQuotientMetric;
use Fatty\Strategy;
use Fatty\Weight;

/**
 * Strategy for pregnant women and new mothers (children < 6 months).
 *
 * Activated automatically by Calculator::getStrategy() when the user
 * is either pregnant or a new mother. Overrides BMI weight source and
 * replaces user-chosen weight goals with BMI-derived quotients safe
 * for pregnancy / postpartum.
 */
class DiaMama extends Strategy
{
	/**
	 * For BMI calculation, prefer the weight recorded before pregnancy
	 * (growing belly would skew BMI). Falls back to current weight when
	 * pregnancy data is missing — e.g. postpartum new mothers who no
	 * longer have an active pregnancy record.
	 */
	public function getBodyMassIndexWeight(Calculator $calculator): ?Weight
	{
		$pregnancy = $calculator->getGender() ? $calculator->getGender()->getPregnancy() : null;
		if ($pregnancy) {
			$weight = $pregnancy->getWeightBeforePregnancy();
			if ($weight) {
				return $weight;
			}
		}

		return $calculator->getWeight();
	}

	/**
	 * Weight-goal quotient derived from BMI instead of user-chosen vector.
	 * Prevents aggressive caloric deficits during pregnancy / postpartum.
	 */
	public function calcWeightGoalQuotient(Calculator $calculator): AmountMetricResult
	{
		$result = new AmountMetricResult(new WeightGoalQuotientMetric);

		$bodyMassIndexResult = $calculator->calcBodyMassIndex();
		$result->addErrors($bodyMassIndexResult->getErrors());

		if (!$result->hasErrors()) {
			$bodyMassIndexValue = $bodyMassIndexResult->getResult()->getNumericalValue();

			if ($bodyMassIndexValue <= 19) {
				$weightGoalQuotient = 1.1;   // podváha → mírné přibírání
			} elseif ($bodyMassIndexValue < 25) {
				$weightGoalQuotient = 1;     // norma → udržování
			} elseif ($bodyMassIndexValue < 30) {
				$weightGoalQuotient = .93;   // nadváha → mírné hubnutí
			} else {
				$weightGoalQuotient = .9;    // obezita → hubnutí
			}

			$result->setResult(new Amount($weightGoalQuotient));
		}

		return $result;
	}

	/**
	 * WGEE = TDEE × weight-goal quotient.
	 */
	public function calcWeightGoalEnergyExpenditure(Calculator $calculator): QuantityMetricResult
	{
		$result = new QuantityMetricResult(new WeightGoalEnergyExpenditureMetric);

		$totalDailyEnergyExpenditureResult = $calculator->calcTotalDailyEnergyExpenditure();
		$result->addErrors($totalDailyEnergyExpenditureResult->getErrors());

		$weightGoalQuotientResult = $this->calcWeightGoalQuotient($calculator);
		$result->addErrors($weightGoalQuotientResult->getErrors());

		if (!$result->hasErrors()) {
			$totalDailyEnergyExpenditureValue = $totalDailyEnergyExpenditureResult->getResult()->getNumericalValue();
			$weightGoalQuotientValue = $weightGoalQuotientResult->getResult()->getNumericalValue();

			$energy = (new Energy(
				new Amount($totalDailyEnergyExpenditureValue * $weightGoalQuotientValue),
				$totalDailyEnergyExpenditureResult->getResult()->getUnit(),
			))->getInUnit($calculator->getUnits());

			$formula = "
				totalDailyEnergyExpenditure[{$totalDailyEnergyExpenditureValue}] * weightGoalQuotient[{$weightGoalQuotientValue}]
				= {$energy->getInUnit("kcal")->getAmount()->getValue()} kcal
				= {$energy->getInUnit("kJ")->getAmount()->getValue()} kJ
			";

			$result->setResult($energy)->setFormula($formula);
		}

		return $result;
	}
}
