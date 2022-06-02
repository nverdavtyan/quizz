<?php

namespace App\Security;
use App\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Doctrine\ORM\EntityManagerInterface;
class UsersAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager, EntityManagerInterface $entityManager)
    {
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->entityManager = $entityManager;
    }
    
 
    public function authenticate(Request $request): Passport
    {
        $user = $this->entityManager->getRepository(User::class)->findOneByEmail($request->get('email'));
        if (!$user) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Aucun compte associé à l’adresse électronique.');
        }
       $token = $user->getToken();
       $enabled = $user->getIsVerified();
         
       if ($token !== null || !$enabled) {
        throw new CustomUserMessageAuthenticationException('Merci de valider votre adresse email !');
    }
        $email = $request->request->get('email', '');
        $request->getSession()->set(Security::LAST_USERNAME, $email);

        if (!$email) {
            throw new CustomUserMessageAuthenticationException('fuck');
        }
        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {

        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        // For example:
       return new RedirectResponse($this->urlGenerator->generate('loginForm'));
    }
  

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}