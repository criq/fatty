<?php

namespace Fatty\Approaches\DiaMama;

use Fatty\Amount;
use Fatty\Calculator;
use Fatty\Metrics\QuantityMetric;
use Fatty\Nutrients\Proteins;

class Standard extends \Fatty\Approaches\Standard
{
	const CARBS_DEFAULT = 150;
	const CODE = "DIAMAMA_STANDARD";
	const LABEL_DECLINATED = "standardní dietu";

	public function calcGoalNutrientProteins(Calculator $calculator): QuantityMetric
	{
		$estimatedFunctionalMass = $calculator->calcEstimatedFunctionalMass()->getResult();
		$estimatedFunctionalMassValue = $estimatedFunctionalMass->getAmount()->getValue();

		$sportProteinCoefficient = $calculator->calcSportProteinCoefficient()->getResult();
		$sportProteinCoefficientValue = $sportProteinCoefficient->getValue();

		$goalNutrientProteinBonus = $calculator->getGender()->calcGoalNutrientProteinBonus($calculator)->getResult();
		$goalNutrientProteinBonusValue = $goalNutrientProteinBonus->getAmount()->getValue();

		$resultValue = ($estimatedFunctionalMassValue * $sportProteinCoefficientValue) + $goalNutrientProteinBonusValue;
		$result = new Proteins(new Amount($resultValue), "g");

		$formula = "
			(estimatedFunctionalMass[$estimatedFunctionalMassValue] * sportProteinCoefficient[$sportProteinCoefficientValue]) + $goalNutrientProteinBonusValue
			" . ($estimatedFunctionalMassValue * $sportProteinCoefficientValue) . " + $goalNutrientProteinBonusValue
			= $resultValue
		";

		return new QuantityMetric(
			"goalNutrientsProteins",
			$result,
			$formula,
		);
	}
}
