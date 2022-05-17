<?php

namespace App\Entity;

use App\Repository\PriceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PriceRepository::class)
 */
class Price
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $stripeId;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private string $unitAmount;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStripeId(): string
    {
        return $this->stripeId;
    }

    public function setStripeId(string $stripeId): self
    {
        $this->stripeId = $stripeId;

        return $this;
    }

    public function getUnitAmount(): string
    {
        return $this->unitAmount;
    }

    public function setUnitAmount(string $unitAmount): self
    {
        $this->unitAmount = $unitAmount;

        return $this;
    }
}
