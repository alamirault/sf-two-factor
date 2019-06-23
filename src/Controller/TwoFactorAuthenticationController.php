<?php

namespace App\Controller;

use App\Entity\Customer;
use App\EventSubscriber\TwoFactorAuthenticationSubscriber;
use App\Form\GoogleAuthenticatorType;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Doctrine\ORM\EntityManagerInterface;
use PragmaRX\Google2FA\Google2FA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;
use Symfony\Component\Translation\TranslatorInterface;

class TwoFactorAuthenticationController extends AbstractController
{
    /**
     * @Route("/two-factor", name="two-factor")
     * @param Request $request
     * @param TokenStorageInterface $tokenStorage
     * @param SessionInterface $session
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException
     * @throws \PragmaRX\Google2FA\Exceptions\InvalidCharactersException
     * @throws \PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException
     */
    public function twoFactorAction(Request $request, TokenStorageInterface $tokenStorage, SessionInterface $session,
                                    EntityManagerInterface $entityManager)
    {
        $form = $this->createForm(GoogleAuthenticatorType::class);

        $form->handleRequest($request);
        $google2fa = new Google2FA();

        $svg = null;

        /** @var Customer $customer */
        $customer = $this->getUser();
        if (!$customer->getGoogleAuthenticatorSecret()) {
            if ($session->get('2fa_secret')) {
                $secret = $session->get('2fa_secret');
            } else {
                $secret = $google2fa->generateSecretKey();
                $request->getSession()->set('2fa_secret', $secret);
            }

            $svg = $this->generateSvgForUser($google2fa, $customer, $secret);
        } else {
            $secret = $customer->getGoogleAuthenticatorSecret();
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $code = $form->getData()["code"];
            $codeIsValid = $google2fa->verifyKey($secret, $code, 4);
            if ($codeIsValid) {
                if (!$customer->getGoogleAuthenticatorSecret()) {
                    $customer->setGoogleAuthenticatorSecret($secret);
                    $entityManager->persist($customer);
                    $entityManager->flush();
                }

                $this->addRoleTwoFA($tokenStorage, $session);

                return $this->redirectToRoute("dashboard");
            }
            $this->addFlash("error", "Invalid verification code");
        }

        return $this->render("security/two-factor.html.twig", [
            "svg" => $svg,
            "form" => $form->createView(),
        ]);
    }

    private function generateSvgForUser(Google2FA $google2FA, Customer $customer, string $secret): string
    {
        $g2faUrl = $google2FA->getQRCodeUrl(
            "My website",
            $customer->getLogin(),
            $secret
        );

        $writer = new Writer(
            new ImageRenderer(
                new RendererStyle(400),
                new SvgImageBackEnd() // can also user new ImagickImageBackEnd() in order to generate png
            )
        );

        return $writer->writeString($g2faUrl);
    }

    private function addRoleTwoFA(TokenStorageInterface $tokenStorage, SessionInterface $session): void
    {
        /** @var PostAuthenticationGuardToken $currentToken */
        $currentToken = $tokenStorage->getToken();
        $roles = array_merge($currentToken->getRoles(), [TwoFactorAuthenticationSubscriber::ROLE_2FA_SUCCEED]);
        $newToken = new PostAuthenticationGuardToken($currentToken->getUser(), $currentToken->getProviderKey(), $roles);
        $tokenStorage->setToken($newToken);
        $session->set('_security_' . $currentToken->getProviderKey(), serialize($newToken));
    }
}