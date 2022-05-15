<?php

namespace App\Service;

use App\Exception\PaymentIntentException;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class StripeService
{
    private $privateKey;
    private $endpointSecretKey;
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;

        $this->endpointSecretKey = $_ENV['ENDPOINT_SECRET_KEY_CLI'];

        if ($_ENV['APP_ENV'] === 'dev') {
            $this->privateKey = $_ENV['STRIPE_SECRET_KEY_TEST'];
        } else {
            $this->privateKey = $_ENV['STRIPE_SECRET_KEY_LIVE'];
        }
    }

    public function paymentIntent(): array
    {
        try {
            Stripe::setApiKey($this->privateKey);

            $paymentIntent = PaymentIntent::create([
                'amount' => 200,
                'currency' => 'eur',
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            return [
                'clientSecret' => $paymentIntent->client_secret,
            ];

        } catch (\Throwable $exception) {
            throw new PaymentIntentException($exception->getMessage());
        }
    }

    public function webhookHandler(Request $request): Response
    {
        $payload = $request->getContent();
        $event = null;

        try {
            $event = $this->serializer->deserialize($payload, Event::class,'json');
        } catch (\UnexpectedValueException $exception) {

        }

        if ($this->endpointSecretKey) {
            $sigHeader = $request->server->get('HTTP_STRIPE_SIGNATURE');

            try {
                $event = Webhook::constructEvent($payload, $sigHeader, $this->endpointSecretKey);
            } catch (SignatureVerificationException $exception) {

            }
        }

        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object; // contains a \Stripe\PaymentIntent
                // Then define and call a method to handle the successful payment intent.
                 $this->handlePaymentIntentSucceeded($paymentIntent);
                break;
            case 'payment_method.attached':
                $paymentMethod = $event->data->object; // contains a \Stripe\PaymentMethod
                // Then define and call a method to handle the successful attachment of a PaymentMethod.
                $this->handlePaymentMethodAttached($paymentMethod);
                break;
            default:
                // Unexpected event type
                //error_log('Received unknown event type');
        }

    }

    public function handlePaymentIntentSucceeded(PaymentIntent $paymentIntent)
    {
        //TODO
    }

    public function handlePaymentMethodAttached(PaymentIntent $paymentIntent)
    {
        //TODO
    }
}