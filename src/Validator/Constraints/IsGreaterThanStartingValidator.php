<?php
/**
 * Created by PhpStorm.
 * User: jeromebutel
 * Date: 07/02/2018
 * Time: 12:28
 */

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsGreaterThanStartingValidator extends ConstraintValidator
{
    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        # Get the event's starting and ending dates
        $startingTimestamp = $this->context->getObject()->getStartingDate()->getTimestamp();
        $endingTimestamp = $this->context->getObject()->getEndingDate()->getTimestamp();

        # Check!
        if (!($endingTimestamp > $startingTimestamp)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}