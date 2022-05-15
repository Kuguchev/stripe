<?php

namespace App\Controller;

use Stripe\Event;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\StripeService;
use Symfony\Component\Serializer\SerializerInterface;

class PaymentController extends AbstractController
{

    private StripeService $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * @Route("/checkout", name="app.payment.checkout", methods={"POST", "GET"})
     */
    public function intent(Request $request): Response
    {
        if ($request->getMethod() === "POST") {
            return $this->json($this->stripeService->paymentIntent());
        } else {
            return $this->render('stripe/checkout.html.twig');
        }
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