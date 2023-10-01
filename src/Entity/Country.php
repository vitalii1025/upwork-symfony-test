<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity()]
class Country
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(length: 2)]
    #[Assert\NotBlank]
    private ?string $code = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    #[Assert\When(
        expression: 'this.getDiscountType() === DISCOUNT_PERCENT',
        constraints: [
            new Assert\LessThanOrEqual(100, message: 'The value should be between 0 and 100!'),
            new Assert\GreaterThanOrEqual(0, message: 'The value should be between 0 and 100!')
        ],
    )]
    #[Assert\When(
        expression: 'this.getDiscountType() === DISCOUNT_FIXED',
        constraints: [
            new Assert\GreaterThanOrEqual(0, message: 'The value should be greater than 0')
        ],
    )]
    private ?float $tax;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
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

    public function getTax(): ?string
    {
        return $this->tax;
    }

    public function setTax(string $tax): static
    {
        $this->tax = $tax;

        return $this;
    }
}
