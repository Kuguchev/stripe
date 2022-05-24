<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Form\CustomerType;

use App\Service\Stripe\StripeCustomerService;
use App\Service\Stripe\StripePriceService;
use App\Service\Stripe\StripeProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CustomerController extends AbstractController
{
    private StripeCustomerService $customerService;
    private StripePriceService $stripePriceService;
    private StripeProductService $stripeProductService;
    
    public function __construct(
        StripeCustomerService $customerService,
        StripePriceService $stripePriceService,
        StripeProductService $stripeProductService
    ) {
        $this->customerService = $customerService;
        $this->stripePriceService = $stripePriceService;
        $this->stripeProductService = $stripeProductService;
    }

    /**
     * @Route("/create-customer", name="app.create.customer", methods={"GET", "POST"})
     */
    public function create(Request $request): Response
    {
        $customer = new Customer();
        $form = $this->createForm(CustomerType::class, $customer);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            
            $customer = $this->customerService->createCustomer($email);
            $product = $this->stripeProductService->createProduct('Basic', 'test-subscription', 444, 'usd');
            $price = $this->stripePriceService->createPrice($product->getStripeId(), 333, 'usd', ['interval' => 'month']);
            
            return $this->render('subscription/subscriptionNew.html.twig', [
                'customerId' => $customer->getStripeId(),
                'priceId' => $price->getStripeId(),
            ]);
        }
        
        return $this->render('customer/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
