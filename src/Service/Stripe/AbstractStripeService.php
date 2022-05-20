<?php

namespace App\Service\Stripe;

use Stripe\Stripe;

abstract class AbstractStripeService
{
    private $privateKey;
    protected string $domain;

    public function __construct()
    {
        if ($_ENV['APP_ENV'] === 'dev') {
            $this->privateKey = $_ENV['STRIPE_SECRET_KEY_TEST'];
            $this->domain = $_ENV['TEST_DOMAIN'];
        } else {
            $this->privateKey = $_ENV['STRIPE_SECRET_KEY_LIVE'];
            $this->domain = $_ENV['LIVE_DOMAIN'];
        }
        Stripe::setApiKey($this->privateKey);
    }
}
