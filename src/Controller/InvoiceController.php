<?php

namespace App\Controller;

use App\Service\Stripe\StripeInvoiceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InvoiceController extends AbstractController
{
    private StripeInvoiceService $stripeInvoiceService;
    
    public function __construct(StripeInvoiceService $stripeInvoiceService)
    {
        $this->stripeInvoiceService = $stripeInvoiceService;
    }

    /**
     * @Route("/send-invoice", name="app.payment.invoice", methods={"GET"})
     */
    public function invoice(): Response
    {
        // up to 250 items per invoice
        for ($i = 0; $i < 10; $i++) {
            $this->stripeInvoiceService->createInvoiceItem('kuguchev.dm@gmail.com', 1000 * ($i + 1), 'usd', 'Item number' . ($i + 1), random_int(1, 3));
        }
        // create invoice
        $invoice = $this->stripeInvoiceService->createInvoice('kuguchev.dm@gmail.com');
       
        $this->stripeInvoiceService->sendInvoice($invoice);
        
        return new Response();
    }
}
