<?php

namespace App\Security;


use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class ApiKeyAuthentificator extends AbstractGuardAuthenticator
{

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new JsonResponse(['error' => 'Authentication Required'], Response::HTTP_UNAUTHORIZED);
    }

    public function supports(Request $request)
    {
        $auth = $request->headers->get('Authorization');

        return $auth && str_starts_with($auth, 'Bearer');
    }

    public function getCredentials(Request $request)
    {
       $token = str_replace('Bearer ', '', $request->headers->get('Authorization'));
         return [
              'apiKey' => $token
         ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        return $userProvider->loadUserByUsername($credentials['apiKey']);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return $user instanceof User && $user->getApiKey() === $credentials['apiKey'] && $user->getIsVerify();
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse(['error' => 'ApiKey invalid'], Response::HTTP_FORBIDDEN);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        return null;
    }

    public function supportsRememberMe()
    {
        return false;
    }
}