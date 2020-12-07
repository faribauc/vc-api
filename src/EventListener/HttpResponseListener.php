<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * Class ResponseListener
 * @package App\EventListener
 */
class HttpResponseListener
{
    /**
     * @param ResponseEvent $event
     * @return Response
     */
    public function onKernelResponse(ResponseEvent $event)
    {
        $event->getResponse()->headers->add(
            [
                'X-Day' => (new \DateTime())->format('l'),
            ]
        );

        return $event->getResponse();
    }
}
