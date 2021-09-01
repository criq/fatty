<?php

namespace Fatty\Genders;

use Fatty\Amount;
use Fatty\BodyType;
use Fatty\Calculator;
use Fatty\Energy;
use Fatty\Exceptions\FattyException;
use Fatty\Exceptions\FattyExceptionList;
use Fatty\Percentage;

class Male extends \Fatty\Gender
{
	const ESSENTIAL_FAT_PERCENTAGE = .5;

	/*****************************************************************************
	 * Procento tělesného tuku - BFP.
	 */
	protected function calcBodyFatPercentageByProportions(Calculator $calculator) : Percentage
	{
		$waist = $calculator->getProportions()->getWaist()->getInCm()->getAmount()->getValue();
		$neck = $calculator->getProportions()->getNeck()->getInCm()->getAmount()->getValue();
		$height = $calculator->getProportions()->getHeight()->getInCm()->getAmount()->getValue();

		return new Percentage(new Amount(((495 / (1.0324 - (0.19077 * log10($waist - $neck)) + (0.15456 * log10($height)))) - 450) * .01));
	}

	public function calcBodyFatPercentageByProportionsFormula(Calculator $calculator) : string
	{
		$waist = $calculator->getProportions()->getWaist()->getInCm()->getAmount()->getValue();
		$neck = $calculator->getProportions()->getNeck()->getInCm()->getAmount()->getValue();
		$height = $calculator->getProportions()->getHeight()->getInCm()->getAmount()->getValue();

		return '((495 / (1.0324 - (0.19077 * log10(waist[' . $waist . '] - neck[' . $neck . '])) + (0.15456 * log10(height[' . $height . '])))) - 450) * .01';
	}

	/*****************************************************************************
	 * Bazální metabolismus - BMR.
	 */
	public function calcBasalMetabolicRate(Calculator $calculator) : Energy
	{
		$exceptionList = new FattyExceptionList;

		if (!$calculator->getWeight()) {
			$exceptionList->append(FattyException::createFromAbbr('missingWeight'));
		}

		if (!$calculator->getProportions()->getHeight()) {
			$exceptionList->append(FattyException::createFromAbbr('missingHeight'));
		}

		if (!$calculator->getBirthday()) {
			$exceptionList->append(FattyException::createFromAbbr('missingBirthday'));
		}

		if (count($exceptionList)) {
			throw $exceptionList;
		}

		$amount = new Amount((10 * $calculator->getWeight()->getInKg()->getAmount()->getValue()) + (6.25 * $calculator->getProportions()->getHeight()->getInCm()->getAmount()->getValue()) - (5 * $calculator->getBirthday()->getAge()) + 5);

		return new Energy($amount, 'kCal');
	}

	public function getBasalMetabolicRateFormula(Calculator $calculator) : string
	{
		return '(10 * weight[' . $calculator->getWeight()->getInKg()->getAmount() . ']) + (6.25 * height[' . $calculator->getProportions()->getHeight()->getInCm()->getAmount() . ']) - (5 * age[' . $calculator->getBirthday()->getAge() . ']) + 5';
	}

	/*****************************************************************************
	 * Typ postavy.
	 */
	public function calcBodyType(Calculator $calculator) : BodyType
	{
		$waistHipRatio = $calculator->calcWaistHipRatio();

		if ($waistHipRatio->getValue() < .85) {
			return new \Fatty\BodyTypes\PearOrHourglass;
		} elseif ($waistHipRatio->getValue() >= .8 && $waistHipRatio->getValue() < .9) {
			return new \Fatty\BodyTypes\Balanced;
		} elseif ($waistHipRatio->getValue() >= .9 && $waistHipRatio->getValue() < .95) {
			return new \Fatty\BodyTypes\Apple;
		} else {
			return new \Fatty\BodyTypes\AppleWithHigherRisk;
		}
	}
}
