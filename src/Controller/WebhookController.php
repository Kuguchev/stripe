<?php

namespace App\Controller;

use App\Service\Stripe\StripeWebHookService;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class WebhookController extends AbstractController
{
    private StripeWebHookService $stripeWebHookService;
    
    public function __construct(StripeWebHookService $stripeWebHookService)
    {
        $this->stripeWebHookService = $stripeWebHookService;
    }

    /**
     * @Route("/webhook", name="app.payment.webhook", methods={"POST"})
     */
    public function hook(Request $request, SerializerInterface $serializer): Response
    {
        $payload = $request->getContent();

        try {
            $event = $serializer->deserialize($payload, Event::class, 'json');
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

        $this->stripeWebHookService->webhookHandler($event);

        return (new Response())->setStatusCode(Response::HTTP_OK);
    }
}
