<?php

namespace App\Service;

use App\Validator;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiValidationService
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Validate values to buy
     * @param array $data
     * @return array
     */
    public function validateBuyRequest(array $data): array /*ConstraintViolationListInterface*/
    {
        $violations = $this->validateCalcRequest($data);
        $paymentProcessorConstrains = [
            new Assert\NotBlank([], 'The `paymentProcessor` field can not be blank'),
            new Assert\Regex(array(
                'pattern' => '/^(paypal|stripe)$/',
                'message' => 'Unknown payment processor.'
            ))
        ];
        $violations[] = $this->validator->validate(
            $data['paymentProcessor'],
            $paymentProcessorConstrains
        );


        return $violations;
    }

    /**
     * Validate values to calculate price
     * @param array $data
     * @return array
     */
    public function validateCalcRequest(array $data): array /*ConstraintViolationListInterface*/
    {
        $productIdConstraints = [
            new Assert\NotBlank([], 'The `product` field can not be blank'),
            new Assert\Type('integer', 'Please use number in the `product` field'),
            new Assert\Regex(array(
                'pattern' => '/^[0-9]\d*$/',
                'message' => 'Please use only positive numbers for filed `product`.'
            ))
        ];
        $taxCodeConstraints = [
            new Assert\NotBlank([], 'The `taxNumber` field can not be blank'),
            new Validator\TaxCode()
        ];
        $couponCodeConstraints = [
            new Assert\Regex('/^[\w]+$/')
        ];
        $violations[] = $this->validator->validate(
            $data['product'],
            $productIdConstraints
        );
        $violations[] = $this->validator->validate(
            $data['taxNumber'],
            $taxCodeConstraints
        );
        $violations[] = $this->validator->validate(
            $data['couponCode'],
            $couponCodeConstraints
        );

        return $violations;
    }
}