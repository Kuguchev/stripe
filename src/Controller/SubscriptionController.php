<?php

namespace App\Controller;

use App\Entity\LookupKey;
use App\Form\LookupKeyType;
use App\Service\Stripe\StripeCustomerService;
use App\Service\Stripe\StripePriceService;
use App\Service\Stripe\StripeProductService;
use App\Service\Stripe\StripeSubscriptionService;
use Laminas\Code\Exception\RuntimeException;
use PhpParser\Error;
use Stripe\Subscription;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SubscriptionController extends AbstractController
{
    private StripeProductService $stripeProductService;
    private StripePriceService $stripePriceService;
    private StripeSubscriptionService $stripeSubscriptionService;
    private StripeCustomerService $stripeCustomerService;
    private RequestStack $requestStack;

    public function __construct(
        StripeProductService $stripeProductService,
        StripePriceService $stripePriceService,
        StripeSubscriptionService $stripeSubscriptionService,
        StripeCustomerService $stripeCustomerService,
        RequestStack $requestStack
    ) {
        $this->stripeProductService = $stripeProductService;
        $this->stripePriceService = $stripePriceService;
        $this->stripeSubscriptionService = $stripeSubscriptionService;
        $this->stripeCustomerService = $stripeCustomerService;
        $this->requestStack = $requestStack;
    }

    /**
     * @Route("/create-checkout-session", name="app.create.checkout.session", methods={"POST", "GET"})
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
     * @Route("/success", name="app.succsess", methods={"GET"})
     */
    public function success(Request $request): Response
    {
        return $this->render('subscription/success.html.twig');
    }

    /**
     * @Route("/cancel", name="app.cancel", methods={"GET"})
     */
    public function cancel(Request $request): Response
    {
        return $this->render('subscription/cancel.html.twig');
    }

    /**
     * @Route("/create-portal-session", name="app.create.portal.session", methods={"POST"})
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

    /**
     * @Route("/create-subscription", methods={"POST"})
     */
    public function createSubscription(Request $request): Response
    {
        try {
            $subscription = $this->stripeSubscriptionService->createSubscription($request);
            return $this->json(['clientSecret' => $subscription->latest_invoice->payment_intent->client_secret]);
        } catch (Error $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/cancel-subscription", methods={"POST"})
     */
    public function cancelSubscription(Request $request): JsonResponse
    {
        try {
            $subscription = $this->stripeSubscriptionService->cancelSubscription($request);
            return $this->json($subscription);
        } catch (Error $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * @Route("/update-subscription", methods={"POST"})
     */
    public function updateSubscription(Request $request): Response
    {
        try {
            $updatedSubscription = $this->stripeSubscriptionService->updateSubscription($request);
            return $this->json($updatedSubscription);
        } catch (Error $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
