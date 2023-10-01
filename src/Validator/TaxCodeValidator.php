<?php

namespace App\Validator;

use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class TaxCodeValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof TaxCode) {
            throw new UnexpectedTypeException($constraint, TaxCode::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $code = substr($value, 0, 2);

        switch ($code) {
            case 'DE':
                $regexp = '/^DE[[:digit:]]{9}$/';
                break;
            case 'ID':
                $regexp = '/^IT[[:digit:]]{11}$/';
                break;
            case 'GR':
                $regexp = '/^GR[[:digit:]]{9}$/';
                break;
            case 'FR':
                $regexp = '/^FR[[:alpha:]]{2}[[:digit:]]{9}$/';
                break;
            default:
                $regexp = null;
                break;
        }

        if ($regexp && !preg_match($regexp, $value, $matches)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%string%', $value)
                ->addViolation();
        }
    }
}