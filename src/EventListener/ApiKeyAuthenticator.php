<?php

namespace App\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\KernelInterface;

class ApiKeyAuthenticator
{
    private $kernel;

    public function __construct(private EntityManagerInterface $em, KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if ($this->kernel->getEnvironment() === 'test') {
            return;
        }

        $request = $event->getRequest();

        if (!$request->attributes->get('_secure_api')) {
            return;
        }

        $apiKey = $request->headers->get('X-API-KEY');

        if (!$apiKey) {
            throw new UnauthorizedHttpException('No API key provided');
        }

        $apiKeyRecord = $this->em->getRepository(\App\Entity\ApiKey::class)->findOneBy(['secretKey' => $apiKey]);

        if (!$apiKeyRecord) {
            throw new UnauthorizedHttpException('Invalid API key');
        }

        $request->attributes->set('clientName', $apiKeyRecord->getClientName());
    }
}
