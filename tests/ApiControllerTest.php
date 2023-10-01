<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiControllerTest extends WebTestCase
{
    public function testCalcPrice()
    {
        $client = static::createClient();
        $client->request(
            'POST', '/calculate-price', [], [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ],
            json_encode([
                "product" => 1,
                "taxNumber" => "GR123456789",
                "couponCode" => "D51",
            ])
        );

        $this->assertResponseStatusCodeSame(200, $client->getResponse()->getStatusCode());
        $this->assertJsonStringEqualsJsonString(116.56, (float)json_decode($client->getResponse()->getContent()));
    }

    public function testPurchase()
    {
        $client = static::createClient();
        $client->request(
            'POST', '/purchase', [], [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ],
            json_encode([
                "product" => 1,
                "taxNumber" => "GR123456789",
                "couponCode" => "D51",
                "paymentProcessor" => "paypal"
            ])
        );

        $this->assertResponseStatusCodeSame(200, $client->getResponse()->getStatusCode());
        $this->assertTrue(json_decode($client->getResponse()->getContent()));
    }
}