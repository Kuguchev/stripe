<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\StripeService;

class PaymentController extends AbstractController
{

    private StripeService $stripeService;
    private ProductRepository $productRepository;
    private EntityManagerInterface $em;

    public function __construct(StripeService $stripeService, ProductRepository $productRepository, EntityManagerInterface $em)
    {
        $this->stripeService = $stripeService;
        $this->productRepository = $productRepository;
        $this->em = $em;
    }

    /**
     * @Route("/checkout", name="app.payment.checkout", methods={"POST", "GET"})
     */
    public function intent(Request $request): Response
    {
        if ($request->getMethod() === "POST") {
            return $this->json($this->stripeService->paymentIntent($request));
        }

//        $stripe = new StripeClient($_ENV['STRIPE_SECRET_KEY_TEST']);
//        $products = $stripe->products->all()->toArray();
//
//        foreach ($products['data'] as $product) {
//
//            $addProduct = (new Product())
//            ->setStripeId($product['id'])
//            ->setName($product['name'])
//            ->setDescription($product['description']);
//
//            $this->em->persist($addProduct);
//        }
//        $this->em->flush();

        $products = $this->productRepository->findAll();

        return $this->render('stripe/checkout.html.twig', [
            'products' => $products,
        ]);
    }

    /**
     * @Route("/webhook", name="app.payment.webhook", methods={"POST"})
     */
    public function hook(Request $request)
    {
        $this->stripeService->webhookHandler($request);

        return (new Response())->setStatusCode(Response::HTTP_OK);
    }
}