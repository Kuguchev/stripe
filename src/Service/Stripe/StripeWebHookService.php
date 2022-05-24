<?php

namespace App\Service\Stripe;

use Stripe\Event;
use Stripe\Invoice;
use Stripe\PaymentIntent;
use Stripe\Subscription;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class StripeWebHookService extends AbstractStripeService
{
    public function __construct()
    {
        parent::__construct();
    }

    public function webhookHandler(Event $event): JsonResponse
    {
        switch ($event->type) {
            case 'payment_intent.succeeded': {
                $paymentIntent = $event->data->object; // contains a \Stripe\PaymentIntent
                // Then define and call a method to handle the successful payment intent.
                $this->handlePaymentIntentSucceeded($paymentIntent);
            } break;
            case 'payment_method.attached': {
                $paymentMethod = $event->data->object; // contains a \Stripe\PaymentMethod
                // Then define and call a method to handle the successful attachment of a PaymentMethod.
                $this->handlePaymentMethodAttached($paymentMethod);
            } break;
            case 'payment_intent.created': {
                $paymentMethod = $event->data->object; // contains a \Stripe\PaymentMethod
                // Then define and call a method to handle the successful attachment of a PaymentMethod.
                $this->handlePaymentIntentCreated($paymentMethod);
            } break;
            case 'customer.created':
            {
//                /** @var Customer $newCustomer */
//                $newCustomer = $event->data->object;
//                $newCustomerDB = (new \App\Entity\Customer())
//                    ->setStripeId($newCustomer->id)
//                    ->setEmail($newCustomer->email);
//                $this->em->persist($newCustomerDB);
//                $this->em->flush();
            } break;
            case 'customer.subscription.trial_will_end':
                {
                    $subscription = $event->data->object; // contains a \Stripe\Subscription
                    // Then define and call a method to handle the trial ending.
                    $this->handleTrialWillEnd($subscription);
                } break;
            case 'customer.subscription.created':
            {
                $subscription = $event->data->object; // contains a \Stripe\Subscription
                // Then define and call a method to handle the subscription being created.
                $this->handleSubscriptionCreated($subscription);
            } break;
            case 'customer.subscription.deleted':
            {
                $subscription = $event->data->object; // contains a \Stripe\Subscription
                // Then define and call a method to handle the subscription being deleted.
                $this->handleSubscriptionDeleted($subscription);
            } break;
            case 'customer.subscription.updated':
            {
                $subscription = $event->data->object; // contains a \Stripe\Subscription
                // Then define and call a method to handle the subscription being updated.
                $this->handleSubscriptionUpdated($subscription);
            } break;
            case 'invoice.paid':
            {
                $invoicePaid = $event->data->object;
            } break;
            case 'invoice.payment_succeeded':
            {
                /** @var Invoice $invoice */
                $invoice= $event->data->object;
                
                if ($invoice->billing_reason === 'subscription_create') {
                    
                    $subscriptionId = $invoice->subscription;
                    $paymentIntentId = $invoice->payment_intent;

                    # Retrieve the payment intent used to pay the subscription
                    $paymentIntent = PaymentIntent::retrieve($paymentIntentId, []);
                    Subscription::update($subscriptionId, [
                        ['default_payment_method' => $paymentIntent->payment_method]
                    ]);
                }
            } break;
            case 'invoice.payment_failed':
            {
                $invoiceFailed = $event->data->object;
            } break;
            default:
                // Unexpected event type
                //error_log('Received unknown event type');
        }
        return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
    }

    private function handlePaymentIntentSucceeded(PaymentIntent $paymentIntent): void
    {
        //TODO
    }

    private function handlePaymentMethodAttached(PaymentIntent $paymentIntent): void
    {
        //TODO
    }

    private function handlePaymentIntentCreated(PaymentIntent $paymentIntent): void
    {
        //TODO
    }

    private function handleSubscriptionUpdated($subscription): void
    {
        //TODO
    }

    private function handleSubscriptionDeleted($subscription): void
    {
        //TODO
    }

    private function handleSubscriptionCreated($subscription): void
    {
        //TODO
    }

    private function handleTrialWillEnd($subscription): void
    {
        //TODO
    }
}
