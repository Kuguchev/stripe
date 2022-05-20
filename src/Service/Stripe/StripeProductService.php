<?php

namespace App\Service\Stripe;

use App\Entity\Price;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Product;
use App\Entity\Product as ProductDB;

class StripeProductService
{
    private ProductRepository $productRepository;
    private EntityManagerInterface $em;

    public function __construct(ProductRepository $productRepository, EntityManagerInterface $em)
    {
        $this->productRepository = $productRepository;
        $this->em = $em;
    }

    public function createProduct(string $productName, string $description, int $price, string $currency): ProductDB
    {
        $productFromDB = $this->productRepository->findProductByName($productName);

        if ($productFromDB === null) {
            $product = Product::create([
                'name' => $productName,
                'description' => $description,
                'default_price_data' => [
                    'currency' => $currency,
                    'unit_amount' => $price,
                ]
            ]);

            $productToDB = (new ProductDB())
                ->setStripeId($product->id)
                ->setName($productName)
                ->setDescription($product->description);
            
            $priceToDB = (new Price())
                ->setStripeId($product->default_price)
                ->setCurrency($currency)
                ->setUnitAmount($price)
                ->setProduct($productToDB);
            
            $this->em->persist($productToDB);
            $this->em->persist($priceToDB);
            $this->em->flush();

            return $productToDB;
        }

        return $productFromDB;
    }
}
