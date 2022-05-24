<?php

namespace App\Model;

class SubscriberModel
{
    private string $priceId;
    private string $customerId;
    
    public function getPriceId(): string
    {
        return $this->priceId;
    }
    
    public function setPriceId(string $priceId): self
    {
        $this->priceId = $priceId;
        
        return $this;
    }
    
    public function getCustomerId(): string
    {
        return $this->customerId;
    }
    
    public function setCustomerId(string $customerId): self
    {
        $this->customerId = $customerId;
        
        return $this;
    }
}
