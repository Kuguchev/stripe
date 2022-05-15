<?php

namespace App\Exception;
use RuntimeException;

class PaymentIntentException extends RuntimeException
{
   public function __construct($message = "")
   {
       parent::__construct($message);
   }
}