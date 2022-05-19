<?php

namespace App\Controller;

use App\Entity\LookupKey;
use App\Form\LookupKeyType;
use App\Repository\ProductRepository;
use App\Service\StripeInvoiceService;
use App\Service\StripePriceService;
use App\Service\StripeProductService;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Error;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\StripeService;
use Symfony\Component\Serializer\SerializerInterface;

class PaymentController extends AbstractController
{

    private StripeService $stripeService;
    private ProductRepository $productRepository;
    private EntityManagerInterface $em;
    private StripeProductService $productService;
    private StripePriceService $priceService;
    private StripeInvoiceService $invoiceService;

    public function __construct(
        StripeService $stripeService,
        ProductRepository $productRepository,
        StripeProductService $productService,
        StripePriceService $priceService,
        StripeInvoiceService $invoiceService,
        EntityManagerInterface $em)
    {
        $this->stripeService = $stripeService;
        $this->productRepository = $productRepository;
        $this->productService = $productService;
        $this->priceService = $priceService;
        $this->invoiceService = $invoiceService;
        $this->em = $em;

    }

    /**
     * @Route("/checkout", name="app.payment.checkout", methods={"POST", "GET"})
     */
    public function intent(Request $request): Response
    {
        if ($request->getMethod() === "POST") {
            try {
                return $this->json($this->stripeService->paymentIntent($request));
            } catch (Error $e) {
                return $this->json(['error' => $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR]);
            }
        }

        // FOR DEBUG
//        $stripe = new StripeClient($_ENV['STRIPE_SECRET_KEY_TEST']);
//
//        $products = $stripe->products->all()->toArray();
//
//        foreach ($products['data'] as $product) {
//            $addProduct = (new Product())
//            ->setStripeId($product['id'])
//            ->setName($product['name'])
//            ->setDescription($product['description']);
//
//            $this->em->persist($addProduct);
//        }
//        $this->em->flush();

        $products = $this->productRepository->findAll();

        return $this->render('stripe/checkout.html.twig', [
            'products' => $products,
        ]);
    }

    /**
     * @Route("/webhook", name="app.payment.webhook", methods={"POST"})
     */
    public function hook(Request $request, SerializerInterface $serializer): Response
    {
        $payload = $request->getContent();
        $event = null;

        try {
            $event = $serializer->deserialize($payload, Event::class,'json');
        } catch (\UnexpectedValueException $exception) {
            return (new Response())
                ->setContent($exception->getMessage())
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        if ($_ENV['ENDPOINT_SECRET_KEY_CLI']) {
            $sigHeader = $request->server->get('HTTP_STRIPE_SIGNATURE');

            try {
                $event = Webhook::constructEvent($payload, $sigHeader, $_ENV['ENDPOINT_SECRET_KEY_CLI']);
            } catch (SignatureVerificationException $exception) {
                return (new Response())
                    ->setContent($exception->getMessage())
                    ->setStatusCode(Response::HTTP_BAD_REQUEST);
            }
        }

        $this->stripeService->webhookHandler($event);

        return (new Response())->setStatusCode(Response::HTTP_OK);
    }

    /**
     * @Route("/send-invoice", name="app.payment.invoice", methods={"GET"})
     */
    public function invoice(): Response
    {
        // up to 250 items per invoice
        for($i = 0; $i < 10; $i++) {
            $this->invoiceService->createInvoiceItem('kuguchev.dm@gmail.com', 1000 * ($i + 1), 'usd', 'Item number' . ($i + 1), random_int(1, 3));
        }
        $invoice = $this->invoiceService->createInvoice('kuguchev.dm@gmail.com');

        // up to 250 items per invoice
        for($i = 0; $i < 10; $i++) {
            $this->invoiceService->createInvoiceItem('kuguchev.dm@gmail.com', 1000 * ($i + 1), 'usd', 'Item number' . ($i + 1), random_int(1, 3));
        }
        $invoice = $this->invoiceService->createInvoice('kuguchev.dm@gmail.com');

        $this->invoiceService->sendInvoice($invoice);



        return new Response();
    }

    /**
     * @Route("/create-checkout-session", methods={"POST", "GET"})
     */
    public function checkout(Request $request): Response
    {
        // Create price model
        $model = $this->stripeService->createPriceModel();

        $lookupKey = (new LookupKey())
            ->setLookupKey($model['lookup_key']);
        $form = $this->createForm(LookupKeyType::class, $lookupKey);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            try {
                $lookupKey = $form->get('lookupKey')->getData();
                $stripeCheckOutSession = $this->stripeService->checkOutSession($lookupKey);

                return $this->redirect($stripeCheckOutSession->url, 303);
            } catch(Error $e) {
                return $this->json(['error' => $e->getMessage()], 500);
            }
        }

        return $this->render('stripe/subscribe.html.twig', [
            'form' => $form->createView(),
            'price' => $model['price'],
            'productName' => $model['productName'],
        ]);
    }

    /**
     * @Route("/success", methods={"GET"})
     */
    public function success(Request $request): Response
    {
        return $this->render('stripe/success.html.twig');
    }

    /**
     * @Route("/cancel", methods={"GET"})
     */
    public function cancel(Request $request): Response
    {
        return $this->render('stripe/cancel.html.twig');
    }

    /**
     * @Route("/create-portal-session", methods={"POST"})
     */
    public function portalSession(Request $request): Response
    {
        try {
            $billingPortalSession = $this->stripeService->createPortalSession($request);
            return $this->redirect($billingPortalSession->url, 303);
        } catch (Error $e) {
            return $this->json(['error' => $e->getMessage()],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}