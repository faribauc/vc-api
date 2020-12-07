<?php

namespace App\Tests\Unit\Security;

use App\Security\TokenAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class TokenAuthenticatorTest extends TestCase
{
    private $entityManager;
    private $userProvider;
    private $tokenAuthenticator;

    protected function setUp(): void
    {
        $this->entityManager = $this->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->userProvider = $this->getMockBuilder(UserProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->tokenAuthenticator = new TokenAuthenticator($this->entityManager);
    }

    public function testTokenAuthenticatorGetCredentials()
    {
        $this->assertEquals(
            [
                'token' => null,
            ],
            $this->tokenAuthenticator->getCredentials(new Request())
        );
    }

    public function testTokenAuthenticatorGetUser()
    {
        $user = $this->tokenAuthenticator->getUser(null, $this->userProvider);
        $this->assertEquals(null, $user);
    }

    public function testOnAuthenticationFailure()
    {
        $exception = new AuthenticationException();
        $response = $this->tokenAuthenticator->onAuthenticationFailure(new Request(), $exception);
        $this->assertEquals(
            [
                'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),
            ],
            json_decode($response->getContent(), true)
        );
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testSupportsRememberMe()
    {
        $this->assertFalse($this->tokenAuthenticator->supportsRememberMe());
    }
}
