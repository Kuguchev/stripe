<?php

namespace App\Controller;

use App\Service\Stripe\StripeOnlinePaymentService;
use App\Service\Stripe\StripePriceService;
use App\Service\Stripe\StripeProductService;
use App\Service\Stripe\StripeService;
use PhpParser\Error;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OnlinePaymentController extends AbstractController
{
    private StripeOnlinePaymentService $stripeOnlinePaymentService;
    private StripeProductService $stripeProductService;
    private StripePriceService $stripePriceService;
    
    public function __construct(
        StripeOnlinePaymentService $stripeOnlinePaymentService,
        StripeProductService $stripeProductService,
        StripePriceService $stripePriceService
    ) {
        $this->stripeOnlinePaymentService = $stripeOnlinePaymentService;
        $this->stripeProductService = $stripeProductService;
        $this->stripePriceService = $stripePriceService;
    }

    /**
     * @Route("/payment-page", name="app.payment.page", methods={"GET"})
     */
    public function payment(): Response
    {
        /** Imitation of order information */
        $order = [];

        $order[] = $this->stripeProductService->createProduct(
            'Monitor',
            'Test Monitor product',
            1000,
            'usd'
        );
        
        $order[] = $this->stripeProductService->createProduct(
            'Mouse',
            'Test Mouse product',
            200,
            'usd'
        );
        
        $order[] = $this->stripeProductService->createProduct(
            'Keyboard',
            'Test Keyboard product',
            600,
            'usd'
        );

        return $this->render('online_payment/payment.html.twig', [
            'products' => $order,
        ]);
    }

    /**
     * @Route("/create-payment-intent", name="app.create.payment.intent", methods={"POST"})
     */
    public function intent(Request $request): Response
    {
        try {
            $newPaymentIntent = $this->stripeOnlinePaymentService->createPaymentIntent($request);
            return $this->json([
                'clientSecret' => $newPaymentIntent->client_secret,
            ]);
        } catch (Error $e) {
            return $this->json([
                'error' => $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            ]);
        }
    }
}
