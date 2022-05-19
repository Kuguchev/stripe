<?php

namespace App\Service;

use App\Entity\Price as PriceDB;
use App\Repository\PriceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Price;

class StripePriceService
{
    private PriceRepository $priceRepository;
    private EntityManagerInterface $em;

    public function __construct(PriceRepository $priceRepository, EntityManagerInterface $em)
    {
        $this->priceRepository = $priceRepository;
        $this->em = $em;
    }

    public function createPrice(string $productId, string $unitAmount, string $currency): PriceDB
    {
        $price = Price::create([
            'currency' => $currency,
            'product' => $productId,
            'unit_amount' => $unitAmount,
        ]);

        $priceToDB = (new PriceDB())
            ->setStripeId($price->id)
            ->setUnitAmount($unitAmount)
            ->setCurrency($currency);

        $this->em->persist($priceToDB);
        $this->em->flush();

        return $priceToDB;
    }

    /**
     * @return PriceDB[]
     */
    public function getPricesByProductId(string $productId): array
    {
        return $this->priceRepository->findPricesByProductId($productId);
    }
}