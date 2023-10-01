<?php

namespace App\Controller;

use App\Entity\Country;
use App\Entity\Coupon;
use App\Entity\Product;
use App\Service\ApiValidationService;
use App\Service\PaymentService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use const App\Entity\DISCOUNT_FIXED;
use const App\Entity\DISCOUNT_PERCENT;

class ApiController extends AbstractController
{
    /**
     * Get final price
     * @param ApiValidationService $validationService
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[Route('/calculate-price', name: 'calculate_price')]
    public function calculateAction(ApiValidationService $validationService, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $jsonData = file_get_contents('php://input');
        // Decode the JSON data into a PHP associative array
        $data = json_decode($jsonData, true);
        $errors = [];
        $violations = $validationService->validateCalcRequest($data);
        foreach ($violations as $violation) {
            if ($violation->count()) {
                $errors[] = $violation;
            }
        }

        if (count($errors) > 0) {
            return new JsonResponse(
                ["error" => implode(' ', $errors)],
                400,
                ["Content-Type" => "application/json"]
            );
        }

        $price = $this->calculatePrice($em, $data);

        return new JsonResponse(
            $price,
            200,
            ["Content-Type" => "application/json"]
        );
    }

    /**
     * Calculate final price
     * @param EntityManagerInterface $em
     * @param array $data
     * @return JsonResponse|void
     */
    private function calculatePrice(EntityManagerInterface $em, array $data = [])
    {
        $product = $em->getRepository(Product::class)->find($data['product']);

        $ret_object = [
            "message" => "",
            "price" => 0,
            "code" => 0,
        ];
        
        if (!$product) {
            return new JsonResponse(
                "Product with id {$data['product']} not found",
                400,
                ["Content-Type" => "application/json"]
            );
        }

        $code = substr($data['taxNumber'], 0, 2);
        $country = $em->getRepository(Country::class)->findOneBy(['code' => $code]);
        if (!$country) {
            return new JsonResponse(
                "Country with taxNumber {$code} not found",
                400,
                ["Content-Type" => "application/json"]
            );
        }

        $coupon = $em->getRepository(Coupon::class)->findOneBy(['code' => $data['couponCode']]);
        $price = $product->getPrice() + (($country->getTax() / 100) * $product->getPrice());
        if ($coupon) {
            if ($coupon->getDiscountType() === DISCOUNT_FIXED) {
                $price = $price - $coupon->getDiscount();
            }
            if ($coupon->getDiscountType() === DISCOUNT_PERCENT) {
                $price = $price - (($coupon->getDiscount() / 100) * $price);
            }
        }

        return $price;
    }

    /**
     * Payment
     * @param ApiValidationService $validationService
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param PaymentService $paymentService
     * @return JsonResponse
     * @throws Exception
     */
    #[Route('/purchase', name: 'purchase')]
    public function purchaseAction(ApiValidationService $validationService, Request $request, EntityManagerInterface $em, PaymentService $paymentService): JsonResponse
    {
        $jsonData = file_get_contents('php://input');
        // Decode the JSON data into a PHP associative array
        $data = json_decode($jsonData, true);
        $violations = $validationService->validateBuyRequest($data);
        $errors = [];
        foreach ($violations as $violation) {
            if ($violation->count()) {
                $errors[][] = [
                    'property' => $violation[0]->getPropertyPath(),
                    'value' => $violation[0]->getInvalidValue(),
                    'message' => $violation[0]->getMessage(),
                ];
            }
        }

        if (count($errors) > 0) {
            return new JsonResponse(
                $errors,
                200,
                ["Content-Type" => "application/json"]
            );
        }
        $price = $this->calculatePrice($em, $data);
        $resp = $paymentService->pay($price, $data['paymentProcessor']);
        return new JsonResponse(
            $resp,
            200,
            ["Content-Type" => "application/json"]
        );
    }
}