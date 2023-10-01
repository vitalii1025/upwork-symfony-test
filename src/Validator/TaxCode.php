<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

class TaxCode extends Constraint
{
    public string $message = 'The `taxNumber` is not valid.';

    public function validatedBy(): string
    {
        return 'app.validator.tax_code';
    }
}