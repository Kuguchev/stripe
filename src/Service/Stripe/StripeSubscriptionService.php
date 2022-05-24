<?php

namespace App\Service\Stripe;

use App\Model\SubscriberModel;
use Stripe\BillingPortal\Session as BillingPortalSession;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Price;
use Stripe\Subscription;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Flex\Response;

class StripeSubscriptionService extends AbstractStripeService
{
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        parent::__construct();
        $this->serializer = $serializer;
    }

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

    public function createSubscription(Request $request): Subscription
    {
        /** @var SubscriberModel $subscriber */
        $subscriber = $this->serializer->deserialize($request->getContent(), SubscriberModel::class, 'json');

        return Subscription::create([
            'customer' => $subscriber->getCustomerId(),
            'items' => [
                ['price' => $subscriber->getPriceId()],
            ],
            'payment_behavior' => 'default_incomplete',
            'expand' => ['latest_invoice.payment_intent'],
        ]);
    }
    
    public function updateSubscription(string $subscriptionId, string $newPriceId)
    {
        $subscription = Subscription::retrieve($subscriptionId);
        Subscription::update($subscriptionId, [
            'cancel_at_period_end' => false,
            'proration_behavior' => 'create_prorations',
            'items' => [
                [
                    'id' => $subscription->items->data,
                    'price' => $newPriceId,
                ]
            ],
        ]);
    }
}
