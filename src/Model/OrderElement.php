<?php

namespace App\Model;

class OrderElement
{
    private string $productId;
    
    public function getId(): string
    {
        return $this->id;
    }
    
    public function setId(string $id): self
    {
        $this->id = $id;
    
        return $this;
    }
}