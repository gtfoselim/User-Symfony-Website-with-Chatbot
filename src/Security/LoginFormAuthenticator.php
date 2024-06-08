<?php

namespace App\Security;

use App\Entity\Medecin;
use App\Repository\MedecinRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception;
use App\Exception\UserBlockedException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;
    private $MedecinRepository;
    public const LOGIN_ROUTE = 'app_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator, MedecinRepository $MedecinRepository)
    {
        $this->MedecinRepository = $MedecinRepository;
    }
    
    

   
    public function authenticate(Request $request): Passport  
    {
        $email = $request->request->get('email', '');
        $user = $this->MedecinRepository->findOneBy(['email' => $email]);

        if ($user && $user->getToken() == 0) {
            // If the user is blocked, deny authentication
            throw new UserBlockedException('User is blocked.');
        }

        $request->getSession()->set(Security::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }
            $user=$token->getUser();
            if ($user instanceof Medecin && $user->getToken() == 0) {
                // If the user is blocked, redirect to a blocked page or show an error message
                return new RedirectResponse($this->urlGenerator->generate('signup'));
                // Alternatively, you can display an error message and render a template
                //return $this->render('blocked.html.twig', ['message' => 'Your account has been blocked.']);
            }
           else if(in_array('admin', $user->getRoles(), true)){
                return new RedirectResponse($this->urlGenerator->generate('showdbuser'));
                 }
                 if(in_array('medecin', $user->getRoles(), true)){
                    return new RedirectResponse($this->urlGenerator->generate('pagehome'));
                     }
        // For example:
         return new RedirectResponse($this->urlGenerator->generate('pagehome'));
        //throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
        //return $this->redirectToRoute('profile');
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }


}


namespace App\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class UserBlockedException extends AuthenticationException
{
    // Optionally, you can define custom logic or properties specific to your application's needs
    private $messageKey = 'Your account has been blocked.';
    
    public function getMessageKey()
    {
        return $this->messageKey;
    }
}