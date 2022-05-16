<?php

namespace App\Model;

class ProductModel
{
    private string $id;

    private string $price;

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