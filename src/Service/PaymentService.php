<?php

namespace App\Service;

use Systemeio\TestForCandidates\PaymentProcessor\PaypalPaymentProcessor;
use Systemeio\TestForCandidates\PaymentProcessor\StripePaymentProcessor;

class PaymentService
{
    /**
     * @throws \Exception
     */
    public function pay(int $price, string $paymentProcessor) {
        $resp = null;
        if ($paymentProcessor == 'paypal') {
            $pp = new PaypalPaymentProcessor();
            $pp->pay($price);
            $resp = true;
        }
        if ($paymentProcessor == 'stripe') {
            $pp = new StripePaymentProcessor();
            $resp = $pp->processPayment($price);
        }

        return $resp;
    }

}