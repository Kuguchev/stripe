<?php

namespace App\Model;

class SubscriptionModel
{
    private string $subscriptionId;
    
    public function getSubscriptionId(): string
    {
        return $this->subscriptionId;
    }
    
    public function setSubscriptionId(string $subscriptionId): self
    {
        $this->subscriptionId = $subscriptionId;
        
        return $this;
    }
}