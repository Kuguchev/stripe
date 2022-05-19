<?php

namespace App\Service;

use App\Model\OrderModel;
use App\Repository\CustomerRepository;
use App\Repository\PriceRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\BillingPortal\Session as BillingPortalSession;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Event;
use Stripe\PaymentIntent;
use Stripe\Price;
use Stripe\Product;
use Stripe\Stripe;
use Stripe\StripeClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class StripeService
{
    private $privateKey;
    private SerializerInterface $serializer;
    private EntityManagerInterface $em;
    private CustomerRepository $customerRepository;
    private PriceRepository $priceRepository;
    private ProductRepository $productRepository;
    private string $domain;

    public function __construct(
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        CustomerRepository $customerRepository,
        PriceRepository $priceRepository,
        ProductRepository $productRepository)
    {
        $this->serializer = $serializer;
        $this->em = $em;
        $this->customerRepository = $customerRepository;
        $this->priceRepository = $priceRepository;
        $this->productRepository = $productRepository;

        if ($_ENV['APP_ENV'] === 'dev') {
            $this->privateKey = $_ENV['STRIPE_SECRET_KEY_TEST'];
            $this->domain = $_ENV['TEST_DOMAIN'];
        } else {
            $this->privateKey = $_ENV['STRIPE_SECRET_KEY_LIVE'];
            $this->domain = $_ENV['LIVE_DOMAIN'];
        }
        Stripe::setApiKey($this->privateKey);
    }

    public function paymentIntent(Request $request): array
    {
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
    }

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

    private function handleSubscriptionUpdated($subscription)
    {
        //TODO
    }

    private function handleSubscriptionDeleted($subscription)
    {
        //TODO
    }

    private function handleSubscriptionCreated($subscription)
    {
        //TODO
    }

    private function handleTrialWillEnd($subscription)
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

    public function createPriceModel(): array
    {
        $product = Product::create([
            'name' => 'Basic Dashboard' . random_int(1, 1000),
        ]);

        $price = Price::create([
            'product' => $product->id,
            'unit_amount' => 2000,
            'currency' => 'usd',
            'recurring' => [
                'interval' => 'month'
            ],
            'lookup_key' => 'my_lookup_key' . random_int(1, 1000),
        ]);

        return [
            'productName' => $product->name,
            'lookup_key' => $price->lookup_key,
            'price' => $price->unit_amount_decimal,
        ];
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
}