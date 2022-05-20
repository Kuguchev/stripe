<?php

namespace App\Service\Stripe;

use Stripe\BillingPortal\Session as BillingPortalSession;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Price;
use Symfony\Component\HttpFoundation\Request;

class StripeSubscriptionService extends AbstractStripeService
{
    public function checkOutSession(string $lookUpKey): CheckoutSession
    {
        // Get the price from lookup key
        $prices = Price::all([
            'lookup_keys' => [ $lookUpKey, ],
            'expand' => ['data.product'],
        ]);

        $checkOutSession = CheckoutSession::create([
            'line_items' => [[
                'price' => $prices->data[0]->id,
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'success_url' => $this->domain . '/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $this->domain . '/cancel',
        ]);

        return $checkOutSession;
    }

    public function createPortalSession(Request $request): BillingPortalSession
    {
        $sessionId = $request->request->get('session_id');

        $checkOutSession = CheckoutSession::retrieve($sessionId);

        $billingPortalSession = BillingPortalSession::create([
            'customer' => $checkOutSession->customer,
            'return_url' => $this->domain . '/success?session_id=' . $sessionId,
        ]);

        return $billingPortalSession;
    }
}