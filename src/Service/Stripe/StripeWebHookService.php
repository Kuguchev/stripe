<?php

namespace App\Service\Stripe;

use Stripe\Event;
use Stripe\PaymentIntent;
use Symfony\Component\HttpFoundation\Response;

class StripeWebHookService
{
    public function webhookHandler(Event $event): Response
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
            default:
                // Unexpected event type
                //error_log('Received unknown event type');
        }
        return (new Response())->setStatusCode(Response::HTTP_OK);
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
