<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

const DISCOUNT_FIXED = 'fix';
const DISCOUNT_PERCENT = 'prc';
#[ORM\Entity()]
class Coupon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 13)]
    #[Assert\Length(
        min: 12,
        max: 13,
        minMessage: 'Tax code can not be less that {{ limit }} symbols',
        maxMessage: 'Tax code can not be larger that {{ limit }} symbols',
    )]
    private ?string $code = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    #[Assert\GreaterThan(0)]
    #[Assert\When(
        expression: 'this.getDiscountType() === DISCOUNT_PERCENT',
        constraints: [
            new Assert\LessThanOrEqual(100, message: 'The value should be between 0.01 and 100!'),
            new Assert\GreaterThanOrEqual(0.01, message: 'The value should be between 0.01 and 100!')
        ],
    )]
    private ?float $discount = 0.01;

    #[ORM\Column(length: 4)]
    #[Assert\Length(exactly: 4)]
    private ?string $discount_type = DISCOUNT_FIXED;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getDiscount(): ?float
    {
        return $this->discount;
    }

    public function setDiscount(float $discount): static
    {
        $this->discount = $discount;

        return $this;
    }

    public function getDiscountType(): ?string
    {
        return $this->discount_type;
    }

    public function setDiscountType(string $discount_type): static
    {
        $this->discount_type = $discount_type;

        return $this;
    }
}
