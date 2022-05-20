<?php

namespace App\Service\Stripe;

use Stripe\Invoice;
use Stripe\InvoiceItem;

class StripeInvoiceService
{
    private StripeCustomerService $customerService;

    public function __construct(StripeCustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    public function createInvoiceItem(
        string $customerEmail,
        int $amount,
        string $currency,
        string $description,
        int $quantity
    ): InvoiceItem {
        // Create customer
        $customer = $this->customerService->createCustomer($customerEmail);

        return InvoiceItem::create([
            'customer' => $customer->getStripeId(),
            'unit_amount' => $amount,
            'currency' => $currency,
            'description' => $description,
            'quantity' => $quantity,
        ]);
    }

    public function createInvoice(string $customerEmail): Invoice
    {
        // Create customer
        $customer = $this->customerService->createCustomer($customerEmail);

        $invoice = Invoice::create([
            'customer' => $customer->getStripeId(),
            'collection_method' => 'send_invoice',
            'days_until_due' => 14,
        ]);

        return $invoice;
    }

    // Requests made in test-mode result in no emails being sent, despite sending an invoice.sent event.
    public function sendInvoice(Invoice $invoice)
    {
        $invoice->sendInvoice();
    }
}
