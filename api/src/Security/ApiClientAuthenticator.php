<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class ApiClientAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly UserProviderInterface $userProvider,
    ) {
    }
    
    public function supports(Request $request): bool
    {
        return $request->headers->has('Authorization');
    }

    public function authenticate(Request $request): Passport
    {
        $auth = $request->headers->get('Authorization');
        $parts = explode(':', $auth, 2);
        if (2 !== count($parts)) {
            throw new CustomUserMessageAuthenticationException('Invalid authorization');
        }

        $userIdentifier = $parts[1];

        try {
            $this->userProvider->loadUserByIdentifier($userIdentifier);
        } catch (UserNotFoundException) {
            throw new CustomUserMessageAuthenticationException('Invalid authorization');
        }

        return new SelfValidatingPassport(new UserBadge($userIdentifier));       
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return $user->getPassword() === $credentials[1];
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }
}
