<?php

namespace App\Service;

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

    public function createProduct(string $productName, string $description): ProductDB
    {
        $productFromDB = $this->productRepository->findProductByName($productName);

        if ($productFromDB === null) {

            $product = Product::create([
                'name' => $productName,
                'description' => $description,
            ]);

            $productToDB = (new ProductDB())
                ->setStripeId($product->id)
                ->setName($productName)
                ->setDescription($product->description);

            $this->em->persist($productToDB);
            $this->em->flush();

            return $productToDB;
        }

        return $productFromDB;
    }
}