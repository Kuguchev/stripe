<?php

namespace App\Service\Stripe;

use App\Model\OrderModel;
use Stripe\PaymentIntent;
use Stripe\Price;
use Stripe\Product;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

class StripeOnlinePaymentService extends AbstractStripeService
{
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
        parent::__construct();
    }

    public function createPaymentIntent(Request $request): PaymentIntent
    {
        /** @var OrderModel $orderModel */
        $orderModel = $this->serializer->deserialize($request->getContent(), OrderModel::class, 'json');

        return PaymentIntent::create([
            'amount' => $this->calculateOrderAmount($orderModel),
            'currency' => 'usd',
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
        ]);
    }

    private function calculateOrderAmount(OrderModel $orderModel): int
    {
        $items = $orderModel->getItems();
        $amount = 0;
        
        foreach ($items as $item) {
            $defaultPriceId = Product::retrieve($item->getId())->default_price;
            $amount += Price::retrieve($defaultPriceId)->unit_amount;
        }
        
        return $amount;
    }
}