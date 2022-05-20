<?php

namespace App\Model;

use App\Entity\Product;

class OrderModel
{
    /**
     * @var OrderElement[]
     */
    private array $items;

    /**
     * @return OrderElement[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param OrderElement[] $items
     * @return OrderModel
     */
    public function setItems(array $items): self
    {
        $this->items = $items;

        return $this;
    }
}