<?php

namespace App\Service;

use App\Exception\PaymentIntentException;
use App\Model\OrderModel;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Customer;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

class StripeService
{
    private $privateKey;
    private $endpointSecretKey;
    private SerializerInterface $serializer;
    private EntityManagerInterface $em;

    public function __construct(SerializerInterface $serializer, EntityManagerInterface $em)
    {
        $this->serializer = $serializer;
        $this->em = $em;

        $this->endpointSecretKey = $_ENV['ENDPOINT_SECRET_KEY_CLI'];

        if ($_ENV['APP_ENV'] === 'dev') {
            $this->privateKey = $_ENV['STRIPE_SECRET_KEY_TEST'];
        } else {
            $this->privateKey = $_ENV['STRIPE_SECRET_KEY_LIVE'];
        }
    }

    public function paymentIntent(Request $request): array
    {
        try {
            Stripe::setApiKey($this->privateKey);

            /** @var OrderModel[] $items */
            $orderModel = $this->serializer->deserialize($request->getContent(), OrderModel::class,'json');

            $paymentIntent = PaymentIntent::create([
                'amount' => $this->calculateOrderAmount($orderModel),
                'currency' => 'usd',
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            return [
                'clientSecret' => $paymentIntent->client_secret,
            ];

        } catch (\Throwable $exception) {
            $test = $exception->getMessage();
            throw new PaymentIntentException($exception->getMessage());
        }
    }

    public function webhookHandler(Request $request)
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
                /** @var Customer $newCustomer */
                $newCustomer = $event->data->object;

                $newCustomerDB = (new \App\Entity\Customer())->setStripeId($newCustomer->id);
                $this->em->persist($newCustomerDB);
                $this->em->flush();
            } break;
            default:
                // Unexpected event type
                //error_log('Received unknown event type');
        }

    }
    private function handlePaymentIntentSucceeded(PaymentIntent $paymentIntent)
    {
        //TODO
    }

    private function handlePaymentMethodAttached(PaymentIntent $paymentIntent)
    {
        //TODO
    }
    private function handlePaymentIntentCreated(PaymentIntent $paymentIntent)
    {
        //TODO
    }

    private function calculateOrderAmount(OrderModel $orderModel): int
    {
        $items = $orderModel->getItems();
        $amount = 0;
        $stripeClient = new StripeClient($this->privateKey);

        foreach ($items as $item) {
            $default_price = $stripeClient->products->retrieve($item->getId())->default_price;
            $price = $stripeClient->prices->retrieve($default_price)->unit_amount_decimal;
            $amount += $price;
        }

        return $amount;
    }
}