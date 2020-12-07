<?php

namespace App\Tests\Functional\EventListener;

use App\EventListener\HttpResponseListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\EventListener\ResponseListener;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class HttpResponseListenerTest
 * @package App\Tests\Unit\EventListener
 */
class HttpResponseListenerTest extends TestCase
{
    private $dispatcher;
    private $kernel;

    protected function setUp(): void
    {
        $this->dispatcher = new EventDispatcher();
        $listener = new ResponseListener('UTF-8');
        $this->dispatcher->addListener(KernelEvents::RESPONSE, [$listener, 'onKernelResponse']);

        $this->kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\HttpKernelInterface')->getMock();
    }

    public function testOnKernelResponse()
    {
        $response = new Response('test');
        $event = new ResponseEvent($this->kernel, new Request(), HttpKernelInterface::MASTER_REQUEST, $response);
        $event->setResponse($response);
        $newResponse = (new HttpResponseListener())->onKernelResponse($event);
        $this->dispatcher->dispatch($event, KernelEvents::RESPONSE);

        $this->assertTrue($newResponse->headers->has('X-Day'));
    }
}
