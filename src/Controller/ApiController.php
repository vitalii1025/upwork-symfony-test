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

        $retData = $this->calculatePrice($em, $data);
        if ($retData["code"] == 0) {
            return new JsonResponse(
                $retData["price"],
                200,
                ["Content-Type" => "application/json"]
            );
        } else {
            return new JsonResponse(
                ["error" => $retData["message"]],
                400,
                ["Content-Type" => "application/json"]
            );           
        }
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
            $ret_object["code"] = -1;
            $ret_object["message"] = "Product with id {$data['product']} not found";
            return $ret_object;
        }

        $code = substr($data['taxNumber'], 0, 2);
        $country = $em->getRepository(Country::class)->findOneBy(['code' => $code]);
        if (!$country) {
            $ret_object["code"] = -1;
            $ret_object["message"] = "Country with taxNumber {$code} not found";
            return $ret_object;
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

        $ret_object["price"] = $price;

        return $ret_object;
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
        $ret_object = $this->calculatePrice($em, $data);
        $resp = $paymentService->pay($ret_object['price'], $data['paymentProcessor']);
        return new JsonResponse(
            $resp,
            200,
            ["Content-Type" => "application/json"]
        );
    }
}