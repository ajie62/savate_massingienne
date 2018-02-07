<?php
/**
 * Created by PhpStorm.
 * User: jeromebutel
 * Date: 07/02/2018
 * Time: 12:27
 */

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 *
 * Class IsGreaterThanStarting
 * @package App\Validator\Constraints
 */
class IsGreaterThanStarting extends Constraint
{
    public $message = 'La date de fin doit être supérieure à la date de début.';
}