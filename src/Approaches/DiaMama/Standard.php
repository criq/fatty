<?php

namespace Fatty\Approaches\DiaMama;

use Fatty\Amount;
use Fatty\Calculator;
use Fatty\Energy;
use Fatty\Metrics\GoalNutrientsCarbsMetric;
use Fatty\Metrics\GoalNutrientsFatsMetric;
use Fatty\Metrics\GoalNutrientsProteinsMetric;
use Fatty\Metrics\MetricResultCollection;
use Fatty\Metrics\QuantityMetricResult;
use Fatty\Nutrients;
use Fatty\Nutrients\Carbs;
use Fatty\Nutrients\Fats;
use Fatty\Nutrients\Proteins;

class Standard extends \Fatty\Approaches\Standard
{
	const CARBS_DEFAULT = 115;
	const CODE = "DIAMAMA_STANDARD";
	const LABEL_DECLINATED = "standardní dietu";

	public function calcGoalNutrientsProteins(Calculator $calculator): QuantityMetricResult
	{
		$result = new QuantityMetricResult(new GoalNutrientsProteinsMetric);

		$estimatedFunctionalMassResult = $calculator->calcEstimatedFunctionalMass();
		$result->addErrors($estimatedFunctionalMassResult->getErrors());

		$sportProteinCoefficientResult = $calculator->calcSportProteinCoefficient();
		$result->addErrors($sportProteinCoefficientResult->getErrors());

		$goalNutrientProteinBonusResult = $calculator->getGender()->calcGoalNutrientProteinBonus($calculator);
		$result->addErrors($goalNutrientProteinBonusResult->getErrors());

		if (!$result->hasErrors()) {
			$estimatedFunctionalMassValue = $estimatedFunctionalMassResult->getResult()->getNumericalValue();
			$sportProteinCoefficientValue = $sportProteinCoefficientResult->getResult()->getNumericalValue();
			$goalNutrientProteinBonusValue = $goalNutrientProteinBonusResult->getResult()->getNumericalValue();

			$value = ($estimatedFunctionalMassValue * $sportProteinCoefficientValue) + $goalNutrientProteinBonusValue;
			$value = min(150, $value);
			$proteins = new Proteins(new Amount($value), "g");

			$formula = "
				MIN(150, (estimatedFunctionalMass[$estimatedFunctionalMassValue] * sportProteinCoefficient[$sportProteinCoefficientValue]) + $goalNutrientProteinBonusValue)
				MIN(150, " . ($estimatedFunctionalMassValue * $sportProteinCoefficientValue) . " + $goalNutrientProteinBonusValue)
				= $value
			";

			$result->setResult($proteins)->setFormula($formula);
		}

		return $result;
	}

	public function calcGoalNutrients(Calculator $calculator): MetricResultCollection
	{
		$carbsResult = new QuantityMetricResult(new GoalNutrientsCarbsMetric);
		$fatsResult = new QuantityMetricResult(new GoalNutrientsFatsMetric);
		$proteinsResult = $this->calcGoalNutrientsProteins($calculator);

		$rdiResult = $calculator->calcReferenceDailyIntake();

		$fatsResult->addErrors($rdiResult->getErrors());

		if (!$carbsResult->hasErrors() && !$fatsResult->hasErrors() && !$proteinsResult->hasErrors()) {
			$nutrients = new Nutrients;
			$nutrients->setProteins($proteinsResult->getResult());
			$nutrients->setCarbs(new Carbs(new Amount(static::CARBS_DEFAULT), "g"));

			$fats = Fats::createFromEnergy(
				new Energy(
					new Amount(
						$rdiResult->getResult()->getInUnit(Energy::getBaseUnit())->getNumericalValue() - $nutrients->getEnergy()->getInBaseUnit()->getAmount()->getValue()
					),
					Energy::getBaseUnit(),
				),
			);
			$nutrients->setFats($fats);

			$carbsResult->setResult($nutrients->getCarbs());
			$fatsResult->setResult($nutrients->getFats());
			$proteinsResult->setResult($nutrients->getProteins());
		}

		return new MetricResultCollection([
			$carbsResult,
			$fatsResult,
			$proteinsResult,
		]);
	}
}
