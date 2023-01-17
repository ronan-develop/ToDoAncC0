<?php

declare(strict_types=1);

namespace App\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', array(
            'last_username' => $lastUsername,
            'error' => $error,
            )
        );
    }

    /**
     * @codeCoverageIgnore
     */
    #[Route('/login_check', name: 'login_check')]
    public function loginCheck(): void
    {
        // This code is never executed.
    }

    /**
     * @codeCoverageIgnore
     * @throws Exception
     */
    #[Route('/logout', name: 'logout', methods: ['GET'])]
    public function logoutCheck(): void
    {
        throw new Exception('Don\'t forget to activate logout in security.yaml');
    }
}