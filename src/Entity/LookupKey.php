<?php

namespace App\Entity;

class LookupKey
{
    private $lookupKey;

    public function getLookupKey()
    {
        return $this->lookupKey;
    }

    public function setLookupKey($lookupKey): self
    {
        $this->lookupKey = $lookupKey;

        return $this;
    }
}