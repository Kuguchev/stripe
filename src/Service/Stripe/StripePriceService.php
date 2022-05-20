<?php

namespace App\Service\Stripe;

use App\Entity\Price as PriceDB;
use App\Repository\PriceRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Price;
use Stripe\Product;

class StripePriceService
{
    private PriceRepository $priceRepository;
    private ProductRepository $productRepository;
    private EntityManagerInterface $em;

    public function __construct(
        PriceRepository $priceRepository,
        ProductRepository $productRepository,
        EntityManagerInterface $em
    ) {
        $this->priceRepository = $priceRepository;
        $this->productRepository = $productRepository;
        $this->em = $em;
    }

    public function createPrice(string $productId, string $unitAmount, string $currency, array $recurring = [], string $lookupkey = null): PriceDB
    {
        if ($lookupkey !== null) {
            $priceFromDB = $this->priceRepository->findPricesByLookupKey($lookupkey);
            
            if ($priceFromDB !== null) {
                return $priceFromDB;
            }
        }
        $price = Price::create([
            'currency' => $currency,
            'product' => $productId,
            'unit_amount' => $unitAmount,
            'recurring' => $recurring,
            'lookup_key' => $lookupkey,
        ]);

        $product = $this->productRepository->findOneBy(['stripeId' => $productId]);

        $priceToDB = (new PriceDB())
            ->setStripeId($price->id)
            ->setUnitAmount($unitAmount)
            ->setCurrency($currency)
            ->setProduct($product)
            ->setLookupkey($lookupkey);

        $this->em->persist($priceToDB);
        $this->em->flush();

        return $priceToDB;
    }
    
    public function getDefaultPrice(string $productId)
    {
        return Product::retrieve($productId)->default_price;
    }

    /**
     * @return PriceDB[]
     */
    public function getPricesByProductId(string $productId): array
    {
        return $this->priceRepository->findPricesByProductId($productId);
    }
}
