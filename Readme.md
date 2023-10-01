# Project Name

Symfony REST Application

This project is a Symfony REST application for calculating product prices and making payments.

## Endpoints

1. Calculate Price Endpoint:
    - Path:  `http://localhost:80/calculate-price` 
    - Method: POST
    - Request Body:
{
    "product": 1,
    "taxNumber": "DE123456789",
    "couponCode": "D15"
}
    - cURL Command:

        curl -X POST http://localhost:80/calculate-price 
        -H "Content-Type: application/json"
        -d '{"product": 1, "taxNumber": "DE123456789", "couponCode": "D15"}'  
2. Complete Purchase Endpoint:
    - Path:  `http://localhost:80/purchase` 
    - Method: POST
    - Request Body:
{
    "product": 1,
    "taxNumber": "DE123456789",
    "couponCode": "D15",
    "paymentProcessor": "paypal"
}
    - cURL Command:

        curl -X POST http://localhost:80/purchase 
        -H "Content-Type: application/json"
        -d '{"product": 1, "taxNumber": "DE123456789", "couponCode": "D15", "paymentProcessor": "paypal"}'  

Please note that you need to replace  `http://localhost:80`  with the actual base URL of your Symfony application.
