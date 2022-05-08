<?php

namespace App\Controller;

use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaymentController extends AbstractController
{
    /**
     * @return Response
     *
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        return $this->render('/payment/index.html.twig', []);
    }
    /**
     * @return Response
     *
     * @Route("/checkout", name="create-checkout-session")
     */
    public function checkout($stripeSK): Response
    {
        Stripe::setApiKey($stripeSK);

        $session = Session::create(
            [
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'T-shirt',
                        ],
                        'unit_amount' => 2000,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $this->generateUrl('success_url', [],
                    UrlGeneratorInterface::ABSOLUTE_URL),
                'cancel_url' => $this->generateUrl('cancel_url', [],
                    UrlGeneratorInterface::ABSOLUTE_URL),
            ]
        );
        return $this->redirect($session->url, 303);
    }

    /**
     * @return Response
     *
     * @Route("/success-url", name="success_url")
     */
    public function successUrl(): Response
    {
        return $this->render('payment/success.html.twig', []);
    }

    /**
     * @return Response
     *
     * @Route("/cancel-url", name="cancel_url")
     */
    public function cancelUrl(): Response
    {
        return $this->render('payment/cancel.html.twig', []);
    }
}