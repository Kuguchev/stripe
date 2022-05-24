<?php

namespace App\Model;

class UpdateSubscriptionModel
{
    private string $subscriptionId;
    private string $priceId;
    
    public function getSubscriptionId(): string
    {
        return $this->subscriptionId;
    }
    
    public function setSubscriptionId(string $subscriptionId): self
    {
        $this->subscriptionId = $subscriptionId;
        
        return $this;
    }
    
    public function getPriceId(): string
    {
        return $this->priceId;
    }
    
    public function setPriceId(string $priceId): self
    {
        $this->priceId = $priceId;
        
        return $this;
    }
}