<?php

namespace Fatty\Exceptions;

class InvalidGoalVectorException extends FattyException
{
	public function __construct()
	{
		$this->message = "Neplatný cílový stav.";
		$this->names = ['goal_vector'];
	}
}
