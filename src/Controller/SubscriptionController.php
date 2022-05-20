<?php

namespace App\Controller;

use App\Entity\LookupKey;
use App\Form\LookupKeyType;
use App\Service\Stripe\StripePriceService;
use App\Service\Stripe\StripeProductService;
use App\Service\Stripe\StripeSubscriptionService;
use PhpParser\Error;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SubscriptionController extends AbstractController
{
    private StripeProductService $stripeProductService;
    private StripePriceService $stripePriceService;
    private StripeSubscriptionService $stripeSubscriptionService;

    public function __construct(
        StripeProductService $stripeProductService,
        StripePriceService $stripePriceService,
        StripeSubscriptionService $stripeSubscriptionService
    ) {
        $this->stripeProductService = $stripeProductService;
        $this->stripePriceService = $stripePriceService;
        $this->stripeSubscriptionService = $stripeSubscriptionService;
    }

    /**
     * @Route("/create-checkout-session", methods={"POST", "GET"})
     */
    public function checkout(Request $request): Response
    {
        // Create test product
        $product = $this->stripeProductService->createProduct('Car', 'Test', 3300, 'usd');
        $price = $this->stripePriceService->createPrice($product->getStripeId(), 200, 'usd', ['interval' => 'year'], '322');

        $lookupKey = (new LookupKey())
            ->setLookupKey($price->getLookupkey());
        $form = $this->createForm(LookupKeyType::class, $lookupKey);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $lookupKey = $form->get('lookupKey')->getData();
                $stripeCheckOutSession = $this->stripeSubscriptionService->checkOutSession($lookupKey);

                return $this->redirect($stripeCheckOutSession->url, 303);
            } catch (Error $e) {
                return $this->json(['error' => $e->getMessage()], 500);
            }
        }

        return $this->render('subscription/subscription.html.twig', [
            'form' => $form->createView(),
            'price' => $price->getUnitAmount(),
            'productName' => $product->getName(),
        ]);
    }

    /**
     * @Route("/success", methods={"GET"})
     */
    public function success(Request $request): Response
    {
        return $this->render('subscription/success.html.twig');
    }

    /**
     * @Route("/cancel", methods={"GET"})
     */
    public function cancel(Request $request): Response
    {
        return $this->render('subscription/cancel.html.twig');
    }

    /**
     * @Route("/create-portal-session", methods={"POST"})
     */
    public function portalSession(Request $request): Response
    {
        try {
            $billingPortalSession = $this->stripeSubscriptionService->createPortalSession($request);
            return $this->redirect($billingPortalSession->url, 303);
        } catch (Error $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
