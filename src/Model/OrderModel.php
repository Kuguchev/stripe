<?php

namespace App\Model;

class OrderModel
{
    /**
     * @var ProductModel[]
     */
    private array $items;

    /**
     * @return ProductModel[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param ProductModel[] $items
     * @return OrderModel
     */
    public function setItems(array $items): self
    {
        $this->items = $items;

        return $this;
    }
}